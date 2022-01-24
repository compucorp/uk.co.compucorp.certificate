<?php

use CRM_Certificate_Test_Fabricator_Contact as ContactFabricator;

/**
 * CompuCert.Getcontactcertificates API Test Case
 *
 * @group headless
 */
class api_v3_CompuCertificate_GetcontactcertificatesTest extends BaseHeadlessTest {

  use \Civi\Test\Api3TestTrait;
  use CRM_Certificate_Test_Helper_Case;
  use CRM_Certificate_Test_Helper_Event;
  use CRM_Certificate_Test_Helper_Session;
  use CRM_Certificate_Test_Helper_Membership;

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
   * Test that the api returns available cases certificate for
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
   * Test that the api returns available cases certificate for
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

  /**
   * Test that the api returns available events certificate for
   * the logged-in contact.
   */
  public function testEventCertficateIsReturnedForLoggedInContact() {
    $participant = $this->createParticipant(['contact_id' => $this->client_id]);
    $this->createEventCertificate(
      [
        'linked_to' => [$participant['event_id']],
        'statuses'  => [$participant['participant_status_id']],
      ]
    );

    $param = ['entity' => 'event'];

    $results = $this->callApiSuccess('CompuCertificate', 'getcontactcertificates', $param);

    $this->assertEquals(1, $results['count']);
    $this->assertEquals($participant['id'], $results['values'][0]['participant_id']);
  }

  /**
   * Test that the api returns available events certificate for
   * the contact ID passed to the API request.
   */
  public function testEventCertficateIsReturnedForContactIdPassedInAPIParam() {
    $participant = $this->createParticipant();
    $this->createEventCertificate(
      [
        'linked_to' => [$participant['event_id']],
        'statuses'  => [$participant['participant_status_id']],
      ]
    );

    $contact = $participant['contact'];

    $param = ['entity' => 'event', 'contact_id' => $contact['id']];

    $results = $this->callApiSuccess('CompuCertificate', 'getcontactcertificates', $param);

    $this->assertEquals($participant['id'], $results['values'][0]['participant_id']);
  }

  /**
   * Test that the api returns available memberships certificate for
   * the logged-in contact.
   */
  public function testMembershipCertficateIsReturnedForLoggedInContact() {
    $membership = $this->createMembership(['contact_id' => $this->client_id]);
    $this->createMembershipCertificate(
      [
        'linked_to' => [$membership['membership_type_id']],
        'statuses'  => [$membership['status_id']],
      ]
    );

    $param = ['entity' => 'membership'];

    $results = $this->callApiSuccess('CompuCertificate', 'getcontactcertificates', $param);

    $this->assertEquals(1, $results['count']);
    $this->assertEquals($membership['id'], $results['values'][0]['membership_id']);
  }

  /**
   * Test that the api returns available memberships certificate for
   * the contact ID passed to the API request.
   */
  public function testMembershipCertficateIsReturnedForContactIdPassedInAPIParam() {
    $membership = $this->createMembership();
    $this->createMembershipCertificate(
      [
        'linked_to' => [$membership['membership_type_id']],
        'statuses'  => [$membership['status_id']],
      ]
    );

    $contact = $membership['contact'];

    $param = ['entity' => 'membership', 'contact_id' => $contact['id']];

    $results = $this->callApiSuccess('CompuCertificate', 'getcontactcertificates', $param);

    $this->assertEquals($membership['id'], $results['values'][0]['membership_id']);
  }

  public function tearDown() {
    $this->unregisterCurrentLoggedInContactFromSession();
    parent::tearDown();
  }

}
