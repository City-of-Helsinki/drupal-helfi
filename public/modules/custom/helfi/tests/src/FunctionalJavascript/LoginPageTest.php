<?php

declare(strict_types = 1);

namespace Drupal\Tests\helfi\FunctionalJavascript;

/**
 * Tests the front page.
 *
 * @group helfi
 */
class LoginPageTest extends TestBase {

  /**
   * Make sure we can log in.
   */
  public function testLoginPage() : void {
    $this->drupalGet('user/login');
    $this->assertSession()->pageTextNotContains('Errors');

    $this->drupalLogin($this->rootUser);
  }

}
