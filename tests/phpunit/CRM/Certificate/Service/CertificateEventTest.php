<?php

/**
 * Test service class for storing new event cretificate configuration.
 *
 * @group headless
 */
class CRM_Certificate_Service_CertificateEventTest extends BaseHeadlessTest {

  /**
   * Test create new instance of event certificate configuration.
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
      'participant_type_id' => 1,
    ];

    $certificateCreator = new CRM_Certificate_Service_CertificateEvent();
    $result = $certificateCreator->store($certificateConfiguration);

    $this->assertTrue(is_array($result));
    $this->assertArrayHasKey('certificate', $result);
  }

  /**
   * Test event attribute is created when creating a new event certificate configuration.
   */
  public function testEventAttributeIsCreatedForEventCertificateConfiguration() {

    $statuses[] = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate()['id'];
    $types[] = CRM_Certificate_Test_Fabricator_Event::fabricate()['id'];

    $certificateConfiguration = [
      'name' => 'test cert',
      'type' => CRM_Certificate_Enum_CertificateType::EVENTS,
      'message_template_id'  => 1,
      'statuses' => $statuses,
      'linked_to' => $types,
      'participant_type_id' => 1,
    ];

    $certificateCreator = new CRM_Certificate_Service_CertificateEvent();
    $result = $certificateCreator->store($certificateConfiguration);

    $this->assertArrayHasKey('eventAttribute', $result);
    $this->assertArrayHasKey('participant_type_id', $result['eventAttribute']);
  }

  /**
   * Test new instance of event certificate configuration is created
   * for empty status and type.
   */
  public function testCreateEventCertificateConfigurationForEmptyStatusAndType() {
    $certificateConfiguration = [
      'name' => 'test cert',
      'type' => CRM_Certificate_Enum_CertificateType::EVENTS,
      'message_template_id'  => 1,
      'statuses' => [],
      'linked_to' => [],
      'participant_type_id' => 1,
    ];

    $certificateCreator = new CRM_Certificate_Service_CertificateEvent();
    $result = $certificateCreator->store($certificateConfiguration);

    $this->assertTrue(is_array($result));
    $this->assertArrayHasKey('certificate', $result);
  }

  /**
   * Test that duplicate certifiacte configuration
   * cannot be created for the same entity.
   */
  public function testExceptionThrownForDuplicateCertificateEventConfiguration() {
    $this->expectException(CRM_Certificate_Exception_ConfigurationExistException::class);
    $statuses = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate()['id'];
    $types = CRM_Certificate_Test_Fabricator_Event::fabricate()['id'];

    $values = [
      'name' => 'test cert',
      'type' => CRM_Certificate_Enum_CertificateType::EVENTS,
      'statuses' => $statuses,
      'linked_to' => $types,
      'participant_type_id' => 1,
    ];

    $this->createCertificate($values);
    $this->createCertificate($values);
  }

  /**
   * Test that duplicate certifiacte configuration
   * cannot be created for the same entity.
   */
  public function testExceptionNotThrownForDifferentCertificateEventConfiguration() {
    $statuses = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate()['id'];
    $types = CRM_Certificate_Test_Fabricator_Event::fabricate()['id'];

    $values = [
      'name' => 'test cert',
      'type' => CRM_Certificate_Enum_CertificateType::EVENTS,
      'statuses' => $statuses,
      'linked_to' => $types,
      'participant_type_id' => 1,
    ];

    $this->createCertificate($values);

    $newStatuses = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate()['id'];

    $values = [
      'name' => 'test cert',
      'type' => CRM_Certificate_Enum_CertificateType::EVENTS,
      'statuses' => $newStatuses,
      'linked_to' => $types,
      'participant_type_id' => 1,
    ];
    $result = $this->createCertificate($values);
    $this->assertTrue(is_array($result));
  }

  private function createCertificate($values = []) {
    return CRM_Certificate_Test_Fabricator_CompuCertificate::fabricate(CRM_Certificate_Enum_CertificateType::EVENTS, $values);
  }

}
