<?php

use Civi\Token\Event\TokenValueEvent;

/**
 * Class CRM_Certificate_Token_Case
 *
 * Generate "certificate_case.*" tokens.
 *
 * This class defines the case tokens (standard fields and custom fields)
 * that are supported by a certificate
 */
class CRM_Certificate_Token_Case extends CRM_Certificate_Token_AbstractCertificateToken {

  const TOKEN = 'certificate case';

  /**
   * Here we define list of standard case fields
   * that are supported as tokens
   */
  const caseFields = [
    "id" => "Case ID",
    "subject" => "Case Subject",
    "start_date" => "Case Start Date",
    "end_date" => "Case End Date",
    "created_date" => "Created Date",
    "role" => "Role in Case",
    "status" => "Case Status",
  ];

  public function __construct($tokenNames = []) {
    $this->tokenNames = $tokenNames;
  }

  /**
   * @inheritDoc
   */
  public static function entityTokens() {
    $tokens = array_merge(static::caseFields, CRM_Utils_Token::getCustomFieldTokens('Case'));

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
    $entityTypeId = $e->getTokenProcessor()->getContextValues('entityId');
    $contactId = $e->getTokenProcessor()->getContextValues('contactId');

    $resolvedTokens = [];

    try {
      if (is_array($entityTypeId)) {
        $entityTypeId = $entityTypeId[0];
        $contactId = $contactId[0];
        $result = civicrm_api3('Case', 'getdetails', [
          'contact_id' => $contactId,
          'id' => $entityTypeId,
          'is_active' => 1,
        ]);

        if ($result['is_error']) {
          return $resolvedTokens;
        }

        $case = array_shift($result['values']);

        $this->resolveFields($e, $case, $resolvedTokens);
        $this->resolveCustomFields($case, $resolvedTokens);
      }
    }
    catch (Exception $e) {
      CRM_Core_Session::setStatus('Error resolving tokens');
    }

    return $resolvedTokens;
  }

  /**
   * Resolve the value of case standard fields in the token event
   *
   * @param \Civi\Token\Event\TokenValueEvent $e
   * @param array $case
   * @param array &$resolvedTokens
   */
  private function resolveFields(TokenValueEvent $e, $case, &$resolvedTokens) {
    $contactId = $e->getTokenProcessor()->getContextValues('contactId');
    $role = '';

    foreach ($case['contacts'] as $contact) {
      if ($contact['contact_id'] == $contactId[0]) {
        $role = $contact['role'];
      }
    }

    $caseStatus = CRM_Case_PseudoConstant::caseStatus('label', TRUE, 'AND value = ' . $case['status_id']);

    $resolvedTokens['id'] = CRM_Utils_Array::value('id', $case, '');
    $resolvedTokens['subject'] = CRM_Utils_Array::value('subject', $case, '');
    $resolvedTokens['start_date'] = CRM_Utils_Date::customFormat(CRM_Utils_Array::value('start_date', $case, ''));
    $resolvedTokens['end_date'] = CRM_Utils_Date::customFormat(CRM_Utils_Array::value('end_date', $case, ''));
    $resolvedTokens['created_date'] = CRM_Utils_Date::customFormat(CRM_Utils_Array::value('created_date', $case, ''));
    $resolvedTokens['role'] = $role;
    $resolvedTokens['status'] = array_pop($caseStatus) ?? '';
  }

  /**
   * Resolve the value of case custom fields in the token event
   *
   * @param array $case
   * @param array &$resolvedTokens
   */
  private function resolveCustomFields($case, &$resolvedTokens) {
    if (empty($this->activeTokens)) {
      return;
    }

    foreach ($this->activeTokens as $field) {
      if ($fieldId = CRM_Core_BAO_CustomField::getKeyID($field)) {
        $customFieldName = "custom_" . $fieldId;
        $resolvedTokens[$field] = $case[$customFieldName] ?? "";
      }
    }
  }

}
