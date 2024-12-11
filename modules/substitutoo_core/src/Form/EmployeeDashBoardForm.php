<?php

namespace Drupal\substitutoo_core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Database\Database;

class EmployeeDashBoardForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'employee_dashboard_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $user_id = \Drupal::routeMatch()->getParameter('user');
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

    $output = '<div class="view-table"><table class="calendar-view-table calendar-view-month responsive-enabled">';
    $output .= '<thead>';
    foreach ($header as $day) {
      $day_summary = substr($day, 0, 3);
      $output .= '<th><div class="month-of-year">' . $day . '</div><div class="month-of-year-mobile">' . $day_summary . '</div></th>';
    }
    $output .= '</thead>';
    foreach ($rows as $row) {
      $output .= '<tr>';
      foreach ($row as $day_info) {
        if (is_array($day_info) && !empty($day_info['date'])) {
          $date = $day_info['date'];
          $day = substr($date, 8, 2);
          $month = substr($date, 5, 2);
          $year = substr($date, 0, 4);
          $date_url = Url::fromRoute('substitutoo_core.manage_employee', ['date' => $day, 'month' => $month, 'year' => $year, 'user' => $user_id])->toString();
          // Query count shift with date
          $shift_storage = \Drupal::entityTypeManager()->getStorage('sub_shift');
          $shifts = $shift_storage->loadByProperties([
            'f_date' => $date,
          ]);

          $shift_ids = array_map(function ($shift) {
            return $shift->id();
          }, $shifts);

          $count_availabilities = 0;
          $count_assignments = 0;
          $count_unavailabilities = 0;

          if ($shift_ids) {
            // Assignmentss
            $assignments = \Drupal::entityTypeManager()->getStorage('sub_assignment')->loadByProperties([
              'f_date' => $date,
              'f_employee' => $user_id,
            ]);
            if (isset($assignments)) {
              $count_assignments = count($assignments);
            }
              
            // Availabilities
            $availabilities = \Drupal::entityTypeManager()->getStorage('sub_availability')->loadByProperties([
              'f_date' => $date,
              'f_employee' => $user_id,
            ]);
            if (isset($availabilities)) {
              $count_availabilities = count($availabilities);
            }

            // Unavailabilities
            $leave_id = \Drupal::service('substitutoo_core.substitutoo_service')->getTaxonomyTermTid('unavailable_type', 'Leave');

            $date_start = new \DateTime($date);
            $date_end = new \DateTime($date);
            $date_start = $date_start->format('Y-m-d') . 'T00:00:00';  
            $date_end = $date_end->format('Y-m-d') . 'T23:59:59';
            $query = \Drupal::database()->select('sub_unavailability', 'u')
              ->fields('u', ['id', 'label', 'status']) 
              ->condition('u.f_unavailable_type', $leave_id)
              ->condition('u.f_employee', $user_id)
              ->condition('u.f_datetime_start', $date_end, '<=')
              ->condition('u.f_datetime_end', $date_start, '>=');

            $results = $query->execute()->fetchAllAssoc('id');
            if (isset($results)) {
              $count_unavailabilities = count($results);
            }
          }
  
          $output .= '<td><div class="day-of-month">'; //open

          $output .= '<div class="date"><a href="' . $date_url . '">' . $day . '</a></div>';
          $output .= '<div class="total-available">' . t('Availabilities') . ': ' . $count_availabilities . '</div>';
          $output .= '<div class="total-assignment">' . t('Assignments') . ':' . $count_assignments .  '</div>';
          $output .= '<div class="assigned-count">' . t('Unavailabilities') . ':' . $count_unavailabilities .  '</div>';

          $output .= '</div></td>';//closest
        } else {
          $output .= '<td><div class="day-of-month"></div></td>';
        }
      }
      $output .= '</tr>';
    }
    $output .= '</table>';
    $output .= '</div>';

    $form['title-date-picker']['filter_calendar']['navigation'] = [
      '#type' => 'markup',
      '#markup' => $navigation,
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
  
}
