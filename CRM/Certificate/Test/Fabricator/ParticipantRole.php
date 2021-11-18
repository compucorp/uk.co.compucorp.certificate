<?php

/**
 * Fabricates participant role using API calls.
 */
class CRM_Certificate_Test_Fabricator_ParticipantRole {

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
    $name = md5(mt_rand());

    return [
      'option_group_id' => 'participant_role',
      'label' => $name,
      'name' => $name,
      'value' => rand(1, 10),
    ];
  }

}
