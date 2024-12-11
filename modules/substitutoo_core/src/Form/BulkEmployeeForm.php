<?php

namespace Drupal\substitutoo_core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\substitutoo_core\SubstitutooService;
use DateTime;
use DateInterval;
use DatePeriod;

class BulkEmployeeForm extends FormBase {

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
    return 'bulk_assign_employee_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {

    $shift_id = $this->request->query->get('shift');
    $order_assignment = $this->request->query->get('order_assignment');
    $shift_entity = $this->entityTypeManager->getStorage('sub_shift')->load($shift_id);
    $shift_scheduler = $shift_entity->get('f_shift_scheduler')->entity;
    $shift_scheduler_info = $this->substitutooService->getShiftSchedulerInfo($shift_scheduler);
    $location = $shift_scheduler->get('field_location')->entity;
    $assignments = $this->entityTypeManager->getStorage('sub_assignment')->loadByProperties([
      'f_shift_scheduler' => $shift_scheduler->id(),
      'f_assignment_order' => $order_assignment,
    ]);

    $employee_schedule = [];
    $employee_options = [];
    foreach ($assignments as $id => $assignment) {
      
        $shift = $assignment->get('f_shift')->entity;
        $available_employees = $this->substitutooService->getAvailableEmployees($shift);
        if ($available_employees) {
          $date = $assignment->get('f_date')->getString();
          foreach ($available_employees as $employee_id => $employee) {
              $employee_name = $employee->getDisplayName();
              
              if (!isset($employee_schedule[$employee_name])) {
                $employee_schedule[$employee_name] = [];
              }
  
              $employee_schedule[$employee_name][] = $date;
              $employee_options[$employee_id] = $employee_name;
          }
        }

    }

    $time_frame = $shift_scheduler_info['time_frames'][0] ?? null;
    $time = '';
    if ($time_frame) {
      $from_time = gmdate("h:i A", $time_frame['from']);
      $to_time = gmdate("h:i A", $time_frame['to']);
      $time = "<div class='title-time'>" . t('Time') . " :</div><div class='value-time'> $from_time - $to_time</div></div>";
    }

    // Format calendar
    $calendar_type = $shift_scheduler_info['calendar_type'] ?? '';
    $start_date = $shift_scheduler_info['between_dates'][0]['value'] ?? '';
    $end_date = $shift_scheduler_info['between_dates'][0]['end_value'] ?? '';
    
    $dates = "<div class='title-dates'>". t('Dates') .": </div>";
    
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $end->modify('+1 day');
    $interval = new DateInterval('P1D');
    $period = new DatePeriod($start, $interval, $end);
    
    $grouped_dates = [];
    
    if ($calendar_type === 'Daily') {
      foreach ($period as $date) {
        $year = $date->format('Y');
        $month = $date->format('F Y');
        $grouped_dates[$year][$month][] = $date->format('j');
      }
    } 
    elseif ($calendar_type === 'Weekly') {
      $days_of_the_week = array_map('ucfirst', $shift_scheduler_info['days_of_the_week'] ?? []);
  
      foreach ($period as $date) {
        $dayName = $date->format('l'); 
        if (in_array($dayName, $days_of_the_week)) {
          $year = $date->format('Y');
          $month = $date->format('F Y');
          $grouped_dates[$year][$month][] = $date->format('j');
        }
      }
    } 
    elseif ($calendar_type === 'Monthly') {
      $days_of_the_month = array_map('intval', $shift_scheduler_info['days_of_the_month'] ?? []);
  
      foreach ($period as $date) {
        $dayOfMonth = (int)$date->format('j');
        if (in_array($dayOfMonth, $days_of_the_month)) {
          $year = $date->format('Y');
          $month = $date->format('F Y');
          $grouped_dates[$year][$month][] = $date->format('j');
        }
      }
    }
    
    $dates .= '<div class="wrapper-year">';
    foreach ($grouped_dates as $year => $months) {
        $dates .= "<div class='group-year-month-day'>";
        foreach ($months as $month => $days) {
          $dates .= "<div class='group-month'><strong>". ($month) .":</strong> " . implode(', ', $days) . "</div>";
        }
        $dates .= '</div>';
    }
    $dates .= '</div>';
    // Load Information location
    $location_trails = $location->get('field_location_trail')->referencedEntities();
    $location_trail_label = '';
    
    if (!empty($location_trails)) {
      foreach ($location_trails as $location_trail) {
        $location_trail_label .= ' > ' . $location_trail->label();
      }
    }


