<?php

namespace Drupal\substitutoo_core\Form;

use Drupal;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;


class RunForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'run_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $request = \Drupal::request();
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Run'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $shift = \Drupal::entityTypeManager()->getStorage('sub_shift')->load(1);
    \Drupal::service('substitutoo_core.substitutoo_service')->getAvailableEmployees($shift);

    exit;
    // \Drupal::service('substitutoo_core.substitutoo_service')->getAvailableEmployees($shift);


    $scheduler = \Drupal::entityTypeManager()->getStorage('sub_shift_scheduler')->load(1);
    \Drupal::service('substitutoo_core.substitutoo_service')->generateShifts($scheduler);
    $scheduler = \Drupal::entityTypeManager()->getStorage('sub_shift_scheduler')->load(2);
    \Drupal::service('substitutoo_core.substitutoo_service')->generateShifts($scheduler);
    $scheduler = \Drupal::entityTypeManager()->getStorage('sub_availability_scheduler')->load(1);
    \Drupal::service('substitutoo_core.substitutoo_service')->generateAvailabilities($scheduler);
  }
}
