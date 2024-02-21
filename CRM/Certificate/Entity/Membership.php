<?php

use CRM_Certificate_Enum_CertificateType as CertificateType;
use CRM_Certificate_BAO_CompuCertificate as CompuCertificate;

class CRM_Certificate_Entity_Membership extends CRM_Certificate_Entity_AbstractEntity {

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
    try {
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
    catch (\Throwable $th) {
      return [];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getStatuses() {
    try {
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
  protected function addEntityConditionals($certificateBAO, $entityId, $contactId) {
    $membership = civicrm_api3('Membership', 'getsingle', [
      'id' => $entityId,
      'contact_id' => $contactId,
      'is_active' => 1,
    ]);

    $certificateBAO->joinAdd(['id', new CRM_Certificate_BAO_CompuCertificateEntityType(), 'certificate_id'], "LEFT");
    $certificateBAO->joinAdd(['id', new CRM_Certificate_BAO_CompuCertificateStatus(), 'certificate_id'], "LEFT");
    $certificateBAO->whereAdd('entity = ' . $this->getEntity());
    $certificateBAO->whereAdd("entity_type_id = {$membership['membership_type_id']} OR entity_type_id IS NULL");
    $certificateBAO->whereAdd("status_id = {$membership['status_id']}  OR status_id IS NULL");
  }

  /**
   * {@inheritDoc}
   */
  public function getContactCertificates($contactId) {
    $configuredCertificates = CompuCertificate::getEntityCertificates($this->getEntity());
    return $this->formatConfiguredCertificatesForContact($configuredCertificates, $contactId);
  }

  /**
   * {@inheritDoc}
   */
  public function formatConfiguredCertificatesForContact(array $configuredCertificates, $contactId) {
    $certificates = [];

    foreach ($configuredCertificates as $configuredCertificate) {
      $condition = [
        'sequential' => 1,
        'is_active' => 1,
        'contact_id' => $contactId,
      ];

      if (!empty($configuredCertificate['status_id'])) {
        $condition['status_id'] = $configuredCertificate['status_id'];
      }

      if (!empty($configuredCertificate['entity_type_id'])) {
        $condition['membership_type_id'] = $configuredCertificate['entity_type_id'];
      }

      $result = [];
      try {
        $result = civicrm_api3('Membership', 'get', $condition);
      }
      catch (\Throwable $th) {
        continue;
      }

      if ($result['is_error']) {
        continue;
      }

      array_walk($result['values'], function ($membership) use (&$certificates, $configuredCertificate, $contactId) {
        $certificate = [
          'membership_id' => $membership['id'],
          'name' => $configuredCertificate['name'],
          'type' => 'Membership',
          'status' => CRM_Member_BAO_MembershipStatus::getMembershipStatus($membership['status_id'])["name"] ?? "",
          'end_date' => $configuredCertificate['end_date'],
          'start_date' => $configuredCertificate['start_date'],
          'linked_to' => $membership['membership_name'],
          'download_link' => $this->getCertificateDownloadUrl($membership['id'], $contactId, TRUE),
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
      "cid" => $contactId,
      "id" => $entityId,
    ];

    $downloadUrl = htmlspecialchars_decode(CRM_Utils_System::url('civicrm/certificates/membership', $query, $absolute));

    return $downloadUrl;
  }

  public function getEntity() {
    return CertificateType::MEMBERSHIPS;
  }

  /**
   * @inheritDoc
   */
  protected function isCertificateValidForAnEntity(\CRM_Certificate_BAO_CompuCertificate $certificate, int $contactId) {
    $membershipDates = (new CRM_Certificate_Service_CertificateMembership())->getMembershipDates($certificate->id, $contactId);

    return ($membershipDates['startDate'] === NULL || $certificate->max_valid_through_date === NULL || $membershipDates['startDate'] <= $certificate->max_valid_through_date)
      && ($membershipDates['endDate'] === NULL || $certificate->min_valid_from_date === NULL || $membershipDates['endDate'] >= $certificate->min_valid_from_date);
  }

}
