<?php

namespace Drupal\substitutoo_core;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Condition\Annotation\Condition;
use Drupal\Core\Database\Database;
use Drupal\Core\Entity\EntityInterface;
use Drupal\sub_shift_scheduler\Entity\SubstitutooShiftScheduler;
use Drupal\sub_shift\Entity\SubstitutooShift;
use Drupal\sub_availability_scheduler\Entity\SubstitutooAvailabilityScheduler;
use Drupal\sub_scheduler_assignment\Entity\SubstitutooSchedulerAssignment;
use tidy;

class SubstitutooService {
  const SCHEDULER_CALENDAR_DAILY = 'Daily';
  const SCHEDULER_CALENDAR_WEEKLY = 'Weekly';
  const SCHEDULER_CALENDAR_MONTHLY = 'Monthly';
  const WEEK_DAY_MONDAY = 'Monday';
  const WEEK_DAY_TUESDAY = 'Tuesday';
  const WEEK_DAY_WEDNESDAY = 'Wednesday';
  const WEEK_DAY_THURSDAY = 'Thursday';
  const WEEK_DAY_FRIDAY = 'Friday';
  const WEEK_DAY_SATURDAY = 'Saturday';
  const WEEK_DAY_SUNDAY = 'Sunday';
  const UNAVAILABILITY_TYPE_ASSIGNED = 'Assigned';
  const UNAVAILABILITY_TYPE_LEAVE = 'Leave';
  const UNAVAILABILITY_TYPE_TRAINING = 'Training';
  const UNAVAILABILITY_TYPE_OTHER = 'Others';
  const CONFIRMATION_STATUS_CONFIRMED = 'Confirmed';
  const CONFIRMATION_STATUS_PENDING = 'Pending';
  const CONFIRMATION_STATUS_REJECTED = 'Rejected';
  const ASSIGNMENT_TYPE_PERMANENT = 'Permanent';
  const ASSIGNMENT_TYPE_TEMPORARY = 'Temporary';
  const ASSIGNMENT_TYPE_SUBSTITUTE = 'Substitute';
  const ASSIGNMENT_STATUS_PENDING = 'Pending';
  const ASSIGNMENT_STATUS_ASSIGNED = 'Assigned';
  const ASSIGNMENT_STATUS_COMPLETED = 'Completed';
  const ASSIGNMENT_STATUS_INCOMPLETE = 'Incomplete';
  const ASSIGNMENT_STATUS_CANCELED = 'Canceled';

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new SubstitutooService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  // function isMatchedEmployee($uid, $f_shift_scheduler, $date) {
  //   $user = \Drupal\user\Entity\User::load($uid);
  //   $genders = array_column($user->get('field_gender')->getValue(), 'target_id');
  //   $birth_date = $user->get('field_date_of_birth')->value;
  //   $experience_levels = array_column($user->get('field_experience_level')->getValue(), 'target_id');
  //   $user_qualifications = array_column($user->get('field_qualifications')->getValue(), 'target_id');
  //   $user_certifications = array_column($user->get('field_certifications')->getValue(), 'target_id');
  
  //   $shift_genders = array_column($f_shift_scheduler->get('field_genders')->getValue(), 'target_id');
  //   $age_ranges = $f_shift_scheduler->get('field_age_ranges')->getValue();
  //   $age_range_ids = $this->getTargetIds($age_ranges);
  //   $age_ranges_shift_scheduler = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadMultiple($age_range_ids);
  //   $shift_experience_levels = array_column($f_shift_scheduler->get('field_experience_levels')->getValue(), 'target_id');
  //   $shift_qualifications = array_column($f_shift_scheduler->get('field_qualifications')->getValue(), 'target_id');
  //   $shift_certifications = array_column($f_shift_scheduler->get('field_certifications')->getValue(), 'target_id');
  

  //   $birth_date_obj = new \DateTime($birth_date);
  //   $current_date_obj = new \DateTime($date);

  //   $age = $current_date_obj->diff($birth_date_obj)->y;

  //   $age_match = FALSE;
  //   foreach ($age_ranges_shift_scheduler as $range) {
  //     $from_age = $range->get('field_from')->value;
  //     $to_age = $range->get('field_to')->value;
  //     if ($age >= $from_age && $age <= $to_age) {
  //       $age_match = TRUE;
  //       break;
  //     }
  //   }

  //   if (!$age_match) {
  //     return FALSE;
  //   }

  //   if (!empty($shift_genders) && empty(array_intersect($genders, $shift_genders))) {
  //     return FALSE;
  //   }

  //   if (!empty($shift_experience_levels) && empty(array_intersect($experience_levels, $shift_experience_levels))) {
  //     return FALSE;
  //   }

  //   if (!empty($shift_qualifications)) {
  //     $missing_qualifications = array_diff($shift_qualifications, $user_qualifications);
  //     if (!empty($missing_qualifications)) {
  //       return FALSE;
  //     }
  //   }

  //   if (!empty($shift_certifications)) {
  //     $missing_certifications = array_diff($shift_certifications, $user_certifications);
  //     if (!empty($missing_certifications)) {
  //       return FALSE;
  //     }
  //   }
  
