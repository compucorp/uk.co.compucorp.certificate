<?php

use CRM_Certificate_ExtensionUtil as E;
use CRM_Certificate_Setup_Manage_CertificateImageFormatManager as CertificateImageFormatManager;

/**
 * Collection of upgrade steps.
 */
class CRM_Certificate_Upgrader extends CRM_Extension_Upgrader_Base {

  /**
   * {@inheritDoc}
   */
  public function postInstall(): void {
    try {
      $steps = [
        new CertificateImageFormatManager(),
      ];

      foreach ($steps as $step) {
        $step->create();
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
        new CertificateImageFormatManager(),
      ];

      foreach ($steps as $step) {
        $step->remove();
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
   * Reactivate disabled entities.
   */
  public function enable() {
    $steps = [
      new CertificateImageFormatManager(),
    ];

    foreach ($steps as $step) {
      $step->activate();
    }
  }

  /**
   * Deactivate neccessary entities.
   */
  public function disable() {
    $steps = [
      new CertificateImageFormatManager(),
    ];

    foreach ($steps as $step) {
      $step->deactivate();
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
    (new CertificateImageFormatManager)->create();
    $this->executeSqlFile('sql/upgrade_0002.sql');

    return TRUE;
  }

  /**
   * Adds columns for download type support.
   */
  public function upgrade_0003() {
    $this->ctx->log->info('Applying update 0003');
    $this->executeSqlFile('sql/upgrade_0003.sql');

    return TRUE;
  }

  /**
   * Adds column for storing configured event type filters.
   */
  public function upgrade_0004() {
    $this->ctx->log->info('Applying update 0004');
    $this->executeSqlFile('sql/upgrade_0004.sql');

    return TRUE;
  }

}
