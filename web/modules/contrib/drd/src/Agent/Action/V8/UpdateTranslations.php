<?php

namespace Drupal\drd\Agent\Action\V8;

/**
 * Provides a 'UpdateTranslations' code.
 */
class UpdateTranslations extends Base {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $moduleHandler = \Drupal::moduleHandler();
    if (!$moduleHandler->moduleExists('locale')) {
      return [];
    }

    $moduleHandler->loadInclude('locale', 'fetch.inc');
    $moduleHandler->loadInclude('locale', 'bulk.inc');

    $langcodes = array_keys(locale_translatable_language_list());

    // Set the translation import options. This determines if existing
    // translations will be overwritten by imported strings.
    $options = _locale_translation_default_update_options();
    locale_translation_clear_status();
    $batch = locale_translation_batch_update_build([], $langcodes, $options);
    batch_set($batch);
    // Set a batch to update configuration as well.
    if ($batch = locale_config_batch_update_components($options, $langcodes)) {
      batch_set($batch);
    }
    batch_process();

    // Allow other modules as well to jump in with translation update routines.
    $moduleHandler->invokeAll('drd_agent_update_translation');
    return [];
  }

}