  //   return TRUE;
  // }
  function isMatchedEmployee($uid, $f_shift_scheduler, $date) {
    $user = \Drupal\user\Entity\User::load($uid);
    $genders = array_column($user->get('field_gender')->getValue(), 'target_id');
    $birth_date = $user->get('field_date_of_birth')->value;
    $experience_levels = array_column($user->get('field_experience_level')->getValue(), 'target_id');
    $user_qualifications = array_column($user->get('field_qualifications')->getValue(), 'target_id');
    $user_certifications = array_column($user->get('field_certifications')->getValue(), 'target_id');

    $shift_genders = array_column($f_shift_scheduler->get('field_genders')->getValue(), 'target_id');
    $age_ranges = $f_shift_scheduler->get('field_age_ranges')->getValue();
    $age_range_ids = $this->getTargetIds($age_ranges);
    $age_ranges_shift_scheduler = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadMultiple($age_range_ids);
    $shift_experience_levels = array_column($f_shift_scheduler->get('field_experience_levels')->getValue(), 'target_id');
    $shift_qualifications = array_column($f_shift_scheduler->get('field_qualifications')->getValue(), 'target_id');
    $shift_certifications = array_column($f_shift_scheduler->get('field_certifications')->getValue(), 'target_id');

    $birth_date_obj = new \DateTime($birth_date);
    $current_date_obj = new \DateTime($date);

    $age = $current_date_obj->diff($birth_date_obj)->y;

    // Check age ranges if they exist.
    if (!empty($age_ranges)) {
      $age_match = FALSE;
      foreach ($age_ranges_shift_scheduler as $range) {
        $from_age = $range->get('field_from')->value;
        $to_age = $range->get('field_to')->value;
        if ($age >= $from_age && $age <= $to_age) {
          $age_match = TRUE;
          break;
        }
      }
      if (!$age_match) {
        return FALSE;
      }
    }

    // Check genders if they exist.
    if (!empty($shift_genders) && empty(array_intersect($genders, $shift_genders))) {
      return FALSE;
    }

    // Check experience levels if they exist.
    if (!empty($shift_experience_levels) && empty(array_intersect($experience_levels, $shift_experience_levels))) {
      return FALSE;
    }

    // Check qualifications if they exist.
    if (!empty($shift_qualifications)) {
      $missing_qualifications = array_diff($shift_qualifications, $user_qualifications);
      if (!empty($missing_qualifications)) {
          return FALSE;
      }
    }

    // Check certifications if they exist.
    if (!empty($shift_certifications)) {
      $missing_certifications = array_diff($shift_certifications, $user_certifications);
      if (!empty($missing_certifications)) {
          return FALSE;
      }
    }

    return TRUE;
  }

  
  function getAllEmployeesByAllCriteria($gender_ids = [], $age_range_ids = [], $experience_level_ids = [], $qualifications_ids = [], $certifications_ids = [], $date) {
    $connection = \Drupal::database();
    $query = $connection->select('users_field_data', 'u')
      ->fields('u', ['uid']);

    $query->join('user__roles', 'r', 'r.entity_id = u.uid');
    $query->condition('r.roles_target_id', 'employee');

    if (!empty($age_range_ids)) {
      $age_ranges = $this->entityTypeManager->getStorage('taxonomy_term')->loadMultiple($age_range_ids);
      $conditions = $query->orConditionGroup();
      
      foreach ($age_ranges as $term) {
        $from_age = $term->get('field_from')->value;
        $to_age = $term->get('field_to')->value;
        $current_date = $date;

        $from_date = date('Y-m-d', strtotime("$current_date - $to_age years"));
        $to_date = date('Y-m-d', strtotime("$current_date - $from_age years"));
        $conditions->condition('dob.field_date_of_birth_value', [$from_date, $to_date], 'BETWEEN');
      }

      $query->join('user__field_date_of_birth', 'dob', 'dob.entity_id = u.uid');
      $query->condition($conditions);
    }

    if (!empty($gender_ids)) {
      $query->join('user__field_gender', 'g', 'g.entity_id = u.uid');
      $query->condition('g.field_gender_target_id', $gender_ids, 'IN');
    }

    if (!empty($experience_level_ids)) {
      $query->join('user__field_experience_level', 'e', 'e.entity_id = u.uid');
      $query->condition('e.field_experience_level_target_id', $experience_level_ids, 'IN');
    }

    if (!empty($certifications_ids)) {
      $query->join('user__field_certifications', 'c', 'c.entity_id = u.uid');
      $query->condition('c.field_certifications_target_id', $certifications_ids, 'IN');
      $cert_count = count($certifications_ids);
      $query->having('COUNT(DISTINCT c.field_certifications_target_id) = :cert_count', [':cert_count' => $cert_count]);
    }

    if (!empty($qualifications_ids)) {
      $query->join('user__field_qualifications', 'q', 'q.entity_id = u.uid');
      $query->condition('q.field_qualifications_target_id', $qualifications_ids, 'IN');
      $qual_count = count($qualifications_ids);
      $query->having('COUNT(DISTINCT q.field_qualifications_target_id) = :qual_count', [':qual_count' => $qual_count]);
    }

    $query->groupBy('u.uid');
    
    // $cert_count = count($certifications_ids);
    // $qual_count = count($qualifications_ids);

    // $query->having('COUNT(DISTINCT c.field_certifications_target_id) = :cert_count', [':cert_count' => $cert_count]);
    // $query->having('COUNT(DISTINCT q.field_qualifications_target_id) = :qual_count', [':qual_count' => $qual_count]);

    $user_ids = $query->execute()->fetchCol();

    return $user_ids;
  }

