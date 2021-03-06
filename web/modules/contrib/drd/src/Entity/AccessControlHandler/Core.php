<?php

namespace Drupal\drd\Entity\AccessControlHandler;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Core entity.
 *
 * @see \Drupal\drd\Entity\Core.
 */
class Core extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /* @var \Drupal\drd\Entity\CoreInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'drd.view unpublished core entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'drd.view published core entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'drd.edit core entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'drd.delete core entities');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'drd.add core entities');
  }

}
