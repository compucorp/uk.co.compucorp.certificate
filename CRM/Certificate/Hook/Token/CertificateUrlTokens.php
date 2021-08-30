<?php

class CRM_Certificate_Hook_Token_CertificateUrlTokens {

  const TOKEN = 'certificate_url';

  /**
   * The service to get case id from the url
   *
   * @var CRM_Certificate_Service_CaseIdFromUrl
   */
  private $caseIdFromUrl;

  public function __construct($caseIdFromUrl) {
    $this->caseIdFromUrl = $caseIdFromUrl;
  }

  /**
   * Add current user tokens.
   *
   * @param array $tokens
   *   List of tokens.
   */
  public function run(array &$tokens) {
    if (!empty($this->caseIdFromUrl->get())) {
      $tokens[self::TOKEN][self::TOKEN . '.case'] = 'Case Certificate URL';
    }
  }

}
