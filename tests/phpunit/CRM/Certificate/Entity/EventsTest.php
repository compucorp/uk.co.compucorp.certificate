<?php

/**
 * Test events entity class
 *
 * @group headless
 */
class CRM_Certificate_Entity_EventTest extends BaseHeadlessTest {

  use CRM_Certificate_Test_Helper_Event;
  use CRM_Certificate_Test_Helper_Certificate;

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
   * Test event certificate respects configured event type filter.
   */
  public function testCertificateConfigurationRespectsEventType() {
    $contactId = CRM_Certificate_Test_Fabricator_Contact::fabricate()['id'];
    $eventTypeA = 1;
    $eventTypeB = 2;
    $eventA = CRM_Certificate_Test_Fabricator_Event::fabricate(['is_active' => 1, 'event_type_id' => $eventTypeA]);
    $eventB = CRM_Certificate_Test_Fabricator_Event::fabricate(['is_active' => 1, 'event_type_id' => $eventTypeB]);
    $statusId = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate(['is_active' => 1])['id'];

    $participantA = CRM_Certificate_Test_Fabricator_Participant::fabricate([
      'contact_id' => $contactId,
      'event_id' => $eventA['id'],
      'status_id' => $statusId,
      'role_id'  => [1, 2],
    ]);

    $participantB = CRM_Certificate_Test_Fabricator_Participant::fabricate([
      'contact_id' => $contactId,
      'event_id' => $eventB['id'],
      'status_id' => $statusId,
      'role_id'  => [1, 2],
    ]);

    $values = [
      'type' => CRM_Certificate_Enum_CertificateType::EVENTS,
      'linked_to' => [],
      'statuses' => [$statusId],
      'participant_type_id' => 1,
      'event_type_ids' => [$eventTypeA],
    ];
    $this->createCertificate($values);

    $eventEntity = new CRM_Certificate_Entity_Event();
    $matchingConfiguration = $eventEntity->getCertificateConfiguration($participantA['id'], $contactId);
    $nonMatchingConfiguration = $eventEntity->getCertificateConfiguration($participantB['id'], $contactId);

    $this->assertInstanceOf(CRM_Certificate_BAO_CompuCertificate::class, $matchingConfiguration);
    $this->assertFalse($nonMatchingConfiguration);
  }

  /**
   * Test event certificate requires both event and event type when both configured.
   */
  public function testCertificateRequiresMatchingEventAndEventTypeWhenBothProvided() {
    $contactId = CRM_Certificate_Test_Fabricator_Contact::fabricate()['id'];
    $eventType = 1;
    $eventA = CRM_Certificate_Test_Fabricator_Event::fabricate(['is_active' => 1, 'event_type_id' => $eventType]);
    $eventB = CRM_Certificate_Test_Fabricator_Event::fabricate(['is_active' => 1, 'event_type_id' => $eventType]);
    $eventC = CRM_Certificate_Test_Fabricator_Event::fabricate(['is_active' => 1, 'event_type_id' => 2]);
    $statusId = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate(['is_active' => 1])['id'];

    $participantA = CRM_Certificate_Test_Fabricator_Participant::fabricate([
      'contact_id' => $contactId,
      'event_id' => $eventA['id'],
      'status_id' => $statusId,
    ]);

    $participantB = CRM_Certificate_Test_Fabricator_Participant::fabricate([
      'contact_id' => $contactId,
      'event_id' => $eventB['id'],
      'status_id' => $statusId,
    ]);

    $participantC = CRM_Certificate_Test_Fabricator_Participant::fabricate([
      'contact_id' => $contactId,
      'event_id' => $eventC['id'],
      'status_id' => $statusId,
    ]);

    $values = [
      'type' => CRM_Certificate_Enum_CertificateType::EVENTS,
      'linked_to' => [$eventA['id']],
      'statuses' => [$statusId],
      'participant_type_id' => NULL,
      'event_type_ids' => [$eventType],
    ];
    $this->createCertificate($values);

    $eventEntity = new CRM_Certificate_Entity_Event();
    $matchingConfiguration = $eventEntity->getCertificateConfiguration($participantA['id'], $contactId);
    $configurationForOtherEvent = $eventEntity->getCertificateConfiguration($participantB['id'], $contactId);
    $configurationForOtherType = $eventEntity->getCertificateConfiguration($participantC['id'], $contactId);

    $this->assertInstanceOf(CRM_Certificate_BAO_CompuCertificate::class, $matchingConfiguration);
    $this->assertFalse($configurationForOtherEvent);
    $this->assertFalse($configurationForOtherType);
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

  /**
   * Test that only certificates wihthin the validitly period is returned.
   */
  public function testExpiredEventCertificatesAreNotReturned() {
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();

    $participant = $this->createParticipant(['contact_id' => $contact['id']]);
    $params = [
      'linked_to' => [$participant['event_id']],
      'statuses'  => [$participant['participant_status_id']],
    ];
    $validCertificate[] = $this->createEventCertificate(array_merge(['start_date' => date('Y-m-d')], $params));

    $participant = $this->createParticipant(['contact_id' => $contact['id']]);
    $params = [
      'linked_to' => [$participant['event_id']],
      'statuses'  => [$participant['participant_status_id']],
    ];
    $invalidCertificate[] = $this->createEventCertificate(array_merge(['start_date' => date('Y-m-d', strtotime(date('Y-m-d') . " 2 days"))], $params));

    $participant = $this->createParticipant(['contact_id' => $contact['id']]);
    $params = [
      'linked_to' => [$participant['event_id']],
      'statuses'  => [$participant['participant_status_id']],
    ];
    $invalidCertificate[] = $this->createEventCertificate(array_merge([
      'start_date' => $this->getDate("- 10 days"),
      'end_date' => $this->getDate("- 6 days"),
    ], $params));

    $eventEntity = new CRM_Certificate_Entity_Event();
    $avaliableCertificates = $eventEntity->getContactCertificates($contact["id"]);

    $this->assertEquals(count($validCertificate), count($avaliableCertificates));

    $expectedEventsId = array_column($validCertificate, "id");
    $avaliableCertificatesEventId = array_column($avaliableCertificates, "event_id");
    $this->assertCount(0, array_diff($expectedEventsId, $avaliableCertificatesEventId));
  }

  /**
   * Test that only certificates wihthin the validitly period is returned.
   *
   * @param string $startDate
   *  Validity start date.
   * @param string $endDate
   *  Validity end date.
   * @param boolean $valid
   *  If the date is considered valid.
   *
   * @dataProvider provideCertificateDateData
   */
  public function testEventCertificatesValidityByDate($startDate, $endDate, $valid) {
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();

    $participant = $this->createParticipant(['contact_id' => $contact['id']]);
    $params = [
      'linked_to' => [$participant['event_id']],
      'statuses'  => [$participant['participant_status_id']],
    ];
    $this->createEventCertificate(array_merge(['start_date' => $startDate, 'end_date' => $endDate], $params));
    $eventEntity = new CRM_Certificate_Entity_Event();
    $avaliableCertificates = $eventEntity->getContactCertificates($contact["id"]);

    $this->assertEquals(count($avaliableCertificates) > 0, $valid);
  }

  private function createCertificate($values = []) {
    return CRM_Certificate_Test_Fabricator_CompuCertificate::fabricate($values['type'], $values);
  }

}
