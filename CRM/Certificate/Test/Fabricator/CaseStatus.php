<?php

/**
 * Fabricates case status using API calls.
 */
class CRM_Certificate_Test_Fabricator_CaseStatus {

  public static function fabricate($params = []) {
    $params = array_merge(self::getDefaultParams(), $params);
    $result = civicrm_api3(
      'OptionValue',
      'create',
      $params
    );
    return array_shift($result['values']);
  }

  public static function getDefaultParams() {
    $statusName = md5(mt_rand());
    return [
      'option_group_id' => 'case_status',
      'name' => $statusName,
      'label' => $statusName,
      'weight' => 99,
    ];
  }

}
