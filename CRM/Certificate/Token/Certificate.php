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

  /**
   * Here we define list of standard certificate configuration fields
   * that are supported as tokens.
   */
  const certificateFields = [
    "name" => "Certificate Name",
    "start_date" => "Certificate Start Date",
    "end_date" => "Certificate End Date",
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

        $this->resolveFields($certificate, $resolvedTokens);
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
   */
  private function resolveFields($certificate, &$resolvedTokens) {
    $resolvedTokens['name'] = $certificate->name;
    $resolvedTokens['start_date'] = CRM_Utils_Date::customFormat($certificate->start_date, '%e/%b/%Y');
    $resolvedTokens['end_date'] = CRM_Utils_Date::customFormat($certificate->end_date, '%e/%b/%Y');
  }

}
