<?php

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

    if (empty($values['linked_to'])) {
      $event = CRM_Certificate_Test_Fabricator_Event::fabricate(['is_active' => 1]);
      $values['linked_to'] = (array) $event['id'];
    }

    if (empty($values['statuses'])) {
      $status = CRM_Certificate_Test_Fabricator_ParticipantStatusType::fabricate(['is_active' => 1]);
      $values['statuses'] = (array) $status['id'];
    }

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
    ];
  }

}