  public function getTargetIds($data, $multiple = TRUE) {
    $target_ids = [];
    foreach ($data as $item) {
      if (isset($item['target_id'])) {
        $target_ids[] = $item['target_id'];
      }
    }
    if ($multiple) {
      return $target_ids;
    } else {
      return reset($target_ids);
    }
  }

  public function getMatchedEmployees($shift_scheduler, $date) {
    $time_start = microtime(TRUE);
    $genders = $shift_scheduler->get('field_genders')->getValue();
    $age_ranges = $shift_scheduler->get('field_age_ranges')->getValue();
    $experience_levels = $shift_scheduler->get('field_experience_levels')->getValue();
    $qualifications = $shift_scheduler->get('field_qualifications')->getValue();
    $certifications = $shift_scheduler->get('field_certifications')->getValue();

    $gender_ids = $this->getTargetIds($genders);
    $age_range_ids = $this->getTargetIds($age_ranges);
    $experience_level_ids = $this->getTargetIds($experience_levels);
    $qualifications_ids = $this->getTargetIds($qualifications);
    $certifications_ids = $this->getTargetIds($certifications);
    // print_r($gender_ids);
    // print_r($age_range_ids);
    // print_r($experience_level_ids);
    // print_r($qualifications_ids);
    // print_r($certifications_ids);

    $user_ids = $this->getAllEmployeesByAllCriteria($gender_ids, $age_range_ids, $experience_level_ids, $qualifications_ids, $certifications_ids, $date);
    $time_end = microtime(TRUE);
    $time = $time_end - $time_start;
    \Drupal::logger('time')->notice("getMatchedEmployees: " . $time);
    return $user_ids;
  }

  public function getAvailableEmployees(SubstitutooShift $shift) {
    // Load start date of shift
    $field_datetime_start = $shift->get('f_datetime_start')->getValue();
    $date = new \DateTime($field_datetime_start[0]['value']);
    $formatted_date = $date->format('Y-m-d');

    $f_location = $shift->get('f_location')->getValue();
    if(empty($f_location)) {
      return NULL;
    }
    $f_shift_scheduler = $shift->get('f_shift_scheduler')->getValue();
    if(empty($f_shift_scheduler)) {
      return NULL;
    }

    //print_r($f_shift_scheduler);
    $f_shift_scheduler_id = $f_shift_scheduler[0]['target_id'];
    $shift_scheduler = $this->entityTypeManager->getStorage('sub_shift_scheduler')->load($f_shift_scheduler_id);

    $matched_criteria_employees = $this->getMatchedEmployees($shift_scheduler, $formatted_date);
    if(empty($matched_criteria_employees)) {
      return [];
    }

    $f_location_id = $f_location[0]['target_id'];
    $f_timestamp_start = $shift->get('f_timestamp_start')->value;
    $f_timestamp_end = $shift->get('f_timestamp_end')->value;

    $connection = Database::getConnection();

    $query = $connection->select('sub_availability', 'sa')
      ->fields('sa', ['f_employee'])
      ->condition('f_timestamp_start', $f_timestamp_start, '<=')
      ->condition('f_timestamp_end', $f_timestamp_end, '>=')
      ->condition('f_employee', $matched_criteria_employees, 'IN');
    
    $available_employees = $query->execute()->fetchCol();

    $query = $connection->select('sub_unavailability', 'su')
      ->fields('su', ['f_employee']);
  
    $or_group = $query->orConditionGroup()
    ->condition(
        $query->andConditionGroup()
          ->condition('f_timestamp_start', $f_timestamp_start, '>')
          ->condition('f_timestamp_start', $f_timestamp_end, '<')
      )
      ->condition(
        $query->andConditionGroup()
          ->condition('f_timestamp_end', $f_timestamp_start, '>')
          ->condition('f_timestamp_end', $f_timestamp_end, '<')
      )
      ->condition(
        $query->andConditionGroup()
          ->condition('f_timestamp_start', $f_timestamp_start, '<=')
          ->condition('f_timestamp_end', $f_timestamp_end, '>=')
      );
  
    $query->condition($or_group);
    if ($available_employees[0]) {
      $query->condition('f_employee', $available_employees, 'IN');
    }
    $unavailable_employees = $query->execute()->fetchCol();
  
    $available_employees = array_diff($available_employees, $unavailable_employees);

    $employees = $this->entityTypeManager->getStorage('user')->loadMultiple($available_employees);

    return $employees;
  }

