<?php

/**
 * CRM_Certificate_Api_Wrapper_CompuCertificate.
 *
 * Implements helper methods to obtain a list of user certificates.
 */
class CRM_Certificate_Api_Wrapper_CompuCertificate implements API_Wrapper {

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
    $loggedContactID = CRM_Core_Session::singleton()->getLoggedInContactID();
    // If no contact_id is provided we default to the logged in contact ID.
    $contactId = $params['contact_id'] ?? $loggedContactID;

    $certificates = [];

    switch (TRUE) {
      case empty($params['entity']):
        $certificates = $this->getContactCasesCertificates($contactId);
        break;

      case $params['entity'] === 'case':
        $certificates = $this->getContactCasesCertificates($contactId);
        break;

      default:
        $certificates = [];
        break;
    }

    return $certificates;
  }

  private function getContactCasesCertificates(int $contactId) {
    $certificateType = CRM_Certificate_Enum_CertificateType::CASES;
    $entity = CRM_Certificate_Entity_EntityFactory::create($certificateType);
    $certificates = $entity->getContactCertificates($contactId);

    return $certificates;
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
