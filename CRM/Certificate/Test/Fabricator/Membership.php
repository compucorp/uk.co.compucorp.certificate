<?php

/**
 * Fabricates memberships.
 */
class CRM_Certificate_Test_Fabricator_Membership {

  private static $defaultParams = [
    'join_date' => '2006-01-21',
    'start_date' => '2006-01-21',
    'end_date' => '2006-12-21',
    'source' => 'Payment',
    'is_override' => 1,
  ];

  public static function fabricate($params = []) {
    if (empty($params['contact_id'])) {
      throw new Exception('Please specify contact_id value');
    }

    if (empty($params['membership_type_id'])) {
      throw new Exception('Please specify membership_type_id value');
    }

    if (empty($params['status_id'])) {
      throw new Exception('Please specify status_id value');
    }

    $params = array_merge(self::$defaultParams, $params);
    $result = civicrm_api3('Membership', 'create', $params);

    return array_shift($result['values']);
  }

}
