<?php

namespace Drupal\content_publishing_job\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Content Publishing Configuration entity.
 *
 * @ConfigEntityType(
 *   id = "publishing_config",
 *   label = @Translation("Content Publishing Configuration"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\content_publishing_job\Controller\PublishingConfigListBuilder",
 *     "form" = {
 *       "add" = "Drupal\content_publishing_job\Form\PublishingConfigForm",
 *       "edit" = "Drupal\content_publishing_job\Form\PublishingConfigForm",
 *       "delete" = "Drupal\content_publishing_job\Form\PublishingConfigDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\content_publishing_job\Controller\PublishingConfigHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "publishing_config",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "content_type" = "content_type",
 *     "date_field" = "date_field",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "content_type",
 *     "date_field",
 *   },
 *   links = {
 *     "canonical" = "/admin/config/system/publishing-config/{publishing_config}",
 *     "add-form" = "/admin/config/system/publishing-config/add",
 *     "edit-form" = "/admin/config/system/publishing-config/{publishing_config}/edit",
 *     "delete-form" = "/admin/config/system/publishing-config/{publishing_config}/delete",
 *     "collection" = "/admin/config/system/publishing-config",
 *   }
 * )
 */
class PublishingConfig extends ConfigEntityBase implements PublishingConfigInterface {

  /**
   * The Entity Config ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Entity Config label.
   *
   * @var string
   */
  protected $label;

  /**
   * The name of the content type.
   *
   * @var string
   */
  protected $content_type;

  /**
   * The name of the date field.
   *
   * @var string
   */
  protected $date_field;

}
