<?php

namespace Drupal\sub_activity\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the sub_activity type entity class.
 *
 * @ConfigEntityType(
 *   id = "sub_activity_type",
 *   label = @Translation("Activity type", context = "Custom Entity Modules"),
 *   label_collection = @Translation("Activity types", context = "Custom Entity Modules"),
 *   label_singular = @Translation("activity type", context = "Custom Entity Modules"),
 *   label_plural = @Translation("activity types", context = "Custom Entity Modules"),
 *   label_count = @PluralTranslation(
 *     singular = "@count activity type",
 *     plural = "@count activity types",
 *     context = "Custom Entity Modules",
 *   ),
 *   handlers = {
 *     "access" = "Drupal\entity\BundleEntityAccessControlHandler",
 *     "list_builder" = "Drupal\sub_activity\SubActivityTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\sub_activity\Form\SubActivityTypeForm",
 *       "edit" = "Drupal\sub_activity\Form\SubActivityTypeForm",
 *       "duplicate" = "Drupal\sub_activity\Form\SubActivityTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "local_task_provider" = {
 *       "default" = "Drupal\entity\Menu\DefaultEntityLocalTaskProvider",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer sub_activity_type",
 *   config_prefix = "sub_activity_type",
 *   bundle_of = "sub_activity",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *     "description",
 *     "traits",
 *     "locked",
 *   },
 *   links = {
 *     "add-form" = "/admin/content/we-activity-types/add",
 *     "edit-form" = "/admin/content/we-activity-types/{sub_activity_type}/edit",
 *     "duplicate-form" = "/admin/content/we-activity-types/{sub_activity_type}/duplicate",
 *     "delete-form" = "/admin/content/we-activity-types/{sub_activity_type}/delete",
 *     "collection" = "/admin/content/we-activity-types",
 *   }
 * )
 */
class SubActivityType extends ConfigEntityBundleBase implements SubActivityTypeInterface {

  /**
   * A brief description of this sub_activity type.
   *
   * @var string
   */
  protected $description;

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * {@inheritdoc}
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }

}
