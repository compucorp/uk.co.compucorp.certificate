<?php

class CRM_Certificate_Hook_Uninstall_RemoveCertificateImageFormatOptionGroup {

  /**
   * Removes image format option group.
   */
  public function apply() {
    $this->removeImageFormatOptionGroup();
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
      'api.OptionGroup.delete' => [],
    ]);
  }

}
