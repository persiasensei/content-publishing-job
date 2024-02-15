<?php

namespace Drupal\Tests\content_publishing_job\Functional;

use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\BrowserTestBase;

/**
 * Default test functionalities of the module.
 */
abstract class ContentPublishingJobTestBase extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'block',
    'node',
    'content_publishing_job',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * The administrator user.
   *
   * @var \Drupal\user\UserInterface
   */

  protected $adminUser;

  /**
   * The list of content types.
   *
   * @var string[]
   */
  protected $nodeTypes = ['event', 'news'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'administer content types',
      'access content',
      'administer site configuration',
    ]);

    foreach ($this->nodeTypes as $nodeType) {
      $this->createNodeType($nodeType);
    }
  }

  /**
   * Create a node type based on a name.
   *
   * @param string $name
   *   The name of the content type.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Return the node type created.
   */
  protected function createNodeType(string $name): EntityInterface {
    $bundle = NodeType::create([
      'type' => strtolower($name),
      'name' => $name,
    ]);

    $bundle->save();
    return $bundle;
  }

}
