<?php

use CRM_Certificate_Enum_CertificateType as CertificateType;
use CRM_Certificate_BAO_CompuCertificate as CompuCertificate;

class CRM_Certificate_Entity_Event implements CRM_Certificate_Entity_EntityInterface {

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

  /**
   * {@inheritdoc}
   */
  public function getStatuses() {
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
  public function getCertificateConfigurationById($certificateId) {
    $certificateDAO = CRM_Certificate_BAO_CompuCertificate::findById($certificateId);
    $statuses = $this->getCertificateConfiguredStatuses($certificateDAO->id);
    $types = $this->getCertificateConfiguredTypes($certificateDAO->id);
    $eventAttribute = $this->getCertificateEventAttribute($certificateDAO->id);

    return [
      'name' => $certificateDAO->name,
      'type' => $certificateDAO->entity,
      'message_template_id' => $certificateDAO->template_id,
      'statuses' => implode(',', array_column($statuses, 'id')),
      'linked_to' => implode(',', array_column($types, 'id')),
      'participant_type_id' => implode(', ', array_column($eventAttribute, 'participant_type_id')),
    ];
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
  public function getCertificateConfiguration($entityId, $contactId) {
    try {
      $participant = civicrm_api3('Participant', 'getsingle', [
        'participant_id' => $entityId,
        'contact_id' => $contactId,
        'is_active' => 1,
      ]);
      $participantRoleIds = implode(',', (array) $participant['participant_role_id']);

      $certificateBAO = new CRM_Certificate_BAO_CompuCertificate();
      $certificateBAO->joinAdd(['id', new CRM_Certificate_BAO_CompuCertificateEntityType(), 'certificate_id'], 'LEFT');
      $certificateBAO->joinAdd(['id', new CRM_Certificate_BAO_CompuCertificateStatus(), 'certificate_id'], 'LEFT');
      $certificateBAO->joinAdd(['id', new CRM_Certificate_BAO_CompuCertificateEventAttribute(), 'certificate_id'], 'LEFT');
      $certificateBAO->whereAdd('entity = ' . CRM_Certificate_Enum_CertificateType::EVENTS);
      $certificateBAO->whereAdd('entity_type_id = ' . $participant['event_id'] . ' OR entity_type_id IS NULL');
      $certificateBAO->whereAdd('status_id = ' . $participant['participant_status_id'] . ' OR status_id IS NULL');
      $certificateBAO->whereAdd('participant_type_id IN (' . $participantRoleIds . ') OR participant_type_id IS NULL');
      $certificateBAO->orderBy(CRM_Certificate_DAO_CompuCertificate::$_tableName . '.id Desc');
      $certificateBAO->selectAdd(CRM_Certificate_DAO_CompuCertificate::$_tableName . '.id');
      $certificateBAO->find(TRUE);

      if (!empty($certificateBAO->id)) {
        return $certificateBAO;
      }
    }
    catch (Exception $e) {
    }
    return FALSE;
  }

  /**
   * {@inheritDoc}
   */
  public function getContactCertificates($contactId) {
    $certificates = [];

    $configuredCertificates = CompuCertificate::getEntityCertificates(CertificateType::EVENTS);

    foreach ($configuredCertificates as $configuredCertificate) {
      $eventAttribute = $this->getCertificateEventAttribute($configuredCertificate['certificate_id']);
      $participantTypeId = array_column($eventAttribute, 'participant_type_id');

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

      $result = civicrm_api3('Participant', 'get', $condition);

      if ($result['is_error']) {
        continue;
      }

      array_walk($result['values'], function ($participant) use (&$certificates, $configuredCertificate, $contactId) {
        if (empty($participant['api.Event.get']['values'])) {
          return;
        }
        $certificate = [
          'participant_id' => $participant['id'],
          'event_id' => $participant['event_id'],
          'name' => $configuredCertificate['name'],
          'type' => 'Event',
          'linked_to' => $participant['event_title'],
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

}
