<?php

declare(strict_types=1);

namespace Drupal\sub_shift;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;

/**
 * Defines the react data schema handler.
 */
class SubstitutooShiftStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);
    if ($data_table = $this->storage->getBaseTable()) {
      $schema[$data_table]['indexes'] += [
        'substitutoo_shift__status' => [
          'status',
        ],
        'substitutoo_shift__f_time_start' => [
          'f_time_start',
        ],
        'substitutoo_shift__f_time_end' => [
          'f_time_end',
        ],
        'substitutoo_shift__f_timestamp_start' => [
          'f_timestamp_start',
        ],
        'substitutoo_shift__f_timestamp_end' => [
          'f_timestamp_end',
        ],
        'substitutoo_shift__f_duration' => [
          'f_duration',
        ],
        'substitutoo_shift__f_required_number_employees' => [
          'f_required_number_employees',
        ],
        'substitutoo_shift__created' => [
          'created',
        ],
        'substitutoo_shift__changed' => [
          'changed',
        ],
      ];
    }

    return $schema;
  }
}
