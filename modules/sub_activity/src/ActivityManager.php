<?php

namespace Drupal\sub_activity;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use \Drupal\node\Entity\Node;
use \Drupal\sub_activity\Entity\SubActivityInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\sub_activity\Entity\SubActivity;
use Drupal\token\TokenEntityMapperInterface;

/**
 * Provides a service to create and manage activities.
 */
class ActivityManager {

  protected $entityTypeManager;

  /**
   * activityManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  public function createActivity(array $data) {
    $notification = $this->entityTypeManager->getStorage('sub_activity')->create([
      'type' => 'default',
      'name' => $data['name'],
      'f_assignment' => $data['assignment'],
      'status' => 1,
    ]);
    $notification->save();
    return $notification;
  }
}
