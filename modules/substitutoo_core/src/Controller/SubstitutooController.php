<?php

namespace Drupal\substitutoo_core\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller methods for handling the form mode.
 */
class SubstitutooController extends ControllerBase {

  /**
   * The entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   *
   * @param \Drupal\Core\Entity\EntityFormBuilderInterface $entity_form_builder
   *   The entity form builder.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityFormBuilderInterface $entity_form_builder, EntityTypeManagerInterface $entity_type_manager) {
    $this->entityFormBuilder = $entity_form_builder;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function managerFormMode($entity_name = NULL, $type = NULL) {
    $user = \Drupal::currentUser();
    if (in_array('manager', $user->getRoles()) || in_array('employee', $user->getRoles())) {
      $entity_name = str_replace("-","_",$entity_name);
      $type = str_replace("-","_",$type);
      $entity = $this->entityTypeManager()->getStorage($entity_name)->create([
        'type' => $entity_name,
      ]);
      if ($entity_name == 'sub_location') {
        $entity = $this->entityTypeManager()->getStorage($entity_name)->create([
          'type' => $entity_name,
          'field_is_root_location' => TRUE,
        ]);
      }
      if ($entity_name == 'sub_unavailability') {
        $leave_id = \Drupal::service('substitutoo_core.substitutoo_service')->getTaxonomyTermTid('unavailable_type', 'Leave');
        $entity = $this->entityTypeManager()->getStorage($entity_name)->create([
          'type' => $entity_name,
          'f_employee' => $user->id(),
          'f_unavailable_type' => $leave_id,
        ]);
      }
      $form = $this->entityFormBuilder()->getForm($entity, $type);

      return $form;     
    }

    return $this->redirect('<front>');
  }

  /**
   * {@inheritdoc}
   */
  public function managerFormModeEdit($entity_name = NULL, $type = NULL, $entity_id = NULL) {
    $user = \Drupal::currentUser();
    if (in_array('manager', $user->getRoles())) {
      $entity_name = str_replace("-","_",$entity_name);
      $type = str_replace("-","_",$type);

      $entity = $this->entityTypeManager()->getStorage($entity_name)->load($entity_id);
      $form = $this->entityTypeManager()
      ->getFormObject($entity_name, 'manager_form')
      ->setEntity($entity);

      if ($entity_name == 'user') {
        $form = $this->entityTypeManager()
        ->getFormObject($entity_name, 'default')
        ->setEntity($entity);
      }

      $form_display = \Drupal::formBuilder()->getForm($form);

      return $form_display;
    }

    return $this->redirect('<front>');
  }
  
  
  /**
   * {@inheritdoc}
   */
  public function titleFormMode($entity_name = NULL, $type = NULL) {
    $title = 'Add ' . str_replace('sub-', ' ', $entity_name);
    return $title;
  }

/**
 * {@inheritdoc}
 */
  public function titleFormModeEdit($entity_name = NULL, $type = NULL, $entity_id = NULL) {
    $entity_name = str_replace("-","_",$entity_name);

    $entity = $this->entityTypeManager()->getStorage($entity_name)->load($entity_id);
    $title = 'Edit ' . $entity->label();
    return $title;
  }
  
  /**
   * {@inheritdoc}
   */
  public function titleDashboard() {

    return t('Dashboard');
  }

    /**
   * {@inheritdoc}
   */
  public function testController() {
    dump(123123213);


    exit;

    return t('Dashboard');
  }

  
  
}