  public function generateShifts(SubstitutooShiftScheduler $ShiftScheduler) {
    $data = $this->getShiftSchedulerInfo($ShiftScheduler);
    $delete_shifts_on_update = $ShiftScheduler->get('field_delete_shifts_on_update')->value;

    \Drupal::logger('generateShifts')->notice("Start generating shifts for scheduler: " . $ShiftScheduler->label());
    if($delete_shifts_on_update) {
      \Drupal::logger('generateShifts')->notice("Start deleting related objects: " . $ShiftScheduler->label());

      $connection = Database::getConnection();

      try {
        $query = "DELETE su FROM {sub_unavailability} AS su
          INNER JOIN {sub_shift} AS ss ON su.f_shift = ss.id
          WHERE ss.f_shift_scheduler = :scheduler_value";
        $connection->query($query, [':scheduler_value' => $ShiftScheduler->id()]);

        $query = "DELETE sa FROM {sub_assignment} AS sa
          INNER JOIN {sub_shift} AS ss ON sa.f_shift = ss.id
          WHERE ss.f_shift_scheduler = :scheduler_value";
        $connection->query($query, [':scheduler_value' => $ShiftScheduler->id()]);

        $query = $connection->delete('sub_shift')
          ->condition('f_shift_scheduler', $ShiftScheduler->id())
          ->execute();
      }
      catch (\Exception $e) {
        \Drupal::logger('substitutoo_core')->error($e->getMessage());
      }
      $ShiftScheduler->set("field_delete_shifts_on_update", 0);
      $ShiftScheduler->save();
      \Drupal::logger('generateShifts')->notice("Complete deleting related objects: " . $ShiftScheduler->label());
      return;
    }
    \Drupal::logger('generateShifts')->notice("Start creating shifts for scheduler: " . $ShiftScheduler->label());

    foreach($data['between_dates'] as $i => $values) {
      $location_id = $data['location'][0]['target_id'];
      $location_name = $this->entityTypeManager->getStorage('sub_location')->load($location_id)->label();

      $start_date_time = strtotime($values['value']);
      $end_date_time = strtotime($values['end_value']) + 86400;

      for($timestamp = $start_date_time; $timestamp < $end_date_time; $timestamp += 86400) {
        $date = date("Y-m-d", $timestamp);
        $date_in_week = date("l", $timestamp);
        $date_in_month = date("j", $timestamp);

        if($data['calendar_type'] == SubstitutooService::SCHEDULER_CALENDAR_WEEKLY) {
          if(!in_array($date_in_week, $data['days_of_the_week'])) {
            continue;
          }
        }
        else if($data['calendar_type'] == SubstitutooService::SCHEDULER_CALENDAR_MONTHLY) {
          if(!in_array($date_in_month, $data['days_of_the_month'])) {
            continue;
          }
        }

        foreach($data['time_frames'] as $j => $time_frame) {
          $from = date("H:i", $time_frame['from']);
          $to = date("H:i", $time_frame['to']);

          $start_timestamp = $timestamp + $time_frame['from'];
          $end_timestamp = $timestamp + $time_frame['to'];
          $f_duration = $end_timestamp - $start_timestamp;

          $now = \Drupal::service('date.formatter')->format($start_timestamp, 'custom', 'Y-m-d\TH:i:s');

          $start_datetime = \Drupal::service('date.formatter')->format($start_timestamp, 'custom', 'Y-m-d\TH:i:s');
          $end_datetime = \Drupal::service('date.formatter')->format($end_timestamp, 'custom', 'Y-m-d\TH:i:s');
          $date_str = date("Y-m-d", $timestamp);

          $shift_info = [
            'label' => t("Shift item for @location on @date_in_week, @date, from @from to @to", [
                '@location' => $location_name,
                '@date_in_week' => $date_in_week,
                '@date' => $date_str,
                '@from' => $from,
                '@to' => $to,
              ]),
            'f_location' => $location_id,
            'f_shift_scheduler' => $ShiftScheduler->id(),
            'f_date' => $date_str,
            'f_time_start' => $from,
            'f_time_end' => $to,
            'f_datetime_start' => $start_datetime,
            'f_datetime_end' => $end_datetime,
            'f_timestamp_start' => $start_timestamp,
            'f_timestamp_end' => $end_timestamp,
            'f_duration' => $f_duration,
            'f_required_number_employees' => $ShiftScheduler->get('field_required_number_employees')->value,
          ];

          $id = $this->createOrUpdateShift($shift_info);
        }
      }
    }
  }

