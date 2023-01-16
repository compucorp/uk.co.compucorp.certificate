<?php

use CRM_Certificate_Enum_DownloadFormat as DownloadFormat;
use CRM_Certificate_BAO_CompuCertificateImageFormat as CompuCertificateImageFormatBAO;

class CRM_Certificate_Service_CertificateDownloader {

  /**
   * Gets the template associated with a certificate configuration and renders it
   *
   * @param \CRM_Certificate_BAO_CompuCertificate $certificate
   * @param int $contactId
   * @param int $entityId
   */
  public function download($certificate, $contactId, $entityId) {
    $certificateGenerator = new CRM_Certificate_Service_CertificateGenerator($certificate->id);
    $generatedTemplate = $certificateGenerator->generate($certificate->template_id, $contactId, $entityId);

    //here we decide to render has PDF or Image.
    if ($certificate->download_format == DownloadFormat::PDF) {
      return $this->renderPDF($generatedTemplate);
    }

    return $this->renderImage($generatedTemplate['html'], $certificate->image_format_id);
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

  /**
   * Converts html content to Image, and return image file to the browser.
   *
   * @param string $html
   * @param int $imageFormatId
   */
  private function renderImage($html, $imageFormatId) {
    $page = new CRM_Certificate_Page_CertificateDownload();
    $page->assign('certificateContent', $html);
    $page->assign('imageFormat', json_encode(CompuCertificateImageFormatBAO::getImageFormat('id', $imageFormatId)));
    $page->run();
  }

}
