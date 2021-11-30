<?php

class CRM_Certificate_Entity_Membership implements CRM_Certificate_Entity_EntityInterface {

  /**
   * {@inheritDoc}
   */
  public function store($values) {
    return (new CRM_Certificate_Service_CertificateMembership())->store($values);
  }

  /**
   * {@inheritDoc}
   */
  public function getTypes() {
    $result = civicrm_api3('MembershipType', 'get', [
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
    $result = civicrm_api3('MembershipStatus', 'get', [
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
    $statusBAO->joinAdd(['status_id', new CRM_Member_DAO_MembershipStatus(), 'id']);
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

    $entityType = new CRM_Member_DAO_MembershipType();
    $entityTypeBAO->joinAdd(['entity_type_id', $entityType, 'id']);
    $entityTypeBAO->find();
    $entityTypes = $entityTypeBAO->fetchAll('id');

    $entityTypes = array_map(function ($entityType) {
      return [
        "id" => $entityType["id"],
        "label" => $entityType["name"],
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

    return [
      'name' => $certificateDAO->name,
      'type' => $certificateDAO->entity,
      'message_template_id' => $certificateDAO->template_id,
      'statuses' => implode(',', array_column($statuses, 'id')),
      'linked_to' => implode(',', array_column($types, 'id')),
    ];
  }

  /**
   * {@inheritDoc}
   */
  public function getCertificateConfiguration($entityId, $contactId) {
    try {
      $membership = civicrm_api3('Membership', 'getsingle', [
        'id' => $entityId,
        'contact_id' => $contactId,
        'is_active' => 1,
      ]);

      $certificateBAO = new CRM_Certificate_BAO_CompuCertificate();
      $certificateBAO->joinAdd(['id', new CRM_Certificate_BAO_CompuCertificateEntityType(), 'certificate_id']);
      $certificateBAO->joinAdd(['id', new CRM_Certificate_BAO_CompuCertificateStatus(), 'certificate_id']);
      $certificateBAO->whereAdd('entity = ' . CRM_Certificate_Enum_CertificateType::MEMBERSHIPS);
      $certificateBAO->whereAdd('entity_type_id = ' . $membership['membership_type_id']);
      $certificateBAO->whereAdd('status_id = ' . $membership['status_id']);
      $certificateBAO->orderBy(CRM_Certificate_DAO_CompuCertificateStatus::$_tableName . '.id Desc');
      $certificateBAO->selectAdd(CRM_Certificate_DAO_CompuCertificateStatus::$_tableName . '.id');
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
    throw new \Exception("Not yet implemented");
  }

  /**
   * {@inheritDoc}
   */
  public function getCertificateDownloadUrl($entityId, $contactId, $absolute = FALSE) {
    $query = [
      "cid" => $contactId,
      "id" => $entityId,
    ];

    $downloadUrl = htmlspecialchars_decode(CRM_Utils_System::url('civicrm/certificates/membership', $query, $absolute));

    return $downloadUrl;
  }

}
