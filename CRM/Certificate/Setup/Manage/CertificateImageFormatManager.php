<?php

use CRM_Certificate_BAO_CompuCertificateImageFormat as CompuCertificateImageFormat;

class CRM_Certificate_Setup_Manage_CertificateImageFormatManager extends CRM_Certificate_Setup_Manage_AbstractManager {

  /**
   * Reflects if the option group was just created.
   *
   * @var bool
   */
  private $fresh = FALSE;

  public function create(): void {
    $this->createImageFormatOptionGroup();
    $this->addDefaultImageFormats();
  }

  public function remove(): void {
    $this->removeImageFormatOptionGroup();
  }

  protected function toggle($status): void {
    civicrm_api3('OptionGroup', 'get', [
      'sequential' => 1,
      'name' => CRM_Certificate_BAO_CompuCertificateImageFormat::NAME,
      'api.OptionGroup.create' => ['id' => '$value.id', 'is_active' => $status],
    ]);
  }

  /**
   * Creates image format option group.
   *
   * @throws CiviCRM_API3_Exception
   */
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

  /**
   * Adds the default image formats option values.
   */
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

  /**
   * Removes image format option group.
   *
   * @throws CiviCRM_API3_Exception
   */
  public function removeImageFormatOptionGroup(): void {
    civicrm_api3('OptionGroup', 'get', [
      'return' => ['id'],
      'name' => CRM_Certificate_BAO_CompuCertificateImageFormat::NAME,
      'api.OptionGroup.delete' => ['id' => '$value.id'],
    ]);
  }

}
