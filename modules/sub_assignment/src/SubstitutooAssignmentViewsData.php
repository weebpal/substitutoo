<?php

namespace Drupal\sub_assignment;

use Drupal\views\EntityViewsData;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Provides the views data for the assignment entity type.
 */
class SubstitutooAssignment extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    /** Custom location relation */
    $sub_notification = $this->entityTypeManager->getDefinition('sub_notification');
    $data['sub_assignment']['sub_notification']['relationship'] = [
      'title' => $this->t('Assignment'),
      'help' => $this->t('Relate location to assignment.'),
      'base' => $this->getViewsTableForEntityType($sub_notification),
      'base field' => $this->getRelationshipFieldForEntityType($sub_notification),
      'relationship field' => 'id',
      'id' => 'standard',
      'label' => $sub_notification->getLabel(),
    ];

    $sub_unavailability = $this->entityTypeManager->getDefinition('sub_unavailability');
    $data['sub_assignment']['sub_unavailability']['relationship'] = [
      'title' => $this->t('Assignment'),
      'help' => $this->t('Relate location to assignment.'),
      'base' => $this->getViewsTableForEntityType($sub_unavailability),
      'base field' => $this->getRelationshipFieldForEntityType($sub_unavailability),
      'relationship field' => 'id',
      'id' => 'standard',
      'label' => $sub_unavailability->getLabel(),
    ];

    return $data;
  }

  /**
   * Helper function:
   * Get entity type relationship field
   */
  public function getRelationshipFieldForEntityType(EntityTypeInterface $entity_type) {
    $storage = $this->entityTypeManager->getStorage($entity_type->id());
    $table_mapping = $storage->getTableMapping();
    
    return $table_mapping->getColumnNames('f_assignment')['target_id'];
  }

}
