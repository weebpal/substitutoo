<?php

namespace Drupal\sub_ai\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Database;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Messenger\MessengerInterface;

/**
 * Configures OBT Gmap for demographic filter.
 *
 * @internal
 */
class AICustomerSupportConfigForm extends ConfigFormBase {

  const CONFIG_FORM_SETTINGS = 'sub_ai.settings';

  /**
   * The File System.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs a \Drupal\aggregator\SettingsForm object.
   */
  public function __construct(ConfigFactoryInterface $config_factory, FileSystemInterface $file_system, Connection $database, MessengerInterface $messenger) {
    parent::__construct($config_factory);
    $this->fileSystem = $file_system;
    $this->database = $database;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('file_system'),
      $container->get('database'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sub_ai.settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::CONFIG_FORM_SETTINGS);

    // Open AI.
    $form['open_ai'] = [
      '#type' => 'details',
      '#title' => t('Open AI'),
      '#open' => TRUE,
    ];

    $form['open_ai']['api_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('API'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE, 
    ];

    $form['open_ai']['api_settings']['api_url'] = [
      '#required' => TRUE,
      '#type' => 'textfield',
      '#title' => $this->t('API Base URL'),
      '#default_value' => $config->get('api_url'),
    ];

    $form['open_ai']['api_settings']['api_key'] = [
      '#required' => TRUE,
      '#type' => 'textarea',
      '#title' => $this->t('API Key'),
      '#default_value' => $config->get('api_key'),
      '#description' => $this->t('The API key is required to interface with OpenAI services. Get your API key by signing up on the <a href=":link" target="_blank">OpenAI website</a>.', [':link' => 'https://openai.com/api']),
      '#attributes' => [
        'rows' => 1,
      ],
    ];

    $form['open_ai']['api_settings']['api_org'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Organization ID'),
      '#default_value' => $config->get('api_org'),
      '#description' => $this->t('The organization ID on your OpenAI account. This is required for some OpenAI services to work correctly.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory->getEditable(static::CONFIG_FORM_SETTINGS);
    $config
      ->set('api_url', $form_state->getValue('api_url'))
      ->set('api_key', $form_state->getValue('api_key'))
      ->set('api_org', $form_state->getValue('api_org'))
      ->set('fine_tuning_id', $form_state->getValue('fine_tuning_id'))
      ->set('model_id', $form_state->getValue('model_id'))
      ->save();
    parent::submitForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [static::CONFIG_FORM_SETTINGS];
  }
}
