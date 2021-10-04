<?php
namespace Drupal\custom_rest_menu_link\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route responses for the Example module.
 */
class SyncAtomIds extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function syncPage() {
    $tree = \Drupal::menuTree()->load('schools', new \Drupal\Core\Menu\MenuTreeParameters());
    if (count($this->loadMenu($tree)) <= 1) {
      return ['#markup' => 'No links found!'];
    }
    foreach ($this->loadMenu($tree) as $menu_item) {
      if (!empty($menu_item['node_parent_atom_id'])) {
        $node_load = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['field_atomid' => $menu_item['node_parent_atom_id']]);
        dump(array_values($node_load));
        $result_node_link = \Drupal::service('plugin.manager.menu.link')->loadLinksByRoute('entity.node.canonical', ['node' => array_values($node_load)[0]->id()]);

        foreach ($result_node_link as $menu_item2) {
          if (is_object($menu_item2) && $menu_item2->getPluginDefinition()['menu_name'] == 'schools') {
            $id = $menu_item2->getPluginDefinition()['metadata']['entity_id'];
            $node_menu_link = \Drupal::entityTypeManager()->getStorage('menu_link_content')->load($id);
            $result = \Drupal::service('plugin.manager.menu.link')->loadLinksByRoute('entity.node.canonical', ['node' => $menu_item['nodeid']]);
            foreach ($result as $menu_item2) {
              if (is_object($menu_item2) && $menu_item2->getPluginDefinition()['menu_name'] == 'schools') {
                $id = $menu_item2->getPluginDefinition()['metadata']['entity_id'];
                $menu_link = \Drupal::entityTypeManager()->getStorage('menu_link_content')
                  ->load($id);
                $menu_link->parent = 'menu_link_content:' . $node_menu_link->uuid();
                $menu_link->save();
              }
            }
          }
        }
      }

      if (!empty($menu_item['node_parent_parent_atom_id'])) {
        $node_parent_load = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['field_atomid' => $menu_item['node_parent_parent_atom_id']]);
        $result_node_parent_link = \Drupal::service('plugin.manager.menu.link')->loadLinksByRoute('entity.node.canonical', ['node' => array_values($node_parent_load)[0]->id()]);

        foreach ($result_node_parent_link as $menu_item2) {
          if (is_object($menu_item2) && $menu_item2->getPluginDefinition()['menu_name'] == 'schools') {
            $id = $menu_item2->getPluginDefinition()['metadata']['entity_id'];
            $node_menu_link = \Drupal::entityTypeManager()->getStorage('menu_link_content')->load($id);
            $result = \Drupal::service('plugin.manager.menu.link')->loadLinksByRoute('entity.node.canonical', ['node' => $menu_item['nodeid']]);
            foreach ($result as $menu_item2) {
              if (is_object($menu_item2) && $menu_item2->getPluginDefinition()['menu_name'] == 'schools') {
                $id = $menu_item2->getPluginDefinition()['metadata']['entity_id'];
                $menu_link = \Drupal::entityTypeManager()->getStorage('menu_link_content')
                  ->load($id);
                $menu_link->parent = 'menu_link_content:' . $node_menu_link->uuid();
                $menu_link->save();
              }
            }
          }
        }
      }

    }
    return ['#markup' => 'Menu syncronized'];
  }

  function loadMenu($tree) {
    $menu = [];
    foreach ($tree as $item) {
      if($item->link->isEnabled()) {
        if ($item->hasChildren) {
          $menu = $this->loadMenu($item->subtree);
        }
        if ($item->link->getUrlObject()->isRouted() == TRUE) {

          $menu[] = [
            'title' => $item->link->getTitle(),
            'pluginid' => $item->link->getPluginId(),
            'nodeid' => $item->link->getUrlObject()->getRouteParameters()['node'],
            'node_atom_id' => \Drupal::entityTypeManager()->getStorage('node')->load($item->link->getUrlObject()->getRouteParameters()['node'])->field_atomid->value,
            'node_parent_atom_id' => \Drupal::entityTypeManager()->getStorage('node')->load($item->link->getUrlObject()->getRouteParameters()['node'])->field_parent_atomid->value,
            'node_parent_parent_atom_id' => \Drupal::entityTypeManager()->getStorage('node')->load($item->link->getUrlObject()->getRouteParameters()['node'])->field_parent_parent_atomid->value
          ];
        }
      }
    }
    return $menu;
  }

}
