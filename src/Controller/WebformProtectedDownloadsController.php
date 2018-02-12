<?php
/**
 * @file
 * Contains \Drupal\hello\Controller\WebformProtectedDownloadsController.
 */
namespace Drupal\webform_protected_downloads\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\Component\Utility\Unicode;
use Drupal\webform\Entity\Webform;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception;

class WebformProtectedDownloadsController extends ControllerBase {

  /**
   * Protected file download controller.
   * {@inheritdoc}
   */
  public function protectedFileDownload($hash) {

    // Get corresponding protected file entry from given URL hash.
    $db = \Drupal::database();
    $query = $db->select('webform_protected_downloads', 'wpd')
      ->fields('wpd', ['wid', 'hash', 'active', 'expire', 'onetime'])
      ->condition('wpd.hash', $hash)
      ->range(0, 1);
    $results = $query->execute();
    $result = $results->fetch(0);

    // Get webform.
    $webform = Webform::load($result->wid);
    $wpd_settings = $webform->getThirdPartySettings('webform_protected_downloads');

    // Return 404 if no results, inactive, expired, no webform found or file not found.
    $expired = ($result->expire < time() && $result->expire != "0");
    if (!$result || !$result->active || $expired || !$webform || !$wpd_settings['protected_file']) {
      throw new Exception\NotFoundHttpException();
    }

    // Get file response.
    $response = $this->sendProtectedFileResponse(current($wpd_settings['protected_file']));

    // Set onetime entry to inactive before returning.
    if ($result->onetime == 1) {
      $db->update('webform_protected_downloads')
        ->condition('hash', $hash)
        ->fields(['active' => 0])
        ->execute();
    }
    return $response;
  }

  /**
   * Gets the file from fid and creates a download http response.
   */
  private function sendProtectedFileResponse($fid) {

    // Get all the needed parameters.
    $file = File::load($fid);
    if (!$file) {
      throw new Exception\NotFoundHttpException();
    }
    $uri = $file->getFileUri($file);
    $mime = \Drupal::service('file.mime_type.guesser')->guess($uri);

    // Set HTTP header parameters.
    $headers = array(
      'Content-Type' => $mime . '; name="' . Unicode::mimeHeaderEncode(basename($uri)) . '"',
      'Content-Length' => filesize($uri),
      'Content-Disposition' => 'attachment; filename="' . Unicode::mimeHeaderEncode($file->getFilename()) . '"',
      'Cache-Control' => 'private',
    );
    return new BinaryFileResponse($uri, 200, $headers);
  }

}