<?php

use CRM_Certificate_Enum_CertificateType as CertificateType;
use CRM_Certificate_Test_Fabricator_Contact as ContactFabricator;
use CRM_Certificate_Test_Fabricator_CompuCertificate as CompuCertificateFabricator;

/**
 * CompuCert.Getcontactcertificates API Test Case
 *
 * @group headless
 */
class api_v3_CompuCertificate_GetcontactcertificatesTest extends BaseHeadlessTest {

  use \Civi\Test\Api3TestTrait;
  use CRM_Certificate_Test_Helper_Session;

  /**
   * Holds logged in contact/case client id.
   *
   * @var int
   */
  private $client_id;

  public function setUp() {
    parent::setUp();
    $contact = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession($contact['id']);
    $this->client_id = $contact['id'];
  }

  /**
   * Test that the api returns available configured certificate for
   * the logged-in contact.
   */
  public function testCaseCertficateIsReturnedForLoggedInContact() {
    $creator = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $caseType = CRM_Certificate_Test_Fabricator_CaseType::fabricate();
    $caseStatus = CRM_Certificate_Test_Fabricator_CaseStatus::fabricate();

    $case = CRM_Certificate_Test_Fabricator_Case::fabricate(
      [
        'status_id' => $caseStatus['value'],
        'contact_id' => $this->client_id,
        'creator_id' => $creator['id'],
        'case_type_id' => $caseType['id'],
      ]
    );

    $values = [
      'type' => CertificateType::CASES,
      'linked_to' => [$caseType['id']],
      'statuses' => [$caseStatus['value']],
    ];

    CompuCertificateFabricator::fabricate(CertificateType::CASES, $values);
    $param = ['entity' => 'case'];

    $results = $this->callApiSuccess('CompuCertificate', 'getcontactcertificates', $param);

    $this->assertEquals(1, $results['count']);
    $this->assertEquals($case['id'], $results['values'][0]['case_id']);
  }

  public function tearDown() {
    $this->unregisterCurrentLoggedInContactFromSession();
    parent::tearDown();
  }

}
