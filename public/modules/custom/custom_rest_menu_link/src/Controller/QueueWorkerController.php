<?php
namespace Drupal\custom_rest_menu_link\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Queue\QueueFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use GuzzleHttp\ClientInterface;
use Drupal\Core\Url;
/**
 * Class ExQueueController.
 *
 * Demonstrates the use of the Queue API
 * There is two routes.
 * 1) \Drupal\custom_rest_menu_link\Controller\QueueWorkerController::getData
 * The getData() methods allows to load external data and
 * for each array element create a queue element
 * Then on Cron run, we update the position of the menu link for each element with
 * 2) \Drupal\custom_rest_menu_link\Controller\QueueWorkerController::deleteTheQueue
 * The deleteTheQueue() methods delete the queue "syncatomids_worker_queue"
 * and all its elements
 * Once the queue is created with tha data, on Cron run
 * we updated the order of menu for each item in the queue with the QueueWorker
 */
class QueueWorkerController extends ControllerBase {
  /**
   * Drupal\Core\Messenger\MessengerInterface definition.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;
  /**
   * Symfony\Component\DependencyInjection\ContainerAwareInterface definition.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerAwareInterface
   */
  protected $queueFactory;
  /**
   * GuzzleHttp\ClientInterface definition.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $client;
  /**
   * Inject services.
   */
  public function __construct(MessengerInterface $messenger, QueueFactory $queue, ClientInterface $client) {
    $this->messenger = $messenger;
    $this->queueFactory = $queue;
    $this->client = $client;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('messenger'),
      $container->get('queue'),
      $container->get('http_client')
    );
  }
  /**
   * Delete the queue 'syncatomids_worker_queue'.
   *
   * Remember that the command drupal dq checks first for a queue worker
   * and if it exists, DC suposes that a queue exists.
   */
  public function deleteTheQueue() {
    $this->queueFactory->get('syncatomids_worker_queue')->deleteQueue();
    $go_back = Url::fromRoute('custom_rest_menu_link.atomid-sync-worker');
    return [
      '#type' => 'markup',
      '#markup' => $this->t('The queue "syncatomids_worker_queue" has been deleted. <a href="@go_back">< Go back to queue</a>', [
        '@go_back' => $go_back->toString(),
      ]),
    ];
  }
  /**
   * Getdata from external source and create a item queue for each data.
   *
   * @return array
   *   Return string.
   */
  public function getData() {
    // 1. Get data into an array of objects
    // 2. Get the queue and the total of items before the operations
    // 3. For each element of the array, create a new queue item
    // 1. Get data into an array of objects
    // We can choose between two methods
    // getDataFromMenu()

    if ($data = $this->getDataFromMenu()) {
      // 2. Get the queue and the total of items before the operations
      // Get the queue implementation for 'syncatomids_worker_queue' queue.
      $queue = $this->queueFactory->get('syncatomids_worker_queue');
      // Get the total of items in the queue before adding new items.
      $totalItemsBefore = $queue->numberOfItems();
      // 3. For each element of the array, create a new queue item.
      foreach ($data as $element) {
        // Create new queue item.
        $queue->createItem($element);
      }
      // 4. Get the total of item in the Queue.
      $totalItemsAfter = $queue->numberOfItems();
      if ($totalItemsAfter == 0) {
        return [
          '#type' => 'markup',
          '#markup' => $this->t('No data found'),
        ];
      }
      else {
        // 5. Get what's in the queue now.
        $tableVariables = $this->getItemList($queue);
        $clear_queue = Url::fromRoute('custom_rest_menu_link.atomid-sync-delete-worker');
        $finalMessage = $this->t('The Queue had @totalBefore items. We should have added @count items in the Queue. Now the Queue has @totalAfter items. <a href="@clear_queue_link">Clear queue</a>',
          [
            '@count' => count($data),
            '@totalAfter' => $totalItemsAfter,
            '@totalBefore' => $totalItemsBefore,
            '@clear_queue_link' => $clear_queue->toString(),
          ]);
        return [
          '#type' => 'table',
          '#caption' => $finalMessage,
          '#header' => $tableVariables['header'],
          '#rows' => $tableVariables['rows'],
          '#attributes' => $tableVariables['attributes'],
          '#sticky' => $tableVariables['sticky'],
          //      'empty' => $this->t('No items.'),
        ];
      }
    } else {
      return [
        '#type' => 'markup',
        '#markup' => $this->t('No data found'),
      ];
    }
  }

  /**
   * Generate an array of objects from an external RSS file.
   *
   * @return array|bool
   *   Return an array or false
   */
  protected function getDataFromMenu() {
    $tree = \Drupal::menuTree()->load('schools', new \Drupal\Core\Menu\MenuTreeParameters());

    if (count($this->loadMenu($tree)) <= 1) {
      return ['#markup' => 'No links found!'];
    }

    foreach ($this->loadMenu($tree) as $menu_item) {
//      if (!empty($menu_item['node_parent_atom_id'])) {
//        $node_load = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties(['field_atomid' => $menu_item['node_parent_atom_id']]);
//        if (!empty(array_values($node_load))) {
//          $result_node_link = \Drupal::service('plugin.manager.menu.link')
//            ->loadLinksByRoute('entity.node.canonical', ['node' => array_values($node_load)[0]->id()]);
//          foreach ($result_node_link as $menu_item2) {
//            if (is_object($menu_item2)
//              && ($menu_item2->getPluginDefinition()['menu_name'] == 'schools' || $menu_item2->getPluginDefinition()['menu_name'] == 'daycare')) {
//              $id = $menu_item2->getPluginDefinition()['metadata']['entity_id'];
//              $node_menu_link = \Drupal::entityTypeManager()
//                ->getStorage('menu_link_content')
//                ->load($id);
//
//              $result = \Drupal::service('plugin.manager.menu.link')
//                ->loadLinksByRoute('entity.node.canonical', ['node' => $menu_item['nodeid']]);
//              foreach ($result as $menu_item2) {
//                if (is_object($menu_item2) && $menu_item2->getPluginDefinition()['menu_name'] == 'schools'
//                  && 'menu_link_content:' . $node_menu_link->uuid() != $menu_item2->getPluginDefinition()['parent']) {
//                  $id = $menu_item2->getPluginDefinition()['metadata']['entity_id'];
//                  $content[] = [
//                    'title' => $menu_item2->getPluginDefinition()['title'],
//                    'menu_link_id' => $id,
//                    'node_uuid' => $node_menu_link->uuid()
//                  ];
//                }
//              }
//            }
//
//          }
//        }
//      }
    }
    if (empty($content)) {
      return FALSE;
    }
    return $content;
  }

  /**
   * Load menu.
   */
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
            'node_parent_atom_id' => \Drupal::entityTypeManager()->getStorage('node')->load($item->link->getUrlObject()->getRouteParameters()['node'])->field_parent_atomid->value
          ];
        }
      }
    }
    return $menu;
  }

  /**
   * Get all items of queue.
   *
   * Next place them in an array so we can retrieve them in a table.
   *
   * @param object $queue
   *   A queue object.
   *
   * @return array
   *   A table array for rendering.
   */
  protected function getItemList($queue) {
    $retrieved_items = [];
    $items = [];
    // Claim each item in queue.
    while ($item = $queue->claimItem()) {
      $retrieved_items[] = [
        'data' => [$item->data['title'], $item->item_id],
      ];
      // Track item to release the lock.
      $items[] = $item;
    }
    // Release claims on items in queue.
    foreach ($items as $item) {
      $queue->releaseItem($item);
    }
    // Put the items in a table array for rendering.
    $tableTheme = [
      'header' => [$this->t('Title'), $this->t('ID')],
      'rows'   => $retrieved_items,
      'attributes' => [],
      'caption' => '',
      'colgroups' => [],
      'sticky' => TRUE,
//      'empty' => $this->t('No items.'),
    ];
    return $tableTheme;
  }
}
