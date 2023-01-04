<?php

use CRM_Certificate_ExtensionUtil as E;
use CRM_Certificate_Hook_PostInstall_AddCertificateImageFormatOptionGroup as AddCertificateImageFormatOptionGroup;
use CRM_Certificate_Hook_Uninstall_RemoveCertificateImageFormatOptionGroup as RemoveCertificateImageFormatOptionGroup;

/**
 * Collection of upgrade steps.
 */
class CRM_Certificate_Upgrader extends CRM_Certificate_Upgrader_Base {

  /**
   * {@inheritDoc}
   */
  public function onPostInstall(): void {
    try {
      $steps = [
        new AddCertificateImageFormatOptionGroup(),
      ];

      foreach ($steps as $step) {
        $step->apply();
      }
    }
    catch (\Throwable $th) {
      \Civi::log()->error(E::ts("Certificate PostInstall Error"), [
        'context' => [
          'backtrace' => $th->getTraceAsString(),
          'message' => $th->getMessage(),
        ],
      ]);
    }
  }

  /**
   * Remove extension data when uninstalled.
   */
  public function uninstall(): void {
    try {
      $steps = [
        new RemoveCertificateImageFormatOptionGroup(),
      ];

      foreach ($steps as $step) {
        $step->apply();
      }
    }
    catch (\Throwable $th) {
      \Civi::log()->error(E::ts("Certificate Uninstall Error"), [
        'context' => [
          'backtrace' => $th->getTraceAsString(),
          'message' => $th->getMessage(),
        ],
      ]);
    }
  }

  /**
   * This upgrade creates compucertificate_event_attribute table
   */
  public function upgrade_0001() {
    $this->ctx->log->info('Applying update 0001');
    $this->executeSqlFile('sql/upgrade_0001.sql');

    return TRUE;
  }

  /**
   * Adds columns and option group for image format support.
   */
  public function upgrade_0002() {
    $this->ctx->log->info('Applying update 0002');
    (new AddCertificateImageFormatOptionGroup)->apply();
    $this->executeSqlFile('sql/upgrade_0002.sql');

    return TRUE;
  }

}
