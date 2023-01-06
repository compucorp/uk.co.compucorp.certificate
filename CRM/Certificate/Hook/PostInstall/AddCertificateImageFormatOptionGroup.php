<?php

use CRM_Certificate_BAO_CompuCertificateImageFormat as CompuCertificateImageFormat;

class CRM_Certificate_Hook_PostInstall_AddCertificateImageFormatOptionGroup {

  /**
   * Reflects if the option group was just created.
   *
   * @var bool
   */
  private $fresh = FALSE;

  public function apply(): bool {
    $this->createImageFormatOptionGroup();
    $this->addDefaultImageFormats();

    return TRUE;
  }

  public function createImageFormatOptionGroup(): void {
    $count = civicrm_api3('OptionGroup', 'getcount', [
      'name' => CompuCertificateImageFormat::NAME,
    ]);

    if ((int) $count == 0) {
      $this->fresh = TRUE;
      civicrm_api3('OptionGroup', 'create', [
        'name' => CompuCertificateImageFormat::NAME,
        'title' => 'Certificate Image Formats',
        'is_reserved' => 1,
        'is_active' => 1,
      ]);
    }
  }

  public function addDefaultImageFormats(): void {
    if (!$this->fresh) {
      return;
    }

    $imageFormatBao = new CompuCertificateImageFormat();
    $jpgFormat = [
      'name' => 'JPG',
      'description' => 'JPG Image Format',
      'quality' => 10,
      'extension' => 'jpg',
      'is_default' => TRUE,
    ];
    $pngFormat = [
      'name' => 'PNG',
      'description' => 'PNG Image Format',
      'quality' => 10,
      'extension' => 'png',
      'is_default' => FALSE,
    ];

    $imageFormatBao->saveImageFormat($jpgFormat);
    $imageFormatBao->saveImageFormat($pngFormat);
  }

}
