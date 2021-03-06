<?php

namespace Drupal\drd\Command;

use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\drd\Plugin\Action\BaseEntityRemote;
use Symfony\Component\Console\Input\InputInterface;

/**
 * Class BaseDomain.
 *
 * @package Drupal\drd
 */
abstract class BaseDomain extends BaseEntity {

  /**
   * {@inheritdoc}
   */
  protected function getEntities(InputInterface $input, DrupalStyle $io, BaseEntityRemote $action) {
    if (!($action instanceof BaseEntityRemote)) {
      return NULL;
    }

    return $this->getService($input)
      ->domains();
  }

}
