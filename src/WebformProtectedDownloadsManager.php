<?php

namespace Drupal\webform_protected_downloads;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\webform_protected_downloads\Entity\WebformProtectedDownloads;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WebformProtectedDownloadsManager.
 *
 * @package Drupal\webform_protected_downloads
 */
class WebformProtectedDownloadsManager implements WebformProtectedDownloadsManagerInterface, ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a GroupContentCardinalityValidator object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getSubmissionByUuid($uuid) {
    // Get all WebformProtectedDownloads.
    $webformProtectedDownloadEntities = WebformProtectedDownloads::loadMultiple();
    foreach ($webformProtectedDownloadEntities as $entity) {
      // Get WebformSubmission.
      $webformSubmission = $entity->getWebformSubmission();
      // Compare uuid's.
      if ($webformSubmission->uuid() == $uuid) {
        // Return WebformProtectedDownload.
        return $entity;
      }
      continue;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSubmissionByHash($hash) {
    $result = $this->entityTypeManager->getStorage('webform_protected_downloads')
      ->getQuery()
      ->condition('hash', $hash)
      ->execute();

    // Return entity if exists or FALSE.
    if ($result) {
      return WebformProtectedDownloads::load(reset($result));
    }
    else {
      return FALSE;
    }
  }

}
