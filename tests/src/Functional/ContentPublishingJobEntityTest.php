<?php

namespace Drupal\Tests\content_publishing_job\Functional;

use Drupal\content_publishing_job\Entity\PublishingConfig;
use Drupal\content_publishing_job\Entity\PublishingConfigInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Functional test to check entity routes accessibility.
 *
 * @group content_publishing_job
 */
class ContentPublishingJobEntityTest extends ContentPublishingJobTestBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'content_publishing_job',
    'content_publishing_job_test',
  ];

  /**
   * The entity object.
   *
   * @var \Drupal\content_publishing_job\Entity\PublishingConfigInterface
   */
  protected $publishingConfig;

  /**
   * {@inheritDoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->publishingConfig = $this->createPublishingConfigEntity('event');
  }

  /**
   * Tests the accessibility of the publishing_config entity canonical page.
   */
  public function testContentPublishingConfigCanonicalPage() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('/admin/config/system/publishing-config/' . $this->publishingConfig->id());

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($this->publishingConfig->label());
  }

  /**
   * Tests the accessibility of the publishing_config entity edition page.
   */
  public function testContentPublishingConfigEditionPage() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('/admin/config/system/publishing-config/' . $this->publishingConfig->id() . '/edit');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($this->t('Edit @label', ['@label' => $this->publishingConfig->label()]));
  }

  /**
   * Test the accessibility of the publishing_config entity deletion page.
   */
  public function testContentPublishingConfigDeletionPage() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('/admin/config/system/publishing-config/' . $this->publishingConfig->id() . '/delete');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($this->t('Are you sure you want to delete @label', ['@label' => $this->publishingConfig->label()]));
  }

  /**
   * Tests the unavailabity of a deleted publishing_config entity pages.
   */
  public function testContentPublishingConfigRemoveEntity() {
    $this->drupalLogin($this->adminUser);
    $this->publishingConfig->delete();

    $this->drupalGet('/admin/config/system/publishing-config/' . $this->publishingConfig->id());
    $this->assertSession()->statusCodeEquals(404);

    $this->drupalGet('/admin/config/system/publishing-config/' . $this->publishingConfig->id() . '/edit');
    $this->assertSession()->statusCodeEquals(404);

    $this->drupalGet('/admin/config/system/publishing-config/' . $this->publishingConfig->id() . '/delete');
    $this->assertSession()->statusCodeEquals(404);
  }

  /**
   * Creation of a Publishing Config entity.
   *
   * @param string $content_type
   *   The content type.
   *
   * @return \Drupal\content_publishing_job\Entity\PublishingConfigInterface
   *   Returns the entity created.
   */
  protected function createPublishingConfigEntity(string $content_type): PublishingConfigInterface {
    $entity = PublishingConfig::create([
      'id' => 'test_' . $content_type . '_job',
      'label' => 'Test ' . $content_type,
      'content_type' => $content_type,
      'date_field' => 'field_' . $content_type . '_date',
    ]);

    $entity->save();
    return $entity;
  }

}
