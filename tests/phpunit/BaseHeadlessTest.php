<?php

use Civi\Test;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * Base test class.
 */
abstract class BaseHeadlessTest extends PHPUnit\Framework\TestCase implements
    HeadlessInterface,
    TransactionalInterface {

  /**
   * {@inheritDoc}
   */
  public function setUpHeadless() {
    return Test::headless()
      ->installMe(__DIR__)
      ->install(['uk.co.compucorp.civicase'])
      ->apply();
  }

}
