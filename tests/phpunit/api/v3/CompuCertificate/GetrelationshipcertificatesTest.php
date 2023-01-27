<?php

use CRM_Certificate_Test_Fabricator_Contact as ContactFabricator;
use CRM_Certificate_Test_Fabricator_Relationship as RelationshipFabricator;
use CRM_Certificate_Test_Fabricator_RelationshipType as RelationshipTypeFabricator;

/**
 * CompuCert.Getrelatedcontactcertificates API Test Case
 *
 * @group headless
 */
class api_v3_CompuCertificate_GetrelatedcontactcertificatesTest extends BaseHeadlessTest {

  use \Civi\Test\Api3TestTrait;
  use CRM_Certificate_Test_Helper_Case;
  use CRM_Certificate_Test_Helper_Event;
  use CRM_Certificate_Test_Helper_Session;
  use CRM_Certificate_Test_Helper_Membership;
  use CRM_Certificate_Test_Helper_Certificate;

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
   * Test that the api returns available cases certificate
   * available to a contact via relationship to anotehr contact.
   */
  public function testRelatedCaseCertficateIsReturnedForContact() {
    $relationshipTypeParams = [
      'name_a_b' => 'A Employee of',
      'name_b_a' => 'A Empoyer of',
    ];

    $relationshipType = RelationshipTypeFabricator::fabricate($relationshipTypeParams);
    $contactA = ContactFabricator::fabricate();
    $contactB = ContactFabricator::fabricate();

    // Contact B is Employer of Contact A
    $params = [
      'contact_id_a' => $contactA['id'],
      'contact_id_b' => $contactB['id'],
      'relationship_type_id' => $relationshipType['id'],
    ];
    RelationshipFabricator::fabricate($params);

    // Contact B has a valid certificate, with access given to Employee relationship
    $caseParam = ['client_id' => $contactB['id'], 'relationship_types' => [$relationshipType['id']]];
    $case = $this->createCaseCertificate($caseParam);

    // Contact A should have access to Contact B certificates based on their relationship.
    $param = ['entity' => 'case', 'contact_id' => $contactA['id']];
    $results = $this->callApiSuccess('CompuCertificate', 'getrelationshipcertificates', $param);

    $this->assertEquals(1, $results['count']);
    $this->assertEquals($case['id'], $results['values'][0]['case_id']);
  }

  public function testRelatedEventCertficateIsReturnedForContact() {
    $relationshipTypeParams = [
      'name_a_b' => 'A Employee of',
      'name_b_a' => 'A Empoyer of',
    ];

    $relationshipType = RelationshipTypeFabricator::fabricate($relationshipTypeParams);
    $contactA = ContactFabricator::fabricate();
    $contactB = ContactFabricator::fabricate();

    // Contact B is Employer of Contact A
    $params = [
      'contact_id_a' => $contactA['id'],
      'contact_id_b' => $contactB['id'],
      'relationship_type_id' => $relationshipType['id'],
    ];
    RelationshipFabricator::fabricate($params);

    // Contact B has a valid certificate, with access given to Employee relationship
    $participant = $this->createParticipant(['contact_id' => $contactB['id']]);
    $certificateParam = ['linked_to' => [$participant['event_id']], 'relationship_types' => [$relationshipType['id']], 'statuses' => [$participant['participant_status_id']]];
    $this->createEventCertificate($certificateParam);

    // Contact A should have access to Contact B certificates based on their relationship.
    $param = ['entity' => 'event', 'contact_id' => $contactA['id']];
    $results = $this->callApiSuccess('CompuCertificate', 'getrelationshipcertificates', $param);

    $this->assertEquals(1, $results['count']);
    $this->assertEquals($participant['id'], $results['values'][0]['participant_id']);
  }

  public function testRelatedMembershipCertficateIsReturnedForContact() {
    $relationshipTypeParams = [
      'name_a_b' => 'A Employee of',
      'name_b_a' => 'A Empoyer of',
    ];

    $relationshipType = RelationshipTypeFabricator::fabricate($relationshipTypeParams);
    $contactA = ContactFabricator::fabricate();
    $contactB = ContactFabricator::fabricate();

    // Contact B is Employer of Contact A
    $params = [
      'contact_id_a' => $contactA['id'],
      'contact_id_b' => $contactB['id'],
      'relationship_type_id' => $relationshipType['id'],
    ];
    RelationshipFabricator::fabricate($params);

    // Contact B has a valid certificate, with access given to Employee relationship
    $membership = $this->createMembership(['contact_id' => $contactB['id']]);
    $this->createMembershipCertificate(
      [
        'statuses'  => [$membership['status_id']],
        'relationship_types' => [$relationshipType['id']],
        'linked_to' => [$membership['membership_type_id']],
      ]
    );

    // Contact A should have access to Contact B certificates based on their relationship.
    $param = ['entity' => 'membership', 'contact_id' => $contactA['id']];

    $results = $this->callApiSuccess('CompuCertificate', 'getrelationshipcertificates', $param);

    $this->assertEquals(1, $results['count']);
    $this->assertEquals($membership['id'], $results['values'][0]['membership_id']);
  }

  public function tearDown() {
    $this->unregisterCurrentLoggedInContactFromSession();
    parent::tearDown();
  }

}
