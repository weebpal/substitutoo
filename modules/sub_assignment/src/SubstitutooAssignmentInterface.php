<?php

declare(strict_types=1);

namespace Drupal\sub_assignment;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a substitutoo assignment entity type.
 */
interface SubstitutooAssignmentInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
