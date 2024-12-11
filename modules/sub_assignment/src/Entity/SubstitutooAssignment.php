<?php

declare(strict_types=1);

namespace Drupal\sub_assignment\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\sub_assignment\SubstitutooAssignmentInterface;
use Drupal\user\EntityOwnerTrait;

/**
 * Defines the substitutoo assignment entity class.
 *
 * @ContentEntityType(
 *   id = "sub_assignment",
 *   label = @Translation("Shift Assignment"),
 *   label_collection = @Translation("Shift Assignments"),
 *   label_singular = @Translation("Shift Assignment"),
 *   label_plural = @Translation("substitutoo assignments"),
 *   label_count = @PluralTranslation(
 *     singular = "@count substitutoo assignments",
 *     plural = "@count substitutoo assignments",
 *   ),
 *   handlers = {
 *     "access" = "Drupal\entity\EntityAccessControlHandler",
 *     "query_access" = "Drupal\entity\QueryAccess\QueryAccessHandler",
 *     "permission_provider" = "Drupal\entity\EntityPermissionProvider",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "storage_schema" = "Drupal\sub_assignment\SubstitutooAssignmentStorageSchema",
 *     "list_builder" = "Drupal\sub_assignment\SubstitutooAssignmentListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "permission_provider" = "Drupal\entity\EntityPermissionProvider",
 *     "form" = {
 *       "add" = "Drupal\sub_assignment\Form\SubstitutooAssignmentForm",
 *       "edit" = "Drupal\sub_assignment\Form\SubstitutooAssignmentForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "delete-multiple-confirm" = "Drupal\Core\Entity\Form\DeleteMultipleForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "translation" = "Drupal\content_translation\ContentTranslationHandler"
 *   },
 *   base_table = "sub_assignment",
 *   admin_permission = "administer sub_assignment",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "owner" = "uid",
 *   },
 *   links = {
 *     "collection" = "/admin/substitutoo/sub-assignment",
 *     "add-form" = "/sub-assignment/add",
 *     "canonical" = "/sub-assignment/{sub_assignment}",
 *     "edit-form" = "/sub-assignment/{sub_assignment}/edit",
 *     "delete-form" = "/sub-assignment/{sub_assignment}/delete",
 *     "delete-multiple-form" = "/admin/substitutoo/sub-assignment/delete-multiple",
 *   },
 *   field_ui_base_route = "entity.sub_assignment.settings",
 * )
 */
final class SubstitutooAssignment extends ContentEntityBase implements SubstitutooAssignmentInterface {

