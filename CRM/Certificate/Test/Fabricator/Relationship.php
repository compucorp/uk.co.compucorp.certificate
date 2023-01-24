<?php

/**
 * Fabricates relationship using API calls.
 */
class CRM_Certificate_Test_Fabricator_Relationship {

  private static $defaultParams = [
    'is_active' => 1,
    'start_date' => NULL,
    'end_date' => NULL,
  ];

  public static function fabricate($params = []) {
    $params = array_merge(self::$defaultParams, $params);

    $result = civicrm_api3('Relationship', 'create', $params);

    return array_shift($result['values']);
  }

}
