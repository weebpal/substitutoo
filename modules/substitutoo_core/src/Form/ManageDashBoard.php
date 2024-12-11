<?php

namespace Drupal\substitutoo_core\Form;

use DateTime;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\Query\QueryInterface;

class ManageDashBoard extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'manage_dashboard_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $request = \Drupal::request();
    $month = $request->query->get('month') ?? date('n');
    $year = $request->query->get('year') ?? date('Y');

    if (!checkdate($month, 1, $year)) {
      $month = date('n');
      $year = date('Y');
    }

    $prev_month = ($month == 1) ? 12 : $month - 1;
    $prev_year = ($month == 1) ? $year - 1 : $year;
    $next_month = ($month == 12) ? 1 : $month + 1;
    $next_year = ($month == 12) ? $year + 1 : $year;

    $prev_url = Url::fromRoute('<current>', ['month' => $prev_month, 'year' => $prev_year])->toString();
    $next_url = Url::fromRoute('<current>', ['month' => $next_month, 'year' => $next_year])->toString();

    $prev_link = '<div class="prev-link"><a href="' . $prev_url . '">Previous</a></div>';
    $next_link = '<div class="next-link"><a href="' . $next_url . '">Next</a></div>';
    $navigation = '<div class="calendar-navigation">' . $prev_link;
    $navigation .= '<div class="calendar-header"><h2>' . t(date('F', mktime(0, 0, 0, $month, 1, $year))) . ' - ' . $year . '</h2></div>';
    $navigation .= $next_link . '</div>';

    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $first_day_of_month = mktime(0, 0, 0, $month, 1, $year);
    $first_day_of_week = date('N', $first_day_of_month);

    $header = [t('MONDAY'), t('TUESDAY'), t('WEDNESDAY'), t('THURSDAY'), t('FRIDAY'), t('SATURDAY'), t('SUNDAY')];
    $rows = [];

    $current_day = 1;
    $row = array_fill(0, $first_day_of_week - 1, '');

    for ($i = $first_day_of_week - 1; $i < 7; $i++) {
      $row[] = [
        'date' => date('Y-m-d', mktime(0, 0, 0, $month, $current_day, $year)),
      ];
      $current_day++;
    }
    $rows[] = $row;

    while ($current_day <= $days_in_month) {
      $row = [];
      for ($i = 0; $i < 7; $i++) {
        if ($current_day <= $days_in_month) {
          $row[] = [
            'date' => date('Y-m-d', mktime(0, 0, 0, $month, $current_day, $year)),
          ];
          $current_day++;
        } else {
          $row[] = '';
        }
      }
      $rows[] = $row;
    }

    $output = '<div class="view-table"><div class="month-of-year-notable-mobile">