    $form['address'] = [
      '#markup' => "<div class='header-address'><div class='title-address'>" . t('Address: ') . " </div><div class='value-address'>" . $location->label() . " $location_trail_label</div></div>",
    ];
    

    $form['time'] = [
        '#markup' => "<div class='header-time'>$time</div>",
    ];
    $form['dates'] = [
        '#markup' => "<div class='header-dates'>$dates</div>",
    ];

    $form['employee_select'] = [
      '#type' => 'select',
      '#title' => $this->t('Select Employee'),
      '#options' => $employee_options,
      '#empty_option' => $this->t('- Select an employee -'),
      '#required' => TRUE,
    ];

    if (!empty(($employee_options))) {
      $form['related_plan'] = [
        '#type' => 'markup',
        '#markup' => '<h3 class="related-plan">'. t('Related Plan') .'</h3>',
      ];
    }

    $output = '';

    // Format user
    foreach ($employee_schedule as $employee_name => $dates) {
      $uid = strtolower(preg_replace('/\s+/', '-', $employee_name));
  
      $output .= "<div class='employee-bulk-assign'>
                      <div class='name-button'><strong>$employee_name</strong> 
                          <div class='toggle-button-bulk' data-user-id='$uid'>Toggle Assignments</div>
                      </div>";
      
      $output .= "<div id='assignments-$uid' class='assignments-list hidden' style='margin-left: 20px;'>";
      
      $grouped_by_month = [];
      foreach ($dates as $date) {
          $month = date('F Y', strtotime($date));
          $day = date('j', strtotime($date));
          $grouped_by_month[$month][] = $day;
      }
      
      foreach ($grouped_by_month as $month => $days) {
          $output .= "<div class='scheduler-month'><strong>$month:</strong> " . implode(', ', $days) . "</div>";
      }
      
      $output .= "</div></div>";
    }
  
    
    $form['#attached']['library'][] = 'substitutoo_core/toggle_assignments';
    

    $form['employee_schedule'] = [
      '#markup' => $output,
    ];

    $form['markup_hidden'] = [
      '#type' => 'hidden',
      '#value' => $output,
    ];

    $form['actions'] = [
      '#type' => 'container',
      '#attributes' => ['class' => 'form-actions'],
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Bulk Assign'),
    ];

    $form['shift_scheduler_id'] = [
      '#type' => 'hidden',
      '#value' => $shift_scheduler->id(),
    ];

    $form['shift_id'] = [
      '#type' => 'hidden',
      '#value' => $shift_id,
    ];

    return $form;
  }
  

  public function submitForm(array &$form, FormStateInterface $form_state) {
    $request = $this->request;
    $shift_id = $form_state->getValue('shift_id');
    $date_param = $request->query->get('date');
    $month_param = $request->query->get('month');
    $year_param = $request->query->get('year');
    $order_assignment = $request->query->get('order_assignment');
    $shift_scheduler_id = $form_state->getValue('shift_scheduler_id');
    $selected_user_id = $form_state->getValue('employee_select');
    $account = \Drupal\user\Entity\User::load($selected_user_id);
    $description = $form_state->getValue('markup_hidden');
    $description = str_replace(['Toggle Assignments', 'hidden'], '', $description);
    $description = preg_replace(
      "/<div class='name-button'>.*?<div class='toggle-button-bulk' data-user-id='.*?'>.*?<\/div>\s*<\/div>/s",
      '',
      $description
    );

    // Create scheduler asignment
    $scheduler_assign = $this->entityTypeManager
    ->getStorage('sub_scheduler_assignment')
    ->create([
      'type' => 'sub_scheduler_assignment',
      'label' => 'Bulk Assignment ' . $account->getDisplayName(),
      'field_assignment_order' => $order_assignment,
      'status' => 1,
      'field_employee' => $selected_user_id,
      'field_shift_scheduler' => $shift_scheduler_id,
      'description' => [
        'value' => $description, 
        //'format' => 'full_html', 
      ],
    ]);
    $scheduler_assign->save();

    $this->messenger->addMessage($this->t('Employee bulk assigned successfully.'));

    // Activity bulk assign
    $shift_scheduler_entity = $this->entityTypeManager->getStorage('sub_shift_scheduler')->load($shift_scheduler_id);
    $name_scheduler = $shift_scheduler_entity->label();
    $data = [
      'name' => 'You bulked assign for ' . $account->getDisplayName() . ' with service contract: ' .  $name_scheduler  . ' on assignment: ' . $order_assignment,
    ];
    \Drupal::service('sub_activity.activity_service')->createActivity($data);

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
