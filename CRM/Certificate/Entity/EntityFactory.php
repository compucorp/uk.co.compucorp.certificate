<?php

class CRM_Certificate_Entity_EntityFactory {

  /**
   * @return CRM_Certificate_Entity_EntityInterface
   */
  public static function create($entity) {
    switch ($entity) {
      case CRM_Certificate_Enum_CertificateType::CASES:
        return new CRM_Certificate_Entity_Case();
      default:
        throw new CRM_Core_Exception("Unsupported certificate entity type");
    }
  }
}
