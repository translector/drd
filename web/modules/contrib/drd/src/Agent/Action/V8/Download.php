<?php

namespace Drupal\drd\Agent\Action\V8;

use Drupal\drd_agent\Controller\Agent;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Offers a download file.
 */
class Download extends Base {

  /**
   * {@inheritdoc}
   */
  public function execute() {
    $args = $this->getArguments();
    $filename = self::$crypt->encryptFile($args['source']);
    $header = Agent::responseHeader();
    $header['X-DRD-Encrypted'] = ($filename != $args['source']);
    return new BinaryFileResponse($filename, 200, $header, FALSE);
  }

}
