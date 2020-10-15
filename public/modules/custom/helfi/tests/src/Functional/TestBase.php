<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * The functional test base.
 */
abstract class TestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static array $modules = ['helfi'];

}
