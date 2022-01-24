<?php

/**
 * Test events entity class
 *
 * @group headless
 */
class CRM_Certificate_Entity_EventTest extends BaseHeadlessTest {

  /**
   * Test the appropraite types are returned
   *  i.e. only active types are returned.
   */
  public function testGetTypesReturnsActiveOnes() {
    $inactiveType = CRM_Certificate_Test_Fabricator_Event::fabricate(['is_active' => 0])['id'];
    $activeType = CRM_Certificate_Test_Fabricator_Event::fabricate(['is_active' => 1])['id'];

    $eventEntity = new CRM_Certificate_Entity_Event();
    $types = $eventEntity->getTypes();

    $this->assertTrue(is_array($types));
    $this->assertTrue(!empty(array_diff([$inactiveType], $types)));
    $this->assertTrue(empty(array_diff([$activeType], $types)));
  }

  /**
   * Test the appropraite statuses are returned
   *  i.e. only active statuses are returned.
   */
  public function testGetStatusesReturnsActiveOnes() {
    $inactiveStatus = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate(['is_active' => 0])['id'];
    $activeStatus = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate(['is_active' => 1])['id'];

    $eventEntity = new CRM_Certificate_Entity_Event();
    $statuses = $eventEntity->getStatuses();

    $this->assertTrue(is_array($statuses));
    $this->assertTrue(!empty(array_diff([$inactiveStatus], $statuses)));
    $this->assertTrue(empty(array_diff([$activeStatus], $statuses)));
  }

  /**
   * Test that a certificate configuration is returned
   * for a participant that meets the status and type of the
   * certificate configuration
   */
  public function testCanGetParticipantCertificateConfiguration() {
    $contactId = CRM_Certificate_Test_Fabricator_Contact::fabricate()['id'];
    $eventId = CRM_Certificate_Test_Fabricator_Event::fabricate(['is_active' => 1])['id'];
    $statusId = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate(['is_active' => 1])['id'];

    $params = [
      'contact_id' => $contactId,
      'event_id' => $eventId,
      'status_id' => $statusId,
    ];
    $participantId = CRM_Certificate_Test_Fabricator_Participant::fabricate($params)['id'];

    $values = [
      'type' => CRM_Certificate_Enum_CertificateType::EVENTS,
      'linked_to' => [$eventId],
      'statuses' => [$statusId],
      'participant_type_id' => NULL,
    ];
    $this->createCertificate($values);

    $eventEntity = new CRM_Certificate_Entity_Event();
    $configuration = $eventEntity->getCertificateConfiguration($participantId, $contactId);

    $this->assertInstanceOf(CRM_Certificate_BAO_CompuCertificate::class, $configuration);
  }

  /**
   * Test that a certificate configuration is returned
   * for a participant with multiple roles where one of
   * the roles matches that of the configured certificate.
   */
  public function testCanGetCertificateConfigurationForParticipantWithMultipleRoles() {
    $contactId = CRM_Certificate_Test_Fabricator_Contact::fabricate()['id'];
    $eventId = CRM_Certificate_Test_Fabricator_Event::fabricate(['is_active' => 1])['id'];
    $statusId = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate(['is_active' => 1])['id'];

    $params = [
      'contact_id' => $contactId,
      'event_id' => $eventId,
      'status_id' => $statusId,
      'role_id'  => [1, 2],
    ];
    $participantId = CRM_Certificate_Test_Fabricator_Participant::fabricate($params)['id'];

    $values = [
      'type' => CRM_Certificate_Enum_CertificateType::EVENTS,
      'linked_to' => [$eventId],
      'statuses' => [$statusId],
      'participant_type_id' => 1,
    ];
    $this->createCertificate($values);

    $eventEntity = new CRM_Certificate_Entity_Event();
    $configuration = $eventEntity->getCertificateConfiguration($participantId, $contactId);

    $this->assertInstanceOf(CRM_Certificate_BAO_CompuCertificate::class, $configuration);
  }

  /**
   * Test that a certificacte configuration is not returned
   * when the case status and type of the certificate is not met
   */
  public function testParticipantCertificateConfigurationNotReturnedIfNoCertificateIsConfigured() {
    $contactId = CRM_Certificate_Test_Fabricator_Contact::fabricate()['id'];
    $eventId = CRM_Certificate_Test_Fabricator_Event::fabricate(['is_active' => 1])['id'];
    $statusId = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate(['is_active' => 1])['id'];

    $params = [
      'contact_id' => $contactId,
      'event_id' => $eventId,
      'status_id' => $statusId,
    ];
    $participantId = CRM_Certificate_Test_Fabricator_Participant::fabricate($params)['id'];

    $eventEntity = new CRM_Certificate_Entity_Event();
    $configuration = $eventEntity->getCertificateConfiguration($participantId, $contactId);

    $this->assertFalse($configuration);
  }

  private function createCertificate($values = []) {
    return CRM_Certificate_Test_Fabricator_CompuCertificate::fabricate($values['type'], $values);
  }

}
