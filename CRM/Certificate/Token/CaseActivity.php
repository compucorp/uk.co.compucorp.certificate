<?php

use Civi\Token\Event\TokenValueEvent;
use Symfony\Component\VarDumper\VarDumper;

class CRM_Certificate_Token_CaseActivity extends CRM_Certificate_Token_AbstractCertificateToken {

  const TOKEN = 'certificate - case activity type';

  public function __construct($tokenNames = []) {
    $this->tokenNames = $tokenNames;
  }

  /**
   * @inheritDoc
   */
  public static function entityTokens() {
    $activityTypes = CRM_Case_PseudoConstant::caseActivityType();
    $tokens = [];

    array_walk($activityTypes, function ($field, $name) use (&$tokens) {
      $name = strtolower(CRM_Utils_String::munge($name));
      $key = $field['id'] . '_' . $name;

      return $tokens[$key] = ts($field['label']);
    });

    return $tokens;
  }

  /**
   * To perform a bulk lookup before rendering tokens
   *
   * @param \Civi\Token\Event\TokenValueEvent $e
   *
   * @return mixed
   */
  public function prefetch(TokenValueEvent $e) {
    $entityTypeId = $e->getTokenProcessor()->getContextValues('entityTypeId');

    $resolvedTokens = [];

    if (is_array($entityTypeId)) {
      $entityTypeId = $entityTypeId[0];
      $activityTypeIdWithNames = $this->getFieldIdFromTokenNames($this->activeTokens);
      $activityTypeIds = array_keys($activityTypeIdWithNames);

      $activities = civicrm_api3('Activity', 'get', [
        'case_id' => $entityTypeId,
        'activity_type_id' => ['IN' => $activityTypeIds],
        'is_active' => 1,
        'options' => ['limit' => 0],
      ])['values'];

      foreach ($activities as $activity) {
        if ($name = $activityTypeIdWithNames[$activity['activity_type_id']]) {
          $previousResolvedToken = $resolvedTokens[$name] ?? "";
          $resolvedTokens[$name] = rtrim($activity["subject"] . ", " . $previousResolvedToken, ", ");
        }
      }
    }

    return $resolvedTokens;
  }

  /**
   * Given an array of custom activity token names
   * e.g. ['56_open_case'] this would return [56 => 'open_case'] 
   * 
   * @param array $tokenNames
   *  e.g. ['56_open_case']
   * 
   * @return array
   *  e.g. [56 => '56_open_case'];
   * 
   */
  public function getFieldIdFromTokenNames($tokenNames) {
    $activityTypeIds = preg_replace("/[^0-9]/", "", $tokenNames);
    return array_combine($activityTypeIds, $tokenNames);
  }
}
