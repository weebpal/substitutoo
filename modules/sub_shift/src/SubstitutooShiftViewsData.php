<?php

namespace Drupal\sub_shift;

use Drupal\views\EntityViewsData;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Provides the views data for the shift entity type.
 */
class SubstitutooShiftViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    /** Custom location relation */
    $sub_assignment = $this->entityTypeManager->getDefinition('sub_assignment');
    $data['sub_shift']['sub_assignment']['relationship'] = [
      'title' => $this->t('Shift Assignment'),
      'help' => $this->t('Relate shift assignment to assignment.'),
      'base' => $this->getViewsTableForEntityType($sub_assignment),
      'base field' => $this->getRelationshipFieldForEntityType($sub_assignment),
      'relationship field' => 'id',
      'id' => 'standard',
      'label' => $sub_assignment->getLabel(),
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
    
    return $table_mapping->getColumnNames('f_shift')['target_id'];
  }

}
