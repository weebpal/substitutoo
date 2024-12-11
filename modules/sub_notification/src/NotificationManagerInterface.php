<?php

namespace Drupal\sub_notification;

/**
 * Defines an interface for the NotificationManager service.
 */
interface NotificationManagerInterface {

   /**
    * {@inheritdoc}
    */
  public function createNotification(array $notificationData);

   /**
    * {@inheritdoc}
    */
  public function loadNotification($notificationId);

   /**
    * {@inheritdoc}
    */
  public function markReadNotification($notificationId);
}
