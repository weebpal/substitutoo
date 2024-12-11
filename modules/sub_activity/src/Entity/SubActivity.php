<?php

namespace Drupal\sub_activity\Entity;

use Drupal\address\AddressInterface;
use Drupal\user\EntityOwnerTrait;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EntityChangedTrait;

/**
 * Defines the sub_activity entity class.
 *
 * @ContentEntityType(
 *   id = "sub_activity",
 *   label = @Translation("Activity Log", context = "Custom Entity Modules"),
 *   label_collection = @Translation("Activity items", context = "Custom Entity Modules"),
 *   label_singular = @Translation("activity item", context = "Custom Entity Modules"),
 *   label_plural = @Translation("activity items", context = "Custom Entity Modules"),
 *   label_count = @PluralTranslation(
 *     singular = "@count activity item",
 *     plural = "@count activity items",
 *     context = "Custom Entity Modules",
 *   ),
 *   bundle_label = @Translation("Activity type", context = "Custom Entity Modules"),
 *   handlers = {
 *     "event" = "Drupal\sub_activity\Event\SubActivityEvent",
 *     "storage" = "Drupal\sub_activity\SubActivityStorage",
 *     "access" = "Drupal\entity\EntityAccessControlHandler",
 *     "query_access" = "Drupal\entity\QueryAccess\QueryAccessHandler",
 *     "permission_provider" = "Drupal\entity\EntityPermissionProvider",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\sub_activity\SubActivityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\sub_activity\Form\SubActivityForm",
 *       "add" = "Drupal\sub_activity\Form\SubActivityForm",
 *       "edit" = "Drupal\sub_activity\Form\SubActivityForm",
 *       "duplicate" = "Drupal\sub_activity\Form\SubActivityForm",
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
 *   base_table = "sub_activity",
 *   data_table = "sub_activity_field_data",
 *   admin_permission = "administer sub_activity",
 *   permission_granularity = "bundle",
 *   translatable = TRUE,
 *   entity_keys = {
 *     "id" = "sub_activity_id",
 *     "uuid" = "uuid",
 *     "bundle" = "type",
 *     "label" = "name",
 *     "langcode" = "langcode",
 *     "owner" = "uid",
 *     "uid" = "uid",
 *   },
 *   links = {
 *     "canonical" = "/we-activity/{sub_activity}",
 *     "add-page" = "/we-activity/add",
 *     "add-form" = "/we-activity/add/{sub_activity_type}",
 *     "edit-form" = "/we-activity/{sub_activity}/edit",
 *     "duplicate-form" = "/we-activity/{sub_activity}/duplicate",
 *     "delete-form" = "/we-activity/{sub_activity}/delete",
 *     "delete-multiple-form" = "/admin/content/we-activity-items/delete",
 *     "collection" = "/admin/content/we-activity-items",
 *   },
 *   bundle_entity_type = "sub_activity_type",
 *   field_ui_base_route = "entity.sub_activity_type.edit_form",
 * )
 */
class SubActivity extends ContentEntityBase implements SubActivityInterface {

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

    $fields['type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel('Type')
      ->setDescription('The sub_activity type.')
      ->setSetting('target_type', 'sub_activity_type')
      ->setReadOnly(TRUE);

    $fields['uid']
      ->setLabel('Owner')
      ->setDescription('The sub_activity owner.')
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayConfigurable('form', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel('Name')
      ->setDescription('The sub_activity name.')
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
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
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

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel('Created')
      ->setDescription('The time when the sub_activity was created.')
      ->setTranslatable(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel('Changed')
      ->setDescription('The time when the sub_activity was last edited.')
      ->setTranslatable(TRUE);

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
