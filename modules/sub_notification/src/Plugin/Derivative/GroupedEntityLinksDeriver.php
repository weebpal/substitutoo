<?php

namespace Drupal\sub_notification\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derivative class that provides the menu links for the Optimus entities.
 */
class GroupedEntityLinksDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * Creates a GroupedEntityLinksDeriver instance.
   */
  public function __construct($base_plugin_id, EntityTypeManagerInterface $entity_type_manager, EntityTypeBundleInfoInterface $entity_type_bundle_info) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('entity_type.manager'),
      $container->get('entity_type.bundle.info')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $links = [];

    foreach ($this->entityTypeManager->getDefinitions() as $entity_type) {
      $entity_type_id = $entity_type->id();

      if ($entity_type_id === 'sub_notification') {
        $links["{$entity_type_id}.add"] = [
          'title' => t('Add'),
          'route_name' => 'entity.sub_notification.add_page',
          'parent' => 'entity.sub_notification.collection',
        ] + $base_plugin_definition;

        if ($entity_type->getBundleEntityType() !== NULL) {
          foreach ($this->entityTypeBundleInfo->getBundleInfo($entity_type_id) as $bundle_id => $bundle) {
            $links["{$entity_type_id}.{$bundle_id}.add"] = [
              'title' => $bundle['label'],
              'route_name' => "entity.{$entity_type_id}.add_form",
              'route_parameters' => [
                'sub_notification_type' => $bundle_id,
              ],
              'parent' => "sub_notification.grouped_entity_links:{$entity_type_id}.add",
            ] + $base_plugin_definition;
          }
        }
      }

    }

    return $links;
  }

}
