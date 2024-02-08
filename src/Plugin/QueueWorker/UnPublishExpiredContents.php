<?php

namespace Drupal\content_publishing_job\Plugin\QueueWorker;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Queue worker to unpublish expired contents.
 *
 * @QueueWorker(
 *   id = "unpublish_expired_contents",
 *   title = @Translation("Unpublish Expired Contents"),
 *   cron = {"time" = 20}
 * )
 */
class UnPublishExpiredContents extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManagerInterface definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * Drupal\Core\Logger\LoggerChannelFactoryInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerChannelFactory;

  /**
   * Constructs a UnPublishExpiredContents object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   The logger channel factory object.
   */
  public function __construct(array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_channel_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerChannelFactory = $logger_channel_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('logger.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Load the content node by its ID.
    if (!empty($data['nid'])) {
      try {
        $node = $this->entityTypeManager->getStorage('node')->load($data['nid']);

        // Check if the node exists and if its end date has passed.
        if ($node instanceof NodeInterface && $this->isContentDateExpired($node)) {
          // Set the status of the content to unpublished.
          $node->setUnpublished();
          $node->save();

          $this->loggerChannelFactory->get('content_publishing_job')
            ->notice('Queue @queue_id: The content id @nid has been unpublished',
              [
                '@queue_id' => $this->pluginId,
                '@nid' => $data['nid'],
              ]);
        }
      }
      catch (\Exception $e) {
        $this->loggerChannelFactory->get('content_publishing_job')
          ->error('Queue @queue_id: Exception throw for content @nid @error',
            [
              '@queue_id' => $this->pluginId,
              '@nid' => $data['nid'],
              '@error' => $e->getMessage(),
            ]);
      }
    }
  }

  /**
   * Check if the end date of the content has passed.
   *
   * @param \Drupal\node\NodeInterface $content
   *   The content node.
   *
   * @return bool
   *   TRUE if the start date has passed, FALSE otherwise.
   */
  private function isContentDateExpired(NodeInterface $content) :bool {
    // Sample code to compare the date with the current date:
    $end_date = $content->hasField('field_date_range') ? $content->get('field_date_range')->end_value : NULL;
    $current_date = new DrupalDateTime();
    $current_date->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));

    return $end_date && $end_date < $current_date->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
  }

}
