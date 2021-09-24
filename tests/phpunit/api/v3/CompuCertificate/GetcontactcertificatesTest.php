<?php

use CRM_Certificate_Test_Fabricator_Contact as ContactFabricator;

/**
 * CompuCert.Getcontactcertificates API Test Case
 *
 * @group headless
 */
class api_v3_CompuCertificate_GetcontactcertificatesTest extends BaseHeadlessTest {

  use \Civi\Test\Api3TestTrait;
  use CRM_Certificate_Test_Helper_Session;
  use CRM_Certificate_Test_Helper_Case;

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
    $caseParam = ['client_id' => $this->client_id];
    $case = $this->createCaseCertificate($caseParam);

    $param = ['entity' => 'case'];

    $results = $this->callApiSuccess('CompuCertificate', 'getcontactcertificates', $param);

    $this->assertEquals(1, $results['count']);
    $this->assertEquals($case['id'], $results['values'][0]['case_id']);
  }

  /**
   * Test that the api returns available configured certificate for
   * the contact ID passed to the API request.
   */
  public function testCaseCertficateIsReturnedForContactIdPassedInAPIParam() {
    $client = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $creator = CRM_Certificate_Test_Fabricator_Contact::fabricate();

    $caseParam = ['creator_id' => $creator['id'], 'client_id' => $client['id']];
    $case = $this->createCaseCertificate($caseParam);

    $param = ['entity' => 'case', 'contact_id' => $client['id']];

    $results = $this->callApiSuccess('CompuCertificate', 'getcontactcertificates', $param);

    $this->assertEquals(1, $results['count']);
    $this->assertEquals($case['id'], $results['values'][0]['case_id']);
  }

  public function tearDown() {
    $this->unregisterCurrentLoggedInContactFromSession();
    parent::tearDown();
  }

}
