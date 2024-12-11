<?php

namespace Drupal\sub_notification\Entity;

use Drupal\address\AddressInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\user\UserInterface;

/**
 * Defines the sub_notification entity class.
 *
 * @ContentEntityType(
 *   id = "sub_notification",
 *   label = @Translation("Notification", context = "Custom Entity Modules"),
 *   label_collection = @Translation("Notification items", context = "Custom Entity Modules"),
 *   label_singular = @Translation("notification item", context = "Custom Entity Modules"),
 *   label_plural = @Translation("notification items", context = "Custom Entity Modules"),
 *   label_count = @PluralTranslation(
 *     singular = "@count notification item",
 *     plural = "@count notification items",
 *     context = "Custom Entity Modules",
 *   ),
 *   bundle_label = @Translation("Notification type", context = "Custom Entity Modules"),
 *   handlers = {
 *     "event" = "Drupal\sub_notification\Event\SubNotificationEvent",
 *     "storage" = "Drupal\sub_notification\SubNotificationStorage",
 *     "access" = "Drupal\entity\EntityAccessControlHandler",
 *     "query_access" = "Drupal\entity\QueryAccess\QueryAccessHandler",
 *     "permission_provider" = "Drupal\entity\EntityPermissionProvider",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\sub_notification\SubNotificationListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\sub_notification\Form\SubNotificationForm",
 *       "add" = "Drupal\sub_notification\Form\SubNotificationForm",
 *       "edit" = "Drupal\sub_notification\Form\SubNotificationForm",
 *       "duplicate" = "Drupal\sub_notification\Form\SubNotificationForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm"
 *     },
 *     "local_task_provider" = {
 *       "default" = "Drupal\entity\Menu\DefaultEntityLocalTaskProvider",
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\entity\Routing\AdminHtmlRouteProvider",
 *       "delete-multiple" = "Drupal\entity\Routing\DeleteMultipleRouteProvider",
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler"
 *   },
 *   base_table = "sub_notification",
 *   data_table = "sub_notification_field_data",
 *   admin_permission = "administer sub_notification",
 *   permission_granularity = "bundle",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "sub_notification_id",
 *     "uuid" = "uuid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "langcode" = "langcode",
 *     "owner" = "uid",
 *     "uid" = "uid",
 *   },
 *   links = {
 *     "canonical" = "/sub-notification/{sub_notification}",
 *     "add-page" = "/sub-notification/add",
 *     "add-form" = "/sub-notification/add/{sub_notification_type}",
 *     "edit-form" = "/sub-notification/{sub_notification}/edit",
 *     "duplicate-form" = "/sub-notification/{sub_notification}/duplicate",
 *     "delete-form" = "/sub-notification/{sub_notification}/delete",
 *     "delete-multiple-form" = "/admin/content/sub-notification-items/delete",
 *     "collection" = "/admin/content/sub-notification-items",
 *   },
 *   bundle_entity_type = "sub_notification_type",
 *   field_ui_base_route = "entity.sub_notification_type.edit_form",
 * )
 */
class SubNotification extends ContentEntityBase implements SubNotificationInterface {

  use EntityOwnerTrait;
  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);
    $fields += static::ownerBaseFieldDefinitions($entity_type);

    $fields['nid'] = BaseFieldDefinition::create('integer')
    ->setLabel(t('Notification ID'))
    ->setDescription(t('The ID of the notification.'))
    ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'text_default',
        'weight' => 10,
      ])
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['body'] = BaseFieldDefinition::create('text_long')
    ->setLabel(t('Body'))
    ->setDescription(t('Body'))
    ->setRequired(TRUE)
    ->setDisplayOptions('view', [
      'label' => 'hidden',
      'type' => 'text_default',
      'weight' => 10,
    ])
    ->setDisplayOptions('form', [
      'type' => 'text_textarea',
      'weight' => 10,
    ])
    ->setDisplayConfigurable('form', TRUE)
    ->setDisplayConfigurable('view', TRUE);

    $fields['source'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Source'))
      ->setDescription(t('The source of the notification in the format key: value.'))
      ->setSettings([
        'default_value' => NULL,
        'max_length' => 255, 
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

  $fields['link'] = BaseFieldDefinition::create('uri')
    ->setLabel(t('Link'))
    ->setDescription(t('A link associated with the notification.'))
    ->setDisplayOptions('view', [
      'label' => 'inline',
      'type' => 'entity_reference_label',
      'weight' => 30,
    ])
    ->setDisplayOptions('form', [
      'type' => 'entity_reference_autocomplete',
      'weight' => 30,
    ])
    ->setDisplayConfigurable('view', TRUE)
    ->setDisplayConfigurable('form', TRUE);

    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The sub_notification type.'))
      ->setSetting('target_type', 'sub_notification_type')
      ->setReadOnly(TRUE);

    $fields['received_uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Received uid'))
      ->setDescription(t('The receiver of the notification.'))
      ->setSetting('target_type', 'user')
      ->setSetting('multiple', TRUE)
      ->setCardinality(FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED)
      ->setDisplayOptions('form', [
        'type' => 'options_buttons',
        'weight' => -2,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['f_assignment'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Assignment'))
      ->setSetting('target_type', 'sub_assignment')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 1,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid']
      ->setLabel(t('Owner'))
      ->setDescription(t('The owner.'))
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('Name'))
      ->setRequired(TRUE)
      ->setTranslatable(TRUE)
      ->setSettings([
        'default_value' => '',
        'max_length' => 255,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

      $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Read Status'))
      ->setDescription(t('Status of post action entity'))
      ->setDefaultValue(TRUE)
      ->setSettings(['on_label' => 'Yes', 'off_label' => 'No'])
      ->setDisplayOptions('view', [
        'label' => 'visible',
        'type' => 'boolean',
        'weight' => 2,
      ])
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => 2,
      ])
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time when the sub_notification was created.'))
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time when the sub_notification was last edited.'))
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    return $fields;
  }

  /**
   * Default value callback for the 'timezone' base field definition.
   *
   * @see ::baseFieldDefinitions()
   *
   * @return array
   *   An array of default values.
   */
  public static function getSiteTimezone() {
    $site_timezone = \Drupal::config('system.date')->get('timezone.default');
    if (empty($site_timezone)) {
      $site_timezone = @date_default_timezone_get();
    }

    return [$site_timezone];
  }

  /**
   * Gets the allowed values for the 'timezone' base field.
   *
   * @return array
   *   The allowed values.
   */
  public static function getTimezones() {
    return system_time_zones(NULL, TRUE);
  }

  /**
   * Gets the allowed values for the 'billing_countries' base field.
   *
   * @return array
   *   The allowed values.
   */
  public static function getAvailableCountries() {
    return \Drupal::service('address.country_repository')->getList();
  }

}
