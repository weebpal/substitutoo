<?php

declare(strict_types=1);

namespace Drupal\sub_location\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\sub_location\SubstitutooLocationInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the substitutoo location entity class.
 *
 * @ContentEntityType(
 *   id = "sub_location",
 *   label = @Translation("Substitutoo Location"),
 *   label_collection = @Translation("Substitutoo Locations"),
 *   label_singular = @Translation("substitutoo location"),
 *   label_plural = @Translation("substitutoo locations"),
 *   label_count = @PluralTranslation(
 *     singular = "@count substitutoo locations",
 *     plural = "@count substitutoo locations",
 *   ),
 *   handlers = {
 *     "access" = "Drupal\entity\EntityAccessControlHandler",
 *     "query_access" = "Drupal\entity\QueryAccess\QueryAccessHandler",
 *     "permission_provider" = "Drupal\entity\EntityPermissionProvider",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\sub_location\SubstitutooLocationListBuilder",
 *     "views_data" = "Drupal\sub_location\SubstitutooLocationViewsData",
 *     "permission_provider" = "Drupal\entity\EntityPermissionProvider",
 *     "form" = {
 *       "add" = "Drupal\sub_location\Form\SubstitutooLocationForm",
 *       "edit" = "Drupal\sub_location\Form\SubstitutooLocationForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler"
 *   },
 *   base_table = "sub_location",
 *   admin_permission = "administer sub_location",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/substitutoo/sub-location",
 *     "add-form" = "/sub-location/add",
 *     "canonical" = "/sub-location/{sub_location}",
 *     "edit-form" = "/sub-location/{sub_location}/edit",
 *     "delete-form" = "/sub-location/{sub_location}/delete",
 *     "delete-multiple-form" = "/admin/substitutoo/sub-location/delete-multiple",
 *   },
 *   field_ui_base_route = "entity.sub_location.settings",
 * )
 */
final class SubstitutooLocation extends ContentEntityBase implements SubstitutooLocationInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      // If no owner has been set explicitly, make the anonymous user the owner.
      $this->setOwnerId(0);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    if ($this->get('field_is_root_location')->value) {
      $field_order_value = $this->get('field_order_value')->value;
      $field_deep = $this->get('field_deep')->value;
      if (empty($field_order_value) || empty($field_deep)) {
        $this->set('field_order_value', $this->id() * 1000000);
        $this->set('field_deep', 1);
        $this->save();
      }
      else {
        $this->setLocationTrailForNestedLocations([], $field_deep, $field_order_value);
      }
    }
  }

  /**
   * Recursively sets the field_location_trail for nested locations.
   */
  protected function setLocationTrailForNestedLocations($location_trail = [], $field_deep = 1, $field_order_value = 0) {
    $location_trail[] = $this->id();
    $nested_locations = $this->get('field_nested_locations')->referencedEntities();
    $new_order_value = $field_order_value;
    $new_deep = $field_deep + 1;
    $delta = 1000000;
    if($new_deep == 2) {
      $delta = 1000;
    }
    else {
      $delta = 1;
    }
      
    foreach ($nested_locations as $nested_location) {
      if ($nested_location instanceof SubstitutooLocation && $nested_location->hasField('field_location_trail')) {
        $new_order_value = $new_order_value + $delta;
        $nested_location->set('field_location_trail', $location_trail);
        $nested_location->set('field_order_value', $new_order_value);
        $nested_location->set('field_deep', $new_deep);
        $nested_location->save();
        $nested_location->setLocationTrailForNestedLocations($location_trail, $new_deep, $new_order_value);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type): array {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['label'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Label'))
      ->setRequired(TRUE)
      ->setSetting('max_length', 255)
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'string',
        'weight' => -5,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['status'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Status'))
      ->setDefaultValue(TRUE)
      ->setSetting('on_label', 'Enabled')
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'settings' => [
          'display_label' => FALSE,
        ],
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'boolean',
        'label' => 'above',
        'weight' => 0,
        'settings' => [
          'format' => 'enabled-disabled',
        ],
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['description'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Description'))
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'type' => 'text_default',
        'label' => 'above',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Author'))
      ->setSetting('target_type', 'user')
      ->setDefaultValueCallback(self::class . '::getDefaultEntityOwner')
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => 60,
          'placeholder' => '',
        ],
        'weight' => 15,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'author',
        'weight' => 15,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Authored on'))
      ->setDescription(t('The time that the substitutoo location was created.'))
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 20,
      ])
      ->setDisplayConfigurable('view', TRUE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the substitutoo location was last edited.'));

    return $fields;
  }

}
