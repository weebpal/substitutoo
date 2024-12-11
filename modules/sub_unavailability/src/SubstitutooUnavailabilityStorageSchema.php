<?php

declare(strict_types=1);

namespace Drupal\sub_unavailability;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\Sql\SqlContentEntityStorageSchema;

/**
 * Defines the react data schema handler.
 */
class SubstitutooUnavailabilityStorageSchema extends SqlContentEntityStorageSchema {

  /**
   * {@inheritdoc}
   */
  protected function getEntitySchema(ContentEntityTypeInterface $entity_type, $reset = FALSE) {
    $schema = parent::getEntitySchema($entity_type, $reset);
    if ($data_table = $this->storage->getBaseTable()) {
      $schema[$data_table]['indexes'] += [
        'substitutoo_unavailability__status' => [
          'status',
        ],
        'substitutoo_unavailability__f_timestamp_start' => [
          'f_timestamp_start',
        ],
        'substitutoo_unavailability__f_timestamp_end' => [
          'f_timestamp_end',
        ],
        'substitutoo_unavailability__f_duration' => [
          'f_duration',
        ],
        'substitutoo_unavailability__created' => [
          'created',
        ],
        'substitutoo_unavailability__changed' => [
          'changed',
        ],
      ];
    }

    return $schema;
  }
}
