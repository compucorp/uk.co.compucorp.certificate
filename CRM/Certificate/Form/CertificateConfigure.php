<?php

use CRM_Certificate_ExtensionUtil as E;

/**
 * CertificateConfigure Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Certificate_Form_CertificateConfigure extends CRM_Core_Form {
  public function preProcess() {
    $this->_id = $this->get('id');
    if (!$this->_id) {
      $this->_id = CRM_Utils_Request::retrieve('id', 'Positive', $this, FALSE, 0);
    }

    $titlePrefix = 'Add';
    if ($this->_id) {
      $titlePrefix = 'Update';
    }

    $this->setTitle($titlePrefix . ' Certificate');
  }

  public function buildQuickForm() {
    $this->add(
      'text',
      'certificate_name',
      'Certificate Name',
      NULL,
      TRUE
    );

    $this->add(
      'select',
      'certificate_type',
      ts('Type'),
      CRM_Certificate_Enum_CertificateType::getOptions(),
      TRUE
    );

    $this->add(
      'text',
      'certificate_linked_to',
      ts('Linked to'),
      ['placeholder' => E::ts('- select type -'), 'disabled'],
      TRUE
    );

    $this->addEntityRef('certificate_msg_template', ts('Message Template'), [
      'entity' => 'MessageTemplate',
      'placeholder' => ts('- Messgae Template -'),
      'select' => ['minimumInputLength' => 0],
      'api' => [
        'params' => [
          "is_active" => 1,
          "workflow_id" => ["IS NULL" => 1]
        ],
        'label_field' => "msg_title",
        "search_field" => "msg_title"
      ]
    ], true);

    $this->add(
      'text',
      'certificate_status',
      ts('Status'),
      ['placeholder' => E::ts('- select linked to -'), 'disabled'],
      TRUE
    );

    $this->addButtons(array(
      array(
        'type' => 'submit',
        'name' => E::ts('Submit'),
        'isDefault' => TRUE,
      ),
    ));

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    $this->assign('entityRefs', CRM_Certificate_Enum_CertificateType::getEnityRefs());
    $this->assign('entityStatusRefs', CRM_Certificate_Enum_CertificateType::getEntityStatusRefs());
    parent::buildQuickForm();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

  /**
   * Called after form has been successfully submitted
   */
  public function postProcess() {
    $values = $this->exportValues();

    try {
      $certificateCreator = new CRM_Certificate_Service_StoreCertificateConfiguration($values);
      $result = $certificateCreator->store();
    } catch (CRM_Certificate_Exception_ConfigurationExistException $e) {
      CRM_Core_Session::setStatus($e->getMessage(), 'failed', 'error');
      return;
    }

    if (empty($result)) {
      $msg = sprintf('Error %s certificate', 'creating');
      CRM_Core_Session::setStatus($msg, 'failed', 'error');
      return;
    }

    $msg = sprintf('Certificate configuration %s successfully', 'created');
    $url = CRM_Utils_System::url('civicrm/admin/certificates', 'reset=1');
    CRM_Core_Session::setStatus($msg, 'success', 'success');
    CRM_Utils_System::redirect($url);
  }
}
