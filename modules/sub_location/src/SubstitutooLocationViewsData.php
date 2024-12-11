<?php

namespace Drupal\sub_location;

use Drupal\views\EntityViewsData;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Provides the views data for the location entity type.
 */
class SubstitutooLocationViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    /** Custom location relation */
    $sub_assignment = $this->entityTypeManager->getDefinition('sub_assignment');
    $data['sub_location']['sub_assignment']['relationship'] = [
      'title' => $this->t('Assignment'),
      'help' => $this->t('Relate location to assignment.'),
      'base' => $this->getViewsTableForEntityType($sub_assignment),
      'base field' => $this->getRelationshipFieldForEntityType($sub_assignment),
      'relationship field' => 'id',
      'id' => 'standard',
      'label' => $sub_assignment->getLabel(),
    ];

    $sub_shift = $this->entityTypeManager->getDefinition('sub_shift');
    $data['sub_location']['sub_shift']['relationship'] = [
      'title' => $this->t('Assignment'),
      'help' => $this->t('Relate location to assignment.'),
      'base' => $this->getViewsTableForEntityType($sub_shift),
      'base field' => $this->getRelationshipFieldForEntityType($sub_shift),
      'relationship field' => 'id',
      'id' => 'standard',
      'label' => $sub_shift->getLabel(),
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
    
    return $table_mapping->getColumnNames('f_location')['target_id'];
  }

}
