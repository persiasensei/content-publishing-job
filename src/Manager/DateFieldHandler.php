<?php

namespace Drupal\content_publishing_job\Manager;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\node\NodeInterface;

/**
 * Date Field Handler class.
 */
class DateFieldHandler implements DateFieldHandlerInterface {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  protected $entityFieldManager;

  /**
   * Constructs an PublishingConfigForm object.
   *
   * @param \Drupal\Core\Entity\EntityFieldManager $entity_field_manager
   *   The entity field manager object.
   */
  public function __construct(EntityFieldManagerInterface $entity_field_manager) {
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveQueryFieldName(string $field_name, ?string $date_field_type): string {
    switch ($date_field_type) {
      case DateFieldHandlerInterface::DATERANGE:
        return $field_name . '.end_value';

      case DateFieldHandlerInterface::TIMESTAMP:
      case DateFieldHandlerInterface::DATETIME:
      default:
        return $field_name;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function resolveDateFieldNamesByContentType(?string $content_type): array {
    $bundle_fields = $this->entityFieldManager->getFieldDefinitions('node', $content_type);
    $bundle_field_names = [];
    foreach ($bundle_fields as $field_name => $field_definition) {
      if (in_array($field_definition->getType(), self::DATE_TYPES_LIST)) {
        $bundle_field_names[$field_name] = $field_definition->getLabel();
      }
    }

    return $bundle_field_names;
  }

  /**
   * {@inheritdoc}
   */
  public function getDateValue(NodeInterface $content, string $field_name): ?string {
    if ($content->hasField($field_name) && $field_definition = $content->get($field_name)->getFieldDefinition()) {
      switch ($field_definition->getType()) {
        case DateFieldHandlerInterface::DATERANGE:
          return $content->get($field_name)->end_value;

        case DateFieldHandlerInterface::TIMESTAMP:
        case DateFieldHandlerInterface::DATETIME:
        default:
          return $content->get($field_name)->value;
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentDateTime(): string {
    $date = new DrupalDateTime();
    $date->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));

    return $date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
  }

}
