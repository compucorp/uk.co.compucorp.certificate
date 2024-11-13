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
    $membershipTokens = [
      '{membership.id}' => ts('Membership ID'),
      '{membership.status_id:label}' => ts('Status'),
      '{membership.membership_type_id:label}' => ts('Membership Type'),
      '{membership.start_date}' => ts('Membership Start Date'),
      '{membership.join_date}' => ts('Member Since'),
      '{membership.end_date}' => ts('Membership Expiration Date'),
      '{membership.fee}' => ts('Membership Fee'),
      '{membership.insurance_premium_ex_ipt}' => ts('Insurance Premium (excl IPT)'),
      '{membership.insurance_premium_ipt_only}' => ts(' Insurance Premium (IPT)'),
      '{membership.insurance_premium_inc_ipt}' => ts('Insurance Premium (incl IPT)'),
    ];
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
    $certificateId = $e->getTokenProcessor()->getContextValues('certificateId');

    $resolvedTokens = [];

    try {
      if (is_array($entityTypeId)) {
        $entityTypeId = $entityTypeId[0];
        $contactId = $contactId[0];
        $certificateId = $certificateId[0] ?? 0;
        $certificate = $certificateId > 0 ?
          CRM_Certificate_BAO_CompuCertificate::findById($certificateId) : new stdClass();
        $result = $this->getMembership($entityTypeId, $contactId, $certificate);
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
        $v = !empty($v) ? new \DateTime($v) : '';
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
   * @param object $certificate
   *
   * @return array
   */
  private function getMembership($membershipId, $contactId, $certificate) {
    $result = civicrm_api3('Membership', 'getsingle', [
      'id' => $membershipId,
      'contact_id' => $contactId,
    ]);

    if (!empty($result['is_error'])) {
      return [];
    }

    $status = CRM_Member_BAO_MembershipStatus::getMembershipStatus($result['status_id']);
    $type = CRM_Member_BAO_MembershipType::getMembershipType($result['membership_type_id']);
    $result['status_idlabel'] = $status['membership_status'] ?? '';
    $result['membership_type_idlabel'] = $type['name'] ?? '';
    $result['fee'] = CRM_Utils_Money::format($type['minimum_fee'] ?? '');
    $insuranceTokens = ['insurance_premium_ex_ipt', 'insurance_premium_inc_ipt', 'insurance_premium_ipt_only'];

    if (empty(array_intersect($this->activeTokens, $insuranceTokens))) {
      return $result;
    }

    $insuranceLineItem = $this->getValidInsuranceLineItem($certificate, $result);

    $result['insurance_premium_ex_ipt'] = !empty($insuranceLineItem['lineTotal']) ?
      CRM_Utils_Money::format($insuranceLineItem['lineTotal']) : '';
    $result['insurance_premium_ipt_only'] = !empty($insuranceLineItem['taxAmount']) ?
      CRM_Utils_Money::format($insuranceLineItem['taxAmount']) : '';
    $result['insurance_premium_inc_ipt'] = !empty($insuranceLineItem['taxAmount']) || !empty($insuranceLineItem['lineTotal']) ?
      CRM_Utils_Money::format((float) $insuranceLineItem['taxAmount'] + (float) $insuranceLineItem['lineTotal']) : '';

    return $result;
  }

  private function getValidInsuranceLineItem($certificate, $membership): array {
    $insuranceFinancialTypes = explode(',', (string) Civi::settings()->get('insurance_premium_financial_type'));
    $query = "
SELECT MAX(c.id) FROM civicrm_contribution c
    INNER JOIN civicrm_membership_payment mp ON c.id = mp.contribution_id
    WHERE mp.membership_id =" . $membership['id'] ?? 0;

    if (!empty($certificate->min_valid_from_date)) {
      $query .= " AND c.receive_date >= '" . $certificate->min_valid_from_date . "'";
    }
    if (!empty($certificate->max_valid_through_date)) {
      $query .= " AND c.receive_date < '" . $certificate->max_valid_through_date . "'";
    }

    $contribution = CRM_Core_DAO::singleValueQuery($query);

    if ($contribution === NULL || empty($insuranceFinancialTypes)) {
      return [];
    }

    $lineItem = Civi\Api4\LineItem::get(FALSE)
      ->addSelect('SUM(tax_amount) AS taxAmount', 'SUM(line_total) AS lineTotal')
      ->addWhere('contribution_id', '=', $contribution)
      ->addWhere('financial_type_id', 'IN', $insuranceFinancialTypes)
      ->execute()
      ->first();

    return $lineItem ?? [];
  }

}
