<?php

declare(strict_types=1);

namespace Drupal\sub_availability;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;

/**
 * Defines the react data schema handler.
 */
class SubstitutooAvailabilityStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);
    if ($data_table = $this->storage->getBaseTable()) {
      $schema[$data_table]['indexes'] += [
        'substitutoo_availability__status' => [
          'status',
        ],
        'substitutoo_availability__f_time_start' => [
          'f_time_start',
        ],
        'substitutoo_availability__f_time_end' => [
          'f_time_end',
        ],
        'substitutoo_availability__f_timestamp_start' => [
          'f_timestamp_start',
        ],
        'substitutoo_availability__f_timestamp_end' => [
          'f_timestamp_end',
        ],
        'substitutoo_availability__f_duration' => [
          'f_duration',
        ],
        'substitutoo_availability__created' => [
          'created',
        ],
        'substitutoo_availability__changed' => [
          'changed',
        ],
      ];
    }

    return $schema;
  }
}
