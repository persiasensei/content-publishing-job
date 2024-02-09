<?php

namespace Drupal\content_publishing_job\Form;

use Drupal\content_publishing_job\Manager\DateFieldHandlerInterface;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for the Example add and edit forms.
 */
class PublishingConfigForm extends EntityForm {

  /**
   * The date field handler manager.
   *
   * @var \Drupal\content_publishing_job\Manager\DateFieldHandlerInterface
   */
  protected DateFieldHandlerInterface $dateFieldHandler;

  /**
   * Constructs an PublishingConfigForm object.
   *
   * @param \Drupal\content_publishing_job\Manager\DateFieldHandlerInterface $date_field_handler
   *   The date field handler object.
   */
  public function __construct(DateFieldHandlerInterface $date_field_handler) {
    $this->dateFieldHandler = $date_field_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('content_publishing_job.date_field_handler'),
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

    $content_type = $publishingConfig->get('content_type') ?: $form_state->getValue('content_type');
    $form['content_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Content Type'),
      '#description' => $this->t('Select the name of the content type in The list.'),
      '#required' => TRUE,
      '#default_value' => $content_type,
      '#options' => $content_types,
      '#ajax' => [
        'callback' => [$this, 'dateFieldAjaxCallback'],
        'disable-refocus' => FALSE,
        'event' => 'change',
        'wrapper' => 'date-field-wrapper',
        'progress' => [
          'type' => 'throbber',
        ],
      ],
    ];

    $bundle_field_names = $this->dateFieldHandler->resolveDateFieldNamesByContentType($content_type);
    $form['date_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Date Field Name'),
      '#description' => $this->t('The date field name to evaluate the validity of the content.'),
      '#default_value' => $publishingConfig->get('date_field'),
      '#options' => $bundle_field_names,
      '#prefix' => '<div id="date-field-wrapper">',
      '#suffix' => '</div>',
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
   * Ajax callback to render date field in the form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The output of the field.
   */
  public function dateFieldAjaxCallback(array &$form, FormStateInterface $form_state) {
    return $form['date_field'];
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
