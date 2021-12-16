<?php

/**
 * Fabricates membership statuses.
 */
class CRM_Certificate_Test_Fabricator_MembershipStatus {

  public static function fabricate($params = []) {
    $params = array_merge(self::getDefaultParams(), $params);
    $result = civicrm_api3(
      'MembershipStatus',
      'create',
      $params
    );

    return array_shift($result['values']);
  }

  public static function getDefaultParams() {
    $name = md5(mt_rand());

    return [
      'name' => $name,
      'is_active' => 1,
    ];
  }

}
