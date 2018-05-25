<?php

namespace Drupal\webform_protected_downloads;

/**
 * Interface WebformProtectedDownloadsManagerInterface.
 *
 * @package Drupal\webform_protected_downloads
 */
interface WebformProtectedDownloadsManagerInterface {

  /**
   * Function will search WebformSubmission by uuid and return WebformProtectedDownloads entity.
   *
   * @param string $uuid
   *   The uuid.
   *
   * @return mixed
   *   Return FALSE|WebformProtectedDownloads
   */
  public function getSubmissionByUuid($uuid);

}
