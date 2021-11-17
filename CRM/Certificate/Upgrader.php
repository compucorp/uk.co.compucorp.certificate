<?php

/**
 * Collection of upgrade steps.
 */
class CRM_Certificate_Upgrader extends CRM_Certificate_Upgrader_Base {

  /**
   * This upgrade creates compucertificate_event_attribute table
   */
  public function upgrade_0001() {
    $this->ctx->log->info('Applying update 0001');
    $this->executeSqlFile('sql/upgrade_0001.sql');

    return TRUE;
  }

}
