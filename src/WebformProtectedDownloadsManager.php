<?php

namespace Drupal\webform_protected_downloads;

use Drupal\webform_protected_downloads\Entity\WebformProtectedDownloads;

/**
 * Class WebformProtectedDownloadsManager.
 *
 * @package Drupal\webform_protected_downloads
 */
class WebformProtectedDownloadsManager implements WebformProtectedDownloadsManagerInterface {

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

}
