<?php

declare(strict_types=1);

namespace Drupal\sub_unavailability;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a substitutoo unavailability entity type.
 */
interface SubstitutooUnavailabilityInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
