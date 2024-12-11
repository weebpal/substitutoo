<?php

declare(strict_types=1);

namespace Drupal\sub_ai_request;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface defining a substitutoo ai request entity type.
 */
interface SubstitutooAIRequestInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

}
