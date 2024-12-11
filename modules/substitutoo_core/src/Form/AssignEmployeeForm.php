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

class AssignEmployeeForm extends FormBase {

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
    return 'asssign_employee_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $shift_id = $this->request->query->get('shift');
    $assignment_id = $this->request->query->get('assignment');
    $type = $this->request->query->get('type');
    $shift_entity = $this->entityTypeManager->getStorage('sub_shift')->load($shift_id);
    $date_param = $this->request->query->get('date');
    $month_param = $this->request->query->get('month');
    $year_param = $this->request->query->get('year');

    $available_employees = $this->substitutooService->getAvailableEmployees($shift_entity);

    $user_options = [];
    foreach ($available_employees as $uid => $user) {
      $user_options[$uid] = $user->label();
    }
  
    $form['employee'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Employee'),
      '#options' => $user_options,
      '#required' => TRUE,
    ];
  
    $form['assignment_id'] = [
      '#type' => 'hidden',
      '#value' => $assignment_id,
    ];
  
    $title_action = $type == 'change' ? $this->t('Change Employee') : $this->t('Assign Employee');
  
    if (!empty(($user_options))) {
      $form['related_plan'] = [
        '#type' => 'markup',
        '#markup' => '<h3 class="related-plan">'. t('Related Plan') .'</h3>',
      ];
    }
  
    $form['assignments_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['assignments-wrapper']],
    ];
    
    $current_date = $year_param. '-' . $month_param . '-' . $date_param;
    if (!empty($available_employees)) {
      foreach ($available_employees as $uid => $user) {
        $assignments = $this->entityTypeManager->getStorage('sub_assignment')->loadByProperties([
          'f_employee' => $uid,
          'f_date' => $current_date,
        ]);
      
        $user_content = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['user-assignment'],
            'data-user-id' => $uid,
          ],
          'user_name' => [
            '#markup' => '<div class="name-button"><h3 class="employee-name">' . $user->label() . 
                         ' </h3><div type="button" class="toggle-button" data-user-id="' . $uid . '">Toggle Assignments</div></div>',
          ],
        ];
      
