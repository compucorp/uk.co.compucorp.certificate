<?php

class CRM_Certificate_Page_CertificateDownload extends CRM_Core_Page {

  /**
   * Handles case certificate download
   */
  public static function downloadCaseCertificate() {
    $contactId = CRM_Utils_Request::retrieve('cid', 'Positive');
    $entityId = CRM_Utils_Request::retrieve('id', 'Positive');

    try {
      $certificate = static::validateRequest(CRM_Certificate_Enum_CertificateType::CASES);
    } catch (CRM_Core_Exception $e) {
      CRM_Core_Session::setStatus($e->getMessage(), 'Error', 'error');
      CRM_Utils_System::redirect('civicrm/admin/certificates');
    }

    $certificateDownload = new CRM_Certificate_Service_CertificateDownload();
    $certificateDownload->download($certificate, $contactId, $entityId);
  }

  /**
   * Perform certificate download request validation, currently it enforces
   * - that the contact Id (cid) and entitty id are passed through the URL parameters
   * - that a configured certificate exists for the entity
   * 
   * @param int $certificateType
   * 
   * @return \CRM_Certificate_BAO_CompuCertificate
   * 
   * @throws CRM_Core_Exception;
   */
  private static function validateRequest(int $certificateType) {
    $contactId = CRM_Utils_Request::retrieve('cid', 'Positive');
    $entityId = CRM_Utils_Request::retrieve('id', 'Positive');

    if (empty($contactId) || empty($entityId)) {
      throw new CRM_Core_Exception(ts('contact Id and Entity Id are required to download a certificate'));
    }

    $entity = CRM_Certificate_Entity_EntityFactory::create($certificateType);
    $configuredCertificate = $entity->getCertificateConfiguration($entityId);

    if (!$configuredCertificate) {
      throw new CRM_Core_Exception(ts('Certificate not available for contact: id=%1.', [1 => $contactId]));
    }

    return $configuredCertificate;
  }
}
