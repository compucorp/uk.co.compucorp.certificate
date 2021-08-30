<?php

use CRM_Certificate_Hook_Token_CertificateUrlTokens as CertificateUrlTokens;
use CRM_Certificate_Hook_Token_CertificateUrlTokensValues as CertificateUrlTokensValues;

/**
 * Test class for the CRM_Certificate_Hook_Token_CertificateUrlTokensValues.
 *
 * @group headless
 */
class CertificateUrlTokensValuesTest extends BaseHeadlessTest {

  use CRM_Certificate_Test_Helper_Case;

  /**
   * Test the run method will resolve the,
   * 'certificate_url.case' token with a valid url,
   * when a certificate is available for the case.
   */
  public function testCertificateCaseUrlTokensWillResolveWhenCertificateExist() {
    ['case' => $case, 'contact' => $contact] = $this->createCertificate();
    $contactValues = [$contact['id'] => []];

    $caseIdFromUrlMock = $this->createMock(CRM_Certificate_Service_CaseIdFromUrl::class);
    $caseIdFromUrlMock->method('get')->willReturn($case['id']);

    $service = new CertificateUrlTokensValues($caseIdFromUrlMock);
    $service->run($contactValues, [$contact['id']], 1, [CertificateUrlTokens::TOKEN => ['case']], '');

    $contactCaseUrl = $contactValues[$contact['id']]['certificate_url.case'] ?? "";

    $this->assertFalse(empty($contactCaseUrl));
    $this->assertTrue(filter_var($contactCaseUrl, FILTER_VALIDATE_URL) !== FALSE);
  }

  /**
   * Test the run method will not resolve the,'certificate_url.case'
   *  token when a certificate is not available for the case.
   */
  public function testCertificateCaseUrlTokensWillNotResolveWhenCertificateDoesntExist() {
    $contactValues = [1 => []];

    $caseIdFromUrlMock = $this->createMock(CRM_Certificate_Service_CaseIdFromUrl::class);
    $caseIdFromUrlMock->method('get')->willReturn(1);

    $service = new CertificateUrlTokensValues($caseIdFromUrlMock);
    $service->run($contactValues, [1], 1, [CertificateUrlTokens::TOKEN => ['case']], '');

    $contactCaseUrl = $contactValues[1]['certificate_url.case'] ?? "";

    $this->assertTrue(empty($contactCaseUrl));
  }

  public function createCertificate() {
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $caseStatus = CRM_Certificate_Test_Fabricator_CaseStatus::fabricate();
    $caseType = CRM_Certificate_Test_Fabricator_CaseType::fabricate();

    $case = CRM_Certificate_Test_Fabricator_Case::fabricate(
      [
        'status_id' => $caseStatus['value'],
        'contact_id' => $contact['id'],
        'creator_id' => $contact['id'],
        'case_type_id' => $caseType['id'],
      ]
    );

    $values = [
      'type' => CRM_Certificate_Enum_CertificateType::CASES,
      'linked_to' => [$caseType['id']],
      'statuses' => [$caseStatus['value']],
    ];
    CRM_Certificate_Test_Fabricator_CompuCertificate::fabricate(CRM_Certificate_Enum_CertificateType::CASES, $values);

    return ['case' => $case, 'contact' => $contact];
  }

}
