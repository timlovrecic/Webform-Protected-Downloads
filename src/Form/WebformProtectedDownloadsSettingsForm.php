<?php

namespace Drupal\webform_protected_downloads\Form;

use Drupal\Core\File\FileSystem;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\file\Entity\File;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WebformProtectedDownloadsSettingsForm.
 *
 * @package Drupal\webform_protected_downloads\Form
 */
class WebformProtectedDownloadsSettingsForm extends FormBase {

  /**
   * CurrentRouteMatch definition.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * FileSystem definition.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * WebformProtectedDownloadsSettingsForm constructor.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $routeMatch
   *   The route match.
   * @param \Drupal\Core\File\FileSystem $fileSystem
   *   The file system.
   */
  public function __construct(CurrentRouteMatch $routeMatch, FileSystem $fileSystem) {
    $this->routeMatch = $routeMatch;
    $this->fileSystem = $fileSystem;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('file_system')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'webform_protected_downloads_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // Get current webform .
    $webform = $this->routeMatch->getParameter('webform');
    // Get form settings.
    $webform_settings = $webform->getThirdPartySettings('webform_protected_downloads');

    $options = [
      '404' => $this->t('404 page'),
      'homepage' => $this->t('Homepage with error message'),
      'page_reload' => $this->t('Form page with error message'),
      'custom' => $this->t('Custom page'),
    ];

    $form['fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Webform protected files settings'),
    ];
    // Create the form.
    $form['fieldset']['enabled_protected_files'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable serving protected files after webform submit - <b>Must be checked for other options to work</b>'),
      '#default_value' => isset($webform_settings['enabled_protected_files']) ? $webform_settings['enabled_protected_files'] : FALSE,
    ];
    $form['fieldset']['container'] = [
      '#type' => 'container',
      '#states' => [
        'invisible' => [
          'input[name="enabled_protected_files"]' => ['checked' => FALSE],
        ],
      ],
    ];
    $form['fieldset']['container']['expire_after'] = [
      '#type' => 'number',
      '#title' => $this->t('Expire after X minutes'),
      '#default_value' => isset($webform_settings['expire_after']) ? $webform_settings['expire_after'] : '',
    ];
    $form['fieldset']['container']['enabled_onetime'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('One time visit link'),
      '#default_value' => isset($webform_settings['enabled_onetime']) ? $webform_settings['enabled_onetime'] : FALSE,
    ];
    $form['fieldset']['container']['expired_link_page'] = [
      '#type' => 'radios',
      '#title' => $this->t('Link expired page'),
      '#description' => t('Select a page to be routed when link expires.'),
      '#options' => $options,
      '#default_value' => isset($webform_settings['expired_link_page']) ? $webform_settings['expired_link_page'] : FALSE,
    ];
    $form['fieldset']['container']['custom_link_page'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Custom link page'),
      '#states' => array(
        'visible' => array(
          ':input[name="expired_link_page"]' => array('value' => 'custom'),
        ),
      ),
      '#default_value' => isset($webform_settings['custom_link_page']) ? $webform_settings['custom_link_page'] : '',
    );
    $defaultTokenText = 'Download file';
    $form['fieldset']['container']['token_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Token text title.'),
      '#description' => $this->t('This title will be shown when token is replaced, default title is @default', ['@default' => $defaultTokenText]),
      '#default_value' => isset($webform_settings['token_text']) ? $webform_settings['token_text'] : $defaultTokenText,
    ];
    $form['fieldset']['container']['error_message'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Error message'),
      '#description' => $this->t('Error message to display.'),
      '#default_value' => $this->t('File not found'),
    );
    $form['fieldset']['container']['protected_file'] = [
      '#name' => 'protected_file',
      '#type' => 'managed_file',
      '#title' => $this->t('Choose a file for protected download'),
      '#multiple' => FALSE,
      '#theme_wrappers' => [],
      '#upload_validators'  => [
        'file_validate_extensions' => isset($webform_settings['protected_file_extensions']) ? [$webform_settings['protected_file_extensions']] : ['gif png jpg jpeg'],
      ],
      '#error_no_message' => TRUE,
      '#upload_location' => 'private://webform_protected_downloads/',
      '#default_value' => isset($webform_settings['protected_file']) ? $webform_settings['protected_file'] : NULL,
    ];
    $form['fieldset']['container']['protected_file_extensions'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Valid File extensions'),
      '#description' => $this->t("Seperate extensions with ,"),
      '#default_value' => isset($webform_settings['protected_file_extensions']) ? $webform_settings['protected_file_extensions'] : '',
    ];
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    // Print an error if private folder is not set.
    $private_folder = $this->fileSystem->realpath('private://');
    if (!$private_folder) {
      $this->messenger()->addError($this->t('Private files folder is not set! Please setup private folder to use this module correctly.'));
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // @todo Implement some form checks!
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get current webform .
    $webform = $this->routeMatch->getParameter('webform');

    // Save/update settings.
    $values = $form_state->getValues();
    foreach ($values as $key => $value) {
      if ($key == 'submit' || $key == 'op') {
        continue;
      }
      elseif ($key == 'protected_file_extensions') {
        // Remove white spaces and replace , white whitespace.
        trim($value);
        $value = str_replace(',', ' ', $value);
      }
      $webform->setThirdPartySetting("webform_protected_downloads", $key, $value);
    }

    // Set file status to TRUE or file will get deleted after cron.
    if ($values['protected_file']) {
      $fileId = current($values['protected_file']);
      File::load($fileId)->set('status', TRUE)->save();
    }

    if ($values['enabled_protected_files'] == 0) {
      $this->messenger()->addWarning($this->t('Make sure to also remove webform protected downloads token instances after disabling this.'));
    }

    $webform->save();
    $this->messenger()->addStatus($this->t('Settings saved.'));
  }

}
