<?php

namespace Drupal\Tests\fraction\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\fraction\Plugin\Field\FieldType\FractionItem;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\node\Entity\NodeType;

/**
 * Class to test generating and setting sample values.
 *
 * @group Fraction
 */
class FractionGenerateSampleValueTest extends EntityKernelTestBase {

  /**
   * Field name to use.
   */
  const FIELD_NAME = 'field_fraction_test';

  /**
   * {@inheritDoc}
   */
  public static $modules = ['node', 'fraction'];

  /**
   * NodeStorage interface.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * Entity interface.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * {@inheritDoc}
   */
  public function setUp() {
    parent::setUp();

    NodeType::create([
      'type' => 'article',
      'label' => 'Article',
    ])->save();

    FieldStorageConfig::create([
      'entity_type' => 'node',
      'field_name' => self::FIELD_NAME,
      'type' => 'fraction',
    ])->save();

    FieldConfig::create([
      'entity_type' => 'node',
      'field_name' => self::FIELD_NAME,
      'bundle' => 'article',
    ])->save();
    $this->nodeStorage = $this->entityTypeManager->getStorage('node');
    $this->entity = $this->nodeStorage->create([
      'type' => 'article',
      'title' => 'test',
    ]);
    $this->entity->save();

    $fieldDefinition = $this->entity
      ->get(self::FIELD_NAME)->getFieldDefinition();
    $value = FractionItem::generateSampleValue($fieldDefinition);
    $this->entity->get(self::FIELD_NAME)->value = $value;

  }

  /**
   * Tests setting the sample value.
   */
  public function testGenerateSampleValue() {

    $result = $this->entity->get(self::FIELD_NAME)->value;

    $this->assertTrue(is_array($result));

    $this->assertArrayHasKey('numerator', $result);

    $this->assertArrayHasKey('denominator', $result);

    $this->assertTrue($result['denominator'] > 0, TRUE);

  }

}
