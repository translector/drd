<?php

namespace Drupal\drd\Plugin\Action;

use Drupal\Core\Action\ActionInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Interface for DRD actions.
 */
interface BaseInterface extends ActionInterface {

  /**
   * Determine if access to this action should be controlled by permissions.
   *
   * @return bool
   *   TRUE if access should be controlled by permissions or FALSE if access
   *   is not restricted, i.e. available to all roles that have access to
   *   DRD actions in general.
   */
  public function restrictAccess();

  /**
   * Set an action argument.
   *
   * @param string $key
   *   The argument key.
   * @param mixed $value
   *   The argument value.
   */
  public function setActionArgument($key, $value);

  /**
   * Set action arguments.
   *
   * @param array $arguments
   *   An associated array of action arguments.
   */
  public function setArguments(array $arguments);

  /**
   * Get actions arguments.
   *
   * @return array
   *   The action arguments.
   */
  public function getArguments();

  /**
   * Get the action output.
   *
   * @return bool|string[]
   *   The action output or FALSE if no output available.
   */
  public function getOutput();

  /**
   * Determine if the current action has the given term associated with it.
   *
   * @param \Drupal\taxonomy\Entity\Term $term
   *   The term to check.
   *
   * @return bool
   *   True if current action has the given term associtated with it.
   */
  public function hasTerm(Term $term);

}
