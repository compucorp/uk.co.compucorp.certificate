<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

abstract class BaseHeadlessTest extends PHPUnit_Framework_TestCase implements
    HeadlessInterface,
    TransactionalInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->install(['uk.co.compucorp.civicase'])
      ->apply();
  }

  public function getDate($from = "0 days") {
    return date('Y-m-d', strtotime(date('Y-m-d') . " $from"));
  }

}
