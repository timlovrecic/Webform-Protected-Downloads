<?php

namespace Drupal\webform_protected_downloads\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\file\Entity\File;
use Drupal\Component\Utility\Unicode;
use Drupal\webform_protected_downloads\WebformProtectedDownloadsManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use \Drupal\Core\Routing\TrustedRedirectResponse;

/**
 * Class WebformProtectedDownloadsController.
 *
 * @package Drupal\webform_protected_downloads\Controller
 */
class WebformProtectedDownloadsController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * WebformProtectedDownloadsManager definition.
   *
   * @var \Drupal\webform_protected_downloads\WebformProtectedDownloadsManager
   */
  protected $webformProtectedDownloadsManager;

  /**
   * WebformProtectedDownloadsController constructor.
   *
   * @param \Drupal\webform_protected_downloads\WebformProtectedDownloadsManager $webform_protected_downloads_manager
   *   WebformProtectedDownloadsManager definition.
   */
  public function __construct(WebformProtectedDownloadsManager $webform_protected_downloads_manager) {
    $this->webformProtectedDownloadsManager = $webform_protected_downloads_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('webform_protected_downloads.manager')
    );
  }

  /**
   * Protected file download controller.
   */
  public function protectedFileDownload($hash) {

    // Get corresponding protected file entry from given URL hash.
    $webformProtectedDownload = $this->webformProtectedDownloadsManager->getSubmissionByHash($hash);
    $webformSubmission = $webformProtectedDownload->getWebformSubmission();
    $webform = $webformSubmission->getWebform();

    // Get webform.
    $wpd_settings = $webform->getThirdPartySettings('webform_protected_downloads');
    // Return error page if no results, inactive, expired, no webform found
    // or file not found.
    $expired = ($webformProtectedDownload->expire->value < time() && $webformProtectedDownload->expire->value != "0");
    if (!$webformProtectedDownload || !$webformProtectedDownload->isActive() || $expired || !$webform || !$wpd_settings['protected_file']) {
      switch ($wpd_settings['expired_link_page']) {

        case "homepage":
          $this->messenger()->addError($wpd_settings['error_message']);
          return $this->redirect('<front>');

        case "page_reload":
          $this->messenger()->addError($wpd_settings['error_message']);
          return new RedirectResponse($webform->toUrl()->setAbsolute()->toString());

        case "custom":
          $this->messenger()->addError($wpd_settings['error_message']);
          return new TrustedRedirectResponse($wpd_settings['custom_link_page']);

        default:
          throw new Exception\NotFoundHttpException();
      }
    }

    // Get file response.
    $response = $this->sendProtectedFileResponse(current($wpd_settings['protected_file']));

    // Set onetime entry to inactive before returning.
    if ($webformProtectedDownload->isOneTimeLink()) {
      $webformProtectedDownload->set('active', FALSE)->save();
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

    $mimeTypeGuesser = \Drupal::service('file.mime_type.guesser');

    // Set HTTP header parameters.
    $headers = array(
      'Content-Type' => $mimeTypeGuesser->guess($uri) . '; name="' . Unicode::mimeHeaderEncode(basename($uri)) . '"',
      'Content-Length' => filesize($uri),
      'Content-Disposition' => 'attachment; filename="' . Unicode::mimeHeaderEncode($file->getFilename()) . '"',
      'Cache-Control' => 'private',
    );
    return new BinaryFileResponse($uri, 200, $headers);
  }

}
