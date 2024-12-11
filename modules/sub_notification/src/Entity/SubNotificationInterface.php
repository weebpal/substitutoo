<?php

namespace Drupal\sub_notification\Entity;

use Drupal\address\AddressInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Defines the interface for notification items.
 */
interface SubNotificationInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the sub_notification name.
   *
   * @return string
   *   The sub_notification name.
   */
  public function getName();

  /**
   * Sets the sub_notification name.
   *
   * @param string $name
   *   The sub_notification name.
   *
   * @return $this
   */
  public function setName($name);

  /**
   * Gets the sub_notification creation timestamp.
   *
   * @return int
   *   The sub_notification creation timestamp.
   */
  public function getCreatedTime();

  /**
   * Sets the sub_notification creation timestamp.
   *
   * @param int $timestamp
   *   The sub_notification creation timestamp.
   *
   * @return $this
   */
  public function setCreatedTime($timestamp);

}
