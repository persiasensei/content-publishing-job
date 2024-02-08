<?php

namespace Drupal\content_publishing_job\Manager;

use Drupal\taxonomy\TermInterface;

/**
 * Event Manager interface.
 */
interface ContentManagerInterface {

  /**
   * To get expired events ids.
   *
   * @param string $content_type
   *    The name of the content type.
   * @param string $date_field
   *     The name of the date field.
   *
   * @return array|int
   *   Return an array of ids.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getExpiredContents(string $content_type, string $date_field): array|int;

  /**
   * To get relative events based on term.
   *
   * @param string $content_type
   *   The name of the content type.
   * @param string $field_relationship_name
   *   The name of the relationship field.
   * @param \Drupal\taxonomy\TermInterface|null $term
   *   The term object to filter event results.
   * @param array|null $nids
   *   An array of ids to exclude from results.
   * @param int|null $limit
   *   Set the maximum of results to return.
   *
   * @return int|array
   *   Return an array of ids.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getRelativeContents(string $content_type, string $field_relationship_name, ?TermInterface $term, ?array $nids, ?int $limit = NULL): array|int;

}
