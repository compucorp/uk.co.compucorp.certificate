<?php

class CRM_Certificate_Entity_Event implements CRM_Certificate_Entity_EntityInterface {

  /**
   * @inheritdoc
   */
  public function getTypes() {
    $result = civicrm_api3('event', 'get', [
      'sequential' => 1,
      'is_active' => 1,
      'return' => ["id"]
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
      'return' => ["id"]
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
            "label" => $status["label"]
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
        "label" => $entityType["title"]
      ];
    }, $entityTypes);

    return $entityTypes;
  }

  /**
   * @inheritDoc
   */
  public function getCertificateConfiguration($entityId, $contactId) {
  }
}
