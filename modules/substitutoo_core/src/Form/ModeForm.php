<?php

namespace Drupal\substitutoo_core\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

class ModeForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildForm($form, $form_state);
 
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->getEntity();
    $status = $entity->save();

    if ($status === SAVED_NEW) {
      $this->messenger()->addMessage($this->t('The %label has been created.', ['%label' => $entity->label()]));
    }
    else {
      $this->messenger()->addMessage($this->t('The %label has been updated.', ['%label' => $entity->label()]));
    }

    $form_state->setRedirectUrl($entity->toUrl('collection'));
  }
}
