<?php

use CRM_Certificate_ExtensionUtil as E;

/**
 * CertificateConfigure Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Certificate_Form_CertificateConfigure extends CRM_Core_Form {

  public function preProcess() {
    $this->_id = CRM_Utils_Request::retrieve('id', 'Positive', $this);

    $titlePrefix = 'Add';
    if ($this->_id) {
      $titlePrefix = 'Update';
    }

    $this->setTitle($titlePrefix . ' Certificate');
  }

  public function buildQuickForm() {
    $this->add(
      'text',
      'name',
      'Certificate Name',
      NULL,
      TRUE
    );

    $this->add(
      'select',
      'type',
      ts('Type'),
      CRM_Certificate_Enum_CertificateType::getOptions(),
      TRUE
    );

    $this->add(
      'text',
      'linked_to',
      ts('Linked to'),
      ['placeholder' => E::ts('- select type -'), 'disabled'],
      TRUE
    );

    $this->addEntityRef('message_template_id', ts('Message Template'), [
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
      'statuses',
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

    $result = $this->saveConfiguration($values);

    if (empty($result)) {
      $msg = sprintf('Error %s certificate', !empty($this->_id) ? 'updating' : 'creating');
      CRM_Core_Session::setStatus($msg, 'failed', 'error');
      return;
    }

    $msg = sprintf('Certificate configuration %s successfully', !empty($this->_id) ? 'updated' : 'created');
    $url = CRM_Utils_System::url('civicrm/admin/certificates', 'reset=1');

    CRM_Core_Session::setStatus($msg, 'success', 'success');
    CRM_Utils_System::redirect($url);
  }

  public function setDefaultValues() {
    if (empty($this->_id)) {
      return [];
    }

    $configuredCertificate = $this->getConfiguredCertificateById($this->_id);

    return $configuredCertificate;
  }

  private function saveConfiguration($values) {
    try {
      $certificateCreator = new CRM_Certificate_Service_Certificate();
      if (!empty($this->_id)) {
        $values['id'] = $this->_id;
      }

      $values['statuses'] = explode(',', $values['statuses']);
      $values['linked_to'] = explode(',', $values['linked_to']);

      $result = $certificateCreator->store($values);
    } catch (CRM_Certificate_Exception_ConfigurationExistException $e) {
      CRM_Core_Session::setStatus($e->getMessage(), 'failed', 'error');
      return;
    }

    return $result;
  }

  private function getConfiguredCertificateById($id) {
    $values = [];

    try {
      $certificateDAO = CRM_Certificate_BAO_CompuCertificate::findById($id);
    } catch (\Exception $e) {
      CRM_Core_Session::setStatus("Certificate configuration with ID $this->_id not found", 'failed', 'error');
      CRM_Utils_System::redirect('civicrm/admin/certificates');
      return;
    }

    $entity = CRM_Certificate_Entity_EntityFactory::create($certificateDAO->entity);
    $statuses = $entity->getCertificateConfiguredStatuses($certificateDAO->id);
    $types = $entity->getCertificateConfiguredTypes($certificateDAO->id);

    $values['name'] = $certificateDAO->name;
    $values['type'] = $certificateDAO->entity;
    $values['message_template_id'] = $certificateDAO->template_id;
    $values['statuses'] = implode(',', array_column($statuses, 'id'));
    $values['linked_to'] = implode(',', array_column($types, 'id'));

    return $values;
  }
}
