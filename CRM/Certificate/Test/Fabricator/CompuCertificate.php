<?php

/**
 * Fabricates ccertificate configuration for an entity.
 */
class CRM_Certificate_Test_Fabricator_CompuCertificate {

  public static function fabricate($entity, $values = []) {
    $certificate = null;

    switch ($entity) {
      case CRM_Certificate_Enum_CertificateType::CASES:
        $certificate =  self::fabricateCaseCertificate($values);
        break;
      default:
        $certificate =  self::fabricateCaseCertificate($values);
    }
    return $certificate;
  }

  public static function fabricateCaseCertificate($values) {
    $values['type'] = CRM_Certificate_Enum_CertificateType::CASES;

    if (empty($values['linked_to'])) {
      $caseType = CRM_Certificate_Test_Fabricator_CaseType::fabricate();
      $values['linked_to'] = (array)$caseType['id'];
    }

    if (empty($values['statuses'])) {
      $status = CRM_Certificate_Test_Fabricator_CaseStatus::fabricate();
      $values['statuses'] = (array)$status['id'];
    }

    $values = array_merge(self::getDefaultParams(), $values);
    $storeCertificate = new CRM_Certificate_Service_Certificate();
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
