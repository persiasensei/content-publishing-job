<?php

namespace Drupal\Tests\content_publishing_job\Functional;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Functional test to check module routes accessibility.
 *
 * @group content_publishing_job
 */
class ContentPublishingJobConfigPageJobTest extends ContentPublishingJobTestBase {

  use StringTranslationTrait;

  /**
   * Tests the accessibility of the publishing_config entity collection page.
   */
  public function testContentPublishingConfigCollectionPage() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('/admin/config/system/publishing-config');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($this->t('There are no @entity_type entities yet.', ['@entity_type' => 'content publishing configuration']));
  }

  /**
   * Tests the accessibility of the publishing_config entity creation page.
   */
  public function testContentPublishingConfigCreationPage() {
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('/admin/config/system/publishing-config/add');

    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($this->t('Add @entity_type', ['@entity_type' => 'content publishing configuration']));
  }

}
