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
   */
    public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        $serializer_formats,
        LoggerInterface $logger,
        AccountInterface $current_user
    ) {
        parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
        $this->currentUser = $current_user;
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
            $container->get('current_user')
        );
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

      foreach ($data as $key => $value) {
        $parent = NULL;
        if ($value['parent_link'] != NULL) {
          $parent = $value['parent_link'];
        }

        $menu_link = MenuLinkContent::create([
            'title' => $value['menu_title'],
             'link' => ['uri' => $value['node_link']],
            'menu_name' => $value['menu_parent'],
            'parent' => $parent,
            'weight' => 0,
            ])->save();
        $this->logger->notice($this->t("Menu saved!\n"));
        }

        $message = $this->t("New Menu Created");

        return new ResourceResponse($message, 200);
    }
}
