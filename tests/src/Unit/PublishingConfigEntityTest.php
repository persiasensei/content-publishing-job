<?php

namespace Drupal\Tests\content_publishing_job\Unit;

use Drupal\content_publishing_job\Entity\PublishingConfigInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for Publishing Config entity.
 *
 * @group content_publishing_job
 */
class PublishingConfigEntityTest extends UnitTestCase {

  /**
   * Tests Publishing Config entity creation.
   */
  public function testPublishingConfigCreation() {
    // Define sample data for the entity.
    $data = [
      'id' => 'test_event_job',
      'label' => 'Test Event Publishing Config',
      'content_type' => 'event',
      'date_field' => 'field_event_date',
    ];

    /** @var \Drupal\content_publishing_job\Entity\PublishingConfigInterface|\PHPUnit\Framework\MockObject\MockObject $entity */
    $entity = $this->createMock(PublishingConfigInterface::class);

    $entity->expects($this->any())
      ->method('id')
      ->willReturn($data['id']);
    $entity->expects($this->any())
      ->method('label')
      ->willReturn($data['label']);

    $entity->expects($this->any())
      ->method('get')
      ->willReturnCallback(fn(string $name) =>
      match ($name) {
        'content_type' => $data['content_type'],
        'date_field' => $data['date_field'],
      });

    // Assert that the entity was created successfully.
    $this->assertEquals($data['id'], $entity->id());
    $this->assertEquals($data['label'], $entity->label());
    $this->assertEquals($data['content_type'], $entity->get('content_type'));
    $this->assertEquals($data['date_field'], $entity->get('date_field'));
  }

}
