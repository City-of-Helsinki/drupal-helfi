<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

/**
 * The functional javascript test base.
 */
abstract class TestBase extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['helfi'];

  /**
   * The default theme.
   *
   * @var string
   *
   * @todo Replace this with our custom theme.
   */
  protected $defaultTheme = 'stark';

}
