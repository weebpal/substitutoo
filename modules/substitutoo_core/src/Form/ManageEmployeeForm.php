<?php

namespace Drupal\substitutoo_core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\substitutoo_core\SubstitutooService;
use Drupal\sub_shift\Entity\SubstitutooShift;
use Drupal\Core\Database\Database;
use Drupal\Core\Datetime\DrupalDateTime;

class ManageEmployeeForm extends FormBase {

  protected $entityTypeManager;
  protected $request;
  protected $substitutooService;
  protected $messenger;

  public function __construct(EntityTypeManagerInterface $entity_type_manager, Request $request, SubstitutooService $substitutoo_service, MessengerInterface $messenger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->request = $request;
    $this->substitutooService = $substitutoo_service;
    $this->messenger = $messenger;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('substitutoo_core.substitutoo_service'),
      $container->get('messenger')
    );
  }

  public function getFormId() {
    return 'manage_employee_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $date_param = $this->request->query->get('date');
    $month_param = $this->request->query->get('month');
    $year_param = $this->request->query->get('year');
    $user_id = $this->request->query->get('user');
    $date = $year_param . '-' . $month_param . '-' . $date_param;

    $availability = $this->getAvailability($user_id, $date);
    $assignments = $this->getAssignments($user_id, $date);
    $unavailabilities = $this->getUnavailabilities($user_id, $date);

    $form['current_date'] = [
      '#type' => 'markup',
      '#markup' => '<h1>' . $date .'</h1>',
    ];

    $user_entity = \Drupal::entityTypeManager()->getStorage('user')->load($user_id);

    $form['user_name'] = [
      '#type' => 'markup',
      '#markup' => '<h1>Employee: ' . $user_entity->getAccountName() .'</h1>',
    ];

    $form['available_plan'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['available-plan wrapper-content']],
    ];

    // Link toggle for Available Plans
    $form['available_plan']['toggle_available_plan'] = [
        '#markup' => '<div class="availability-title title-plan">Available Plan</div><div class="toggle-button toggle-available-plan" data-target=".available-plan-content">' . $this->t('Click') . '</div>',
    ];

    // Content wrapper for available plans
    $form['available_plan']['available_plan_content'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['available-plan-content', 'toggle-content'], 'style' => 'display: flex;'],
    ];

    foreach ($availability as $availability_item) {
        $form['available_plan']['available_plan_content']['availability_' . $availability_item->id()] = [
            '#markup' => '<div class="availability-item">' . 
                          $availability_item->get('f_time_start')->getString() . ' - ' . 
                          $availability_item->get('f_time_end')->getString() . 
                          '</div>',
        ];
    }

    // Form Assignments
    $form['assignments'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['assignments wrapper-content']],
    ];

    // Link toggle for Assignments
    $form['assignments']['toggle_assignments'] = [
        '#markup' => '<div class="assignment-title title-plan">Assignments</div><div class="toggle-button toggle-assignments" data-target=".assignments-content">' . $this->t('Click') . '</div>',
    ];

    // Content wrapper for assignments
    $form['assignments']['assignments_content'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['assignments-content', 'toggle-content'], 'style' => 'display: flex;'],
    ];

    foreach ($assignments as $assignment_item) {
      $time_start = $assignment_item->get('f_time_start')->value;
      $time_end = $assignment_item->get('f_time_end')->value;

      $start_time_format = new DrupalDateTime($time_start);
      $end_time_format = new DrupalDateTime($time_end);

      $formatted_start_time = $start_time_format->format('h:i A');
      $formatted_end_time = $end_time_format->format('h:i A');
        $form['assignments']['assignments_content']['assignment_' . $assignment_item->id()] = [
            '#markup' => '<div class="assignment-item">' . 
                          $assignment_item->get('f_location')->entity->label() . ': ' . 
                          $formatted_start_time . ' - ' . 
                          $formatted_end_time . 
                          '</div>',
        ];
    }

    // Form Unavailable
    $form['unavailable'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['unavailable wrapper-content']],
    ];

    // Link toggle for Unavailable
    $form['unavailable']['toggle_unavailable'] = [
        '#markup' => '<div class="unavailability-title title-plan">Unavailabilities</div><div class="toggle-button toggle-unavailable" data-target=".unavailable-content">' . $this->t('Click') . '</div>',
    ];

    // Content wrapper for unavailability
    $form['unavailable']['unavailable_content'] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['unavailable-content', 'toggle-content'], 'style' => 'display: flex;'],
    ];

    foreach ($unavailabilities as $unavailability_item) {
      $time_start = $unavailability_item->get('f_datetime_start')->value;
      $time_end = $unavailability_item->get('f_datetime_end')->value;

      $start_time_format = new DrupalDateTime($time_start);
      $end_time_format = new DrupalDateTime($time_end);

      $formatted_start_time = $start_time_format->format('Y-m-d h:i A');
      $formatted_end_time = $end_time_format->format('Y-m-d h:i A');
        $form['unavailable']['unavailable_content']['unavailability_' . $unavailability_item->id()] = [
            '#markup' => '<div class="unavailability-item">' . 
                          'Leave: ' . 
                          $formatted_start_time . ' - ' . 
                          $formatted_end_time . 
                          '</div>',
        ];
    }

    // Attach the JavaScript file
    $form['#attached']['library'][] = 'substitutoo_core/employee_toggle';

    return $form;
}




  private function getAvailability($user_id, $date) {
    $date_start = new \DateTime($date);
    $date_start_iso = $date_start->format('Y-m-d') . 'T00:00:00';
    $date_end = $date_start->format('Y-m-d') . 'T23:59:59';

    $query = \Drupal::entityTypeManager()->getStorage('sub_availability')->getQuery();
    $query->condition('f_employee', $user_id)
          ->condition('f_datetime_start', $date_end, '<=') 
          ->accessCheck(FALSE)
          ->condition('f_datetime_end', $date_start_iso, '>=');
          
    $availability_ids = $query->execute();
    return \Drupal::entityTypeManager()->getStorage('sub_availability')->loadMultiple($availability_ids);
  }

  private function getAssignments($user_id, $date) {
    $date_start = new \DateTime($date);
    $date_start_iso = $date_start->format('Y-m-d') . 'T00:00:00';
    $date_end = $date_start->format('Y-m-d') . 'T23:59:59';

    $query = \Drupal::entityTypeManager()->getStorage('sub_assignment')->getQuery();
    $query->condition('f_employee', $user_id)
          ->condition('f_datetime_start', $date_end, '<=') 
          ->accessCheck(FALSE)
          ->condition('f_datetime_end', $date_start_iso, '>=');
    
    $assignment_ids = $query->execute();
    return \Drupal::entityTypeManager()->getStorage('sub_assignment')->loadMultiple($assignment_ids);
  }

  private function getUnavailabilities($user_id, $date) {
    $leave_id = \Drupal::service('substitutoo_core.substitutoo_service')->getTaxonomyTermTid('unavailable_type', 'Leave');

    $date_start = new \DateTime($date);
    $date_end = new \DateTime($date);
    $date_start = $date_start->format('Y-m-d') . 'T00:00:00';  
    $date_end = $date_end->format('Y-m-d') . 'T23:59:59';
    $query = \Drupal::database()->select('sub_unavailability', 'u')
      ->fields('u', ['id']) 
      ->condition('u.f_unavailable_type', $leave_id)
      ->condition('u.f_employee', $user_id)
      ->condition('u.f_datetime_start', $date_end, '<=')
      ->condition('u.f_datetime_end', $date_start, '>=');

    $results = $query->execute()->fetchCol();

    return \Drupal::entityTypeManager()->getStorage('sub_unavailability')->loadMultiple($results);
  }
  

  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
