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
   *   Supported keys:
   *     - entity: The type of certificate entity to fetch.
   *     - contact_id: Contact requesting the certificates.
   *     - primary_contact_id: (optional) Primary/related contact to restrict
   *       results to.
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
      $certificates = $entity->getRelationshipCertificates($params['contact_id']);

      if (isset($params['primary_contact_id'])) {
        $primaryId = (int) $params['primary_contact_id'];
        $certificates = array_values(array_filter(
          $certificates,
          function ($certificate) use ($primaryId) {
            return (int) CRM_Utils_Array::value('related_contact', $certificate) === $primaryId;
          }
        ));
      }

      return $certificates;
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
