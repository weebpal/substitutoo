<?php

declare(strict_types=1);

namespace Drupal\sub_shift;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a substitutoo shift entity type.
 */
interface SubstitutooShiftInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