        if (empty($assignments)) {
          $user_content['plan'] = [
            '#markup' => '<div class="no-assign">'. t('No assign available this day') .'</div>',
          ];
        } else {
          $assignment_items = [];
          foreach ($assignments as $assignment) {
            $location = $assignment->get('f_location')->entity;
            $location_trails = $location->get('field_location_trail')->referencedEntities();
            $location_trail_label = '';
      
            if (!empty($location_trails)) {
              foreach ($location_trails as $location_trail) {
                $location_trail_label .= '<div class="location-trail">' . $location_trail->label() . '</div>';
              }
            }
      
            $start_time = $assignment->get('f_datetime_start')->getString();
            $end_time = $assignment->get('f_datetime_end')->getString();
            $start_time_format = new DrupalDateTime($start_time);
            $end_time_format = new DrupalDateTime($end_time);
  
            $formatted_start_time = $start_time_format->format('Y-m-d h:i A');
            $formatted_end_time = $end_time_format->format('Y-m-d h:i A');
     
            $assignment_items[] = [
              '#type' => 'container',
              '#attributes' => ['class' => ['assignment-plan']],
              'location' => [
                '#markup' => '<div class="location"><strong>Location:</strong> ' . $location->label() . '</div>',
              ],
              'location_trail' => [
                '#markup' => '<div class="location-trail-container"><strong>Location Trail: </strong>' . $location_trail_label . '</div>',
              ],
              'start_time' => [
                '#markup' => '<div class="start-time"><strong>Start Time:</strong> ' . $formatted_start_time . '</div>',
              ],
              'end_time' => [
                '#markup' => '<div class="end-time"><strong>End Time:</strong> ' . $formatted_end_time . '</div>',
              ],
            ];
          }
          $user_content['assignments'] = [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['assignments-list']
            ],
            'items' => $assignment_items,
          ];
        }
      
        $form['assignments_wrapper'][$uid] = $user_content;
      }
    }
    else {
      $form['message_wrapper'] = [
        '#type' => 'markup',
        '#markup' => '<div class="empty-employee">'. t('No one is available at this time') .'</div>',
      ];
    }
    
    
  
    $form['actions'] = [
      '#type' => 'container',
      '#attributes' => ['class' => 'form-actions'],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $title_action,
    ];

    $form['#attached']['library'][] = 'substitutoo_core/location_toggle';  

  
    return $form;
  }
  

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $request = $this->request;
    $date_param = $request->query->get('date');
    $month_param = $request->query->get('month');
    $year_param = $request->query->get('year');
    $date_format = $year_param . '-' . $month_param . '-' . $date_param;
    $selected_user_id = $form_state->getValue('employee');
    $user = \Drupal\user\Entity\User::load($selected_user_id);
    $name_user = $user->getDisplayName();
    $assignment_id = $form_state->getValue('assignment_id');
    $shift_id = $request->query->get('shift');

    // Load shift entity
    $shift_entity = \Drupal::entityTypeManager()->getStorage('sub_shift')->load($shift_id);

    $f_timestamp_start = strtotime($shift_entity->get('f_datetime_start')->value);
    $f_timestamp_end = strtotime($shift_entity->get('f_datetime_end')->value);


    // Load availability
    $connection = Database::getConnection();

    $query = $connection->select('sub_availability', 'sa')
      ->fields('sa', ['id'])
      ->condition('f_timestamp_start', $f_timestamp_start, '<=')
      ->condition('f_timestamp_end', $f_timestamp_end, '>=')
      ->condition('f_employee', $selected_user_id);

    $available_employees = $query->execute()->fetchCol();

    $type = $request->query->get('type');

    $assignment = $this->entityTypeManager->getStorage('sub_assignment')->load($assignment_id);
    if ($assignment) {
      $start_time = $assignment->get('f_datetime_start')->getString();
      $end_time = $assignment->get('f_datetime_end')->getString();
      $start_time_format = new DrupalDateTime($start_time);
      $end_time_format = new DrupalDateTime($end_time);
      $formatted_start_time = $start_time_format->format('h:i A');
      $formatted_end_time = $end_time_format->format('h:i A');
      $location = $assignment->get('f_location')->entity;
      $location_trails = $location->get('field_location_trail')->referencedEntities();
      $location_trail_label = $location->label();
      
      if (!empty($location_trails)) {
        foreach ($location_trails as $location_trail) {
          $location_trail_label .= ' > ' . $location_trail->label();
        }
      }
      $employee_before_entity = $assignment->get('f_employee')->entity;
      $assignment->set('f_employee', $selected_user_id);
      // Update availability of assignment
      $assignment->set('f_availability', reset($available_employees));
      $assignment->save();

      $data = [
        'name' => 'You assigned ' . $name_user . ' from ' .  $location_trail_label  . ' on ' . $date_format . ' from ' . $formatted_start_time . ' to ' . $formatted_end_time,
        'assignment' =>  $assignment->id(),
      ];

      if ($type == 'change' && $employee_before_entity) {
        $data = [
          'name' => 'You changed ' . $employee_before_entity->label() . '->' . $name_user .' from ' .  $location_trail_label  . ' on ' . $date_format . ' from ' . $formatted_start_time . ' to ' . $formatted_end_time,
        ];
        $sub_unavailability = $this->entityTypeManager->getStorage('sub_unavailability')->loadByProperties([
          'f_shift' => $shift_id,
          'f_assignment' => $assignment->id(),
          'f_employee' => $employee_before_entity->id(),
        ]);
        if ($sub_unavailability) {
          $sub_unavailability = reset($sub_unavailability);
          $sub_unavailability->delete();
        }
      }

      $this->messenger->addMessage($this->t('Employee assigned successfully.'));

      // Create activity
      \Drupal::service('sub_activity.activity_service')->createActivity($data);
    }

    $action = $request->query->get('action');
    if ($action == 'shifts') {
      $form_state->setRedirectUrl(\Drupal\Core\Url::fromUri('internal:/shifts'));
    } 
    else {
      $form_state->setRedirect('substitutoo_core.date', [
        'date' => $date_param,
        'month' => $month_param,
        'year' => $year_param,
      ]);
    }
  }

}
