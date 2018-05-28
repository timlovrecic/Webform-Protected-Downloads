<?php

namespace Drupal\webform_protected_downloads;

/**
 * Interface WebformProtectedDownloadsManagerInterface.
 *
 * @package Drupal\webform_protected_downloads
 */
interface WebformProtectedDownloadsManagerInterface {

  /**
   * Function will search submission by uuid.
   *
   * @param string $uuid
   *   Uuid.
   *
   * @return mixed
   *   Return value or FALSE.
   */
  public function getSubmissionByUuid($uuid);

  /**
   * Get WebformProtectedDownloads entity by hash.
   *
   * @param string $hash
   *   The hash.
   *
   * @return mixed
   *   Return entity or false.
   */
  public function getSubmissionByHash($hash);

}
