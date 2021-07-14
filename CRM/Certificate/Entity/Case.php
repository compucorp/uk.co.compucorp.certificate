<?php

class CRM_Certificate_Entity_Case implements CRM_Certificate_Entity_EntityInterface {

  /**
   * @inheritdoc
   */
  public function getTypes() {
    $result = civicrm_api3('CaseType', 'get', [
      'sequential' => 1,
      'is_active' => 1,
      'return' => ["id"],
    ]);

    if ($result["is_error"]) {
      return NULL;
    }

    return array_column($result["values"], 'id');
  }

  /**
   * @inheritdoc
   */
  public function getStatuses() {
    $result = civicrm_api3('OptionValue', 'get', [
      'sequential' => 1,
      'is_active' => 1,
      'return' => ["value"],
      'option_group_id' => "case_status",
    ]);

    if ($result["is_error"]) {
      return NULL;
    }

    return array_column($result["values"], 'value');
  }

  /**
   * @inheritDoc
   */
  public function getCertificateConfiguredStatuses($certificateId) {
    $statuses = CRM_Utils_SQL_Select::from(CRM_Certificate_DAO_CompuCertificateStatus::$_tableName . ' ccs')
      ->join('optionValue', 'INNER JOIN `civicrm_option_value` ON (`civicrm_option_value`.`value` = `ccs`.`status_id`)')
      ->join('optionGroup', 'INNER JOIN `civicrm_option_group` ON (`civicrm_option_group`.`id` = `civicrm_option_value`.`option_group_id` AND `civicrm_option_group`.`name` = @groupName)')
      ->where('ccs.certificate_id = @certificateId')
      ->select('`civicrm_option_value`.`value` id, `civicrm_option_value`.`label`')
      ->param([
        'groupName' => 'case_status',
        'certificateId' => $certificateId
      ])
      ->execute()
      ->fetchAll();

    return $statuses;
  }

  /**
   * @inheritDoc
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
        "label" => $entityType["name"]
      ];
    }, $entityTypes);

    return $entityTypes;
  }
}
