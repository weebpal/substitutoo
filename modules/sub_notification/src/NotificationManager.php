<?php

namespace Drupal\sub_notification;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use \Drupal\node\Entity\Node;
use \Drupal\sub_notification\Entity\SubNotificationInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Url;

/**
 * Provides a service to count nodes.
 */
class NotificationManager implements NotificationManagerInterface {

  protected $entityTypeManager;

  /**
   * MigrateManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  public function createNotification(array $notificationData) {
    // Create a new notification entity.
    $notification = $this->entityTypeManager->getStorage('sub_notification')->create([
      'type' => 'default',
      'name' => $notificationData['shortDescription'],
      'body' => $notificationData['detailedDescription'],
      'link' => $notificationData['link'],
      'status' => 0,
      // Set other fields as needed.
    ]);
    $notification->save();
    return $notification;
  }

   /**
    * {@inheritdoc}
    */
  public function loadNotification($notificationId) {
    return $this->entityTypeManager->getStorage('sub_notification')->load($notificationId);
  }

   /**
    * {@inheritdoc}
    */
  public function markReadNotification($notificationId) {
    $notification = \Drupal::entityTypeManager()->getStorage('sub_notification')->load($notificationId);
    if ($notification) {
      // Update the status to mark it as read.
      $notification->set('status', 1);
      $notification->save();
    }
  }
}