  /*
  * Create or update assignment
  */
  public function createOrDeleteAssignment(SubstitutooShift $shift, $shift_info) {
    $number_employee = $shift->get('f_required_number_employees')->value;

    $connection = Database::getConnection();
    try {
      $query = "DELETE sa FROM {sub_assignment} AS sa
        WHERE sa.f_shift = :shift_id AND sa.f_assignment_order > :number_employee";
      $connection->query($query, [':shift_id' => $shift->id(), ':number_employee' => $number_employee]);
    }
    catch (\Exception $e) {
      \Drupal::logger('substitutoo_core')->error($e->getMessage());
    }

    $date = $shift->get('f_date')->value;
    $date_in_week = date("l", strtotime($date));
    $from = date("H:i", strtotime($shift->get('f_time_start')->value));
    $to = date("H:i", strtotime($shift->get('f_time_end')->value));

    $f_location_id = $shift_info['f_location'];
    $f_location = $shift->get("f_location")->getValue();
    if($f_location) {
      $f_location_id = $f_location[0]['target_id'];
    }
    $f_location = $f_location_id;
    $location_name = $this->entityTypeManager->getStorage('sub_location')->load($f_location)->label();

    $f_shift_scheduler_id = $shift_info['f_shift_scheduler'];
    $f_shift_scheduler = $shift->get("f_shift_scheduler")->getValue();
    if($f_shift_scheduler) {
      $f_shift_scheduler_id = $f_shift_scheduler[0]['target_id'];
    }
    $f_shift_scheduler = $f_shift_scheduler_id;

    $f_shift = $shift->id();

    $assignment_info = [
      'label' => t("Assignment item for @location on @date_in_week, @date, from @from to @to", [
          '@location' => $location_name,
          '@date_in_week' => $date_in_week,
          '@date' => $date,
          '@from' => $from,
          '@to' => $to,
        ]),
      'f_location' => $f_location,
      'f_shift' => $f_shift,
      'f_shift_scheduler' => $f_shift_scheduler,
      'f_date' => $shift->get('f_date')->value,
      'f_time_start' => $shift->get('f_time_start')->value,
      'f_time_end' => $shift->get('f_time_end')->value,
      'f_datetime_start' => $shift->get('f_datetime_start')->value,
      'f_datetime_end' => $shift->get('f_datetime_end')->value,
      'f_timestamp_start' => $shift->get('f_timestamp_start')->value,
      'f_timestamp_end' => $shift->get('f_timestamp_end')->value,
      'f_duration' => $shift->get('f_duration')->value,
    ];

    for($f_assignment_order = 1; $f_assignment_order <= $number_employee; $f_assignment_order ++) {
      $query = $this->entityTypeManager->getStorage('sub_assignment')->getQuery();
      $query->condition('f_location', $f_location);
      $query->condition('f_shift', $f_shift);
      $query->condition('f_shift_scheduler', $f_shift_scheduler);
      $query->condition('f_assignment_order', $f_assignment_order);
      $query->accessCheck(TRUE);
      $ids = $query->execute();
      if(count($ids)) {
        $id = reset($ids);
        $assignment = $this->entityTypeManager->getStorage('sub_assignment')->load($id);
        $assignment->set('label', $assignment_info['label']);
        $assignment->set('f_location', $assignment_info['f_location']);
        $assignment->set('f_shift', $assignment_info['f_shift']);
        $assignment->set('f_shift_scheduler', $assignment_info['f_shift_scheduler']);
        $assignment->set('f_date', $assignment_info['f_date']);
        $assignment->set('f_time_start', $assignment_info['f_time_start']);
        $assignment->set('f_time_end', $assignment_info['f_time_end']);
        $assignment->set('f_datetime_start', $assignment_info['f_datetime_start']);
        $assignment->set('f_datetime_end', $assignment_info['f_datetime_end']);
        $assignment->set('f_timestamp_start', $assignment_info['f_timestamp_start']);
        $assignment->set('f_timestamp_end', $assignment_info['f_timestamp_end']);
        $assignment->set('f_duration', $assignment_info['f_duration']);
        $assignment->save();
      }
      else {
        $assignment_info['f_assignment_order'] = $f_assignment_order;
        $assignment = $this->entityTypeManager->getStorage('sub_assignment')->create($assignment_info);
        $assignment->save();
      }
    }
  }

  /*
  * Create or update shift
  */
  public function createOrUpdateShift($shift_info) {
    $query = $this->entityTypeManager->getStorage('sub_shift')->getQuery();
    $query->condition('f_location', $shift_info['f_location']);
    $query->condition('f_shift_scheduler', $shift_info['f_shift_scheduler']);
    $query->condition('f_datetime_start', $shift_info['f_datetime_start']);
    $query->condition('f_datetime_end', $shift_info['f_datetime_end']);
    $query->accessCheck(TRUE);
    $ids = $query->execute();
    $shift = NULL;
    if(count($ids)) {
      $id = reset($ids);
      $shift = $this->entityTypeManager->getStorage('sub_shift')->load($id);
    }

    if($shift) {
      $shift->set('label', $shift_info['label']);
      $shift->set('f_location', $shift_info['f_location']);
      $shift->set('f_shift_scheduler', $shift_info['f_shift_scheduler']);
      $shift->set('f_date', $shift_info['f_date']);
      $shift->set('f_time_start', $shift_info['f_time_start']);
      $shift->set('f_time_end', $shift_info['f_time_end']);
      $shift->set('f_datetime_start', $shift_info['f_datetime_start']);
      $shift->set('f_datetime_end', $shift_info['f_datetime_end']);
      $shift->set('f_timestamp_start', $shift_info['f_timestamp_start']);
      $shift->set('f_timestamp_end', $shift_info['f_timestamp_end']);
      $shift->set('f_duration', $shift_info['f_duration']);
      $shift->set('f_required_number_employees', $shift_info['f_required_number_employees']);
      $shift->save();
      $this->createOrDeleteAssignment($shift, $shift_info);
      return $shift->id();
    }
    else {
      $shift = $this->entityTypeManager->getStorage('sub_shift')->create($shift_info);
      $shift->save();
      $this->createOrDeleteAssignment($shift, $shift_info);
      return $shift->id();
    }
  }

