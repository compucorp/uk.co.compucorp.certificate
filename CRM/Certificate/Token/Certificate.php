<?php

use Civi\Token\Event\TokenValueEvent;
use Civi\Api4\Membership;

/**
 * Class CRM_Certificate_Token_Certificate
 *
 * Generate "certificate.*" tokens.
 *
 * This class defines tokens for the certificate configuration fields.
 */
class CRM_Certificate_Token_Certificate extends CRM_Certificate_Token_AbstractCertificateToken {

  const TOKEN = 'certificate';

  private const MEMBERSHIP_STATUS_CURRENT = 2;

  /**
   * Here we define list of standard certificate configuration fields
   * that are supported as tokens.
   */
  const certificateFields = [
    "name" => "Certificate Name",
    "start_date" => "Certificate Start Date",
    "end_date" => "Certificate End Date",
    "valid_from" => "Certificate Valid From Date",
    "valid_to" => "Certificate Valid To Date",
    "rolling_start_or_renewal_date" => "Certificate Rolling Start Or Renewal Date",
  ];

  public function __construct($tokenNames = []) {
    $this->tokenNames = $tokenNames;
  }

  /**
   * @inheritDoc
   */
  public static function entityTokens() {
    return static::certificateFields;
  }

  /**
   * To perform a bulk lookup before rendering tokens
   *
   * @param \Civi\Token\Event\TokenValueEvent $e
   *
   * @return mixed
   */
  public function prefetch(TokenValueEvent $e) {
    $certificateId = $e->getTokenProcessor()->getContextValues('certificateId');

    $resolvedTokens = [];

    try {
      if (is_array($certificateId)) {
        $certificateId = $certificateId[0];
        $certificate = CRM_Certificate_BAO_CompuCertificate::findById($certificateId);

        if (empty($certificate)) {
          return $resolvedTokens;
        }

        $this->resolveFields($certificate, $this->getMembershipDates($e), $resolvedTokens);
      }
    }
    catch (Exception $e) {
      CRM_Core_Session::setStatus('Error resolving tokens');
    }

    return $resolvedTokens;
  }

  private function getMembershipDates(TokenValueEvent $e): array {
    $startDate = NULL;
    $endDate = NULL;

    $contactIds = $e->getTokenProcessor()->getContextValues('contactId');
    $contactId = (is_array($contactIds) && !empty($contactIds[0])) ? $contactIds[0] : 0;

    // get all memberships for given contact to get min start_date and max end_date of all memberships
    $memberships = Membership::get(FALSE)
      ->addSelect('start_date', 'end_date')
      ->addWhere('contact_id', '=', $contactId)
      ->addWhere('status_id', '=', self::MEMBERSHIP_STATUS_CURRENT)
      ->execute()
      ->getArrayCopy();

    foreach ($memberships as $membership) {
      $startDate = $startDate === NULL || strtotime($membership['start_date']) < strtotime($startDate) ?
        $membership['start_date'] : $startDate;
      $endDate = $endDate === NULL || strtotime($membership['end_date']) > strtotime($endDate) ?
        $membership['end_date'] : $endDate;
    }

    return ['start_date' => $startDate, 'end_date' => $endDate];
  }

  /**
   * Resolve the value of ceritificate configuration token fields.
   *
   * @param CRM_Certificate_DAO_CompuCertificate $certificate
   * @param array $membershipDates
   * @param array &$resolvedTokens
   */
  private function resolveFields($certificate, array $membershipDates, &$resolvedTokens) {
    $membershipStartTimestamp = !empty($membershipDates['start_date']) ? strtotime($membershipDates['start_date']) : '';
    $membershipEndTimestamp = !empty($membershipDates['end_date']) ? strtotime($membershipDates['end_date']) : '';
    $certificateValidityStartTimestamp = !empty($certificate->min_valid_from_date) ? strtotime($certificate->min_valid_from_date) : '';
    $certificateValidityEndTimestamp = !empty($certificate->max_valid_through_date) ? strtotime($certificate->max_valid_through_date) : '';
    $resolvedTokens['rolling_start_or_renewal_date'] = '';

    if ($membershipStartTimestamp && $membershipEndTimestamp) {
      $renewalTimestamp = strtotime($membershipDates['end_date'] . " -1 year 1 day");
      $resolvedTokens['rolling_start_or_renewal_date'] = $renewalTimestamp > $membershipStartTimestamp
        ? CRM_Utils_Date::customFormat(date('Y-m-d', $renewalTimestamp), '%e/%b/%Y')
        : CRM_Utils_Date::customFormat($membershipDates['start_date'], '%e/%b/%Y');
    }

    $validityStartDate = empty($certificateValidityStartTimestamp) || $membershipStartTimestamp > $certificateValidityStartTimestamp ?
      $membershipDates['start_date'] : (string) $certificate->min_valid_from_date;
    $validityEndDate = empty($certificateValidityEndTimestamp) || (!empty($membershipEndTimestamp) && $certificateValidityEndTimestamp > $membershipEndTimestamp) ?
      $membershipDates['end_date'] : (string) $certificate->max_valid_through_date;

    $resolvedTokens['name'] = $certificate->name;
    $resolvedTokens['start_date'] = CRM_Utils_Date::customFormat($certificate->start_date, '%e/%b/%Y');
    $resolvedTokens['end_date'] = CRM_Utils_Date::customFormat($certificate->end_date, '%e/%b/%Y');
    $resolvedTokens['valid_from'] = !empty($validityStartDate)
      ? CRM_Utils_Date::customFormat($validityStartDate, '%e/%b/%Y') : '';
    $resolvedTokens['valid_to'] = !empty($validityEndDate)
      ? CRM_Utils_Date::customFormat($validityEndDate, '%e/%b/%Y') : '';
  }

}
