<?php

use CRM_Certificate_Enum_CertificateType as CertificateType;

/**
 * CRM_Certificate_Api_Wrapper_CompuCertificate.
 *
 * Implements helper methods to obtain a list of user certificates.
 */
class CRM_Certificate_Api_Wrapper_CompuCertificate implements API_Wrapper {

  /**
   * Entities supported by the API.
   *
   * @var array
   */
  public const SUPPORTED_ENTITY_PARAMS = [
    'case' => CertificateType::CASES,
    'event' => CertificateType::EVENTS,
    'membership' => CertificateType::MEMBERSHIPS,
  ];

  /**
   * Returns list of certificates accessible to loggedin user.
   *
   * @param array $params
   *   Parameters array for the API call.
   *
   * @return array
   *   Result with the list of certificates.
   */
  public function getContactCertificates(array $params) {
    $params = $this->setDefaultParams($params);

    if (empty($params['contact_id'])) {
      return [];
    }

    $entity = CRM_Utils_Array::value($params['entity'], self::SUPPORTED_ENTITY_PARAMS);
    if (!empty($entity)) {
      $entity = CRM_Certificate_Entity_EntityFactory::create($entity);
      return $entity->getContactCertificates($params['contact_id']);
    }

    return [];
  }

  /**
   * Returns list of certificates contact has access to based on relationship.
   *
   * @param array $params
   *   Parameters array for the API call.
   *
   * @return array
   *   Result with the list of certificates.
   */
  public function getRelationshipCertificates(array $params) {
    $params = $this->setDefaultParams($params);

    if (empty($params['contact_id'])) {
      return [];
    }

    $entity = CRM_Utils_Array::value($params['entity'], self::SUPPORTED_ENTITY_PARAMS);
    if (!empty($entity)) {
      $entity = CRM_Certificate_Entity_EntityFactory::create($entity);
      return $entity->getRelationshipCertificates($params['contact_id']);
    }

    return [];
  }

  private function setDefaultParams($params) {
    return array_merge([
      'entity' => 'case',
      'contact_id' => CRM_Core_Session::singleton()->getLoggedInContactID(),
    ], $params);
  }

  /**
   * {@inheritdoc}
   */
  public function fromApiInput($apiRequest) {
    return $apiRequest;
  }

  /**
   * {@inheritdoc}
   */
  public function toApiOutput($apiRequest, $result) {
    return $result;
  }

}
