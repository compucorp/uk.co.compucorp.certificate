<?php

use CRM_Certificate_Enum_CertificateType as CertificateType;
use CRM_Certificate_Test_Fabricator_CompuCertificate as CompuCertificateFabricator;

trait CRM_Certificate_Test_Helper_Event {

  private function createParticipant($params = []) {
    $contact = CRM_Certificate_Test_Fabricator_Contact::fabricate();
    $event = CRM_Certificate_Test_Fabricator_Event::fabricate(['is_active' => 1]);
    $participantStatus = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate();

    $params = array_merge([
      'contact_id' => $contact['id'],
      'event_id' => $event["id"],
      'particpant_status_id'  => $participantStatus["id"],
    ], $params);

    $participant = CRM_Certificate_Test_Fabricator_Participant::fabricate($params);

    $participant = civicrm_api3('Participant', 'getsingle', [
      'contact_id' => $params['contact_id'],
      'id' => $participant['id'],
    ]);

    $participant["contact"] = $contact;
    $participant["event"] = $event;

    return $participant;
  }

  private function createEventCertificate($params = []) {
    $event = CRM_Certificate_Test_Fabricator_Event::fabricate(['is_active' => 1]);
    $statuses = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate()['id'];

    $values = array_merge([
      'type' => CertificateType::EVENTS,
      'linked_to' => $event['id'],
      'statuses' => $statuses,
      'participant_type_id' => 1,
    ], $params);

    return CompuCertificateFabricator::fabricate(CertificateType::EVENTS, $values);
  }

}
