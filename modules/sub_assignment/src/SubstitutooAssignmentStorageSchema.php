<?php

declare(strict_types=1);

namespace Drupal\sub_assignment;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;

/**
 * Defines the react data schema handler.
 */
class SubstitutooAssignmentStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);
    if ($data_table = $this->storage->getBaseTable()) {
      $schema[$data_table]['indexes'] += [
        'substitutoo_assignment__status' => [
          'status',
        ],
        'substitutoo_assignment__f_time_start' => [
          'f_time_start',
        ],
        'substitutoo_assignment__f_time_end' => [
          'f_time_end',
        ],
        'substitutoo_assignment__f_timestamp_start' => [
          'f_timestamp_start',
        ],
        'substitutoo_assignment__f_timestamp_end' => [
          'f_timestamp_end',
        ],
        'substitutoo_assignment__f_duration' => [
          'f_duration',
        ],
        'substitutoo_assignment__created' => [
          'created',
        ],
        'substitutoo_assignment__changed' => [
          'changed',
        ],
      ];
    }

    return $schema;
  }
}
