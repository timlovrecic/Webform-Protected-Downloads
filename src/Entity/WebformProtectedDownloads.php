<?php

namespace Drupal\webform_protected_downloads\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Defines the Webform protected downloads entity.
 *
 * @ingroup webform_protected_downloads
 *
 * @ContentEntityType(
 *   id = "webform_protected_downloads",
 *   label = @Translation("Webform protected downloads"),
 *   base_table = "webform_protected_downloads",
 *   entity_keys = {
 *     "id" = "id",
 *   },
 * )
 */
class WebformProtectedDownloads extends ContentEntityBase {

  /**
   * Return WebformSubmission.
   *
   * @return \Drupal\webform\Entity\WebformSubmission
   *   Return WebformSubmission.
   */
  public function getWebformSubmission() {
    return $this->get('webform_submission')->first()->get('entity')->getTarget()->getValue();
  }

  /**
   * Get hash.
   *
   * @return null|string
   *   Return hash if exists.
   */
  public function getHash() {
    return $this->hash->value;
  }

  /**
   * Check if link is active.
   *
   * @return bool
   *   Return bool.
   */
  public function isActive() {
    return (bool) $this->active->value;
  }

  /**
   * Check if link is only one time usable.
   *
   * @return bool
   *    Return bool.
   */
  public function isOneTimeLink() {
    return (bool) $this->onetime->value;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['webform_submission'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Webform Submission'))
      ->setSetting('target_type', 'webform_submission')
      ->setRequired(TRUE);

    $fields['file'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('File'))
      ->setSetting('target_type', 'file')
      ->setRequired(TRUE);

    $fields['hash'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Hash'))
      ->setRequired(TRUE);

    $fields['active'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Active'))
      ->setDefaultValue(FALSE);

    $fields['expire'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Expire'))
      ->setRequired(TRUE);

    $fields['onetime'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Onetime'))
      ->setDefaultValue(FALSE)
      ->setRequired(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
  }

}
