<?php

use CRM_Certificate_Enum_DownloadFormat as DownloadFormat;
use CRM_Certificate_BAO_CompuCertificateImageFormat as CompuCertificateImageFormatBAO;
use CRM_Certificate_BAO_CompuCertificateTemplateImageFormat as CompuCertificateTemplateImageFormat;

class CRM_Certificate_Service_CertificateDownloader {

  public ?int $format;
  public int $entityId;
  public int $contactId;
  public \CRM_Certificate_BAO_CompuCertificate $certificate;
  private \CRM_Certificate_Service_CertificateGenerator $certificateGenerator;

  /**
   * @param \CRM_Certificate_BAO_CompuCertificate $certificate
   * @param int $contactId
   * @param int $entityId
   * @param int $format
   */
  public function __construct($certificate, $contactId, $entityId, $format) {
    $this->entityId = $entityId;
    $this->contactId = $contactId;
    $this->certificate = $certificate;
    $this->format = $format;
    $this->certificateGenerator = new CRM_Certificate_Service_CertificateGenerator($certificate->id);
  }

  /**
   * Gets the template associated with a certificate configuration and renders it.
   */
  public function download() {
    $generatedTemplate = $this->certificateGenerator->generate($this->certificate->template_id, $this->contactId, $this->entityId);
    return $this->render($generatedTemplate);
  }

  /**
   * Renders the certificate message template in the specified format.
   *
   * @param array $generatedTemplate
   *  Parsed template to be rendered.
   */
  private function render($generatedTemplate) {
    if ($this->format == DownloadFormat::IMAGE) {
      $templateImageFormat = CompuCertificateTemplateImageFormat::getByTemplateId($this->certificate->template_id);
      return $this->renderImage($generatedTemplate['html'], $templateImageFormat->image_format_id ?? NULL);
    }

    if ($this->format == DownloadFormat::HTML) {
      return $this->renderRaw($generatedTemplate['html']);
    }

    return $this->renderPDF($generatedTemplate);
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
    $imageFormat = [];
    if (!is_null($imageFormatId)) {
      $imageFormat = CompuCertificateImageFormatBAO::getImageFormat('id', $imageFormatId);
    }

    if (empty($imageFormat)) {
      $imageFormat = CompuCertificateImageFormatBAO::getDefaultFormat();
    }

    $page = new CRM_Certificate_Page_CertificateDownload();
    $page->assign('certificateContent', $html);
    $page->assign('imageFormat', json_encode($imageFormat ?? []));
    $page->run();
  }

  /**
   * Returns raw html coontent as file to the browser.
   *
   * @param string $html
   */
  public function renderRaw($html) {
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Transfer-Encoding: binary');
    header('Content-Disposition: attachment; filename="certificate.html"');
    ob_end_clean();
    flush();
    echo $html;
    CRM_Utils_System::civiExit();
  }

}
