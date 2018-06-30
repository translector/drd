<?php

namespace Drupal\drd\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class Actions.
 *
 * @package Drupal\drd\Form
 */
class Actions extends FormBase {

  /**
   * DRD action service object.
   *
   * @var \Drupal\drd\RemoteActionsInterface
   */
  protected $actionService;

  /**
   * {@inheritdoc}
   */
  public function __construct() {
    $this->actionService = \Drupal::service('drd.remote.actions');
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'drd_action_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $this->actionService->buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $this->actionService->validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->actionService->submitForm($form, $form_state);
    if ($this->actionService->getExecutedCount()) {
      drupal_set_message(t('%action was queued.', [
        '%action' => $this->actionService->getSelectedAction()->label(),
      ]));
    }
  }

}
