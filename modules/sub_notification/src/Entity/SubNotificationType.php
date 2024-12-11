<?php

namespace Drupal\sub_notification\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the sub_notification type entity class.
 *
 * @ConfigEntityType(
 *   id = "sub_notification_type",
 *   label = @Translation("Notification type", context = "Custom Entity Modules"),
 *   label_collection = @Translation("Notification types", context = "Custom Entity Modules"),
 *   label_singular = @Translation("notification type", context = "Custom Entity Modules"),
 *   label_plural = @Translation("notification types", context = "Custom Entity Modules"),
 *   label_count = @PluralTranslation(
 *     singular = "@count notification type",
 *     plural = "@count notification types",
 *     context = "Custom Entity Modules",
 *   ),
 *   handlers = {
 *     "access" = "Drupal\entity\BundleEntityAccessControlHandler",
 *     "list_builder" = "Drupal\sub_notification\SubNotificationTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\sub_notification\Form\SubNotificationTypeForm",
 *       "edit" = "Drupal\sub_notification\Form\SubNotificationTypeForm",
 *       "duplicate" = "Drupal\sub_notification\Form\SubNotificationTypeForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     },
 *     "local_task_provider" = {
 *       "default" = "Drupal\entity\Menu\DefaultEntityLocalTaskProvider",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer sub_notification_type",
 *   config_prefix = "sub_notification_type",
 *   bundle_of = "sub_notification",
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
 *     "add-form" = "/admin/content/sub-notification-types/add",
 *     "edit-form" = "/admin/content/sub-notification-types/{sub_notification_type}/edit",
 *     "duplicate-form" = "/admin/content/sub-notification-types/{sub_notification_type}/duplicate",
 *     "delete-form" = "/admin/content/sub-notification-types/{sub_notification_type}/delete",
 *     "collection" = "/admin/content/sub-notification-types",
 *   }
 * )
 */
class SubNotificationType extends ConfigEntityBundleBase implements SubNotificationTypeInterface {

  /**
   * A brief description of this sub_notification type.
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
