<?php

declare(strict_types=1);

namespace Drupal\sub_scheduler_assignment;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a substitutoo scheduler assignment entity type.
 */
interface SubstitutooSchedulerAssignmentInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
