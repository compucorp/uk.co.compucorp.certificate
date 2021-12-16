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

  /**
   * Test that only the membership certificate for contact with the Id
   * passed is returned.
   */
  public function testOnlyMembershipCertificateForContactIsReturned() {
    // Create Membership certificates for contact 1.
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $contactMembership = array_map(function () use ($contact) {
      $membership = $this->createMembership(['contact_id' => $contact["id"]]);
      $this->createMembershipCertificate(
        [
          'linked_to' => [$membership['membership_type_id']],
          'statuses'  => [$membership['status_id']],
        ]
      );
    }, [1, 2]);

    // Create Membership certificates for contact 2.
    $otherContact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $membership = $this->createMembership(['contact_id' => $otherContact["id"]]);
    $this->createMembershipCertificate(
      [
        'linked_to' => [$membership['membership_type_id']],
        'statuses'  => [$membership['status_id']],
      ]
    );

    $entity = new CRM_Certificate_Entity_Membership();

    // Retrieve certificates for contact 1.
    $avaliableCertificates = $entity->getContactCertificates($contact["id"]);
    $expectedMembershipsId = array_column($contactMembership, "id");
    $avaliableCertificatesMembershipId = array_column($avaliableCertificates, "membership_id");

    // Assert only contact 1 certificates are returned.
    $this->assertCount(0, array_diff($expectedMembershipsId, $avaliableCertificatesMembershipId));
  }

  /**
   * Test that the expected number of certificate returned is what is expected.
   */
  public function testExpectedNumberOfMembershipCertificateForContactIsReturned() {
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    array_map(function () use ($contact) {
      $membership = $this->createMembership(['contact_id' => $contact["id"]]);
      $this->createMembershipCertificate(
        [
          'linked_to' => [$membership['membership_type_id']],
          'statuses'  => [$membership['status_id']],
        ]
      );
    }, [1, 2, 3, 4]);

    $entity = new CRM_Certificate_Entity_Membership();
    $avaliableCertificates = $entity->getContactCertificates($contact["id"]);

    $this->assertEquals(4, count($avaliableCertificates));
  }

  /**
   * Test contact certificate is returned for a certificate
   * configured with all status and/or all type.
   */
  public function testMembershipGetContactCertificateReturnsCertificateForBlankStatusAndType() {
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $membership = $this->createMembership(['contact_id' => $contact["id"]]);

    $this->createCertificate(
      [
        'linked_to' => NULL,
        'statuses'  => NULL,
      ]
    );

    $entity = new CRM_Certificate_Entity_Membership();
    $avaliableCertificates = $entity->getContactCertificates($contact["id"]);

    $this->assertEquals(1, count($avaliableCertificates));
    $this->assertEquals($membership['id'], $avaliableCertificates[0]['membership_id']);
  }

  /**
   * Test certificate configuration is returned when a certificate
   * with all status and/or all type is configured.
   */
  public function testMembershipGetCertificateConfigurationReturnsCertificateForBlankStatusAndType() {
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $membership = $this->createMembership(['contact_id' => $contact["id"]]);

    $configuration = $this->createCertificate(
      [
        'linked_to' => NULL,
        'statuses'  => NULL,
      ]
    );

    $entity = new CRM_Certificate_Entity_Membership();
    $availableCertificate = $entity->getCertificateConfiguration($membership['id'], $contact["id"]);

    $this->assertEquals($configuration['certificate']->id, $availableCertificate->id);
  }

  private function createCertificate($values) {
    $values['type'] = CRM_Certificate_Enum_CertificateType::MEMBERSHIPS;
    $values['name'] = md5(mt_rand());
    $values['message_template_id']  = 1;
    $storeCertificate = new CRM_Certificate_Service_CertificateMembership();

    return $storeCertificate->store($values);
  }

}
