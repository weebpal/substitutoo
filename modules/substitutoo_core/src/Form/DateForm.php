<?php

namespace Drupal\substitutoo_core\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;
use Drupal\Core\Datetime\DrupalDateTime;

class DateForm extends FormBase {

  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  public function getFormId() {
    return 'date_form';
  }

  public function buildForm(array $form, FormStateInterface $form_state) {
    $root_locations = $this->getRootLocations();
    
    $date_param = \Drupal::request()->query->get('date');
    $month_param = \Drupal::request()->query->get('month');
    $year_param = \Drupal::request()->query->get('year');
    $current_date = "{$year_param}-{$month_param}-{$date_param}"; 

    $form['current_date'] = [
      '#type' => 'markup',
      '#markup' => '<h1>' . $current_date .'</h1>',
    ];

    $form['nested_locations'] = [
      '#type' => 'markup',
      '#markup' => '<div class="form-location">' . $this->buildNestedLocations($root_locations) . '</div',
      '#allowed_tags' => ['select', 'div', 'button', 'option', 'p', 'h4', 'span', 'a'],
    ];
    $form['#attached']['library'][] = 'substitutoo_core/location_toggle'; 
    $form['#attached']['library'][] = 'substitutoo_core/toggle_assignments'; 

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

  private function getRootLocations() {
    $storage = $this->entityTypeManager->getStorage('sub_location');
    $query = $storage->getQuery()
      ->condition('field_is_root_location', TRUE)
      ->accessCheck(FALSE);
    $ids = $query->execute();
    return $storage->loadMultiple($ids);
  }

  private function buildNestedLocations($locations, $level = 1) {
    $output = '';
    
    $date_param = \Drupal::request()->query->get('date');
    $month_param = \Drupal::request()->query->get('month');
    $year_param = \Drupal::request()->query->get('year');
    $current_date = "{$year_param}-{$month_param}-{$date_param}"; 
  
    foreach ($locations as $location) {
      $location_label = $location->label();
      $output .= "<div class='location-level level-$level " . ($level == 1 ? "level-root" : "") . "'>";
  
      // Check nested levels
      
      if ($location->hasField('field_nested_locations') && !$location->get('field_nested_locations')->isEmpty()) {
        $output .= "<button type='button' class='toggle-btn' data-level='$level'>+</button>";
      }
  
      $output .= "<span class='location-label'>$location_label</span>";
  
      // Check if the location is at the last level with assignments
      if ($location->hasField('field_assignment_count')) {
        $assignment_count = $location->get('field_assignment_count')->value; 
  
        // Load sub_shift entities based on the current date
        $shift_storage = $this->entityTypeManager->getStorage('sub_shift');
        $shifts = $shift_storage->loadByProperties([
          'f_date' => $current_date,
          'f_location' => $location->id(),
        ]);
        //dump($shifts, $location->id());
        // If there are shifts and the assignment count is greater than 0
        if (!empty($shifts)) {
          $output .= "<div class='shift-times'>";
          foreach ($shifts as $shift) {
            //dump($shift);
            $time_start = $shift->get('f_time_start')->value;
            $time_end = $shift->get('f_time_end')->value;

            $start_time_format = new DrupalDateTime($time_start);
            $end_time_format = new DrupalDateTime($time_end);
  
            $formatted_start_time = $start_time_format->format('h:i A');
            $formatted_end_time = $end_time_format->format('h:i A');

            $output .= "<div class='shift-assignment-button'><button type='button' class='toggle-btn assignment-toggle' data-level='$level'>+</button>";
            $output .= "<div class='shift-assignment'>$formatted_start_time - $formatted_end_time</div>";
            $output .= "<div class='assignment-counts hidden'>";
            $assignments = \Drupal::entityTypeManager()->getStorage('sub_assignment')->loadByProperties([
              'f_shift' => $shift->id(),
            ]);

            foreach ($assignments as $id => $assignment) {
              // check employee
              $i = $assignment->get('f_assignment_order')->getString();
              if ($assignment->get('f_employee')->entity == NULL) {
                $date_url = Url::fromRoute('substitutoo_core.assign_employee', 
                ['date' => $date_param, 'month' => $month_param, 'year' => $year_param, 'shift' => $shift->id() ,'assignment' => $assignment->id(), 'type' => 'assign'])
                ->toString();

                // Bulk Assign
                $bulk_assign_url = Url::fromRoute('substitutoo_core.bulk_employee', 
                ['date' => $date_param, 'month' => $month_param, 'year' => $year_param, 'shift' => $shift->id() ,'order_assignment' => $i])
                ->toString();

                // popup assign employee
                $output .= "<div class='shift-assignment'><div class='name-employee'>". t('Assignment') ." $i:</div>";
                $output .= "<div class='shift-assignment-button-assign'><a href='$date_url' class='use-ajax' data-dialog-type='modal' data-dialog-options='{\"width\":700, \"title\":\"". t('Assign Employee') ."\"}'>". t('Assign') ."</a><a href='$bulk_assign_url' class='bulk-assign use-ajax' data-dialog-type='modal' data-dialog-options='{\"width\":700, \"title\":\"". t('Bulk Assignment') ."\"}'>". t('Bulk Assign') ."</a></div></div>";
              }
              else {
                $date_url = Url::fromRoute('substitutoo_core.assign_employee', 
                ['date' => $date_param, 'month' => $month_param, 'year' => $year_param, 'shift' => $shift->id() ,'assignment' => $assignment->id(), 'type' => 'change'])
                ->toString();

                $name_employee = $assignment->get('f_employee')->entity->label();
                $url_employee = $assignment->get('f_employee')->entity->toUrl()->toString();
                $output .= "<div class='shift-assignment'><div class='name-employee'>".t('Employee').": <a href='$url_employee'>$name_employee</a></div>";
                $output .= "<div class='shift-assignment-button-change'><a href='$date_url' class='use-ajax' data-dialog-type='modal' data-dialog-options='{\"width\":700, \"title\":\"". t('Change Employee') ."\"}'>". t('Change') ."</a></div></div>";
              }
            }

            $output .= "</div></div>";
          }
          $output .= "</div>";
        }
      }
  
      // If nested levels exist
      if ($location->hasField('field_nested_locations') && !$location->get('field_nested_locations')->isEmpty()) {
        $nested_entities = $location->get('field_nested_locations')->referencedEntities();
  
        $output .= "<div class='nested-level level-$level-hidden' style='display: flex;'>";
        $output .= $this->buildNestedLocations($nested_entities, $level + 1);
        $output .= "</div>";
      }
  
      $output .= "</div>";
    }
  
    return $output;
  }
  
}
