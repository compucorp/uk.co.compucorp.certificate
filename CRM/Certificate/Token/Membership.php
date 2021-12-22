<?php

use Civi\Token\Event\TokenValueEvent;

/**
 * Class CRM_Certificate_Token_Membership
 *
 * Generate "certificate_membership.*" tokens.
 *
 * This class defines the membership tokens (standard fields and custom fields)
 * that are supported by a certificate.
 */
class CRM_Certificate_Token_Membership extends CRM_Certificate_Token_AbstractCertificateToken {

  const TOKEN = 'certificate membership';

  public function __construct($tokenNames = []) {
    $this->tokenNames = $tokenNames;
  }

  /**
   * @inheritDoc
   */
  public static function entityTokens() {
    $membershipCustomFields = CRM_Utils_Token::getCustomFieldTokens('Membership');
    $membershipTokens = CRM_Core_SelectValues::membershipTokens();
    $extraTokens = [
      'source' => ts('Membership Source'),
      'membership_name' => ts('Membership Name'),
      'relationship_name' => ts('Relationship Name'),
    ];

    // we clean up the array because they are keyed in the format {membership.field}
    $filtered_keys = preg_replace(['/(membership\.)/', '/(\W*)/'], '', array_keys($membershipTokens));
    $membershipTokens = array_combine($filtered_keys, array_values($membershipTokens));
    $tokens = array_merge($membershipCustomFields, $membershipTokens, $extraTokens);

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
        $result = $this->getMembership($entityTypeId, $contactId);
        if (empty($result)) {
          return $resolvedTokens;
        }

        $this->resolveFields($result, $resolvedTokens);
      }
    }
    catch (Exception $e) {
      CRM_Core_Session::setStatus('Error resolving tokens');
    }

    return $resolvedTokens;
  }

  /**
   * Resolve the value of membership fields in the token event.
   *
   * @param array $membership
   * @param array &$resolvedTokens
   */
  private function resolveFields($membership, &$resolvedTokens) {
    // Convert date fields to human readable format (2022-12-01 12:12:00 -> 1st December 2022 12:12 PM).
    array_walk($membership, function(&$v, $k) {
      $dateFields = [
        "end_date",
        "start_date",
        "join_date",
      ];

      if (in_array($k, $dateFields)) {
        $v = CRM_Utils_Date::customFormat($v);
      }
    });

    foreach ($this->activeTokens as $value) {
      $resolvedTokens[$value] = CRM_Utils_Array::value($value, $membership, '');
    }
  }

  /**
   * Returns a single membership entity with associated data.
   *
   * @param int $membershipId
   * @param int $contactId
   *
   * @return array
   */
  private function getMembership($membershipId, $contactId) {
    $result = civicrm_api3('Membership', 'getsingle', [
      'id' => $membershipId,
      'contact_id' => $contactId,
    ]);

    if (!empty($result['is_error'])) {
      return [];
    }

    $status = CRM_Member_BAO_MembershipStatus::getMembershipStatus($result['status_id']);
    $type = CRM_Member_BAO_MembershipType::getMembershipType($result['membership_type_id']);
    $result['status'] = $status['membership_status'] ?? '';
    $result['type'] = $type['name'] ?? '';
    $result['fee'] = CRM_Utils_Money::format($type['minimum_fee'] ?? '');

    return $result;
  }

}
