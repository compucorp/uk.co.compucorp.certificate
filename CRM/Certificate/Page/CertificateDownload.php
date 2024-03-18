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
      $configurationId = CRM_Utils_Request::retrieve('ccid', 'Positive');
      $certificate = self::checkIfCertificateAvailable($contactId, $entityId, $certificateType, $configurationId);
    }
    catch (CRM_Core_Exception $e) {
      CRM_Core_Session::setStatus($e->getMessage(), 'Error', 'error');
      CRM_Utils_System::redirect('/civicrm?reset=1');
    }

    $format = $certificate->download_format;
    $overrideFormat = CRM_Utils_Request::retrieve('format', 'String');
    if (!empty($overrideFormat) && $overrideFormat === 'html') {
      $format = CRM_Certificate_Enum_DownloadFormat::HTML;
    }

    $certificateDownload = new CRM_Certificate_Service_CertificateDownloader($certificate, $contactId, $entityId, $format);
    $certificateDownload->download();
  }

  /**
   * Checks if certificates available for the given contact id and case id.
   *
   * @param int $contactId
   * @param int $entityId
   * @param int $certificateType
   * @param int|bool $certificateId
   *
   * @return \CRM_Certificate_BAO_CompuCertificate
   *
   * @throws \CRM_Core_exception
   */
  public static function checkIfCertificateAvailable($contactId, $entityId, $certificateType, $certificateId = NULL) {
    if (empty($contactId) || empty($entityId)) {
      throw new CRM_Core_Exception('You do not have permission to access this contact.', 403);
    }

    $certificates = self::validateCertificate($contactId, $entityId, $certificateType);
    $certificateWithAccess = [];
    foreach ($certificates as $certificate) {
      if (!empty($certificateId) && $certificate->id != $certificateId) {
        continue;
      }

      $accessChecker = new CRM_Certificate_Service_CertificateAccessChecker($contactId, $certificate);
      $hasAccess = $accessChecker->check();

      if ($hasAccess) {
        $certificateWithAccess[$certificate->id] = $certificate;
      }
    }

    if (!empty($certificateWithAccess)) {
      return end($certificateWithAccess);
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
   * @return \CRM_Certificate_BAO_CompuCertificate[]
   *
   * @throws CRM_Core_Exception;
   */
  private static function validateCertificate(int $contactId, int $entityId, int $certificateType) {
    $entity = CRM_Certificate_Entity_EntityFactory::create($certificateType);
    $configuredCertificates = $entity->getCertificateConfiguration($entityId, $contactId, TRUE);

    if (empty($configuredCertificates)) {
      throw new CRM_Core_Exception(ts('Certificate not available for contact'));
    }

    return $configuredCertificates;
  }

}
