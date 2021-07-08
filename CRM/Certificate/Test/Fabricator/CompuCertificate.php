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
    }
    return $certificate;
  }

  public static function fabricateCaseCertificate($values = []) {
    $values['certificate_type'] = CRM_Certificate_Enum_CertificateType::CASES;

    if (empty($values['certificate_linked_to'])) {
      $caseType = CRM_Certificate_Test_Fabricator_CaseType::fabricate();
      $values['certificate_linked_to'] = implode(',', (array)$caseType['id']);
    }

    if (empty($values['certificate_status'])) {
      $status = CRM_Certificate_Test_Fabricator_CaseStatus::fabricate();
      $values['certificate_status'] = implode(',', (array)$status['id']);
    }

    $values = array_merge(self::getDefaultParams(), $values);
    $storeCertificate = new CRM_Certificate_Service_StoreCertificateConfiguration($values);
    return $storeCertificate->store();
  }

  public static function getDefaultParams() {
    $name = md5(mt_rand());
    return [
      'certificate_name' => $name,
      'certificate_msg_template'  => 1,
    ];
  }
}
