<?php

namespace Drupal\content_publishing_job\Form;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for the Example add and edit forms.
 */
class PublishingConfigForm extends EntityForm {

  /**
   * An entity type manager object.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  protected $entityFieldManager;

  /**
   * Constructs an PublishingConfigForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity Type Manage object.
   * @param \Drupal\Core\Entity\EntityFieldManager $entity_field_manager
   *   The entity field manager object.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityFieldManagerInterface $entity_field_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity_field.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $publishingConfig = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $publishingConfig->label(),
      '#description' => $this->t('Label for the Example.'),
      '#required' => TRUE,
    ];

    $node_types = $this->entityTypeManager
      ->getStorage('node_type')
      ->loadMultiple();

    $content_types = array_map(function ($node_type) {
      return $node_type->label();
    }, $node_types);

    $form['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content Type'),
      '#description' => $this->t('Select the name of the content type in The list.'),
      '#required' => TRUE,
      '#default_value' => $publishingConfig->get('content_type'),
      '#options' => $content_types,
    ];

    $bundle_fields = $this->entityFieldManager->getFieldDefinitions('node', $publishingConfig->get('content_type'));
    $bundle_field_names = [];
    foreach ($bundle_fields as $field_name => $field_definition) {
      if (!empty($field_definition->getTargetBundle())) {
        $bundle_field_names[$field_name] = $field_definition->getLabel();
      }
    }

    $form['date_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Date Field Name'),
      '#description' => $this->t('The date field name to evaluate the validity of the content.'),
      '#default_value' => $publishingConfig->get('date_field'),
      '#options' => $bundle_field_names,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $publishingConfig->id(),
      '#machine_name' => [
        'exists' => [$this, 'exist'],
      ],
      '#disabled' => !$publishingConfig->isNew(),
    ];

    // You will need additional form elements for your custom properties.
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $publishingConfig = $this->entity;
    $status = $publishingConfig->save();

    if ($status === SAVED_NEW) {
      $this->messenger()->addMessage($this->t('The %label Theme Config created.', [
        '%label' => $publishingConfig->label(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('The %label Theme Config updated.', [
        '%label' => $publishingConfig->label(),
      ]));
    }

    $form_state->setRedirect('entity.publishing_config.collection');
  }

  /**
   * Helper function to check whether a PublishingConfig entity exists.
   *
   * @param string|null $id
   *   The id of the entity.
   *
   * @return bool
   *   Return TRUE if the entity exist, FALSE if not.
   */
  public function exist(?string $id): bool {
    $entity = $this->entityTypeManager->getStorage('publishing_config')->getQuery()
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

}
