<?php

use CRM_Certificate_Enum_CertificateType as CertificateType;
use CRM_Certificate_BAO_CompuCertificate as CompuCertificate;

class CRM_Certificate_Entity_Event extends CRM_Certificate_Entity_AbstractEntity {

  /**
   * Serializes event type IDs for storage.
   *
   * @param array $eventTypeIds
   *
   * @return string|null
   */
  public static function serializeEventTypeIds(array $eventTypeIds) {
    $eventTypeIds = array_values(array_unique(array_filter(array_map('intval', $eventTypeIds))));

    if (empty($eventTypeIds)) {
      return NULL;
    }

    $separator = CRM_Core_DAO::VALUE_SEPARATOR;

    return $separator . implode($separator, $eventTypeIds) . $separator;
  }

  /**
   * Deserializes stored event type IDs into an array.
   *
   * @param string|null $eventTypeIds
   *
   * @return array
   */
  public static function deserializeEventTypeIds($eventTypeIds) {
    if (empty($eventTypeIds)) {
      return [];
    }

    $separator = CRM_Core_DAO::VALUE_SEPARATOR;
    $trimmed = trim($eventTypeIds, $separator);

    if ($trimmed === '') {
      return [];
    }

    return array_values(array_filter(array_map('intval', explode($separator, $trimmed))));
  }

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
    $certificate['event_type_ids'] = self::deserializeEventTypeIds($certificateBAO->event_type_ids);
  }

  private function getCertificateEventAttribute($certificateId) {
    $eventAttribute = new CRM_Certificate_BAO_CompuCertificateEventAttribute();
    $eventAttribute->whereAdd("certificate_id = " . $certificateId);
    $eventAttribute->find();

    return $eventAttribute->fetchAll();
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
    $eventTypeId = CRM_Core_DAO::getFieldValue('CRM_Event_DAO_Event', $participant['event_id'], 'event_type_id');

    $certificateBAO->joinAdd(['id', new CRM_Certificate_BAO_CompuCertificateEntityType(), 'certificate_id'], 'LEFT');
    $certificateBAO->joinAdd(['id', new CRM_Certificate_BAO_CompuCertificateStatus(), 'certificate_id'], 'LEFT');
    $certificateBAO->joinAdd(['id', new CRM_Certificate_BAO_CompuCertificateEventAttribute(), 'certificate_id'], 'LEFT');
    $certificateBAO->whereAdd('entity = ' . $this->getEntity());
    $certificateBAO->whereAdd('entity_type_id = ' . $participant['event_id'] . ' OR entity_type_id IS NULL');
    $certificateBAO->whereAdd('status_id = ' . $participant['participant_status_id'] . ' OR status_id IS NULL');
    $certificateBAO->whereAdd('participant_type_id IN (' . $participantRoleIds . ') OR participant_type_id IS NULL');
    $certificateBAO->whereAdd($this->buildEventTypeWhereClause($eventTypeId));
  }

  /**
   * Builds the WHERE clause used to limit certificates by event type.
   *
   * @param int|null $eventTypeId
   *
   * @return string
   */
  private function buildEventTypeWhereClause($eventTypeId) {
    $column = CRM_Certificate_DAO_CompuCertificate::getTableName() . '.event_type_ids';
    $clauses = ["{$column} IS NULL", "{$column} = ''"];

    if (!empty($eventTypeId)) {
      $separator = CRM_Core_DAO::VALUE_SEPARATOR;
      $pattern = '%' . $separator . (int) $eventTypeId . $separator . '%';
      $clauses[] = "{$column} LIKE " . CRM_Utils_Type::escape($pattern, 'String');
    }

    return '(' . implode(' OR ', $clauses) . ')';
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
      $eventTypeIds = self::deserializeEventTypeIds($configuredCertificate['event_type_ids'] ?? NULL);

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
        $condition['api.Event.get']['return'] = ['event_type_id'];
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
          $event = reset($participant['api.Event.get']['values']);
          $eventTypeId = $event['event_type_id'] ?? NULL;

          if (empty($eventTypeId) || !in_array((int) $eventTypeId, $eventTypeIds, TRUE)) {
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
