<?php

namespace Drupal\content_publishing_job\Manager;

use Drupal\node\NodeInterface;

interface DateFieldHandlerInterface
{
  const DATETIME = 'datetime';
  const DATERANGE = 'daterange';
  const TIMESTAMP = 'timestamp';

  /**
   * The name of the field to use in the query.
   *
   * @param string $field_name
   * @param string|null $date_field_type
   *
   * @return string
   *   Return the name of the field to use in the query.
   */
  public function resolveQueryFieldName(string $field_name, ?string $date_field_type): string;

  /**
   * The name of the field to use in the query.
   *
   * @param NodeInterface $content
   * @param string $field_name
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
  public function getCurrentDateTime(): string ;
}
