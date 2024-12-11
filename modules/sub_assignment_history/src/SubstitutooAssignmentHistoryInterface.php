<?php

declare(strict_types=1);

namespace Drupal\sub_assignment_history;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a substitutoo assignment history entity type.
 */
interface SubstitutooAssignmentHistoryInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
