<?php

/**
 * Fabricates custom group.
 */
class CRM_Certificate_Test_Fabricator_CustomGroup {

  public static function fabricate($params = array()) {
    $params = array_merge(static::getDefaultParams(), $params);
    $group = civicrm_api3('CustomGroup', 'create', [
      'title' => $params['title'],
      'extends' => $params['extends'],
    ]);

    return array_shift($group['values']);
  }

  public static function getDefaultParams() {
    return ['title' => md5(mt_rand()), 'extends' => ['0' => 'Case']];
  }

}
