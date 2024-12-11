<?php

declare(strict_types=1);

namespace Drupal\sub_location;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a substitutoo location entity type.
 */
interface SubstitutooLocationInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