  use EntityChangedTrait;
  use EntityOwnerTrait;

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage): void {
    parent::preSave($storage);
    if (!$this->getOwnerId()) {
      $this->setOwnerId(0);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    $f_employee = $this->get("f_employee")->getValue();

    if($f_employee) {
      $employee_id = $f_employee[0]['target_id'];
      $user = \Drupal\user\Entity\User::load($employee_id);
      $employee_name = $user->getDisplayName();

      $f_shift = $this->get("f_shift")->getValue();
      $f_shift_id = $f_shift[0]['target_id'];

      $f_date = $this->get('f_date')->value;
      $date = $f_date;
      $date_in_week = date("l", strtotime($date));
      $from = $this->get('f_time_start')->value;
      $to = $this->get('f_time_end')->value;
  
      $f_datetime_start = $this->get('f_datetime_start')->value;
      $f_datetime_end = $this->get('f_datetime_end')->value;
      $f_timestamp_start = $this->get('f_timestamp_start')->value;
      $f_timestamp_end = $this->get('f_timestamp_end')->value;
      $f_duration = $this->get('f_duration')->value;

      $unavailability_info = [
        'label' => t("Unavailability item for @employee on @date_in_week, @date, from @from to @to", [
            '@employee' => $employee_name,
            '@date_in_week' => $date_in_week,
            '@date' => $date,
            '@from' => $from,
            '@to' => $to,
          ]),
        'f_shift' => $f_shift_id,
        'f_employee' => $employee_id,
        'f_assignment' => $this->id(),
        'f_assignment_order' => $this->get('f_assignment_order')->value,
        'f_unavailable_type' => \Drupal::service('substitutoo_core.substitutoo_service')->getTaxonomyTermTid('unavailable_type', 'Assigned'),
        'f_confirmation_status' => \Drupal::service('substitutoo_core.substitutoo_service')->getTaxonomyTermTid('confirmation_status', 'Pending'),
        'f_reason' => t("Assigned to a Shift"),
        'f_datetime_start' => $this->get('f_datetime_start')->value,
        'f_datetime_end' => $this->get('f_datetime_end')->value,
        'f_timestamp_start' => $this->get('f_timestamp_start')->value,
        'f_timestamp_end' => $this->get('f_timestamp_end')->value,
        'f_duration' => $this->get('f_duration')->value,
      ];

      $query = \Drupal::entityTypeManager()->getStorage('sub_unavailability')->getQuery();
      $query->condition('f_employee', $unavailability_info['f_employee']);
      $query->condition('f_shift', $unavailability_info['f_shift']);
      $query->condition('f_assignment', $unavailability_info['f_assignment']);
      $query->accessCheck(TRUE);
      $ids = $query->execute();
      if(count($ids)) {
        $id = reset($ids);
        $unavailability = \Drupal::entityTypeManager()->getStorage('sub_unavailability')->load($id);
      }
  
      if (!empty($unavailability)) {
        $unavailability->set('label', $unavailability_info['label']);
        $unavailability->set('f_employee', $unavailability_info['f_employee']);
        $unavailability->set('f_assignment', $unavailability_info['f_assignment']);
        $unavailability->set('f_assignment_order', $unavailability_info['f_assignment_order']);
        $unavailability->set('f_reason', $unavailability_info['f_reason']);
        $unavailability->set('f_unavailable_type', $unavailability_info['f_unavailable_type']);
        $unavailability->set('f_confirmation_status', $unavailability_info['f_confirmation_status']);
        $unavailability->set('f_shift', $unavailability_info['f_shift']);
        $unavailability->set('f_datetime_start', $unavailability_info['f_datetime_start']);
        $unavailability->set('f_datetime_end', $unavailability_info['f_datetime_end']);
        $unavailability->set('f_timestamp_start', $unavailability_info['f_timestamp_start']);
        $unavailability->set('f_timestamp_end', $unavailability_info['f_timestamp_end']);
        $unavailability->set('f_duration', $unavailability_info['f_duration']);
        $unavailability->save();
      }
      else {
        $unavailability = \Drupal::entityTypeManager()->getStorage('sub_unavailability')->create($unavailability_info);
        $unavailability->save();
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

    $fields['assignment_type'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Assignment Type'))
      ->setDescription(t('The assignment type of Assignment, such as permanent, substitute, temporary.'))
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default')
      ->setSetting('handler_settings', [
        'target_bundles' => [
          'assignment_type' => 'assignment_type',
        ],
      ])
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => t('Select an assignment type ...'),
        ],
        'weight' => 3,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
        'settings' => [
          'link' => TRUE,
        ],
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['assignment_status'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Assignment Status'))
      ->setDescription(t('The assignment status of Assignment, such as pending, assigned, completed, incomplete, canceled.'))
      ->setSetting('target_type', 'taxonomy_term')
      ->setSetting('handler', 'default')
      ->setSetting('handler_settings', [
        'target_bundles' => [
          'assignment_status' => 'assignment_status',
        ],
      ])
      ->setRequired(TRUE)
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'placeholder' => t('Select an assignment status ...'),
        ],
        'weight' => 3,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'entity_reference_label',
        'settings' => [
          'link' => TRUE,
        ],
        'weight' => 3,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['manager_notes'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Manager Notes'))
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

    $fields['employee_notes'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Employee Notes'))
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

    $fields['f_location'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Location'))
      ->setSetting('target_type', 'sub_location')
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

    $fields['f_shift'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Shift'))
      ->setSetting('target_type', 'sub_shift')
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

    $fields['f_scheduler_assignment'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Scheduler Assignment'))
      ->setSetting('target_type', 'sub_scheduler_assignment')
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

    $fields['f_assignment_order'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Assignment Order'))
      ->setDefaultValue(0)
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number_integer',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['f_shift_scheduler'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Shift Scheduler'))
      ->setSetting('target_type', 'sub_shift_scheduler')
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

    $fields['f_availability'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Availability'))
      ->setSetting('target_type', 'sub_availability')
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

    $fields['f_employee'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Employee'))
      ->setSetting('target_type', 'user')
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

    $fields['f_date'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Date'))
      ->setDescription(t('Date of the substitutoo shift.'))
      ->setRequired(TRUE)
      ->setSetting('datetime_type', 'date')
      ->setDisplayOptions('form', [
        'type' => 'date_default',
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['f_time_start'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Start Time'))
      ->setDescription(t('The start time of the substitutoo shift.'))
      ->setRequired(TRUE)
      ->setSettings([
        'max_length' => 8,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'settings' => [
          'placeholder' => 'HH:MM:SS',
        ],
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);
          
    $fields['f_time_end'] = BaseFieldDefinition::create('string')
      ->setLabel(t('End Time'))
      ->setDescription(t('The end time of the substitutoo shift.'))
      ->setRequired(TRUE)
      ->setSettings([
        'max_length' => 8,
        'text_processing' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'settings' => [
          'placeholder' => 'HH:MM:SS',
        ],
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['f_datetime_start'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('Start Date'))
      ->setDescription(t('Start date of the substitute shift.'))
      ->setRequired(TRUE)
      ->setSetting('datetime_type', 'datetime')
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 1,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'datetime_default',
        'settings' => [
          'format_type' => 'short',
        ],
        'weight' => 1,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['f_datetime_end'] = BaseFieldDefinition::create('datetime')
      ->setLabel(t('End Date'))
      ->setDescription(t('End date of the substitute shift.'))
      ->setRequired(TRUE)
      ->setSetting('datetime_type', 'datetime')
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 2,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'datetime_default',
        'settings' => [
          'format_type' => 'short',
        ],
        'weight' => 2,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['f_timestamp_start'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Start Timestamp'))
      ->setDescription(t('The start timestamp for the entity.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'datetime_default',
        'settings' => [
          'date_format' => 'short',
        ],
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 5,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['f_timestamp_end'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('End Timestamp'))
      ->setDescription(t('The end timestamp for the entity.'))
      ->setRequired(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'datetime_default',
        'settings' => [
          'date_format' => 'short',
        ],
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'datetime_default',
        'weight' => 6,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['f_duration'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Duration'))
      ->setDefaultValue(0)
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'number_integer',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
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
      ->setDescription(t('The time that the substitutoo assignment was created.'))
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
      ->setDescription(t('The time that the substitutoo assignment was last edited.'));

    return $fields;
  }

}
