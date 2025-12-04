<?php

use CRM_Certificate_ExtensionUtil as E;
use CRM_Certificate_Enum_DownloadType as DownloadType;
use CRM_Certificate_BAO_CompuCertificate as CompuCertificate;

/**
 * CertificateConfigure Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Certificate_Form_CertificateConfigure extends CRM_Core_Form {

  /**
   * @var int
   * Certificate ID
   */
  public $_id;

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
      TRUE
    );

    $this->add(
      'text',
      'participant_type_id',
      ts('Event Role'),
      [
        'placeholder' => E::ts('- Select Participant Type -'),
        1 => 'disabled',
        'class' => 'form-control',
      ],
      FALSE
    );

    $this->addEntityRef('event_type_ids', ts('Event Types'), [
      'entity' => 'OptionValue',
      'placeholder' => ts('- Select -'),
      'select' => ['multiple' => TRUE, 'minimumInputLength' => 0],
      'api' => [
        'params' => [
          'option_group_id' => 'event_type',
          'is_active' => 1,
        ],
      ],
      'class' => 'form-control',
    ], FALSE);

    $this->add(
      'select',
      'download_type',
      ts('Certificate Type'),
      CompuCertificate::getSupportedDownloadTypes(),
      TRUE,
      ['class' => 'form-control']
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
    ]);

    $safeExtensions = implode(', ', array_keys(CRM_Core_OptionGroup::values('safe_file_extension', TRUE)));
    $this->addElement('file', 'download_file', E::ts('File Upload'), 'size=30 maxlength=255 class=form-control accept="' . $safeExtensions . '"');
    $this->addUploadElement('download_file');

    $this->add(
      'text',
      'statuses',
      ts('Status'),
      [
        'placeholder' => E::ts('- Select Status -'),
        1 => 'disabled',
        'class' => 'form-control',
      ],
      TRUE
    );

    $this->add(
      'datepicker',
      'start_date',
      ts('Start Date'),
      NULL,
      FALSE,
      ['time' => FALSE]
    );

    $this->add(
      'datepicker',
      'end_date',
      ts('End Date'),
      NULL,
      FALSE,
      ['time' => FALSE]
    );

    $this->add(
      'select',
      'download_format',
      ts('Download Format'),
      CompuCertificate::getSupportedDownloadFormats(),
      ['class' => 'form-control']
    );

    $this->addEntityRef('relationship_types', ts('Access For Related Contacts'), [
      'entity' => 'RelationshipType',
      'placeholder' => ts('- Select Relationship -'),
      'select' => ['multiple' => TRUE],
      'class' => 'form-control',
    ], FALSE);

    $this->add(
      'datepicker',
      'min_valid_from_date',
      ts('Min Valid From Date'),
      NULL,
      FALSE,
      ['time' => FALSE, 'class' => 'form-control']
    );

    $this->add(
      'datepicker',
      'max_valid_through_date',
      ts('Max Valid Through Date'),
      NULL,
      FALSE,
      ['time' => FALSE, 'class' => 'form-control']
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

    $elementWithHelpTexts = ['relationship_types', 'min_valid_from_date', 'max_valid_through_date', 'download_type', 'event_type_ids'];

    $this->assign('help', $elementWithHelpTexts);
    $this->assign('previousFile', $this->getPreviousFileURL());
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
    $files = $this->getVar('_submitFiles');

    $result = $this->saveConfiguration(array_merge($values, $files));

    if (empty($result)) {
      $msg = sprintf('Error %s certificate', !empty($this->_id) ? 'updating' : 'creating');
      CRM_Core_Session::setStatus($msg, 'failed', 'error');
      return;
    }

    $createOrUpdate = !empty($this->_id) ? 'updated' : 'created';

    $msg = sprintf('Certificate configuration %s successfully', $createOrUpdate);

    CRM_Core_Session::setStatus($msg, 'Item ' . $createOrUpdate, 'success');
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
      $entity = CRM_Certificate_Entity_EntityFactory::create($values['type']);
      if (!empty($this->_id)) {
        $values['id'] = $this->_id;
      }

      $values['statuses'] = empty($values['statuses']) ? [] : explode(',', $values['statuses']);
      $values['linked_to'] = empty($values['linked_to']) ? [] : explode(',', $values['linked_to']);
      $values['relationship_types'] = empty($values['relationship_types']) ? [] : explode(',', $values['relationship_types']);
      $values['event_type_ids'] = empty($values['event_type_ids']) ? [] : explode(',', $values['event_type_ids']);

      $result = $entity->store($values);
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
    $values = $entity->getCertificateConfigurationById($certificateDAO->id);

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
   * @param array $files
   *
   * @return array|bool
   */
  public function certificateRule($values, $files) {
    $errors = [];

    $this->validateCertificateName($values, $errors);
    $this->validateLinkedToField($values, $errors);
    $this->validateStatusesField($values, $errors);
    $this->validateDateFields($values, $errors);
    $this->validateDownloadType($values, $files, $errors);

    // The participant_type field should only be validated for Event Certificate.
    if ($values['type'] == CRM_Certificate_Enum_CertificateType::EVENTS) {
      $this->validateParticipantTypeField($values, $errors);
      $this->validateEventCertificateFilters($values, $errors);
    }

    return $errors ?: TRUE;
  }

  /**
   * Validates the download type related fields.
   *
   * @param array $values
   * @param array $files
   * @param array $errors
   */
  public function validateDownloadType($values, $files, &$errors) {
    if ($values['download_type'] == DownloadType::TEMPLATE) {
      if (empty($values['download_format'])) {
        $errors['download_format'] = ts('The download format field is required');
      }
      if (empty($values['message_template_id'])) {
        $errors['message_template_id'] = ts('The message template field is required');
      }
    }
    else {
      $ext = CRM_Utils_File::getAcceptableExtensionsForMimeType($files['download_file']['type'])[0] ?? NULL;
      if (!empty($files['download_file']['tmp_name']) &&
        !CRM_Utils_File::isExtensionSafe($ext)
          ) {
        $errors['download_file'] = ts('Invalid file format');
      }
      elseif (empty($files['download_file']['tmp_name']) && !empty($this->_id) && !empty(CompuCertificate::getFile($this->_id))) {
        // It's an update and already linked to a file.
        return;
      }
      elseif (empty($files['download_file']['tmp_name'])) {
        $errors['download_file'] = ts('The download file field is required');
      }
    }
  }

  /**
   * Validates the statuses field.
   *
   * @param array $values
   * @param array $errors
   */
  public function validateStatusesField(&$values, &$errors) {
    if (empty($values['statuses'])) {
      $errors['statuses'] = ts('The status field is required');
    }
  }

  /**
   * Validates the linked_to field.
   *
   * @param array $values
   * @param array $errors
   */
  public function validateLinkedToField(&$values, &$errors) {
    if ($values['type'] == CRM_Certificate_Enum_CertificateType::EVENTS) {
      return;
    }

    if (empty($values['linked_to'])) {
      $errors['linked_to'] = ts('The "linked to" field is required');
    }
  }

  /**
   * Validates certificate name is unique.
   *
   * @param array $values
   * @param array $errors
   */
  public function validateCertificateName($values, &$errors) {
    $certificateService = new CRM_Certificate_Service_Certificate();
    if ($certificateService->certificateNameExist($values['name'], $this->_id)) {
      $errors['name'] = ts('The certificate name already exists');
    }
  }

  /**
   * Validates participant type field.
   *
   * @param array $values
   * @param array $errors
   */
  public function validateParticipantTypeField($values, &$errors) {
    if (empty($values['participant_type_id'])) {
      $errors['participant_type_id'] = ts('The "Event role" field is required');
    }
  }

  /**
   * Validates that either event type or event is selected for event certificates.
   *
   * @param array $values
   * @param array $errors
   */
  public function validateEventCertificateFilters($values, &$errors) {
    $hasEventType = !empty($values['event_type_ids']);
    $hasEvent = !empty($values['linked_to']);

    if ($hasEventType || $hasEvent) {
      return;
    }

    $message = ts('Select at least one Event Type or Specific Event.');
    $errors['event_type_ids'] = $message;
    $errors['linked_to'] = $message;
  }

  /**
   * Validates date field.
   *
   * @param array $values
   * @param array $errors
   */
  public function validateDateFields($values, &$errors) {
    if (!empty($values['start_date']) && !empty($values['end_date']) && strtotime($values['end_date']) <= strtotime($values['start_date'])) {
      $errors['end_date'] = ts('End date field must be after start date');
    }

    if (!empty($values['max_valid_through_date']) && !empty($values['min_valid_from_date']) && strtotime($values['max_valid_through_date']) <= strtotime($values['min_valid_from_date'])) {
      $errors['max_valid_through_date'] = ts('Max valid through date field must be after min valid from date');
    }
  }

  public function getPreviousFileURL() {
    $fileURL = "";

    if (empty($this->_id)) {
      return json_encode($fileURL);
    }

    $configuredCertificate = $this->getConfiguredCertificateById($this->_id);
    if ($configuredCertificate['download_type'] == DownloadType::FILE_DOWNLOAD) {
      $file = \CRM_Core_BAO_File::getEntityFile(CompuCertificate::getTableName(), $this->_id);
      $fileURL = end($file)['url'] ?? "";
    }

    return json_encode($fileURL);
  }

}
