<?php

namespace Drupal\sub_ai;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;

class AICustomerSupportManager implements AICustomerSupportManagerInterface {

  /**
   * The current primary database.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $database;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected LanguageManagerInterface $languageManager;

  /**
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected MailManagerInterface $mailManager;

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected RendererInterface $renderer;

  /**
   * The HTTP client to make requests.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * @param \Drupal\Core\Database\Connection $database
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   * @param \Drupal\Core\Render\RendererInterface $renderer
   */
  public function __construct(Connection $database, EntityTypeManagerInterface $entityTypeManager, LanguageManagerInterface $language_manager, MailManagerInterface $mail_manager, RendererInterface $renderer, ClientInterface $http_client)
  {
    $this->database = $database;
    $this->entityTypeManager = $entityTypeManager;
    $this->languageManager = $language_manager;
    $this->mailManager = $mail_manager;
    $this->renderer = $renderer;
    $this->httpClient = $http_client;
  }


    /**
   * {@inheritdoc}
   */
  public function prompt($question) {
    $base_url = \Drupal::config('sub_ai.settings')->get('api_url');
    $api_key = \Drupal::config('sub_ai.settings')->get('api_key');

    $url = $base_url . "/chat/completions";
  
    $payload = [
      'model' => 'gpt-4o-mini',
      'messages' => [
        ['role' => 'user', 'content' => $question],
      ],
    ];
  
    try {
      $response = $this->httpClient->post($url, [
        'headers' => [
          'Authorization' => 'Bearer ' . $api_key,
          'Content-Type' => 'application/json',
        ],
        'json' => $payload,
        'timeout' => 300,
      ]);

      $data_response = json_decode($response->getBody(), true);

      if (isset($data_response['choices'][0]['message']['content'])) {
        $content = $data_response['choices'][0]['message']['content'];
        preg_match('/\{(?:[^{}]|(?R))*\}/', $content, $json_match);
        if (!empty($json_match)) {
          $json_content = json_decode($json_match[0], true);
        
          if (json_last_error() === JSON_ERROR_NONE) {
            $ai_request = $this->entityTypeManager->getStorage('sub_ai_request')->create([
              'type' => 'default',
              'label' => 'Test',
              'description' => json_encode($json_content),
              'status' => 1,
            ]);
            $ai_request->save();
          }
        }
      }
      
      return $data_response['choices'][0]['message']['content'];
    }
    catch (\Exception $e) {
      \Drupal::logger('openai_integration')->error($e->getMessage());
      \Drupal::messenger()->addStatus('An error occurred while connecting to OpenAI.');
      return FALSE;
    }
  }
}
