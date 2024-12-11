<?php

namespace Drupal\sub_notification;

use Drupal\Core\Entity\Sql\SqlContentEntityStorage;
use Drupal\sub_notification\Entity\SubNotificationInterface;

/**
 * Defines the sub_notification storage.
 */
class SubNotificationStorage extends SqlContentEntityStorage implements SubNotificationStorageInterface {
}