  public function generateAvailabilities(SubstitutooAvailabilityScheduler $AvailabilityScheduler) {
    $data = $this->getAvailabilitySchedulerInfo($AvailabilityScheduler);
    $field_delete_avail_on_update = $AvailabilityScheduler->get('field_delete_avail_on_update')->value;
    
    \Drupal::logger('generateAvailabilities')->notice("Start generating availabilities for scheduler: " . $AvailabilityScheduler->label());
    if($field_delete_avail_on_update) {
      \Drupal::logger('generateAvailabilities')->notice("Start deleting related objects: " . $AvailabilityScheduler->label());
      $connection = Database::getConnection();

      try {
        $query = $connection->delete('sub_availability')
          ->condition('f_availability_scheduler', $AvailabilityScheduler->id())
          ->execute();
      }
      catch (\Exception $e) {
        \Drupal::logger('substitutoo_core')->error($e->getMessage());
      }
      $AvailabilityScheduler->set("field_delete_avail_on_update", 0);
      $AvailabilityScheduler->save();
      \Drupal::logger('generateAvailabilities')->notice("Complete deleting related objects: " . $AvailabilityScheduler->label());
      return;
    }
    \Drupal::logger('generateAvailabilities')->notice("Start creating availabilities for scheduler: " . $AvailabilityScheduler->label());

    foreach($data['between_dates'] as $i => $values) {
      $employee_id = $data['employee'][0]['target_id'];
      $user = \Drupal\user\Entity\User::load($employee_id);
      $employee_name = $user->getDisplayName();

      $start_date_time = strtotime($values['value']);
      $end_date_time = strtotime($values['end_value']) + 86400;

      for($timestamp = $start_date_time; $timestamp < $end_date_time; $timestamp += 86400) {
        $date = date("Y-m-d", $timestamp);
        $date_in_week = date("l", $timestamp);;
        $date_in_month = date("j", $timestamp);

        if($data['calendar_type'] == SubstitutooService::SCHEDULER_CALENDAR_WEEKLY) {
          if(!in_array($date_in_week, $data['days_of_the_week'])) {
            continue;
          }
        }
        else if($data['calendar_type'] == SubstitutooService::SCHEDULER_CALENDAR_MONTHLY) {
          if(!in_array($date_in_month, $data['days_of_the_month'])) {
            continue;
          }
        }

        foreach($data['time_frames'] as $j => $time_frame) {
          $from = date("H:i", $time_frame['from']);
          $to = date("H:i", $time_frame['to']);

          $start_timestamp = $timestamp + $time_frame['from'];
          $end_timestamp = $timestamp + $time_frame['to'];
          $f_duration = $end_timestamp - $start_timestamp;
          $start_datetime = \Drupal::service('date.formatter')->format($start_timestamp, 'custom', 'Y-m-d\TH:i:s');
          $end_datetime = \Drupal::service('date.formatter')->format($end_timestamp, 'custom', 'Y-m-d\TH:i:s');
          $date_str = date("Y-m-d", $timestamp);

          $availability_info = [
            'label' => t("Availability item for @employee on @date_in_week, @date, from @from to @to", [
                '@employee' => $employee_name,
                '@date_in_week' => $date_in_week,
                '@date' => $date_str,
                '@from' => $from,
                '@to' => $to,
              ]),
            'f_employee' => $employee_id,
            'f_availability_scheduler' => $AvailabilityScheduler->id(),
            'f_date' => $date_str,
            'f_time_start' => $from,
            'f_time_end' => $to,
            'f_datetime_start' => $start_datetime,
            'f_datetime_end' => $end_datetime,
            'f_timestamp_start' => $start_timestamp,
            'f_timestamp_end' => $end_timestamp,
            'f_duration' => $f_duration,
          ];
          $id = $this->createOrUpdateAvailability($availability_info);
        }
      }
    }
  }

  /*
  * Create or update shift
  */
  public function createOrUpdateAvailability($shift_info) {
    $query = $this->entityTypeManager->getStorage('sub_availability')->getQuery();
    $query->condition('f_employee', $shift_info['f_employee']);
    $query->condition('f_availability_scheduler', $shift_info['f_availability_scheduler']);
    $query->condition('f_datetime_start', $shift_info['f_datetime_start']);
    $query->condition('f_datetime_end', $shift_info['f_datetime_end']);
    $query->accessCheck(TRUE);
    $ids = $query->execute();
    if (count($ids)) {
      $id = reset($ids);
      $availability = $this->entityTypeManager->getStorage('sub_availability')->load($id);
    }

    if (!empty($availability)) {
      $availability->set('label', $shift_info['label']);
      $availability->set('f_employee', $shift_info['f_employee']);
      $availability->set('f_availability_scheduler', $shift_info['f_availability_scheduler']);
      $availability->set('f_date', $shift_info['f_date']);
      $availability->set('f_time_start', $shift_info['f_time_start']);
      $availability->set('f_time_end', $shift_info['f_time_end']);
      $availability->set('f_datetime_start', $shift_info['f_datetime_start']);
      $availability->set('f_datetime_end', $shift_info['f_datetime_end']);
      $availability->set('f_duration', $shift_info['f_duration']);
      $availability->save();
      return $availability->id();
    }
    else {
      $availability = $this->entityTypeManager->getStorage('sub_availability')->create($shift_info);
      $availability->save();
      return $availability->id();
    }
  }

