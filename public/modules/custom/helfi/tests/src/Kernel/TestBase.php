<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * The kernel test base.
 */
abstract class TestBase extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['helfi'];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->installConfig(['helfi']);
  }

}
