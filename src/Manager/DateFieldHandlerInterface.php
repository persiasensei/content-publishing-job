<?php

namespace Drupal\content_publishing_job\Manager;

use Drupal\node\NodeInterface;

/**
 * Date Field Handler interface.
 */
interface DateFieldHandlerInterface {

  const DATETIME = 'datetime';
  const DATERANGE = 'daterange';
  const TIMESTAMP = 'timestamp';
  const DATE_TYPES_LIST = [self::DATETIME, self::DATERANGE, self::TIMESTAMP];

  /**
   * The name of the field to use in the query.
   *
   * @param string $field_name
   *   The name of the date field.
   * @param string|null $date_field_type
   *   The type of the date field.
   *
   * @return string
   *   Return the name of the field to use in the query.
   */
  public function resolveQueryFieldName(string $field_name, ?string $date_field_type): string;

  /**
   * Get list of the date field name for a content type.
   *
   * @param string|null $content_type
   *   The name of the content type.
   *
   * @return array
   *   Return the list of the date field name for a content type.
   */
  public function resolveDateFieldNamesByContentType(?string $content_type): array;

  /**
   * The name of the field to use in the query.
   *
   * @param \Drupal\node\NodeInterface $content
   *   The node object.
   * @param string $field_name
   *   The name of the field.
   *
   * @return string|null
   *   Return the name of the field to use in the query.
   */
  public function getDateValue(NodeInterface $content, string $field_name): ?string;

  /**
   * Get the current date and time formatted as a string.
   *
   * @return string
   *   Return the value of the current date time in string.
   */
  public function getCurrentDateTime(): string;

}
