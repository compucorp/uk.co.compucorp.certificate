<?php

use CRM_Certificate_Enum_CertificateType as CertificateType;

class CRM_Certificate_Hook_Token_CaseCertificateUrlTokensValues {

  const TOKEN = 'certificate_url';

  /**
   * Current case id
   *
   * @var int
   */
  private $caseId;

  public function __construct($caseId) {
    $this->caseId = $caseId;
  }

  /**
   * Add certificate url token values.
   *
   * @param array $values
   *   Token values.
   * @param array $cids
   *   Contact ids.
   * @param int $job
   *   Job id.
   * @param array $tokens
   *   Token names that are used actually.
   * @param string $context
   *   Context name.
   */
  public function run(array &$values, array $cids, $job, array $tokens, $context) {
    $prefix = self::TOKEN;

    if (!isset($tokens[$prefix]) || empty($values)) {
      return;
    }

    if (in_array('case', $tokens[$prefix]) && !empty($this->caseId)) {
      $this->resolveCaseCertificateURLToken($values, $cids, $this->caseId);
    }
  }

  /**
   * Resolve case certificate url token
   *
   * @param array $values
   *   Token values.
   * @param array $cids
   *   Contact ids.
   * @param int $caseId
   *   Case Id.
   */
  private function resolveCaseCertificateURLToken(&$values, $cids, $caseId) {
    $prefix = self::TOKEN;

    foreach ($cids as $cid) {
      $entity = CRM_Certificate_Entity_EntityFactory::create(CertificateType::CASES);
      $configuredCertificate = $entity->getCertificateConfiguration($caseId, $cid);

      if ($configuredCertificate) {
        $cs = CRM_Contact_BAO_Contact_Utils::generateChecksum($cid);
        $query = [
          "id" => $caseId,
          "cid" => $cid,
          "cs" => $cs,
        ];

        $url = CRM_Utils_System::url('civicrm/certificates/case', $query, TRUE, NULL, FALSE);
        $values[$cid][$prefix . '.case'] = $url;
      }
    }
  }

}
