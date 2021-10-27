<?php
namespace Drupal\custom_rest_menu_link\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Menu\MenuTreeParameters;

/**
 * Provides route responses for the Example module.
 */
class SyncAtomIds extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The edited menu link.
   *
   * @var \Drupal\Core\Menu\MenuLinkInterface
   */
  protected $menuLink;

  /**
   * Inject services.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, MenuLinkManagerInterface $menu_link_manager) {
    $this->entityTypeManager = $entityTypeManager;
    $this->menuLinkManager = $menu_link_manager;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.menu.link')
    );
  }

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function syncPage() {
    $tree = \Drupal::menuTree()->load('schools', new MenuTreeParameters());

    if (count($this->loadMenu($tree)) <= 1) {
      return ['#markup' => 'No links found!'];
    }

    foreach ($this->loadMenu($tree) as $menu_item) {
      if (!empty($menu_item['node_parent_atom_id'])) {
        $node_load = $this->entityTypeManager->getStorage('node')->loadByProperties(['field_atomid' => $menu_item['node_parent_atom_id']]);
        if (!empty(array_values($node_load))) {
          $result_node_link = $this->menuLinkManager
            ->loadLinksByRoute('entity.node.canonical', ['node' => array_values($node_load)[0]->id()]);
          foreach ($result_node_link as $menu_item2) {
            if (is_object($menu_item2) && $menu_item2->getPluginDefinition()['menu_name'] == 'schools') {
              $id = $menu_item2->getPluginDefinition()['metadata']['entity_id'];
              $node_menu_link = $this->entityTypeManager
                ->getStorage('menu_link_content')
                ->load($id);
              $result = $this->menuLinkManager
                ->loadLinksByRoute('entity.node.canonical', ['node' => $menu_item['nodeid']]);
              foreach ($result as $menu_item2) {
                if (is_object($menu_item2) && $menu_item2->getPluginDefinition()['menu_name'] == 'schools') {
                  $id = $menu_item2->getPluginDefinition()['metadata']['entity_id'];
                  $menu_link = $this->entityTypeManager
                    ->getStorage('menu_link_content')
                    ->load($id);
                  $menu_link->parent = 'menu_link_content:' . $node_menu_link->uuid();
                  $menu_link->save();
                }
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
            'node_atom_id' => $this->entityTypeManager->getStorage('node')->load($item->link->getUrlObject()->getRouteParameters()['node'])->field_atomid->value,
            'node_parent_atom_id' => $this->entityTypeManager->getStorage('node')->load($item->link->getUrlObject()->getRouteParameters()['node'])->field_parent_atomid->value
          ];
        }
      }
    }
    return $menu;
  }

}
