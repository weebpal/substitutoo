<?php

declare(strict_types=1);

namespace Drupal\sub_shift_scheduler\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for the substitutoo shift scheduler entity edit forms.
 */
final class SubstitutooShiftSchedulerForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
  
    $form['field_days_of_the_week']['#states']['visible'] = [
      ':input[name="field_calendar_type"]' => ['value' => \Drupal::service('substitutoo_core.substitutoo_service')->getTaxonomyTermTid('calendar_type', 'weekly')],
    ];      

    $form['field_days_of_the_month']['#states']['visible'] = [
      ':input[name="field_calendar_type"]' => ['value' => \Drupal::service('substitutoo_core.substitutoo_service')->getTaxonomyTermTid('calendar_type', 'monthly')],
    ];      

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state): int {
    $result = parent::save($form, $form_state);

    $message_args = ['%label' => $this->entity->toLink()->toString()];
    $logger_args = [
      '%label' => $this->entity->label(),
      'link' => $this->entity->toLink($this->t('View'))->toString(),
    ];

    switch ($result) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('New substitutoo shift scheduler %label has been created.', $message_args));
        $this->logger('sub_shift_scheduler')->notice('New substitutoo shift scheduler %label has been created.', $logger_args);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The substitutoo shift scheduler %label has been updated.', $message_args));
        $this->logger('sub_shift_scheduler')->notice('The substitutoo shift scheduler %label has been updated.', $logger_args);
        break;

      default:
        throw new \LogicException('Could not save the entity.');
    }

    $form_state->setRedirectUrl($this->entity->toUrl());

    return $result;
  }

}
