<?php

use CRM_Certificate_Enum_DownloadType as DownloadType;
use CRM_Certificate_Enum_DownloadFormat as DownloadFormat;

/**
 * Fabricates ccertificate configuration for an entity.
 */
class CRM_Certificate_Test_Fabricator_CompuCertificate {

  public static function fabricate($entity, $values = []) {
    $certificate = NULL;

    switch ($entity) {
      case CRM_Certificate_Enum_CertificateType::CASES:
        $certificate = self::fabricateCaseCertificate($values);
        break;

      case CRM_Certificate_Enum_CertificateType::EVENTS:
        $certificate = self::fabricateEventCertificate($values);
        break;

      case CRM_Certificate_Enum_CertificateType::MEMBERSHIPS:
        $certificate = self::fabricateMembershipCertificate($values);
        break;

      default:
        $certificate = self::fabricateCaseCertificate($values);
    }
    return $certificate;
  }

  public static function fabricateCaseCertificate($values) {
    $values['type'] = CRM_Certificate_Enum_CertificateType::CASES;

    if (empty($values['linked_to'])) {
      $caseType = CRM_Certificate_Test_Fabricator_CaseType::fabricate();
      $values['linked_to'] = (array) $caseType['id'];
    }

    if (empty($values['statuses'])) {
      $status = CRM_Certificate_Test_Fabricator_CaseStatus::fabricate();
      $values['statuses'] = (array) $status['id'];
    }

    $values = array_merge(self::getDefaultParams(), $values);
    $storeCertificate = new CRM_Certificate_Service_CertificateCase();
    return $storeCertificate->store($values);
  }

  public static function fabricateEventCertificate($values) {
    $values['type'] = CRM_Certificate_Enum_CertificateType::EVENTS;

    if (!array_key_exists('linked_to', $values)) {
      $event = CRM_Certificate_Test_Fabricator_Event::fabricate(['is_active' => 1]);
      $values['linked_to'] = (array) $event['id'];
    }
    else {
      $values['linked_to'] = (array) $values['linked_to'];
    }

    if (empty($values['statuses'])) {
      $status = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate(['is_active' => 1]);
      $values['statuses'] = (array) $status['id'];
    }

    $values['event_type_ids'] = array_values(array_filter((array) ($values['event_type_ids'] ?? [])));

    $values = array_merge(self::getDefaultParams(), $values);
    $storeCertificate = new CRM_Certificate_Service_CertificateEvent();
    return $storeCertificate->store($values);
  }

  public static function fabricateMembershipCertificate($values) {
    $values['type'] = CRM_Certificate_Enum_CertificateType::MEMBERSHIPS;

    if (empty($values['linked_to'])) {
      $membershipType = CRM_Certificate_Test_Fabricator_MembershipType::fabricate(['is_active' => 1]);
      $values['linked_to'] = (array) $membershipType['id'];
    }

    if (empty($values['statuses'])) {
      $status = CRM_Certificate_Test_Fabricator_MembershipStatus::fabricate(['is_active' => 1]);
      $values['statuses'] = (array) $status['id'];
    }

    $values = array_merge(self::getDefaultParams(), $values);
    $storeCertificate = new CRM_Certificate_Service_CertificateMembership();
    return $storeCertificate->store($values);
  }

  public static function getDefaultParams() {
    $name = md5(mt_rand());
    return [
      'name' => $name,
      'message_template_id'  => 1,
      'downolad_format' => DownloadFormat::IMAGE,
      'download_type' => DownloadType::TEMPLATE,
      'start_date' => date("Y-m-d"),
      'end_date' => date("Y-m-d", strtotime(date("Y-m-d") . " + 10 days")),
      'min_valid_from_date' => date("Y-m-d"),
      'max_valid_through_date' => date("Y-m-d", strtotime(date("Y-m-d") . " + 30 days")),
      'relationship_types' => [],
      'event_type_ids' => [],
    ];
  }

}
