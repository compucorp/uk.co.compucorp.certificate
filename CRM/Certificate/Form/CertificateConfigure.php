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

    $this->setTitle($titlePrefix . ' Certificate Configuration');
    $url = CRM_Utils_System::url('civicrm/admin/certificates', 'reset=1');
    $session = CRM_Core_Session::singleton();
    $session->replaceUserContext($url);
  }

  public function buildQuickForm() {
    $this->add(
      'text',
      'name',
      ts('Certificate Name'),
      [
        'placeholder' => ts('Certificate Name'),
        'class' => 'form-control',
      ],
      TRUE
    );

    $this->add(
      'select',
      'type',
      ts('Type'),
      CRM_Certificate_Enum_CertificateType::getOptions(),
      TRUE,
      ['class' => 'form-control']
    );

    $this->add(
      'text',
      'linked_to',
      ts('Linked to'),
      [
        'placeholder' => E::ts('- Select Type -'),
        1 => 'disabled',
        'class' => 'form-control',
      ],
      FALSE
    );

    $this->addEntityRef('message_template_id', ts('Message Template'), [
      'entity' => 'MessageTemplate',
      'placeholder' => ts('- Message Template -'),
      'select' => ['minimumInputLength' => 0],
      'api' => [
        'params' => [
          "is_active" => 1,
          "workflow_id" => ["IS NULL" => 1],
        ],
        'label_field' => "msg_title",
        "search_field" => "msg_title",
      ],
      'class' => 'form-control',
    ], TRUE);

    $this->add(
      'text',
      'statuses',
      ts('Status'),
      [
        'placeholder' => E::ts('- Select Status -'),
        1 => 'disabled',
        'class' => 'form-control',
      ],
      FALSE
    );

    $this->addButtons([
      [
        'type' => 'submit',
        'name' => E::ts('Save'),
        'isDefault' => TRUE,
      ],
      [
        'type' => 'cancel',
        'name' => E::ts('Cancel'),
        'class' => 'btn-secondary-outline',
      ],
    ]);

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
    $elementNames = [];
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

    CRM_Core_Session::setStatus($msg, 'success', 'success');
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

      $values['statuses'] = empty($values['statuses']) ? [] : explode(',', $values['statuses']);
      $values['linked_to'] = empty($values['linked_to']) ? [] : explode(',', $values['linked_to']);

      $result = $certificateCreator->store($values);
    }
    catch (CRM_Certificate_Exception_ConfigurationExistException $e) {
      CRM_Core_Session::setStatus($e->getMessage(), 'failed', 'error');
      return;
    }

    return $result;
  }

  private function getConfiguredCertificateById($id) {
    $values = [];

    try {
      $certificateDAO = CRM_Certificate_BAO_CompuCertificate::findById($id);
    }
    catch (\Exception $e) {
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

  public function addRules() {
    $this->addFormRule([$this, 'certificateRule']);
  }

  /**
   * This enforces the rule whereby,
   * linked_to and status field are only
   * required for certificate of type 'case'
   * but not required for other types as blank/empty
   * translates to all.
   *
   * @param array $values
   *
   * @return array|bool
   */
  public function certificateRule($values) {
    $errors = [];

    // only validate statuses and linked_to if the certificate is attached to cases.
    if ($values['type'] != CRM_Certificate_Enum_CertificateType::CASES) {
      return $errors;
    }

    $this->validateCertificateFields($values, $errors);

    return $errors ?: TRUE;
  }

  /**
   * Validates the statuses and linked_to field.
   *
   * @param array $values
   * @param array $errors
   */
  public function validateCertificateFields(&$values, &$errors) {
    if (empty($values['linked_to'])) {
      $errors['linked_to'] = ts('The linked to field is required');
    }

    if (empty($values['statuses'])) {
      $errors['statuses'] = ts('The status field is required');
    }
  }

}
