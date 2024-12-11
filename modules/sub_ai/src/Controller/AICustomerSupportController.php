<?php

namespace Drupal\sub_ai\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\commerce_order\Entity\Order;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Exception;
use Drupal\Core\Datetime\DrupalDateTime;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Drupal\Core\Entity\EntityTypeManagerInterface;



/**
 * Upload file endpoint.
 */
class AICustomerSupportController extends ControllerBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ChatGptCustomController constructor.
   *
   * @param \OpenAI\Client $client
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
    );
  }

  public function createFineTuning(){
    $response = [];
    $aiCustomerSupportManager = \Drupal::service('sub_ai.manager');
    $id = $aiCustomerSupportManager->createFile();
    if ($id) {
      $config = \Drupal::configFactory()->getEditable('sub_ai.settings');
      $result = $aiCustomerSupportManager->createFineTuning($id);
      if ($result['id']) {
        $config->set('fine_tuning_id', $result['id']);
        $config->set('model_id', '');
        $config->save();
        $response['id_fine_tuning'] = $result['id'];
      }
    } else {
      $response['error'] = 'obt::Not enough data, make sure you have more than 10 data';
    }
    return new JsonResponse($response);
  }

  public function getFineTuning() {
    $fine_tuning_id = \Drupal::config('sub_ai.settings')->get('fine_tuning_id');
    $response = [];
    if ($fine_tuning_id) {
      $aiCustomerSupportManager = \Drupal::service('sub_ai.manager');
      $status = $aiCustomerSupportManager->getFineTuning($fine_tuning_id);
      $response['status'] = $status;
    }
    else{
      $response['error'] = 'obt::Missing id fine tuning';
    }
    return new JsonResponse($response);
  }

  public function getModelId() {
    $fine_tuning_id = \Drupal::config('sub_ai.settings')->get('fine_tuning_id');
    $response = [];
    if ($fine_tuning_id) {
      $aiCustomerSupportManager = \Drupal::service('sub_ai.manager');
      $model_id = $aiCustomerSupportManager->getModelId($fine_tuning_id);
      if($model_id) {
        $config = \Drupal::configFactory()->getEditable('sub_ai.settings');
        $config->set('model_id', $model_id);
        $config->save();
        $response['model_id'] = $model_id;
      }
      else {
        $response['error'] = 'obt::Fine tuned model is not available';
      }
    }
    else{
      $response['error'] = 'obt::Missing id fine tuning';
    }
    return new JsonResponse($response);
  }

  public function moveToFineTunning(Request $request) {
    $id = $request->attributes->get('chatgpt_chat_log_id');
    $gpt_chat = $this->entityTypeManager->getStorage('chatgpt_chat')->load($id);

    // Get all field values as an array.
    if(!empty($gpt_chat)) {
      $values = [];
      foreach ($gpt_chat->getFields() as $field_name => $field_item_list) {
        $values[$field_name] = $field_item_list->getValue();
      }
  
      $field_values = array_filter($values, function($key) {
        return strpos($key, 'field_') === 0;
      }, ARRAY_FILTER_USE_KEY);
  
      $field_values['type'] = "chatgpt_fine_tunning_item";
      $chatgpt_train_storage = $this->entityTypeManager->getStorage('chatgpt_train');
      $chatgpt_train_storage->create($field_values)->save();
  
      $gpt_chat->set('field_used_for_fine_tuning', 1);
      $gpt_chat->save();
    }
    return $this->redirect('entity.chatgpt_chat.collection');
  }

  public function deleteChatLog(Request $request) {
    $entities = $this->entityTypeManager->getStorage('chatgpt_chat')->loadByProperties([
      'type' => 'chatgpt_chat_log'
    ]);

    if (!empty($entities)) {
      // Delete the loaded entities.
      $this->entityTypeManager->getStorage('chatgpt_chat')->delete($entities);
    }
    $response['message'] = t('Delete done !');
    return new JsonResponse($response);
  }
}
