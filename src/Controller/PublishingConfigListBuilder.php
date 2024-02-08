<?php

namespace Drupal\content_publishing_job\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of PublishingConfig.
 */
class PublishingConfigListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Label');
    $header['id'] = $this->t('Id');
    $header['content_type'] = $this->t('Name of the content type');
    $header['date_field'] = $this->t('Date Field Name');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['content_type'] = $entity->get('content_type');
    $row['date_field'] = $entity->get('date_field');

    return $row + parent::buildRow($entity);
  }

}
