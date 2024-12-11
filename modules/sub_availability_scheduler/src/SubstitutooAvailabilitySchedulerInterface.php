<?php

declare(strict_types=1);

namespace Drupal\sub_availability_scheduler;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a substitutoo availability scheduler entity type.
 */
interface SubstitutooAvailabilitySchedulerInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
