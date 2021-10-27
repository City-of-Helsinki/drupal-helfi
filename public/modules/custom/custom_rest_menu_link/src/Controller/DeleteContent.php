<?php
namespace Drupal\custom_rest_menu_link\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;

/**
 * Provides route responses for the Example module.
 */
class DeleteContent extends ControllerBase {
  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function deleteContent() {
    $nids = \Drupal::entityQuery('node')->execute();
    foreach ($nids as $nid) {
      if ($nid == '10258' || $nid == '4166') {
        continue;
      }
      Node::load($nid)->delete();
    }
    return [
      '#markup' => 'All nodes deleted',
    ];
  }
}
