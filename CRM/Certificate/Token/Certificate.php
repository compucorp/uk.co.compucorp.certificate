<?php

use Civi\Token\Event\TokenValueEvent;

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

        $this->resolveFields($certificate, $resolvedTokens, $e);
      }
    }
    catch (Exception $e) {
      CRM_Core_Session::setStatus('Error resolving tokens');
    }

    return $resolvedTokens;
  }

  /**
   * Resolve the value of ceritificate configuration token fields.
   *
   * @param CRM_Certificate_DAO_CompuCertificate $certificate
   * @param array &$resolvedTokens
   * @param Civi\Token\Event\TokenValueEvent $e
   */
  private function resolveFields($certificate, &$resolvedTokens, $e) {
    $resolvedTokens['rolling_start_or_renewal_date'] = '';
    $resolvedTokens['valid_from'] = '';
    $resolvedTokens['valid_to'] = '';

    if ((int) $certificate->entity === CRM_Certificate_Enum_CertificateType::MEMBERSHIPS) {
      $contactIds = $e->getTokenProcessor()->getContextValues('contactId');
      $contactId = (is_array($contactIds) && !empty($contactIds[0])) ? $contactIds[0] : 0;
      $service = new CRM_Certificate_Service_CertificateMembership();
      $membershipDates = $service->getMembershipDates($certificate->id, $contactId);
      $membershipStartTimestamp = !empty($membershipDates['startDate']) ? strtotime($membershipDates['startDate']) : '';
      $membershipEndTimestamp = !empty($membershipDates['endDate']) ? strtotime($membershipDates['endDate']) : '';
      $certificateValidityStartTimestamp = !empty($certificate->min_valid_from_date) ? strtotime($certificate->min_valid_from_date) : '';
      $certificateValidityEndTimestamp = !empty($certificate->max_valid_through_date) ? strtotime($certificate->max_valid_through_date) : '';

      $resolvedTokens['rolling_start_or_renewal_date'] = $service->getMembershipRenewalDate($certificate->id, $contactId);
      $validityStartDate = empty($certificateValidityStartTimestamp) || $membershipStartTimestamp > $certificateValidityStartTimestamp ?
        $membershipDates['startDate'] : (string) $certificate->min_valid_from_date;
      $validityEndDate = empty($certificateValidityEndTimestamp) || (!empty($membershipEndTimestamp) && $certificateValidityEndTimestamp > $membershipEndTimestamp) ?
        $membershipDates['endDate'] : (string) $certificate->max_valid_through_date;
      $resolvedTokens['valid_from'] = !empty($validityStartDate)
        ? CRM_Utils_Date::customFormat($validityStartDate, '%e/%b/%Y') : '';
      $resolvedTokens['valid_to'] = !empty($validityEndDate)
        ? CRM_Utils_Date::customFormat($validityEndDate, '%e/%b/%Y') : '';
    }

    $resolvedTokens['name'] = $certificate->name;
    $resolvedTokens['start_date'] = CRM_Utils_Date::customFormat($certificate->start_date, '%e/%b/%Y');
    $resolvedTokens['end_date'] = CRM_Utils_Date::customFormat($certificate->end_date, '%e/%b/%Y');
  }

}
