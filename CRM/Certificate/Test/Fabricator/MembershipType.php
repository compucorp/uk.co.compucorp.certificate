<?php

/**
 * Fabricates membership types.
 */
class CRM_Certificate_Test_Fabricator_MembershipType {

  public static function fabricate($params = []) {
    $params = array_merge(self::getDefaultParams(), $params);
    $result = civicrm_api3(
      'MembershipType',
      'create',
      $params
    );

    return array_shift($result['values']);
  }

  public static function getDefaultParams() {
    $title = md5(mt_rand());
    $memberOf = CRM_Certificate_Test_Fabricator_Contact::fabricate()['id'];

    return [
      'name' => $title,
      'description' => $title,
      'member_of_contact_id' => $memberOf,
      'financial_type_id' => 1,
      'domain_id' => '1',
      'minimum_fee' => '200',
      'duration_unit' => 'month',
      'duration_interval' => '10',
      'period_type' => 'rolling',
      'visibility' => 'public',
      'is_active' => 1,
    ];
  }

}
