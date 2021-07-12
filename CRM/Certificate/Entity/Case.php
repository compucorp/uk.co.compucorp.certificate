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
}
