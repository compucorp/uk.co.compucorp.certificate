<?php

use CRM_Certificate_Api_Wrapper_CompuCertificate as RelationshipCertificateWrapper;
use CRM_Certificate_Enum_CertificateType as CertificateType;
use CRM_Certificate_Test_Fabricator_CompuCertificate as CertificateFabricator;
use CRM_Certificate_Test_Fabricator_Contact as ContactFabricator;
use CRM_Certificate_Test_Fabricator_Membership as MembershipFabricator;
use CRM_Certificate_Test_Fabricator_MembershipStatus as MembershipStatusFabricator;
use CRM_Certificate_Test_Fabricator_MembershipType as MembershipTypeFabricator;
use CRM_Certificate_Test_Fabricator_Relationship as RelationshipFabricator;
use CRM_Certificate_Test_Fabricator_RelationshipType as RelationshipTypeFabricator;

/**
 * @group headless
 */
class CRM_Certificate_Api_Wrapper_CompuCertificateFilterTest extends BaseHeadlessTest {

  use CRM_Certificate_Test_Helper_Session;

  /**
   * Logged in member contact.
   *
   * @var array
   */
  private $member;

  /**
   * @var array
   */
  private $organisationA;

  /**
   * @var array
   */
  private $organisationB;

  /**
   * @var array
   */
  private $membershipType;

  /**
   * @var array
   */
  private $membershipStatus;

  /**
   * @var array
   */
  private $relationshipType;

  public function setUp(): void {
    parent::setUp();

    $this->member = ContactFabricator::fabricate();
    $this->registerCurrentLoggedInContactInSession($this->member['id']);

    $this->membershipType = MembershipTypeFabricator::fabricate(['is_active' => 1]);
    $this->membershipStatus = MembershipStatusFabricator::fabricate(['is_active' => 1]);
    $this->relationshipType = RelationshipTypeFabricator::fabricate([
      'name_a_b' => 'Admin of',
      'name_b_a' => 'Admined by',
      'contact_type_a' => 'Individual',
      'contact_type_b' => 'Organization',
    ]);

    $this->organisationA = $this->createOrganisation('Organisation A');
    $this->organisationB = $this->createOrganisation('Organisation B');

    $this->createRelationship($this->organisationA['id']);
    $this->createRelationship($this->organisationB['id']);

    $this->createMembershipCertificateConfiguration();
    $this->createMembershipForOrganisation($this->organisationA['id']);
    $this->createMembershipForOrganisation($this->organisationB['id']);
  }

  public function tearDown(): void {
    $this->unregisterCurrentLoggedInContactFromSession();
    parent::tearDown();
  }

  public function testFilterCertificatesByPrimaryContactId(): void {
    $wrapper = new RelationshipCertificateWrapper();
    $baseParams = [
      'entity' => 'membership',
      'contact_id' => $this->member['id'],
    ];

    $allCertificates = $wrapper->getRelationshipCertificates($baseParams);
    $this->assertCount(2, $allCertificates);
    $relatedContacts = array_map('intval', array_column($allCertificates, 'related_contact'));
    $this->assertContains($this->organisationA['id'], $relatedContacts);
    $this->assertContains($this->organisationB['id'], $relatedContacts);

    $orgACertificates = $wrapper->getRelationshipCertificates($baseParams + ['primary_contact_id' => $this->organisationA['id']]);
    $this->assertCount(1, $orgACertificates);
    $this->assertSame($this->organisationA['id'], (int) $orgACertificates[0]['related_contact']);

    $unrelatedContact = ContactFabricator::fabricate();
    $this->assertSame([], $wrapper->getRelationshipCertificates($baseParams + ['primary_contact_id' => $unrelatedContact['id']]));
  }

  private function createOrganisation(string $name): array {
    $result = civicrm_api3('Contact', 'create', [
      'contact_type' => 'Organization',
      'organization_name' => $name,
      'display_name' => $name,
    ]);

    return array_shift($result['values']);
  }

  private function createRelationship(int $organisationId): void {
    RelationshipFabricator::fabricate([
      'contact_id_a' => $this->member['id'],
      'contact_id_b' => $organisationId,
      'relationship_type_id' => $this->relationshipType['id'],
    ]);
  }

  private function createMembershipCertificateConfiguration(): void {
    CertificateFabricator::fabricate(CertificateType::MEMBERSHIPS, [
      'linked_to' => [$this->membershipType['id']],
      'statuses' => [$this->membershipStatus['id']],
      'relationship_types' => [$this->relationshipType['id']],
    ]);
  }

  private function createMembershipForOrganisation(int $organisationId): void {
    MembershipFabricator::fabricate([
      'contact_id' => $organisationId,
      'membership_type_id' => $this->membershipType['id'],
      'status_id' => $this->membershipStatus['id'],
    ]);
  }

}
