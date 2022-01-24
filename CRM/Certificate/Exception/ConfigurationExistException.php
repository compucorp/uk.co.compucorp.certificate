<?php

class CRM_Certificate_Exception_ConfigurationExistException extends CRM_Core_Exception {

  public function __construct() {
    parent::__construct('A certificate configuration exist for the entity that satisfies either the selected status or type');
  }

}
