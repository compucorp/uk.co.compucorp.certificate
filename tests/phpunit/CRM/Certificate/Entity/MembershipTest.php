<?php

/**
 * Test membership entity class
 *
 * @group headless
 */
class CRM_Certificate_Entity_MembershipTest extends BaseHeadlessTest {

  use CRM_Certificate_Test_Helper_Membership;

  /**
   * Test the appropraite types are returned
   *  i.e. only active types are returned
   */
  public function testGetTypesReturnsActiveOnes() {
    $inactiveType = CRM_Certificate_Test_Fabricator_MembershipType::fabricate(['is_active' => 0])['id'];
    $activeType = CRM_Certificate_Test_Fabricator_MembershipType::fabricate(['is_active' => 1])['id'];

    $membershipEntity = new CRM_Certificate_Entity_Membership();
    $types = $membershipEntity->getTypes();

    $this->assertTrue(is_array($types));
    $this->assertTrue(!empty(array_diff([$inactiveType], $types)));
    $this->assertTrue(empty(array_diff([$activeType], $types)));
  }

  /**
   * Test the appropraite statuses are returned
   *  i.e. only active statuses are returned
   */
  public function testGetStatusesReturnsActiveOnes() {
    $inactiveStatus = CRM_Certificate_Test_Fabricator_MembershipStatus::fabricate(['is_active' => 0])['id'];
    $activeStatus = CRM_Certificate_Test_Fabricator_MembershipStatus::fabricate(['is_active' => 1])['id'];

    $membershipEntity = new CRM_Certificate_Entity_Membership();
    $statuses = $membershipEntity->getStatuses();

    $this->assertTrue(is_array($statuses));
    $this->assertTrue(!empty(array_diff([$inactiveStatus], $statuses)));
    $this->assertTrue(empty(array_diff([$activeStatus], $statuses)));
  }

  /**
   * Test that only the statuses configured for a membership certificate is returned
   */
  public function testOnlyStatusesConfiguredForCertificateAreReturned() {
    $membershipType = CRM_Certificate_Test_Fabricator_MembershipType::fabricate();
    $membershipStatus = CRM_Certificate_Test_Fabricator_MembershipStatus::fabricate();

    $values = [
      'type' => CRM_Certificate_Enum_CertificateType::MEMBERSHIPS,
      'linked_to' => [$membershipType['id']],
      'statuses' => [$membershipStatus['id']],
    ];

    $expectedStatus = ['id' => $membershipStatus['id'], 'label' => $membershipStatus['label']];

    $certificate = $this->createCertificate($values)['certificate'];

    $membership = new CRM_Certificate_Entity_Membership();
    $statuses = $membership->getCertificateConfiguredStatuses($certificate->id);

    $this->assertTrue(is_array($statuses));
    $this->assertCount(1, $statuses);
    $this->assertContains($expectedStatus, $statuses);
  }

  /**
   * Test that only the types configured for a membership certificate is returned
   */
  public function testOnlyTypesConfiguredForCertificateAreReturned() {
    $membershipType = CRM_Certificate_Test_Fabricator_MembershipType::fabricate();
    $membershipStatus = CRM_Certificate_Test_Fabricator_MembershipStatus::fabricate();

    $values = [
      'type' => CRM_Certificate_Enum_CertificateType::MEMBERSHIPS,
      'linked_to' => [$membershipType['id']],
      'statuses' => [$membershipStatus['id']],
    ];

    $expectedType = ['id' => $membershipType['id'], 'label' => $membershipType['name']];

    $certificate = $this->createCertificate($values)['certificate'];

    $membership = new CRM_Certificate_Entity_Membership();
    $types = $membership->getCertificateConfiguredTypes($certificate->id);

    $this->assertTrue(is_array($types));
    $this->assertCount(1, $types);
    $this->assertContains($expectedType, $types);
  }

  /**
   * Test that a certificate configuration is returned
   * for a membership that meets the status and type of the
   * certificate configuration
   */
  public function testCanGetMembershipCertificateConfiguration() {
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $membershipType = CRM_Certificate_Test_Fabricator_MembershipType::fabricate();
    $membershipStatus = CRM_Certificate_Test_Fabricator_MembershipStatus::fabricate();

    $membership = CRM_Certificate_Test_Fabricator_Membership::fabricate(
      [
        'status_id' => $membershipStatus['id'],
        'contact_id' => $contact['id'],
        'creator_id' => $contact['id'],
        'membership_type_id' => $membershipType['id'],
      ]
    );

    $values = [
      'type' => CRM_Certificate_Enum_CertificateType::MEMBERSHIPS,
      'linked_to' => [$membershipType['id']],
      'statuses' => [$membershipStatus['id']],
    ];
    $this->createCertificate($values);

    $membershipEntity = new CRM_Certificate_Entity_Membership();
    $configuration = $membershipEntity->getCertificateConfiguration($membership["id"], $contact['id']);

    $this->assertInstanceOf(CRM_Certificate_BAO_CompuCertificate::class, $configuration);
  }

  /**
   * Test that a certificacte configuration is not returned
   * when the membership status and type of the certificate is not met
   */
  public function testCertificationConfigurationNotReturnedIfNoCertificateIsConfigured() {
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $membershipStatus = CRM_Certificate_Test_Fabricator_MembershipStatus::fabricate();
    $membershipType = CRM_Certificate_Test_Fabricator_MembershipType::fabricate();

    $membership = CRM_Certificate_Test_Fabricator_Membership::fabricate(
      [
        'status_id' => (int) $membershipStatus['id'],
        'contact_id' => $contact['id'],
        'membership_type_id' => (int) $membershipType['id'],
      ]
    );
    $membershipEntity = new CRM_Certificate_Entity_Membership();
    $configuration = $membershipEntity->getCertificateConfiguration($membership["id"], $contact['id']);

    $this->assertFalse($configuration);
  }

  private function createCertificate($values = []) {
    return CRM_Certificate_Test_Fabricator_CompuCertificate::fabricate(CRM_Certificate_Enum_CertificateType::MEMBERSHIPS, $values);
  }

}
