<?php

use CRM_Certificate_Page_CertificateDownload as CertificateDownload;

/**
 * CRM_Certificate_Api_Wrapper_Case.
 *
 * Implements helper methods to obtain whether the case has any certificate
 * available to be downloaded.
 */
class CRM_Certificate_Api_Wrapper_Case implements API_Wrapper {

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
    foreach ($result['values'] as $key => &$header) {
      $clientID = $this->getClientID($header['contacts']);

      try {
        CertificateDownload::checkIfCertificateAvailable($clientID, $header['id'], CRM_Certificate_Enum_CertificateType::CASES);
        $header['is_download_certificate_available'] = TRUE;
      }
      catch (CRM_Core_Exception $e) {
        $header['is_download_certificate_available'] = FALSE;
      }
    }

    return $result;
  }

  /**
   * Get the client id from the given set of contacts.
   *
   * @param array $contacts
   *  Contacts list.
   * @return string
   *   Client Contact ID.
   */
  private function getClientID($contacts) {
    foreach ($contacts as $contact) {
      if (empty($contact['relationship_type_id'])) {
        return $contact['contact_id'];
      }
    }
  }

}