  public function bulkAssignments(SubstitutooSchedulerAssignment $scheduler_assignment) {
    $employee = $scheduler_assignment->get("field_employee")->getValue();
    $employee_id = NULL;
    if($employee) {
      $employee_id = $employee[0]['target_id'];;
    }

    $shift_scheduler = $scheduler_assignment->get("field_shift_scheduler")->getValue();
    $shift_scheduler_id = NULL;
    if($shift_scheduler) {
      $shift_scheduler_id = $shift_scheduler[0]['target_id'];
    }

    if(empty($employee_id) || empty($shift_scheduler_id)) {
      return;
    }
    $field_assignment_order = $scheduler_assignment->get('field_assignment_order')->value;
    $field_reset_assignment_on_update = $scheduler_assignment->get('field_reset_assignment_on_update')->value;

    \Drupal::logger('bulkAssignments')->notice("Start bulk assigning for scheduler: " . $scheduler_assignment->label());
    if($field_reset_assignment_on_update) {
      \Drupal::logger('bulkAssignments')->notice("Start reseting related assignments: " . $scheduler_assignment->label());

      $connection = Database::getConnection();

      try {
        $query = "UPDATE {sub_assignment} SET f_employee = NULL WHERE f_scheduler_assignment = :f_scheduler_assignment AND f_assignment_order > :f_assignment_order";
        $connection->query($query, [':f_scheduler_assignment' => $scheduler_assignment->id(), ':f_assignment_order' => $field_assignment_order]);

        $query = $connection->delete('sub_unavailability')
         ->condition('f_scheduler_assignment', $scheduler_assignment->id())
         ->condition('f_assigment_order', $field_assignment_order)
         ->execute();
      }
      catch (\Exception $e) {
        \Drupal::logger('substitutoo_core')->error($e->getMessage());
      }
      $scheduler_assignment->set("field_reset_assignment_on_update", 0);
      $scheduler_assignment->save();
      \Drupal::logger('bulkAssignments')->notice("End reseting related assignments: " . $scheduler_assignment->label());
      return;
    }
    \Drupal::logger('bulkAssignments')->notice("End bulk assigning for scheduler: " . $scheduler_assignment->label());

    $query = $this->entityTypeManager->getStorage('sub_assignment')->getQuery();

    $query->condition('f_assignment_order', $field_assignment_order)
          ->condition('f_shift_scheduler', $shift_scheduler_id)
          ->condition('f_employee', NULL, 'IS NULL')
          ->accessCheck(TRUE);
    $ids = $query->execute();
    foreach($ids as $assignment_id) {
      $assignment = $this->entityTypeManager->getStorage('sub_assignment')->load($assignment_id);
      if($this->isAvailableEmployee($assignment, $employee_id)) {
        $assignment->set('f_employee', $employee_id);
        $assignment->save();
      }
    }
  }

  public function isAvailableEmployee($assignment, $employee_id) {
    // Load date of assignment
    $field_datetime_start = $assignment->get('f_date')->getValue();
    $date = new \DateTime($field_datetime_start[0]['value']);
    $formatted_date = $date->format('Y-m-d');

    $f_location = $assignment->get('f_location')->getValue();
    if(empty($f_location)) {
      return NULL;
    }
    $f_shift_scheduler = $assignment->get('f_shift_scheduler')->getValue();
    if(empty($f_shift_scheduler)) {
      return NULL;
    }
    $f_location_id = $f_location[0]['target_id'];
    $f_shift_scheduler_id = $f_shift_scheduler[0]['target_id'];
    $f_shift_scheduler_entity = $assignment->get('f_shift_scheduler')->entity;

//    $time_start = microtime();
    $matched_criteria_employees = $this->isMatchedEmployee($employee_id, $f_shift_scheduler_entity, $formatted_date);
//    $time_end = microtime();
//    $time = $time_end - $time_start;
//    \Drupal::logger('time')->notice("isMatchedEmployee: " . $time);
    // dump($matched_criteria_employees);
    // exit;
    if(!$matched_criteria_employees) {
      return FALSE;
    }

    $f_timestamp_start = $assignment->get('f_timestamp_start')->value;
    $f_timestamp_end = $assignment->get('f_timestamp_end')->value;

    $connection = Database::getConnection();

    $query = $connection->select('sub_availability', 'sa')
      ->fields('sa', ['f_employee'])
      ->condition('f_employee', $employee_id)
      ->condition('f_timestamp_start', $f_timestamp_start, '<=')
      ->condition('f_timestamp_end', $f_timestamp_end, '>=');
    
    $available_employees = $query->execute()->fetchCol();

    if(empty($available_employees)) {
      return FALSE;
    }

    $query = $connection->select('sub_unavailability', 'su')
      ->fields('su', ['f_employee']);
  
    $or_group = $query->orConditionGroup()
      ->condition(
        $query->andConditionGroup()
          ->condition('f_timestamp_start', $f_timestamp_start, '>')
          ->condition('f_timestamp_start', $f_timestamp_end, '<')
      )
      ->condition(
        $query->andConditionGroup()
          ->condition('f_timestamp_end', $f_timestamp_start, '>')
          ->condition('f_timestamp_end', $f_timestamp_end, '<')
      )
      ->condition(
        $query->andConditionGroup()
          ->condition('f_timestamp_start', $f_timestamp_start, '<=')
          ->condition('f_timestamp_end', $f_timestamp_end, '>=')
      );
  
    $query->condition($or_group);
    $query->condition('f_employee', $employee_id);
    
    $unavailable_employees = $query->execute()->fetchCol();

    if(empty($unavailable_employees)) {
      return TRUE;
    }
    return FALSE;
  }

