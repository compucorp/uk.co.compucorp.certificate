<?php

/**
 * Test service class for storing new cretificate
 *
 * @group headless
 */
class CRM_Certificate_Service_CertificateTest extends BaseHeadlessTest {

  /**
   * Test new instance of case certificate configuration is created.
   */
  public function testCreateCaseCertificateConfiguration() {
    $caseStatus[] = CRM_Certificate_Test_Fabricator_CaseStatus::fabricate()['value'];
    $caseType[] = CRM_Certificate_Test_Fabricator_CaseType::fabricate()['id'];

    $certificateConfiguration = [
      'name' => 'test cert',
      'type' => CRM_Certificate_Enum_CertificateType::CASES,
      'message_template_id'  => 1,
      'statuses' => $caseStatus,
      'linked_to' => $caseType,
    ];

    $certificateCreator = new CRM_Certificate_Service_Certificate();
    $result = $certificateCreator->store($certificateConfiguration);

    $this->assertTrue(is_array($result));
    $this->assertArrayHasKey('statuses', $result);
    $this->assertArrayHasKey('certificate', $result);
    $this->assertArrayHasKey('entityTypes', $result);
  }

  /**
   * Test that duplicate certifiacte configuration
   * cannot be created for the same entity
   */
  public function testExceptionThrownForDuplicateCertificateConfiguration() {
    $this->expectException(CRM_Certificate_Exception_ConfigurationExistException::class);

    $caseStatus[] = CRM_Certificate_Test_Fabricator_CaseStatus::fabricate()['value'];
    $caseType[] = CRM_Certificate_Test_Fabricator_CaseType::fabricate()['id'];

    $values = [
      'type' => CRM_Certificate_Enum_CertificateType::CASES,
      'linked_to' => $caseType,
      'statuses' => $caseStatus,
    ];

    $this->createCertificate($values);

    $values = [
      'type' => CRM_Certificate_Enum_CertificateType::CASES,
      'linked_to' => $caseType,
      'statuses' => $caseStatus,
    ];
    $this->createCertificate($values);
  }

  /**
   * Test that duplicate certifiacte configuration
   * cannot be created for the same entity
   */
  public function testExceptionNotThrownForDifferentCertificateConfiguration() {
    $caseStatus[] = CRM_Certificate_Test_Fabricator_CaseStatus::fabricate()['value'];
    $caseType[] = CRM_Certificate_Test_Fabricator_CaseType::fabricate()['id'];

    $values = [
      'type' => CRM_Certificate_Enum_CertificateType::CASES,
      'linked_to' => $caseType,
      'statuses' => $caseStatus,
    ];

    $this->createCertificate($values);

    $newCaseStatus[] = CRM_Certificate_Test_Fabricator_CaseStatus::fabricate()['value'];

    $values = [
      'type' => CRM_Certificate_Enum_CertificateType::CASES,
      'linked_to' => $caseType,
      'statuses' => $newCaseStatus,
    ];
    $result = $this->createCertificate($values);
    $this->assertTrue(is_array($result));
  }

  /**
   * Test create new instance of event certificate configuration
   */
  public function testCreateEventCertificateConfiguration() {
    $statuses[] = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate()['id'];
    $types[] = CRM_Certificate_Test_Fabricator_Event::fabricate()['id'];

    $certificateConfiguration = [
      'name' => 'test cert',
      'type' => CRM_Certificate_Enum_CertificateType::EVENTS,
      'message_template_id'  => 1,
      'statuses' => $statuses,
      'linked_to' => $types,
    ];

    $certificateCreator = new CRM_Certificate_Service_Certificate();
    $result = $certificateCreator->store($certificateConfiguration);

    $this->assertTrue(is_array($result));
    $this->assertArrayHasKey('certificate', $result);
  }

  /**
   * Test new instance of event certificate configuration is created
   * for empty status and type
   */
  public function testCreateEventCertificateConfigurationForEmptyStatusAndType() {
    $certificateConfiguration = [
      'name' => 'test cert',
      'type' => CRM_Certificate_Enum_CertificateType::EVENTS,
      'message_template_id'  => 1,
      'statuses' => [],
      'linked_to' => [],
    ];

    $certificateCreator = new CRM_Certificate_Service_Certificate();
    $result = $certificateCreator->store($certificateConfiguration);

    $this->assertTrue(is_array($result));
    $this->assertArrayHasKey('certificate', $result);
  }

  private function createCertificate($values = []) {
    return CRM_Certificate_Test_Fabricator_CompuCertificate::fabricate(CRM_Certificate_Enum_CertificateType::CASES, $values);
  }

}
