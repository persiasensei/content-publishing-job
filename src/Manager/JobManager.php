<?php

namespace Drupal\content_publishing_job\Manager;

use Drupal\content_publishing_job\Entity\PublishingConfigInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Job Manager class.
 */
class JobManager
{
  /**
   * An entity type manager object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private EntityTypeManagerInterface $entityTypeManager;

  /**
   * Constructs an event manager object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * @return PublishingConfigInterface|array
   */
  public function loadPublishingConfigJobs(): array {
    return $this->entityTypeManager->getStorage('publishing_config')->loadMultiple();
  }

    /**
     * @param string $content_type
     *
     * @return PublishingConfigInterface|null
     */
  public function loadPublishingConfigJobByContentType(string $content_type): ?PublishingConfigInterface {
    $entities = $this->entityTypeManager->getStorage('publishing_config')
      ->loadByProperties(['content_type' => $content_type]);
    return reset($entities);
  }

}