  public function getEntityOptions($entity_type, $bundle) {
      $query = $this->entityTypeManager->getStorage($entity_type)->getQuery();
      $query->condition('type', $bundle);
      $query->accessCheck(TRUE);
      $entity_ids = $query->execute();

      $options = [];
      if ($entity_ids) {
          $entities = $this->entityTypeManager->getStorage($entity_type)->loadMultiple($entity_ids);
          foreach ($entities as $entity_id => $entity) {
              $options[$entity_id] = $entity->label();
          }
      }

      return $options;
  }

  public function getTaxonomyOptions($vocabulary) {
      $options = [];
      $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vocabulary);

      foreach ($terms as $term) {
          $term_entity = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($term->tid);
          $key_value = '';

          // Check if the field exists and has a value.
          if ($term_entity->hasField('field_key') && !$term_entity->get('field_key')->isEmpty()) {
              $key_value = $term_entity->get('field_key')->value;
          }

          $options[$key_value] = $term->name;
      }

      return $options;
  }

  public function getTaxonomyTermTid($vocabulary, $key) {
      $options = [];

      $query = $this->entityTypeManager->getStorage('taxonomy_term')->getQuery();
      $query->condition('vid', $vocabulary);
      $query->condition('field_key', $key);
      $query->accessCheck(TRUE);
      $tids = $query->execute();
      if (!empty($tids)) {
          return reset($tids);
      }

      return NULL;
  }

  public function getTaxonomyTermKey($vocabulary, $tid) {
      $term = $this->entityTypeManager->getStorage('taxonomy_term')->load($tid);
      if($term) {
        return $term->get('field_key')->value;
      }
      return NULL;
  }

  public function getShiftSchedulerInfo($scheduler) {
    if(empty($scheduler)) {
      return NULL;
    }
    $data = [];

    $data['title'] = $scheduler->label();
    $data['description'] = $scheduler->get('description')->value;

    $data['calendar_type'] = $this->getTaxonomyTermKey('calendar_type', $scheduler->get('field_calendar_type')->target_id);

    $days_of_the_month = array_column($scheduler->get('field_days_of_the_month')->getValue(), 'target_id');
    $days_of_the_week = array_column($scheduler->get('field_days_of_the_week')->getValue(), 'target_id');

    foreach($days_of_the_month as $key => $value) {
      $data['days_of_the_month'][$key] = $this->getTaxonomyTermKey('days_of_the_month', $value);
    }

    foreach($days_of_the_week as $key => $value) {
      $data['days_of_the_week'][$key] = $this->getTaxonomyTermKey('days_of_the_week', $value);
    }

    $data['between_dates'] = $scheduler->get('field_between_dates')->getValue();
    $data['timezone'] = \Drupal::config('system.date')->get('timezone')['default'];

    $data['location'] = $scheduler->get('field_location')->getValue();
    $data['time_frames'] = $scheduler->get('field_time_frames')->getValue();
    return $data;
  }

  public function getAvailabilitySchedulerInfo($scheduler) {
    if(empty($scheduler)) {
      return NULL;
    }
    $data = [];

    $data['title'] = $scheduler->label();
    $data['description'] = $scheduler->get('description')->value;

    $data['calendar_type'] = $this->getTaxonomyTermKey('calendar_type', $scheduler->get('field_calendar_type')->target_id);

    $days_of_the_month = array_column($scheduler->get('field_days_of_the_month')->getValue(), 'target_id');
    $days_of_the_week = array_column($scheduler->get('field_days_of_the_week')->getValue(), 'target_id');

    foreach($days_of_the_month as $key => $value) {
      $data['days_of_the_month'][$key] = $this->getTaxonomyTermKey('days_of_the_month', $value);
    }

    foreach($days_of_the_week as $key => $value) {
      $data['days_of_the_week'][$key] = $this->getTaxonomyTermKey('days_of_the_week', $value);
    }

    $data['between_dates'] = $scheduler->get('field_between_dates')->getValue();
    $data['timezone'] = \Drupal::config('system.date')->get('timezone')['default'];

    $data['employee'] = $scheduler->get('field_employee')->getValue();

    $data['time_frames'] = $scheduler->get('field_time_frames')->getValue();
    return $data;
  }
}