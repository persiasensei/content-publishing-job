<?php

namespace Drupal\content_publishing_job\Manager;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\node\NodeInterface;

/**
 * Date Field Handler class.
 */
class DateFieldHandler implements DateFieldHandlerInterface
{
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
