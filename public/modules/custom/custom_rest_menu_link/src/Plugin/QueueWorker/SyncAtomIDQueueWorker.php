<?php
namespace Drupal\custom_rest_menu_link\Plugin\QueueWorker;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
/**
 * Save queue item in a node.
 *
 * To process the queue items whenever Cron is run,
 * we need a QueueWorker plugin with an annotation witch defines
 * to witch queue it applied.
 *
 * @QueueWorker(
 *   id = "syncatomids_worker_queue",
 *   title = @Translation("Update menu links order"),
 *   cron = {"time" = 5}
 * )
 */
class SyncAtomIDQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {
  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;
  /**
   * Drupal\Core\Logger\LoggerChannelFactoryInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  private $loggerChannelFactory;
  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration,
                                    $plugin_id,
                                    $plugin_definition,
                              EntityTypeManagerInterface $entityTypeManager,
                              LoggerChannelFactoryInterface $loggerChannelFactory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->loggerChannelFactory = $loggerChannelFactory;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('logger.factory')
    );
  }
  /**
   * {@inheritdoc}
   */
  public function processItem($item) {
    // Check the values of $item.
    $menu_link_id = isset($item['menu_link_id']) && $item['menu_link_id']? $item['menu_link_id'] : NULL;
    $node_uuid = isset($item['node_uuid']) && $item['node_uuid'] ? $item['node_uuid'] : NULL;
    try {
      // Check if we have a menu link id and a node uuid.
      if (!$menu_link_id || !$node_uuid) {
        throw new \Exception('Missing menu link id or node uuid');
      }
      $menu_link_storage = \Drupal::entityTypeManager()
        ->getStorage('menu_link_content')
        ->load($menu_link_id);
      $menu_link_storage->parent = 'menu_link_content:' . $node_uuid;
      $menu_link_storage->save();

      // Log in the watchdog for debugging purpose.
      $this->loggerChannelFactory->get('debug')
        ->debug('Update menu link @id from queue %item',
          [
            '@id' => $menu_link_storage->id(),
            '%item' => print_r($item, TRUE),
          ]);
    }
    catch (\Exception $e) {
      $this->loggerChannelFactory->get('Warning')
        ->warning('Exception trow for queue @error',
          ['@error' => $e->getMessage()]);
    }
  }

  /**
   * Processes cron queues.
   */
  protected function processQueues() {
    // Grab the defined cron queues.
    foreach ($this->queueManager->getDefinitions() as $queue_name => $info) {
      if (isset($info['cron'])) {
        // Make sure every queue exists. There is no harm in trying to recreate
        // an existing queue.
        $this->queueFactory->get($queue_name)->createQueue();
        $queue_worker = $this->queueManager->createInstance($queue_name);
        $end = time() + (isset($info['cron']['time']) ? $info['cron']['time'] : 15);
        $queue = $this->queueFactory->get($queue_name);
        $lease_time = isset($info['cron']['time']) ?: NULL;
        while (time() < $end && ($item = $queue->claimItem($lease_time))) {
          try {
            $queue_worker->processItem($item->data);
            $queue->deleteItem($item);
          }
          catch (RequeueException $e) {
            // The worker requested the task be immediately requeued.
            $queue->releaseItem($item);
          }
          catch (SuspendQueueException $e) {
            // If the worker indicates there is a problem with the whole queue,
            // release the item and skip to the next queue.
            $queue->releaseItem($item);
            watchdog_exception('cron', $e);
            // Skip to the next queue.
            continue 2;
          }
          catch (\Exception $e) {
            // In case of any other kind of exception, log it and leave the item
            // in the queue to be processed again later.
            watchdog_exception('cron', $e);
          }
        }
      }
    }
  }
}
