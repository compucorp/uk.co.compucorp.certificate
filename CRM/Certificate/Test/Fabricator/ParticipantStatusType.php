<?php

/**
 * Fabricates participant status type using API calls.
 */
class CRM_Certificate_Test_Fabricator_ParticipantStatusType {

  public static function fabricate($params = []) {
    $params = array_merge(self::getDefaultParams(), $params);
    $result = civicrm_api3(
      'ParticipantStatusType',
      'create',
      $params
    );

    return array_shift($result['values']);
  }

  public static function getDefaultParams() {
    $name = md5(mt_rand());
    return [
      'name' => $name,
      'label' => $name,
      'class' => 'Positive',
      'weight' => rand(1, 10),
    ];
  }

}
