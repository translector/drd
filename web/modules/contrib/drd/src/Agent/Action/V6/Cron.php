<?php

namespace Drupal\drd\Agent\Action\V6;

/**
 * Provides a 'Cron' code.
 */
class Cron extends Base {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    drupal_cron_run();
    return array();
  }

}
