<?php

namespace Drupal\sub_activity;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\sub_activity\Entity\SubActivityInterface;

/**
 * Defines the sub_activity storage.
 */
class SubActivityStorage extends SqlContentEntityStorage implements SubActivityStorageInterface {
}
