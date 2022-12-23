<?php

class CRM_Certificate_Hook_PostInstall_AddCertificateImageFormatOptionGroup {

  public function apply(): bool {
    $this->createImageFormatOptionGroup();

    return TRUE;
  }

  public function createImageFormatOptionGroup(): void {
    $count = civicrm_api3('OptionGroup', 'getcount', [
      'name' => CRM_Certificate_BAO_CompuCertificateImageFormat::NAME,
    ]);

    if ((int) $count == 0) {
      civicrm_api3('OptionGroup', 'create', [
        'name' => CRM_Certificate_BAO_CompuCertificateImageFormat::NAME,
        'title' => 'Certificate Image Formats',
        'is_reserved' => 1,
        'is_active' => 1,
      ]);
    }
  }

}
