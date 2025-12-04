<?php

use CRM_Certificate_Enum_CertificateType as CertificateType;
use CRM_Certificate_BAO_CompuCertificate as CompuCertificate;

class CRM_Certificate_Entity_Event extends CRM_Certificate_Entity_AbstractEntity {

  /**
   * {@inheritDoc}
   */
  public function store($values) {
    return (new CRM_Certificate_Service_CertificateEvent())->store($values);
  }

  /**
   * {@inheritdoc}
   */
  public function getTypes() {
    try {
      $result = civicrm_api3('event', 'get', [
        'sequential' => 1,
        'is_active' => 1,
        'return' => ["id"],
        'options' => ['limit' => 0],
      ]);

      if ($result["is_error"]) {
        return NULL;
      }

      return array_column($result["values"], 'id');
    }
    catch (\Throwable $th) {
      return [];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getStatuses() {
    try {
      $result = civicrm_api3('ParticipantStatusType', 'get', [
        'sequential' => 1,
        'is_active' => 1,
        'return' => ["id"],
        'options' => ['limit' => 0],
      ]);

      if ($result["is_error"]) {
        return NULL;
      }

      return array_column($result["values"], 'id');
    }
    catch (\Throwable $th) {
      return [];
    }
  }

  /**
   * {@inheritDoc}
   */
  public function getCertificateConfiguredStatuses($certificateId) {
    $statusBAO = new CRM_Certificate_BAO_CompuCertificateStatus();

    $statusBAO->whereAdd("certificate_id = " . $certificateId);
    $statusBAO->joinAdd(['status_id', new CRM_Event_DAO_ParticipantStatusType(), 'id']);
    $statusBAO->find();
    $statuses = $statusBAO->fetchAll('id');

    $statuses = array_map(function ($status) {
        return [
          "id" => $status["id"],
          "label" => $status["label"],
        ];
    }, $statuses);

    return $statuses;
  }

  /**
   * {@inheritDoc}
   */
  public function getCertificateConfiguredTypes($certificateId) {
    $entityTypeBAO = new CRM_Certificate_BAO_CompuCertificateEntityType();
    $entityTypeBAO->whereAdd("certificate_id = " . $certificateId);

    $entityType = new CRM_Event_DAO_Event();
    $entityTypeBAO->joinAdd(['entity_type_id', $entityType, 'id']);
    $entityTypeBAO->find();
    $entityTypes = $entityTypeBAO->fetchAll('id');

    $entityTypes = array_map(function ($entityType) {
      return [
        "id" => $entityType["id"],
        "label" => $entityType["title"],
      ];
    }, $entityTypes);

    return $entityTypes;
  }

  /**
   * {@inheritDoc}
   */
  protected function  addEntityExtraField($certificateBAO, &$certificate) {
    $eventAttribute = $this->getCertificateEventAttribute($certificateBAO->id);
    $certificate['participant_type_id'] = implode(', ', array_column($eventAttribute, 'participant_type_id'));
    $certificate['event_type_ids'] = implode(',', $this->getEventAttributeEventTypeIds($eventAttribute));
  }

  private function getCertificateEventAttribute($certificateId) {
    $eventAttribute = new CRM_Certificate_BAO_CompuCertificateEventAttribute();
    $eventAttribute->whereAdd("certificate_id = " . $certificateId);
    $eventAttribute->find();

    return $eventAttribute->fetchAll();
  }

  /**
   * Retrieves event type ids from an event attribute configuration.
   *
   * @param array $eventAttribute
   *
   * @return array
   */
  private function getEventAttributeEventTypeIds(array $eventAttribute) {
    $eventTypeIds = [];

    foreach ($eventAttribute as $attribute) {
      if (!empty($attribute['event_type_ids'])) {
        $eventTypeIds = array_merge($eventTypeIds, explode(',', $attribute['event_type_ids']));
      }
    }

    return $this->sanitizeEventTypeIds($eventTypeIds);
  }

  /**
   * Sanitizes event type ids and removes deleted values.
   *
   * @param array $eventTypeIds
   *
   * @return array
   */
  private function sanitizeEventTypeIds(array $eventTypeIds) {
    $eventTypes = CRM_Core_OptionGroup::values('event_type', FALSE, FALSE, FALSE, 'AND is_active = 1');

    $eventTypeIds = array_map('intval', $eventTypeIds);

    return array_values(array_intersect($eventTypeIds, array_keys($eventTypes)));
  }

  /**
   * Build event type condition for certificate retrieval.
   *
   * @param int $eventTypeId
   *
   * @return string
   */
  private function getEventTypeCondition($eventTypeId) {
    $column = CRM_Certificate_BAO_CompuCertificateEventAttribute::getTableName() . '.event_type_ids';
    $eventTypeCondition = "$column IS NULL";

    if (!empty($eventTypeId)) {
      $eventTypeCondition = sprintf('(%s OR FIND_IN_SET(%s, %s))', $eventTypeCondition, $eventTypeId, $column);
    }

    return $eventTypeCondition;
  }

  /**
   * {@inheritDoc}
   */
  protected function addEntityConditionals($certificateBAO, $entityId, $contactId) {
    $participant = civicrm_api3('Participant', 'getsingle', [
      'participant_id' => $entityId,
      'contact_id' => $contactId,
      'is_active' => 1,
    ]);
    $participantRoleIds = implode(',', (array) $participant['participant_role_id']);
    $event = civicrm_api3('Event', 'getsingle', [
      'id' => $participant['event_id'],
      'return' => ['event_type_id'],
    ]);
    $eventTypeId = (int) ($event['event_type_id'] ?? 0);

    $certificateBAO->joinAdd(['id', new CRM_Certificate_BAO_CompuCertificateEntityType(), 'certificate_id'], 'LEFT');
    $certificateBAO->joinAdd(['id', new CRM_Certificate_BAO_CompuCertificateStatus(), 'certificate_id'], 'LEFT');
    $certificateBAO->joinAdd(['id', new CRM_Certificate_BAO_CompuCertificateEventAttribute(), 'certificate_id'], 'LEFT');
    $certificateBAO->whereAdd('entity = ' . $this->getEntity());
    $certificateBAO->whereAdd('entity_type_id = ' . $participant['event_id'] . ' OR entity_type_id IS NULL');
    $certificateBAO->whereAdd('status_id = ' . $participant['participant_status_id'] . ' OR status_id IS NULL');
    $certificateBAO->whereAdd('participant_type_id IN (' . $participantRoleIds . ') OR participant_type_id IS NULL');
    $certificateBAO->whereAdd($this->getEventTypeCondition($eventTypeId));
  }

  /**
   * {@inheritDoc}
   */
  public function getContactCertificates($contactId) {
    $configuredCertificates = CompuCertificate::getEntityCertificates($this->getEntity());

    return $this->formatConfiguredCertificatesForContact($configuredCertificates, $contactId);
  }

  public function formatConfiguredCertificatesForContact(array $configuredCertificates, $contactId) {
    $certificates = [];

    foreach ($configuredCertificates as $configuredCertificate) {
      $eventAttribute = $this->getCertificateEventAttribute($configuredCertificate['certificate_id']);
      $participantTypeId = array_column($eventAttribute, 'participant_type_id');
      $eventTypeIds = $this->getEventAttributeEventTypeIds($eventAttribute);

      $condition = [
        'contact_id' => $contactId,
        'api.Event.get' => ['is_active' => 1],
      ];

      if (!empty($configuredCertificate['entity_type_id'])) {
        $condition['event_id'] = $configuredCertificate['entity_type_id'];
      }

      if (!empty($configuredCertificate['status_id'])) {
        $condition['participant_status_id'] = $configuredCertificate['status_id'];
      }

      if (!empty($participantTypeId)) {
        $condition['participant_role_id'] = ['IN' => (array) $participantTypeId];
      }

      if (!empty($eventTypeIds)) {
        $condition['event_type_id'] = ['IN' => $eventTypeIds];
      }

      $result = [];
      try {
        $result = civicrm_api3('Participant', 'get', $condition);
      }
      catch (\Throwable $th) {
        continue;
      }

      if ($result['is_error']) {
        continue;
      }

      array_walk($result['values'], function ($participant) use (&$certificates, $configuredCertificate, $contactId, $eventTypeIds) {
        if (empty($participant['api.Event.get']['values'])) {
          return;
        }

        if (!empty($eventTypeIds)) {
          $matchedEvent = reset($participant['api.Event.get']['values']);
          $matchedEventTypeId = (int) ($matchedEvent['event_type_id'] ?? 0);
          if (empty($matchedEventTypeId) || !in_array($matchedEventTypeId, $eventTypeIds)) {
            return;
          }
        }
        $certificate = [
          'participant_id' => $participant['id'],
          'event_id' => $participant['event_id'],
          'name' => $configuredCertificate['name'],
          'type' => 'Event',
          'linked_to' => $participant['event_title'],
          'status' => $participant['participant_status'],
          'end_date' => $configuredCertificate['end_date'],
          'start_date' => $configuredCertificate['start_date'],
          'download_link' => $this->getCertificateDownloadUrl($participant['id'], $contactId, TRUE),
          'participant_role' => $participant['participant_role'],
        ];
        array_push($certificates, $certificate);
      });
    }

    return $certificates;
  }

  /**
   * {@inheritDoc}
   */
  public function getCertificateDownloadUrl($entityId, $contactId, $absolute = FALSE) {
    $query = [
      "contact_id" => $contactId,
      "participant_id" => $entityId,
    ];

    $downloadUrl = htmlspecialchars_decode(CRM_Utils_System::url('civicrm/certificates/event', $query, $absolute));

    return $downloadUrl;
  }

  /**
   * {@inheritDoc}
   */
  public function getEntity() {
    return CertificateType::EVENTS;
  }

}
