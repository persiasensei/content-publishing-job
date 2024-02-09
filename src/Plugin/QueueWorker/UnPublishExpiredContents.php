<?php

namespace Drupal\content_publishing_job\Plugin\QueueWorker;

use Drupal\content_publishing_job\Manager\DateFieldHandlerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
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
   * The entity type manager object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The logger channel factory object.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected LoggerChannelFactoryInterface $loggerChannelFactory;

  /**
   * The date field handler object.
   *
   * @var \Drupal\content_publishing_job\Manager\DateFieldHandlerInterface
   */
  protected DateFieldHandlerInterface $dateFieldHandler;

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
   * @param \Drupal\content_publishing_job\Manager\DateFieldHandlerInterface $date_field_handler
   *   The date field handler object.
   */
  public function __construct(array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    LoggerChannelFactoryInterface $logger_channel_factory,
    DateFieldHandlerInterface $date_field_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->loggerChannelFactory = $logger_channel_factory;
    $this->dateFieldHandler = $date_field_handler;
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
      $container->get('logger.factory'),
      $container->get('content_publishing_job.date_field_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Load the content node by its ID.
    if (!empty($data['nid']) && !empty($data['date_field'])) {
      try {
        $node = $this->entityTypeManager->getStorage('node')->load($data['nid']);

        // Check if the node exists and if its end date has passed.
        if ($node instanceof NodeInterface && $this->isContentDateExpired($node, $data['date_field'])) {
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
   * @param string $field_name
   *   The name of the date field.
   *
   * @return bool
   *   TRUE if the start date has passed, FALSE otherwise.
   */
  private function isContentDateExpired(NodeInterface $content, string $field_name) :bool {
    $content_date = $this->dateFieldHandler->getDateValue($content, $field_name);
    return $content_date && $content_date < $this->dateFieldHandler->getCurrentDateTime();
  }

}
