<?php

/**
 * Fabricates events.
 */
class CRM_Certificate_Test_Fabricator_Participant {

  public static function fabricate($params = []) {

    $params = array_merge(self::getDefaultParams(), $params);
    $result = civicrm_api3('Participant', 'create', $params);

    return array_shift($result['values']);
  }

  public static function getDefaultParams() {
    return [
      'role_id' => 1,
      'register_date' => date('Y-m-d'),
      'source' => 'Online Event Testing',
    ];
  }

}
