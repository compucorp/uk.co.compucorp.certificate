<?php

use CRM_Certificate_Enum_CertificateType as CertificateType;
use CRM_Certificate_BAO_CompuCertificate as CompuCertificate;

class CRM_Certificate_Entity_Case extends CRM_Certificate_Entity_AbstractEntity {

  /**
   * {@inheritDoc}
   */
  public function store($values) {
    return (new CRM_Certificate_Service_CertificateCase())->store($values);
  }

  /**
   * {@inheritDoc}
   */
  public function getTypes() {
    try {
      $result = civicrm_api3('CaseType', 'get', [
        'sequential' => 1,
        'is_active' => 1,
        'return' => ["id"],
        'options' => ['limit' => 0],
      ]);

      if ($result["is_error"]) {
        return NULL;
      }

      return array_column($result["values"], 'id');
    }
    catch (\Throwable $th) {
      return [];
    }
  }

  /**
   * @inheritdoc
   */
  public function getStatuses() {
    try {
      $result = civicrm_api3('OptionValue', 'get', [
        'sequential' => 1,
        'is_active' => 1,
        'return' => ["value"],
        'options' => ['limit' => 0],
        'option_group_id' => "case_status",
      ]);

      if ($result["is_error"]) {
        return NULL;
      }

      return array_column($result["values"], 'value');
    }
    catch (\Throwable $th) {
      return [];
    }
  }

  /**
   * {@inheritDoc}
   */
  public function getCertificateConfiguredStatuses($certificateId) {
    $statuses = CRM_Utils_SQL_Select::from(CRM_Certificate_DAO_CompuCertificateStatus::$_tableName . ' ccs')
      ->join('optionValue', 'INNER JOIN `civicrm_option_value` ON (`civicrm_option_value`.`value` = `ccs`.`status_id`)')
      ->join('optionGroup', 'INNER JOIN `civicrm_option_group` ON (`civicrm_option_group`.`id` = `civicrm_option_value`.`option_group_id` AND `civicrm_option_group`.`name` = @groupName)')
      ->where('ccs.certificate_id = @certificateId')
      ->select('`civicrm_option_value`.`value` id, `civicrm_option_value`.`label`')
      ->param([
        'groupName' => 'case_status',
        'certificateId' => $certificateId,
      ])
      ->execute()
      ->fetchAll();

    return $statuses;
  }

  /**
   * {@inheritDoc}
   */
  public function getCertificateConfiguredTypes($certificateId) {
    $entityTypeBAO = new CRM_Certificate_BAO_CompuCertificateEntityType();
    $entityTypeBAO->whereAdd("certificate_id = " . $certificateId);

    $entityType = new CRM_Case_DAO_CaseType();
    $entityTypeBAO->joinAdd(['entity_type_id', $entityType, 'id']);
    $entityTypeBAO->find();
    $entityTypes = $entityTypeBAO->fetchAll('id');

    $entityTypes = array_map(function ($entityType) {
      return [
        "id" => $entityType["id"],
        "label" => $entityType["name"],
      ];
    }, $entityTypes);

    return $entityTypes;
  }

  /**
   * {@inheritDoc}
   */
  protected function addEntityConditionals($certificateBAO, $entityId, $contactId) {
    $case = civicrm_api3('Case', 'getsingle', [
      'id' => $entityId,
      'contact_id' => $contactId,
      'is_active' => 1,
    ]);

    $certificateBAO->joinAdd(['id', new CRM_Certificate_BAO_CompuCertificateEntityType(), 'certificate_id']);
    $certificateBAO->joinAdd(['id', new CRM_Certificate_BAO_CompuCertificateStatus(), 'certificate_id']);
    $certificateBAO->whereAdd('entity = ' . $this->getEntity());
    $certificateBAO->whereAdd('entity_type_id = ' . $case['case_type_id']);
    $certificateBAO->whereAdd('status_id = ' . $case['status_id']);
  }

  /**
   * {@inheritDoc}
   */
  public function getContactCertificates($contactId) {
    $configuredCertificates = CompuCertificate::getEntityCertificates($this->getEntity());

    return $this->formatConfiguredCertificatesForContact($configuredCertificates, $contactId);
  }

  /**
   * {@inheritDoc}
   */
  public function formatConfiguredCertificatesForContact(array $configuredCertificates, $contactId) {
    $certificates = [];

    foreach ($configuredCertificates as $configuredCertificate) {

      $result = [];
      try {
        $result = civicrm_api3('CaseContact', 'get', [
          'sequential' => 1,
          'return' => ['case_id.case_type_id.title', 'case_id.status_id.label', 'case_id'],
          'case_id.case_type_id' => $configuredCertificate['entity_type_id'],
          'case_id.status_id' => $configuredCertificate['status_id'],
          'contact_id' => $contactId,
          'case_id.is_deleted' => 0,
        ]);
      }
      catch (\Throwable $th) {
        continue;
      }

      if ($result['is_error']) {
        continue;
      }

      array_walk(
        $result['values'],
        function ($caseContact) use (&$certificates, $configuredCertificate, $contactId) {
          $certificate = [
            'case_id' => $caseContact['case_id'],
            'name' => $configuredCertificate['name'],
            'end_date' => $configuredCertificate['end_date'],
            'start_date' => $configuredCertificate['start_date'],
            'type' => 'Case',
            'status' => $caseContact['case_id.status_id.label'],
            'linked_to' => $caseContact['case_id.case_type_id.title'],
            'download_link' => $this->getCertificateDownloadUrl($caseContact['case_id'], $contactId, TRUE),
          ];
          array_push($certificates, $certificate);
        });
    }

    return $certificates;
  }

  /**
   * {@inheritDoc}
   */
  public function getCertificateDownloadUrl($entityId, $contactId, $absolute = FALSE) {
    $query = [
      "cid" => $contactId,
      "id" => $entityId,
    ];

    $downloadUrl = htmlspecialchars_decode(CRM_Utils_System::url('civicrm/certificates/case', $query, $absolute));

    return $downloadUrl;
  }

  public function getEntity() {
    return CertificateType::CASES;
  }

}
