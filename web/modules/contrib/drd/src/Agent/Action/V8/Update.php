<?php

namespace Drupal\drd\Agent\Action\V8;

/**
 * Provides a 'Update' code.
 */
class Update extends Base {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    require_once DRUPAL_ROOT . '/core/includes/install.inc';
    require_once DRUPAL_ROOT . '/core/includes/update.inc';
    drupal_load_updates();
    update_fix_compatibility();

    // Pending hook_update_N() implementations.
    $pending = update_get_update_list();

    // Pending hook_post_update_X() implementations.
    $post_updates = \Drupal::service('update.post_update_registry')->getPendingUpdateInformation();

    $result = [];
    $start = [];
    if (count($pending) || count($post_updates)) {
      foreach (['update', 'post_update'] as $update_type) {
        $updates = $update_type == 'update' ? $pending : $post_updates;
        foreach ($updates as $module => $module_updates) {
          if (isset($module_updates['start'])) {
            if (!empty($module_updates['pending'])) {
              $start += [$module => []];
              $start[$module] = array_merge($start[$module], $module_updates['pending']);
            }
          }
        }
      }
      $result = $this->batch($start);
    }
    return $result;
  }

  /**
   * Callback to apply updates to all projects.
   *
   * @param array $context
   *   Arguments for the operation.
   */
  public static function entityUpdates(array &$context) {
    try {
      \Drupal::entityDefinitionUpdateManager()->applyUpdates();
    }
    catch (\Exception $e) {
      watchdog_exception('update', $e);
    }
  }

  /**
   * Callback to determine and execute all required operations.
   *
   * @param array $start
   *   A list of operations.
   *
   * @return array
   *   List of results.
   */
  private function batch(array $start) {
    $result = [];
    $updates = update_resolve_dependencies($start);
    $dependency_map = [];
    foreach ($updates as $function => $update) {
      $dependency_map[$function] = !empty($update['reverse_paths']) ? array_keys($update['reverse_paths']) : [];
    }

    $operations = [];
    foreach ($updates as $update) {
      if ($update['allowed']) {
        // Set the installed version of each module so updates will start at the
        // correct place. (The updates are already sorted, so we can simply base
        // this on the first one we come across in the above foreach loop.)
        if (isset($start[$update['module']])) {
          drupal_set_installed_schema_version($update['module'], $update['number'] - 1);
          unset($start[$update['module']]);
        }
        // Add this update function to the batch.
        $function = $update['module'] . '_update_' . $update['number'];
        $operations[] = [
          'update_do_one',
          [
            $update['module'],
            $update['number'],
            $dependency_map[$function],
          ],
        ];
        drupal_set_message('Updating ' . $update['module'] . ': version ' . $update['number']);
      }
    }

    // Apply post update hooks.
    $post_updates = \Drupal::service('update.post_update_registry')->getPendingUpdateFunctions();
    if ($post_updates) {
      $operations[] = ['drupal_flush_all_caches', []];
      foreach ($post_updates as $function) {
        $operations[] = ['update_invoke_post_update', [$function]];
      }
    }

    // Lastly, perform entity definition updates, which will update storage
    // schema if needed. If module update functions need to work with specific
    // entity schema they should call the entity update service for the specific
    // update themselves.
    // @see \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface::applyEntityUpdate()
    // @see \Drupal\Core\Entity\EntityDefinitionUpdateManagerInterface::applyFieldUpdate()
    $operations[] = ['\Drupal\drd\Agent\Action\V8\Update::entityUpdates', []];

    \Drupal::service('state')->set('system.maintenance_mode', TRUE);
    $context = [
      'sandbox'  => [],
      'results'  => [],
      'finished' => FALSE,
      'message'  => '',
    ];
    foreach ($operations as $operation) {
      $operation[1][] = &$context;
      call_user_func_array($operation[0], $operation[1]);
    }
    if (!empty($context['results']['#abort'])) {
      $result['failed'] = TRUE;
    }
    $this->captureUpdateMessages($context);
    \Drupal::service('state')->set('system.maintenance_mode', FALSE);
    return $result;
  }

  /**
   * Callback to finally capture all messages from all operations.
   *
   * @param array $results
   *   The context of the oprations.
   */
  private function captureUpdateMessages(array $results) {
    foreach ($results as $module => $updates) {
      if ($module != '#abort') {
        foreach ($updates as $number => $queries) {
          foreach ($queries as $query) {
            // If there is no message for this update, don't show anything.
            if (empty($query['query'])) {
              continue;
            }

            if ($query['success']) {
              drupal_set_message($query['query']);
            }
            else {
              drupal_set_message($query['query'], 'error');
            }
          }
        }
      }
    }
  }

}
