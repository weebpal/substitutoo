<?php

declare(strict_types=1);

namespace Drupal\sub_shift_scheduler;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a substitutoo shift scheduler entity type.
 */
interface SubstitutooShiftSchedulerInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
