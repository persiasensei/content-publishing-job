<?php

namespace Drupal\content_publishing_job\Plugin\Block;

use Drupal\content_publishing_job\Manager\ContentManagerInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityViewBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block that displays related contents.
 *
 * @Block(
 *   id = "related_contents_block",
 *   admin_label = @Translation("Related contents block"),
 *   category = @Translation("Content"),
 * )
 */
class RelatedContentsBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * An entity type manager object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected EntityFieldManagerInterface $entityFieldManager;

  /**
   * The current node.
   *
   * @var \Drupal\node\NodeInterface|null
   */
  protected ?NodeInterface $node;

  /**
   * The entity view builder interface.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  protected EntityViewBuilderInterface $viewBuilder;

  /**
   * Drupal\Core\Logger\LoggerChannelFactoryInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected LoggerChannelFactoryInterface $loggerChannelFactory;

  /**
   * The content manager interface.
   *
   * @var \Drupal\content_publishing_job\Manager\ContentManagerInterface
   */
  protected ContentManagerInterface $contentManager;

  /**
   * Constructs a RelatedContentsBlock object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager object.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   *   The entity field manager object.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_channel_factory
   *   The logger channel factory object.
   * @param \Drupal\content_publishing_job\Manager\ContentManagerInterface $content_manager
   *   The content manager object.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager, LoggerChannelFactoryInterface $logger_channel_factory, ContentManagerInterface $content_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
    $this->contentManager = $content_manager;
    $this->loggerChannelFactory = $logger_channel_factory;

    $this->viewBuilder = $this->entityTypeManager->getViewBuilder('node');
    $this->node = $route_match->getParameter('node');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
      $container->get('logger.factory'),
      $container->get('content_publishing_job.content_manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    if (!$this->node instanceof NodeInterface || $this->node->bundle() !== $this->configuration['content_type']) {
      return $build;
    }

    try {
      $nids = $this->getRelatedContents();
      $contents = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

      return $this->viewBuilder->viewMultiple($contents, 'teaser');
    }
    catch (\Exception $e) {
      $this->loggerChannelFactory->get('content_publishing_job')
        ->error('An exception occurred: @error', [
          '@error' => $e->getMessage(),
        ]);
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    // Every new url this block will rebuild.
    return Cache::mergeContexts(parent::getCacheContexts(), ['url']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // With this when the current node change your block will rebuild.
    $default_cache_tags = Cache::mergeTags(parent::getCacheTags(), ['node_list:' . $this->configuration['content_type']]);
    if (!empty($this->node)) {
      // If there is node add its cache tag.
      return Cache::mergeTags($default_cache_tags, $this->node->getCacheTags());
    }

    // Return default tags instead.
    return $default_cache_tags;
  }

  /**
   * {@inheritdoc}
   */
  public function blockAccess(AccountInterface $account, $return_as_object = FALSE) {
    if (empty($this->node)) {
      return AccessResult::allowed();
    }
    return $this->node->access('view', NULL, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration(): array {
    return [
      'content_type' => '',
      'relationship_field' => '',
      'max_number' => 3,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $node_types = $this->entityTypeManager
      ->getStorage('node_type')
      ->loadMultiple();

    $content_types = array_map(function ($node_type) {
      return $node_type->label();
    }, $node_types);

    $complete_form_state = $form_state instanceof SubformStateInterface ? $form_state->getCompleteFormState() : $form_state;
    $content_type = $this->configuration['content_type'] ?: $complete_form_state->getValue('content_type');

    $form['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content Types'),
      '#description' => $this->t('Select the name of the content type in the list.'),
      '#required' => TRUE,
      '#default_value' => $content_type,
      '#options' => $content_types,
      '#ajax' => [
        'callback' => [$this, 'relationshipFieldAjaxCallback'],
        'disable-refocus' => FALSE,
        'event' => 'change',
        'wrapper' => 'relationship-field-wrapper',
        'progress' => [
          'type' => 'throbber',
        ],
      ],
    ];
    $bundle_field_names = $this->getBundleFieldNamesList($content_type);

    $form['relationship_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Relationship Field Name'),
      '#description' => $this->t('The relationship field name to bind related contents.'),
      '#default_value' => $this->configuration['relationship_field'],
      '#options' => $bundle_field_names,
      '#validated' => TRUE,
      '#prefix' => '<div id="relationship-field-wrapper">',
      '#suffix' => '</div>',
    ];

    $form['max_number'] = [
      '#type' => 'textfield',
      '#attributes' => [
        ' type' => 'number',
      ],
      '#title' => $this->t('Max number'),
      '#description' => $this->t('Max number of contents to display'),
      '#required' => TRUE,
      '#maxlength' => 3,
      '#default_value' => $this->configuration['max_number'],
    ];

    return $form;
  }

  /**
   * Ajax callback to render relationship fields list in the form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The output of the field.
   */
  public function relationshipFieldAjaxCallback(array &$form, FormStateInterface $form_state) {
    $bundle_field_names = $this->getBundleFieldNamesList($form_state->getValue('settings')['content_type']);
    $form['settings']['relationship_field']['#options'] = $bundle_field_names;

    return $form['settings']['relationship_field'];
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configuration['content_type'] = $values['content_type'];
    $this->configuration['relationship_field'] = $values['relationship_field'];
    $this->configuration['max_number'] = $values['max_number'];
  }

  /**
   * Get the list of entity reference fields for content type.
   *
   * @param string|null $content_type
   *   The content type of the node.
   *
   * @return array
   *   Return a list of field names.
   */
  private function getBundleFieldNamesList(?string $content_type) :array {
    $bundle_fields = $this->entityFieldManager->getFieldDefinitions('node', $content_type);
    $bundle_field_names = [];
    foreach ($bundle_fields as $field_name => $field_definition) {
      // Only accepts entity reference fields.
      if (!empty($field_definition->getTargetBundle()) && $field_definition->getType() === 'entity_reference') {
        $bundle_field_names[$field_name] = $field_definition->getLabel();
      }
    }

    return $bundle_field_names;
  }

  /**
   * Load the related contents based on current content.
   *
   * @return array
   *   Returns an array of ids.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getRelatedContents() :array {
    $nids = [];
    // To not repeat the current content in the block results.
    $excluded_nids = [$this->node->id()];

    $field_relationship_name = $this->configuration['relationship_field'];
    if ($this->node->hasField($field_relationship_name) && !$this->node->get($field_relationship_name)->isEmpty()) {
      /** @var \Drupal\taxonomy\TermInterface[] $term */
      $term = $this->node->get($field_relationship_name)->referencedEntities();
      if (!empty($term = reset($term))) {
        $nids = $this->contentManager->getRelativeContents($this->configuration['content_type'], $field_relationship_name, $term, $excluded_nids, $this->configuration['max_number']);
      }
    }

    // Load the remaining contents if the max number of contents is not reached.
    $remaining_nids_count = $this->configuration['max_number'] - count($nids);
    if ($remaining_nids_count > 0) {
      // To not repeat the contents already loaded in the block results.
      $excluded_nids = array_merge($excluded_nids, array_values($nids));
      $remaining_nids = $this->contentManager->getRelativeContents($this->configuration['content_type'], $field_relationship_name, NULL, $excluded_nids, $remaining_nids_count);
      $nids += $remaining_nids;
    }

    return $nids;
  }

}
