<?php

/**
 * Test service class for storing new membership cretificate configuration.
 *
 * @group headless
 */
class CRM_Certificate_Service_CertificateMembershipTest extends BaseHeadlessTest {

  /**
   * Test create new instance of membership certificate configuration.
   */
  public function testCreateMembershipCertificateConfiguration() {
    $statuses[] = CRM_Certificate_Test_Fabricator_MembershipStatus::fabricate()['id'];
    $types[] = CRM_Certificate_Test_Fabricator_MembershipType::fabricate()['id'];

    $certificateConfiguration = [
      'name' => 'test cert',
      'type' => CRM_Certificate_Enum_CertificateType::MEMBERSHIPS,
      'message_template_id'  => 1,
      'statuses' => $statuses,
      'linked_to' => $types,
      'start_date' => date('Y-m-d'),
      'end_date' => date('Y-m-d', strtotime(date('Y-m-d') . " 2 days")),
    ];

    $certificateCreator = new CRM_Certificate_Service_CertificateMembership();
    $result = $certificateCreator->store($certificateConfiguration);

    $this->assertTrue(is_array($result));
    $this->assertArrayHasKey('certificate', $result);
  }

  /**
   * Test new instance of membership certificate configuration is created
   * for empty status and type.
   */
  public function testCreateEventCertificateConfigurationForEmptyStatusAndType() {
    $certificateConfiguration = [
      'name' => 'test cert',
      'type' => CRM_Certificate_Enum_CertificateType::MEMBERSHIPS,
      'message_template_id'  => 1,
      'statuses' => [],
      'linked_to' => [],
      'participant_type_id' => 1,
      'start_date' => date('Y-m-d'),
      'end_date' => date('Y-m-d'),
    ];

    $certificateCreator = new CRM_Certificate_Service_CertificateMembership();
    $result = $certificateCreator->store($certificateConfiguration);

    $this->assertTrue(is_array($result));
    $this->assertArrayHasKey('certificate', $result);
  }

  /**
   * Test that duplicate certifiacte configuration
   * cannot be created for the same entity.
   */
  public function testExceptionNotThrownForDuplicateCertificateMembershipConfiguration() {
    $statuses = CRM_Certificate_Test_Fabricator_MembershipStatus::fabricate()['id'];
    $types = CRM_Certificate_Test_Fabricator_MembershipType::fabricate()['id'];

    $values = [
      'name' => 'test cert',
      'type' => CRM_Certificate_Enum_CertificateType::MEMBERSHIPS,
      'statuses' => $statuses,
      'linked_to' => $types,
      'start_date' => date('Y-m-d'),
      'end_date' => date('Y-m-d'),
    ];

    $cert1 = $this->createCertificate($values);
    $cert2 = $this->createCertificate($values);

    $this->assertNotEmpty($cert1);
    $this->assertNotEmpty($cert2);
  }

  /**
   * Test that duplicate certifiacte configuration
   * cannot be created for the same entity.
   */
  public function testExceptionNotThrownForDifferentCertificateEventConfiguration() {
    $statuses = CRM_Certificate_Test_Fabricator_MembershipStatus::fabricate()['id'];
    $types = CRM_Certificate_Test_Fabricator_MembershipType::fabricate()['id'];

    $values = [
      'name' => 'test cert',
      'type' => CRM_Certificate_Enum_CertificateType::MEMBERSHIPS,
      'statuses' => $statuses,
      'linked_to' => $types,
      'start_date' => date('Y-m-d'),
      'end_date' => date('Y-m-d'),
    ];

    $this->createCertificate($values);

    $newStatuses = CRM_Certificate_Test_Fabricator_MembershipStatus::fabricate()['id'];

    $values = [
      'name' => 'test cert',
      'type' => CRM_Certificate_Enum_CertificateType::MEMBERSHIPS,
      'statuses' => $newStatuses,
      'linked_to' => $types,
    ];
    $result = $this->createCertificate($values);
    $this->assertTrue(is_array($result));
  }

  private function createCertificate($values = []) {
    return CRM_Certificate_Test_Fabricator_CompuCertificate::fabricate(CRM_Certificate_Enum_CertificateType::MEMBERSHIPS, $values);
  }

}
