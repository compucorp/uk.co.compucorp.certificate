<?php

/**
 * Tests event certificate filtering by event type.
 *
 * @group headless
 */
class CRM_Certificate_Entity_EventTypeCertificateTest extends BaseHeadlessTest {

  /**
   * Ensures a certificate configured only with event types matches participants of matching events.
   */
  public function testCertificateMatchesParticipantByEventType() {
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $status = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate(['is_active' => 1]);
    $eventTypeId = $this->createEventType('Event Type Match');
    $event = CRM_Certificate_Test_Fabricator_Event::fabricate([
      'is_active' => 1,
      'event_type_id' => $eventTypeId,
    ]);
    $participant = CRM_Certificate_Test_Fabricator_Participant::fabricate([
      'contact_id' => $contact['id'],
      'event_id' => $event['id'],
      'status_id' => $status['id'],
    ]);

    CRM_Certificate_Test_Fabricator_CompuCertificate::fabricateEventCertificate([
      'linked_to' => [],
      'statuses' => [$status['id']],
      'event_type_ids' => [$eventTypeId],
      'participant_type_id' => 1,
    ]);

    $eventEntity = new CRM_Certificate_Entity_Event();
    $configuration = $eventEntity->getCertificateConfiguration($participant['id'], $contact['id']);

    $this->assertInstanceOf(CRM_Certificate_BAO_CompuCertificate::class, $configuration);
  }

  /**
   * Ensures certificates scoped to event types do not match events of other types.
   */
  public function testCertificateDoesNotMatchWhenEventTypeDiffers() {
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $status = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate(['is_active' => 1]);
    $matchingTypeId = $this->createEventType('Matching Type');
    $otherTypeId = $this->createEventType('Different Type');

    $event = CRM_Certificate_Test_Fabricator_Event::fabricate([
      'is_active' => 1,
      'event_type_id' => $otherTypeId,
    ]);
    $participant = CRM_Certificate_Test_Fabricator_Participant::fabricate([
      'contact_id' => $contact['id'],
      'event_id' => $event['id'],
      'status_id' => $status['id'],
    ]);

    CRM_Certificate_Test_Fabricator_CompuCertificate::fabricateEventCertificate([
      'linked_to' => [],
      'statuses' => [$status['id']],
      'event_type_ids' => [$matchingTypeId],
      'participant_type_id' => 1,
    ]);

    $eventEntity = new CRM_Certificate_Entity_Event();
    $configuration = $eventEntity->getCertificateConfiguration($participant['id'], $contact['id']);

    $this->assertFalse($configuration);
  }

  /**
   * Certificates configured with both event ID and event type must satisfy both filters.
   */
  public function testCertificateMatchesOnlyWhenEventAndTypeMatch() {
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $status = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate(['is_active' => 1]);
    $matchingTypeId = $this->createEventType('Conference Type');
    $nonMatchingTypeId = $this->createEventType('Workshop Type');

    $event = CRM_Certificate_Test_Fabricator_Event::fabricate([
      'is_active' => 1,
      'event_type_id' => $matchingTypeId,
    ]);
    $participant = CRM_Certificate_Test_Fabricator_Participant::fabricate([
      'contact_id' => $contact['id'],
      'event_id' => $event['id'],
      'status_id' => $status['id'],
    ]);

    $eventEntity = new CRM_Certificate_Entity_Event();

    CRM_Certificate_Test_Fabricator_CompuCertificate::fabricateEventCertificate([
      'linked_to' => [$event['id']],
      'statuses' => [$status['id']],
      'event_type_ids' => [$nonMatchingTypeId],
      'participant_type_id' => 1,
    ]);

    $configurationMismatch = $eventEntity->getCertificateConfiguration($participant['id'], $contact['id']);
    $this->assertFalse($configurationMismatch);

    $matchingCertificate = CRM_Certificate_Test_Fabricator_CompuCertificate::fabricateEventCertificate([
      'linked_to' => [$event['id']],
      'statuses' => [$status['id']],
      'event_type_ids' => [$matchingTypeId],
      'participant_type_id' => 1,
    ]);

    $configuration = $eventEntity->getCertificateConfiguration($participant['id'], $contact['id']);

    $this->assertInstanceOf(CRM_Certificate_BAO_CompuCertificate::class, $configuration);
    $this->assertEquals($matchingCertificate['certificate']->id, $configuration->id);
  }

  /**
   * Creates an active event type and returns its option value ID.
   *
   * @param string $label
   *
   * @return int
   */
  private function createEventType($label) {
    $result = civicrm_api3('OptionValue', 'create', [
      'option_group_id' => 'event_type',
      'label' => $label,
      'name' => CRM_Utils_String::munge($label . '_' . uniqid()),
      'value' => CRM_Utils_String::munge($label . '_' . uniqid()),
      'is_active' => 1,
    ]);

    $optionValue = array_shift($result['values']);

    return (int) $optionValue['id'];
  }

}
