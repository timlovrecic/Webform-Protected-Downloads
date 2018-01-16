<?php
/**
 * @file
 * Contains \Drupal\hello\Controller\WebformProtectedDownloadsController.
 */
namespace Drupal\webform_protected_downloads\Controller;

use Drupal\Core\Controller\ControllerBase;

class WebformProtectedDownloadsController extends ControllerBase {

  /**
   * {@inheritdoc}
   */
  public function test() {
    $build = [
      '#markup' => t('Hello World!'),
    ];
    return $build;
  }

}