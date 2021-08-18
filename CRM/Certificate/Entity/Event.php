<?php

class CRM_Certificate_Entity_Event implements CRM_Certificate_Entity_EntityInterface {

  /**
   * @inheritdoc
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
   * @inheritdoc
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
   * @inheritDoc
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
   * @inheritDoc
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
   * @inheritDoc
   */
  public function getCertificateConfiguration($entityId, $contactId) {
    try {
      $participant = civicrm_api3('Participant', 'getsingle', [
        'participant_id' => $entityId,
        'contact_id' => $contactId,
        'is_active' => 1
      ]);

      $certificateBAO = new CRM_Certificate_BAO_CompuCertificate();
      $certificateBAO->joinAdd(['id', new CRM_Certificate_BAO_CompuCertificateEntityType(), 'certificate_id'], 'LEFT');
      $certificateBAO->joinAdd(['id', new CRM_Certificate_BAO_CompuCertificateStatus(), 'certificate_id'], 'LEFT');
      $certificateBAO->whereAdd('entity = ' . CRM_Certificate_Enum_CertificateType::EVENTS);
      $certificateBAO->whereAdd('entity_type_id = ' . $participant['event_id'] . ' OR entity_type_id IS NULL');
      $certificateBAO->whereAdd('status_id = ' . $participant['participant_status_id']. ' OR status_id IS NULL');
      $certificateBAO->orderBy(CRM_Certificate_DAO_CompuCertificate::$_tableName . '.id Desc');
      $certificateBAO->selectAdd(CRM_Certificate_DAO_CompuCertificate::$_tableName . '.id');
      $certificateBAO->find(TRUE);

      if (!empty($certificateBAO->id)) {
        return $certificateBAO;
      }
    } catch (Exception $e) {
    }
    return FALSE;
  }

}
