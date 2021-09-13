<?php

/**
 * Fabricates message template.
 */
class CRM_Certificate_Test_Fabricator_MessageTemplate {

  public static function fabricate($params = array()) {
    $params = array_merge(static::getDefaultParams(), $params);
    $group = civicrm_api3('MessageTemplate', 'create', $params);

    return array_shift($group['values']);
  }

  public static function getDefaultParams() {
    $template = CRM_Core_DAO::createTestObject('CRM_Core_DAO_MessageTemplate')->toArray();

    return [
      'msg_title' => $template['msg_title'],
      'msg_subject' => $template['msg_subject'],
      'msg_text' => $template['msg_text'],
      'msg_html' => $template['msg_html'],
      'workflow_id' => $template['workflow_id'],
      'is_default' => $template['is_default'],
      'is_reserved' => $template['is_reserved'],
    ];
  }

}
