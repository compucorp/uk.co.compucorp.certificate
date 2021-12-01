<?php

class CRM_Certificate_Page_CertificateDownload extends CRM_Core_Page {

  /**
   * Handles case certificate download
   */
  public static function downloadCaseCertificate() {
    $contactId = CRM_Utils_Request::retrieve('contact_id', 'Positive') ?? CRM_Utils_Request::retrieve('cid', 'Positive');
    $entityId = CRM_Utils_Request::retrieve('case_id', 'Positive') ?? CRM_Utils_Request::retrieve('id', 'Positive');
    $certificateType = CRM_Certificate_Enum_CertificateType::CASES;

    self::downloadCertificate($contactId, $entityId, $certificateType);
  }

  /**
   * Handles event certificate download
   */
  public static function downloadEventCertificate() {
    $contactId = CRM_Utils_Request::retrieve('contact_id', 'Positive') ?? CRM_Utils_Request::retrieve('cid', 'Positive');
    $entityId = CRM_Utils_Request::retrieve('participant_id', 'Positive') ?? CRM_Utils_Request::retrieve('id', 'Positive');
    $certificateType = CRM_Certificate_Enum_CertificateType::EVENTS;

    self::downloadCertificate($contactId, $entityId, $certificateType);
  }

  /**
   * Handles membership certificate download
   */
  public static function downloadMembershipCertificate() {
    $contactId = CRM_Utils_Request::retrieve('contact_id', 'Positive') ?? CRM_Utils_Request::retrieve('cid', 'Positive');
    $entityId = CRM_Utils_Request::retrieve('membership_id', 'Positive') ?? CRM_Utils_Request::retrieve('id', 'Positive');
    $certificateType = CRM_Certificate_Enum_CertificateType::MEMBERSHIPS;

    self::downloadCertificate($contactId, $entityId, $certificateType);
  }

  /**
   * Handles certificate download
   *
   * @param int $contactId
   * @param int $entityId
   * @param int $certificateType
   */
  private static function downloadCertificate($contactId, $entityId, $certificateType) {
    try {
      $certificate = self::checkIfCertificateAvailable($contactId, $entityId, $certificateType);
    }
    catch (CRM_Core_Exception $e) {
      CRM_Core_Session::setStatus($e->getMessage(), 'Error', 'error');
      CRM_Utils_System::redirect('/civicrm?reset=1');
    }

    $certificateDownload = new CRM_Certificate_Service_CertificateDownloader();
    $certificateDownload->download($certificate, $contactId, $entityId);
  }

  /**
   * Checks if certificates available for the given contact id and case id.
   *
   * @param int $contactId
   * @param int $entityId
   * @param int $certificateType
   *
   * @return int - contact id
   */
  public static function checkIfCertificateAvailable($contactId, $entityId, $certificateType) {
    self::checkPermission($contactId, $entityId);

    return self::validateCertificate($contactId, $entityId, $certificateType);
  }

  /**
   * Performs access level check to ensure user has access to contact certificate
   *
   * The user would be granted access to the certificate if any of the condition below is true
   * - if the contact id is the same as the current logged in user
   * - if a checksum is provided in the URL and it is valid for the contact id
   * - Lastly, the user is not logged in and no checksum is provided if the user has
   *   the express permission to view the contact
   *
   * - if all fails return error.
   * @param int $contactId
   * @param int $entityId
   *
   * @return int - contact id
   */
  private static function checkPermission($contactId, $entityId) {
    if (empty($contactId) || empty($entityId)) {
      throw new CRM_Core_Exception(ts('Contact Id and Entity Id are required to download a certificate'));
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

    throw new CRM_Core_Exception('You do not have permission to access this contact.', 403);
  }

  /**
   * Validates user access to certificate, it enforces
   * that a configured certificate exists for the entity and contact
   *
   * @param $contactId
   * @param $entityId
   * @param int $certificateType
   *
   * @return \CRM_Certificate_BAO_CompuCertificate
   *
   * @throws CRM_Core_Exception;
   */
  private static function validateCertificate(int $contactId, int $entityId, int $certificateType) {
    $entity = CRM_Certificate_Entity_EntityFactory::create($certificateType);
    $configuredCertificate = $entity->getCertificateConfiguration($entityId, $contactId);

    if (!$configuredCertificate) {
      throw new CRM_Core_Exception(ts('Certificate not available for contact'));
    }

    return $configuredCertificate;
  }

}
