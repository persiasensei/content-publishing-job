<?php

namespace Drupal\content_publishing_job\Manager;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Content Manager class.
 */
class ContentManager implements ContentManagerInterface {

  /**
   * The entity type manager object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */

  private EntityTypeManagerInterface $entityTypeManager;

  /**
   * The date field handler object.
   *
   * @var \Drupal\content_publishing_job\Manager\DateFieldHandlerInterface
   */
  private DateFieldHandlerInterface $dateFieldHandler;

  /**
   * Constructs an event manager object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager object.
   * @param \Drupal\content_publishing_job\Manager\DateFieldHandlerInterface $date_field_handler
   *   The date field handler object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, DateFieldHandlerInterface $date_field_handler) {
    $this->entityTypeManager = $entity_type_manager;
    $this->dateFieldHandler = $date_field_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function getRelativeContents(string $content_type, string $field_relationship_name, ?TermInterface $term, ?array $nids, ?int $limit = NULL): array|int {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $query
      ->accessCheck()
      ->condition('type', $content_type)
      ->condition('status', NodeInterface::PUBLISHED)
      ->sort('created', 'asc');

    if ($term) {
      $query->condition($field_relationship_name . '.target_id', $term->id());
    }
    // To prevent to repeat the current event in the events block.
    if (!empty($nids)) {
      $query->condition('nid', $nids, 'NOT IN');
    }
    $query->range(0, $limit);

    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function getExpiredContents(string $content_type, string $date_field): array|int {
    $query = $this->entityTypeManager->getStorage('node')->getQuery();
    $date = $this->dateFieldHandler->getCurrentDateTime();

    $date_field_type = $this->loadFieldStorageTypeByName($date_field);
    $query_date_field_name = $this->dateFieldHandler->resolveQueryFieldName($date_field, $date_field_type);

    $query
      ->accessCheck()
      ->condition('type', $content_type)
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition($query_date_field_name, $date, '<');

    return $query->execute();
  }

  /**
   * The type of the date field.
   *
   * @param string $field_name
   *   The name of field.
   *
   * @return string|null
   *   Return the type of the date field.
   */
  private function loadFieldStorageTypeByName(string $field_name): ?string {
    /** @var \Drupal\field\FieldStorageConfigInterface $field */
    $field = $this->entityTypeManager
      ->getStorage('field_storage_config')
      ->load('node.' . $field_name);

    return $field?->getType();
  }

}
