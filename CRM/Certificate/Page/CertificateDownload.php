<?php

class CRM_Certificate_Page_CertificateDownload extends CRM_Core_Page {

  /**
   * Handles case certificate download
   */
  public static function downloadCaseCertificate() {
    $contactId = static::getContactId();
    $entityId = CRM_Utils_Request::retrieve('id', 'Positive');

    try {
      $certificate = static::validateRequest($contactId, $entityId, CRM_Certificate_Enum_CertificateType::CASES);
    } catch (CRM_Core_Exception $e) {
      CRM_Core_Session::setStatus($e->getMessage(), 'Error', 'error');
      CRM_Utils_System::redirect('/civicrm?reset=1');
    }

    $certificateDownload = new CRM_Certificate_Service_CertificateDownloader();
    $certificateDownload->download($certificate, $contactId, $entityId);
  }

  /**
   * Perform certificate download request validation, it enforces
   * - that the contact Id (cid) and entitty id are not empty
   * - that a configured certificate exists for the entity
   * 
   * @param $contactId
   * @param $entityId
   * @param int $certificateType
   * 
   * @return \CRM_Certificate_BAO_CompuCertificate
   * 
   * @throws CRM_Core_Exception;
   */
  private static function validateRequest(int $contactId, int $entityId, int $certificateType) {
    if (empty($contactId) || empty($entityId)) {
      throw new CRM_Core_Exception(ts('Contact Id and Entity Id are required to download a certificate'));
    }

    $entity = CRM_Certificate_Entity_EntityFactory::create($certificateType);
    $configuredCertificate = $entity->getCertificateConfiguration($entityId, $contactId);

    if (!$configuredCertificate) {
      throw new CRM_Core_Exception(ts('Certificate not available for contact'));
    }

    return $configuredCertificate;
  }

  /**
   * Performs access level check and return appropraite contact ID
   * 
   * The user would be granted access to the certificate if any of the condition below is true
   * - if the contact id in the URL is the same as the current logged in user
   * - if a checksum is provided in the URL and it is valid for the contact id
   * - Lastly, the user is not logged in and no checksum is provided if the user has 
   *   the express permission to view the contact
   * 
   * - if all fails return error.
   * 
   * @return int - contact id
   */
  private static function getContactId() {
    $contactId = CRM_Utils_Request::retrieve('cid', 'Positive');

    if (empty($contactId)) {
      CRM_Core_Error::statusBounce(ts('Contact Id is required to download a certificate.'));
    }

    $userChecksum = CRM_Utils_Request::retrieve('cs', 'String');

    $isLoggedInUser = CRM_Core_Session::getLoggedInContactID() == $contactId;
    $hasViewPermission = CRM_Contact_BAO_Contact_Permission::allow($contactId, CRM_Core_Permission::VIEW);
    $checksumValid = FALSE;

    if ($userChecksum && $contactId) {
      $checksumValid = CRM_Contact_BAO_Contact_Utils::validChecksum($contactId, $userChecksum);
    }

    if ($checksumValid || $isLoggedInUser || $hasViewPermission) {
      return $contactId;
    }

    CRM_Core_Error::statusBounce(ts('You do not have permission to access this contact.'));
  }
}
