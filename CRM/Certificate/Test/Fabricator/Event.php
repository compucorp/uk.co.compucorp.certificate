<?php

/**
 * Fabricates events.
 */
class CRM_Certificate_Test_Fabricator_Event {

  public static function fabricate($params = []) {

    $params = array_merge(self::getDefaultParams(), $params);
    $result = civicrm_api3('Event', 'create', $params);

    return array_shift($result['values']);
  }

  public static function getDefaultParams() {
    return [
      'title' => md5(mt_rand()),
      'start_date' => date('Y-m-d'),
      'min_valid_from_date' => date("Y-m-d"),
      'max_valid_through_date' => date("Y-m-d", strtotime(date("Y-m-d") . " + 30 days")),
      'event_type_id' => 1,
      'summary' => md5(mt_rand()),
      'description' => md5(mt_rand()),
    ];
  }

}