<div>MONDAY</div>
<div>TUESDAY</div>
<div>WEDNESDAY</div>
<div>THURSDAY</div>
<div>FRIDAY</div>
<div>SATURDAY</div>
<div>SUNDAY</div>
</div><table class="calendar-view-table calendar-view-month responsive-enabled">';
    $output .= '<thead>';
    foreach ($header as $day) {
      $day_summary = substr($day, 0, 3);
      $output .= '<th><div class="month-of-year">' . $day . '</div><div class="month-of-year-mobile">' . $day_summary . '</div></th>';
    }
    $output .= '</thead>';

    // Total time current month
    $total_shifts = 0;
    $total_assignments = 0;
    $total_assigned = 0;
    $total_unassigned = 0;
    $total_time_required = 0;
    $total_time_assigned = 0;
    $total_time_resources = 0;
    $total_time_available = 0;
    $time_total_unassigned = 0;

    foreach ($rows as $row) {
      $output .= '<tr>';
      foreach ($row as $day_info) {
        if (is_array($day_info) && !empty($day_info['date'])) {
          $date = $day_info['date'];
          //dump($date);
          $day = substr($date, 8, 2);
          $date_format = DateTime::createFromFormat('Y-m-d', $date);
          $day_format =  substr($date_format->format('F'),0,3) . ' ' . $this->addOrdinalSuffix($date_format->format('j')) . ', ' . $date_format->format('Y');
          $month = substr($date, 5, 2);
          $year = substr($date, 0, 4);
          $date_url = Url::fromRoute('substitutoo_core.date', ['date' => $day, 'month' => $month, 'year' => $year])->toString();
    
          $day_shifts_count = 0;
          $day_assignments_count = 0;
          $day_assigned_count = 0;
          $count_empty_assignments = 0;
          $time_required = 0;
          $time_assigned = 0;
          $time_resources = 0;
    
          // Query Shifts.
          $shift_storage = \Drupal::entityTypeManager()->getStorage('sub_shift');
          $shifts = $shift_storage->loadByProperties(['f_date' => $date]);
          $day_shifts_count = count($shifts);
          $total_shifts += $day_shifts_count;
    
          $shift_ids = array_map(function ($shift) {
            return $shift->id();
          }, $shifts);
    
          if ($shift_ids) {
            // Query Assignments.
            $assignments = \Drupal::entityTypeManager()->getStorage('sub_assignment')->loadByProperties(['f_shift' => $shift_ids]);
            $day_assignments_count = count($assignments);
            $total_assignments += $day_assignments_count;
    
            foreach ($assignments as $assignment) {

              $time_required += intval($assignment->get('f_duration')->getString());
    
              if ($assignment->get('f_employee')->entity !== NULL) {
                $assignment_date = $assignment->get('f_datetime_end')->getString();
                if (date('Y-m-d', strtotime($assignment_date)) === $date) {
                  $time_assigned += intval($assignment->get('f_duration')->getString());
                  $day_assigned_count++;
                }
              }
            }

            $total_time_required += $time_required;
            $total_time_assigned += $time_assigned;
            $day_unassigned_count = $day_assignments_count - $day_assigned_count;
            $total_unassigned += $day_unassigned_count;

            $time_total_unassigned_day = $time_required - $time_assigned;
            $time_total_unassigned += $time_total_unassigned_day;
    
            $empty_assignments_query = \Drupal::entityTypeManager()->getStorage('sub_assignment')->getQuery()
              ->condition('f_shift', $shift_ids, 'IN')
              ->notExists('f_employee')
              ->accessCheck(FALSE);
            $count_empty_assignments = count($empty_assignments_query->execute());
          }
    
          // Query Resources.
          $availabilities = \Drupal::entityTypeManager()->getStorage('sub_availability')->loadByProperties(['f_date' => $date]);
          foreach ($availabilities as $availability) {
            $time_resources += intval($availability->get('f_duration')->getString());
          }

          $total_time_resources += $time_resources;

          $day_total_available = $time_resources - $time_assigned;
          $total_time_available += $day_total_available;
          
          // Format second
          $time_required_format = $this->formatSecond($time_required);
          $time_assigned_format = $this->formatSecond($time_assigned);
          $time_unassigned_format = $this->formatSecond($time_required - $time_assigned);
          $time_resources_format = $this->formatSecond($time_resources);
          $total_available = $this->formatSecond($time_resources - $time_assigned);

          if ($time_required == 0) {
            $time_assigned_rate = '-%';
          }
          else {
            $time_assigned_rate = ($time_required > 0) ? round(($time_assigned / $time_required) * 100, 2) . '%' : '0%';
          }

          if ($time_resources == 0) {
            $total_available_rate = '-%';
          }
          else {
            $total_available_rate = ($time_resources > 0) ? round(($time_assigned / $time_resources) * 100, 2) . '%' : '0%';
          }
          $output .= '<td><div class="day-of-month">';
          $output .= '<div class="date"><a href="' . $date_url . '">' . $day_format . '</a></div>';

          // Gr div total shift
          $output .= '<div class="total-shift-calendar">';
          $output .= '<div class="total-shift">' . t('Total Shifts: ') . $day_shifts_count . '</div>';
          $output .='</div>';
          
          // Gr div Assignmnet
          $output .= '<div class="total-assignment-calendar">';
          $output .= '<div class="total-assignment">' . t('Assignment Required: ') . $day_assignments_count . ' (' . $time_required_format . ')' . '</div>';
          $output .= '<div class="assigned-count">' . t('Assignment Assigned: ') . $day_assigned_count . ' (' . $time_assigned_format . ')' . '</div>';
          $output .= '<div class="empty-assignment">' . t('Assignment Unassigned: ') . $count_empty_assignments . ' (' . $time_unassigned_format . ')' . '</div>';
          $output .= '<div class="total-time-assigned-rate">' . t('Assignment Coverage Rate: ') . $time_assigned_rate . '</div>';
          $output .='</div>';

          // Gr div 
          $output .= '<div class="total-resource-calendar">';
          $output .= '<div class="total-time-resource">' . t('Resource Capacity: ') . $time_resources_format . '</div>';
          $output .= '<div class="total-time-resource">' . t('Resource Assigned: ') . $time_assigned_format . '</div>';
          $output .= '<div class="total-time-available">' . t('Resource Available: ') . $total_available . '</div>';
          $output .= '<div class="total-available-rate">' . t('Resource Utilization Rate: ') . $total_available_rate . '</div>';
          $output .='</div>';

          $output .= '</div></td>';
        } 
        else {
          $output .= '<td><div class="day-of-month"></div></td>';
        }
      }
      $output .= '</tr>';
    }
    
    $output .= '</table>';
    $output .= '</div>';

    

    $time_assigned_rate_total = ($total_time_required > 0) ? round(($total_time_assigned / $total_time_required) * 100, 2) . '%' : '0%';
    


    if ($total_time_required == 0) {
      $time_assigned_rate = '-%';
    }
    else {
      $time_assigned_rate_total = ($total_time_required > 0) ? round(($total_time_assigned / $total_time_required) * 100, 2) . '%' : '0%';
    }

    if ($total_time_resources == 0) {
      $total_available_rate = '-%';
    }
    else {
      $total_available_rate_total = ($total_time_resources > 0) ? round(($total_time_assigned / $total_time_resources) * 100, 2) . '%' : '0%';
    }

    $form['wrapper-monthly-navigation'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => 'wrapper-monthly-navigation',
      ],
    ];

    $form['wrapper-monthly-navigation']['current_month_summary'] = [
      '#type' => 'markup',
      '#markup' => '<div class="monthly-summary">' . 
                  '<div class="total-shift">' .
                   '<div class="navigation">'. $navigation .'</div>' . '<div><strong>' . t('Total Shifts: ') . '</strong>' . $total_shifts . '</div></div>' .
                   
                   '<div class="total-assignment">' .
                   '<div><strong>' . t('Assignment Required: ') . '</strong>' . $total_assignments . '</div>' .
                   '<div><strong>' . t('Assignment Assigned: ') . '</strong>' . ($total_assignments - $total_unassigned) . '</div>' .
                   '<div><strong>' . t('Assignment Unassigned: ') . '</strong>' . $total_unassigned . '</div>' .
                   '<div><strong>' . t('Assignment Coverage Rate: ') . '</strong>' . $time_assigned_rate_total . '</div></div>' .
                   '<div class="total-resource">' .
                   '<div><strong>' . t('Resource Capacity: ') . '</strong>' . $this->formatSecond($total_time_resources) . '</div>' .
                   '<div><strong>' . t('Resource Assigned: ') . '</strong>' . $this->formatSecond($total_time_assigned) . '</div>' .
                   '<div><strong>' . t('Resource Available: ') . '</strong>' . $this->formatSecond($total_time_available) . '</div>' .
                   '<div><strong>' . t('Resource Utilization Rate: ') . '</strong>' . $total_available_rate_total . '</div>' .
                   '</div></div>',
    ];

    $form['filter_calendar'] = [
      '#type' => 'container',
      '#attributes' => [
        'id' => 'calendar-wrapper-filter',
        'class' => 'calendar-filter hidden',
      ],
    ];

    $form['filter_calendar']['title-date-picker']['filter_calendar']['filter_date'] = [
      '#type' => 'textfield',
      '#title' => $this->t(''),
      '#default_value' => '',
      '#attributes' => [
        'id' => 'datepicker',
        'style' => 'width: 40px; height: 40px;',
      ],
    ];

    $form['calendar'] = [
      '#type' => 'markup',
      '#markup' => $output,
    ];

    $form['#attached']['library'][] = 'substitutoo_core/manage_dashboard';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {}

    /**
   * {@inheritdoc}
   */
  public function formatSecond($second) {
    $hours = floor($second / 3600);
    $minutes = floor(($second % 3600) / 60);
  
    if ($hours > 0 && $minutes > 0) {
      $time_required_format = sprintf('%dh %dm', $hours, $minutes);
    } elseif ($hours > 0) {
      $time_required_format = sprintf('%dh', $hours);
    } elseif ($minutes > 0) {
      $time_required_format = sprintf('%dm', $minutes);
    } else {
      $time_required_format = '0h';
    }
  
    return $time_required_format;
  }

  function addOrdinalSuffix($day) {
    if (!in_array(($day % 100), [11, 12, 13])) {
      switch ($day % 10) {
        case 1:
          return $day . 'st';
        case 2:
          return $day . 'nd';
        case 3:
          return $day . 'rd';
      }
    }
    return $day . 'th';
  }
  
}
