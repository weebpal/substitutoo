<?php

namespace Drupal\sub_activity\Entity;

use Drupal\address\AddressInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * Defines the interface for activity items.
 */
interface SubActivityInterface extends ContentEntityInterface, EntityOwnerInterface, EntityChangedInterface {

  /**
   * Gets the sub_activity name.
   *
   * @return string
   *   The sub_activity name.
   */
  public function getName();

  /**
   * Sets the sub_activity name.
   *
   * @param string $name
   *   The sub_activity name.
   *
   * @return $this
   */
  public function setName($name);

  /**
   * Gets the sub_activity creation timestamp.
   *
   * @return int
   *   The sub_activity creation timestamp.
   */
  public function getCreatedTime();

  /**
   * Sets the sub_activity creation timestamp.
   *
   * @param int $timestamp
   *   The sub_activity creation timestamp.
   *
   * @return $this
   */
  public function setCreatedTime($timestamp);

}
