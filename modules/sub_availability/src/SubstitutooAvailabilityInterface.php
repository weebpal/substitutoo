<?php

declare(strict_types=1);

namespace Drupal\sub_availability;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a substitutoo availability entity type.
 */
interface SubstitutooAvailabilityInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
