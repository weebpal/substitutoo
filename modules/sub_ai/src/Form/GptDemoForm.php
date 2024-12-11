<?php

namespace Drupal\sub_ai\Form;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Drupal\Core\Url;
use Drupal\user\Entity\User;

class GptDemoForm extends FormBase {

  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'gpt_demo_form';
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {

    // $model_id = \Drupal::config('sub_ai.settings')->get('model_id');
    // $form['model_id'] = [
    //   '#type' => 'markup',
    //   '#markup' => '<span>Model: ' . $model_id . '</span>',
    // ];

    $form['prompt'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Prompt'),
    ];

    $form['response'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Response from OpenAI'),
      '#attributes' =>
        [
          'readonly' => 'readonly',
        ],
      '#prefix' => '<div id="openai-prompt-response">',
      '#suffix' => '</div>',
      '#description' => $this->t('The response from OpenAI will appear in the textbox above.'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Ask OpenAI'),
      '#ajax' => [
        'callback' => [$this, 'ajaxSubmit'],
        'event' => 'click',
        'wrapper' => 'openai-prompt-response',
      ],
    ];

    return $form;
  }


  /**
   * @inheritDoc
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  /**
   * @inheritDoc
   */
  public function ajaxSubmit(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $prompt = $values['prompt'];
    if($prompt) {
      $aiCustomerSupportManager = \Drupal::service('sub_ai.manager');
      $answer = $aiCustomerSupportManager->prompt($prompt);
    }
    $form['response']['#value'] = $answer ?? 'No answer was provided.';
   
    return $form['response'];
  }


}
