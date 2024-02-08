<?php

namespace Drupal\content_publishing_job\Manager;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\node\NodeInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Content Manager class.
 */
class ContentManager implements ContentManagerInterface {

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
    $date = $this->getCurrentDateTime();

    $query
      ->accessCheck()
      ->condition('type', $content_type)
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition($date_field, $date, '<');

    return $query->execute();
  }

  /**
   * Get the current date and time formatted as a string.
   *
   * @return string
   *   Return the value of the current date time in string.
   */
  private function getCurrentDateTime(): string {
    $date = new DrupalDateTime();
    $date->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));

    return $date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
  }

}
