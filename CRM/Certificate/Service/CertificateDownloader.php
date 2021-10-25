<?php

class CRM_Certificate_Service_CertificateDownloader {

  /**
   * Gets the template associated with a certificate configuration and renders it
   *
   * @param \CRM_Certificate_BAO_CompuCertificate $certificate
   * @param int $contactId
   * @param int $entityId
   */
  public function download($certificate, $contactId, $entityId) {
    $certificateGenerator = new CRM_Certificate_Service_CertificateGenerator($certificate->template_id);
    $generatedTemplate = $certificateGenerator->generate($certificate->template_id, $contactId, $entityId);
    $this->renderPDF($generatedTemplate);
  }

  /**
   * Converts html content to PDF, and return PDF file to the browser
   *
   * @param array $content
   */
  private function renderPDF(array $content) {
    ob_end_clean();
    CRM_Utils_PDF_Utils::html2pdf(
      $content['html'],
      'certificate.pdf',
      FALSE,
      $content["format"]
    );
    CRM_Utils_System::civiExit();
  }

}
