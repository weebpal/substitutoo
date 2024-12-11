<?php

namespace Drupal\sub_activity;

use Drupal\sub_activity\Entity\SubActivityType;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Defines the list builder for activity items.
 */
class SubActivityListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['name'] = $this->t('Name');
    $header['type'] = $this->t('Type');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /** @var \Drupal\sub_activity\Entity\SubActivityTypeInterface $entity */
    $sub_activity_type = SubActivityType::load($entity->bundle());

    $row['name']['data'] = [
      '#type' => 'link',
      '#title' => $entity->label(),
    ] + $entity->toUrl()->toRenderArray();
    $row['type'] = $sub_activity_type->label();

    return $row + parent::buildRow($entity);
  }

}
