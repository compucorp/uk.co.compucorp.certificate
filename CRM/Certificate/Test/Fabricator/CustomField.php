<?php

/**
 * Fabricates custom field.
 */
class CRM_Certificate_Test_Fabricator_CustomField {

  public static function fabricate($params = array()) {
    $params = array_merge(static::getDefaultParams(), $params);

    if (empty($params['custom_group_id'])) {
      $params['custom_group_id'] = CRM_Certificate_Test_Fabricator_CustomGroup::fabricate()['id'];
    }

    $field = civicrm_api3('CustomField', 'create', [
      'custom_group_id' => $params['custom_group_id'],
      'name' => $params['name'],
      'label' => $params['label'],
      'html_type' => $params['html_type'],
    ]);

    return array_shift($field['values']);
  }

  public static function getDefaultParams() {
    return [
      'name' => md5(mt_rand()),
      'label' => md5(mt_rand()),
      'html_type' => 'Text',
    ];
  }

}
