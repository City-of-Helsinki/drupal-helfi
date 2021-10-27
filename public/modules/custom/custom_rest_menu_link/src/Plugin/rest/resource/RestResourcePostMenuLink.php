<?php

namespace Drupal\custom_rest_menu_link\Plugin\rest\resource;

use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\Core\Session\AccountInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Core\Menu\MenuTreeParameters;

/**
 * Provides a resource to post nodes.
 *
 * @RestResource(
 *   id = "rest_resource_post_menu_links",
 *   label = @Translation("Create menu links using Rest API with POST method"),
 *   serialization_class = "Drupal\custom_rest_menu_link\normalizer\JsonDenormalizer",
 *   uri_paths = {
 *     "create" = "/rest/api/post/menu-create"
 *   }
 * )
 */
class RestResourcePostMenuLink extends ResourceBase
{
    use StringTranslationTrait;

  /**
   * The currently authenticated user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
    protected $currentUser;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a Drupal\rest\Plugin\ResourceBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The currently authenticated user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager parameter.
   */
    public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        $serializer_formats,
        LoggerInterface $logger,
        AccountInterface $current_user,
        EntityTypeManagerInterface $entityTypeManager
    ) {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
        $this->currentUser = $current_user;
        $this->entityTypeManager = $entityTypeManager;
    }

  /**
   * {@inheritdoc}
   */
    public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
    {
        return new static(
            $configuration,
            $plugin_id,
            $plugin_definition,
            $container->getParameter('serializer.formats'),
            $container->get('logger.factory')->get('rest'),
            $container->get('current_user'),
            $container->get('entity_type.manager')
        );
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
             'pluginid' => $item->link->pluginId,
             'nodeid' => $item->link->getUrlObject()->getRouteParameters()['node'],
             'node_atom_id' => $this->entityTypeManager->getStorage('node')->load($item->link->getUrlObject()->getRouteParameters()['node'])->field_atomid->value,
             'node_parent_atom_id' => $this->entityTypeManager->getStorage('node')->load($item->link->getUrlObject()->getRouteParameters()['node'])->field_parent_atomid->value,           ];
         }
      }
    }
    return $menu;
  }

  /**
   * Responds to POST requests.
   *
   * Creates a new node.
   *
   * @param mixed $data
   *   Data to create the node.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
    public function post($data)
    {

      // Use current user after pass authentication to validate access.
        if (!$this->currentUser->hasPermission('restful post rest_resource_post_menu_links')) {
          // Display the default access denied page.
           throw new AccessDeniedHttpException('Access Denied.');
        }


        $parent = NULL;
        if ($data['menu_parent'] != NULL && $data['menu_parent'] == 'schools') {
          $parent = "menu_link_content:0f788e10-fb7a-49d6-af6d-4682785ecb0f";
        } elseif ($data['menu_parent'] != NULL && $data['menu_parent'] == 'daycare') {
          $parent = "menu_link_content:ef334336-70b7-47b0-9e6b-6579ed351b4d";
        }

        MenuLinkContent::create([
            'title' => $data['menu_title'],
            'link' => ['uri' => 'entity:node/' . $data['node_id']],
            'menu_name' => $data['menu_parent'],
            'parent' => $parent,
            'weight' => 0,
            ])->save();
          $this->logger->notice($this->t("Menu saved!\n"));


        $message = "New Menu Created with parent id: " . $parent;
        return new ResourceResponse($message, 200);
    }
}
