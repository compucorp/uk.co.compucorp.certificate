<?php

class CRM_Certificate_Entity_EntityFactory {

  /**
   * @return CRM_Certificate_Entity_EntityInterface
   */
  public static function create($entity) {
    switch ($entity) {
      case CRM_Certificate_Enum_CertificateType::CASES:
        return new CRM_Certificate_Entity_Case();

      case CRM_Certificate_Enum_CertificateType::EVENTS:
        return new CRM_Certificate_Entity_Event();

      case CRM_Certificate_Enum_CertificateType::MEMBERSHIPS:
        return new CRM_Certificate_Entity_Membership();

      default:
        throw new CRM_Core_Exception("Unsupported certificate entity type");
    }
  }

}
