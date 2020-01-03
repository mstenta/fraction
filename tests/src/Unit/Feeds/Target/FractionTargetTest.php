<?php

namespace Drupal\Tests\fraction\Unit\Feeds\Target;

use Drupal\Core\Form\FormState;
use Drupal\feeds\Feeds\Target\Text;
use Drupal\fraction\Feeds\Target\FractionTarget;
use Drupal\Tests\feeds\Unit\Feeds\Target\FieldTargetTestBase;

/**
 * @coversDefaultClass \Drupal\fraction\Feeds\Target\FractionTarget
 * @group feeds
 */
class FractionTargetTest extends FieldTargetTestBase {

  /**
   * {@inheritdoc}
   */
  protected function getTargetClass() {
    return FractionTarget::class;
  }

  /**
   * @covers ::prepareValue
   */
  public function testPrepareValueFraction() {
    $method = $this->getMethod('Drupal\fraction\Feeds\Target\FractionTarget', 'prepareTarget')->getClosure();

    $configuration = [
      'feed_type' => $this->getMock('Drupal\feeds\FeedTypeInterface'),
      'target_definition' => $method($this->getMockFieldDefinition()),
      'type' => 'fraction',
    ];
    $target = new FractionTarget($configuration, 'fraction', []);
    $method = $this->getProtectedClosure($target, 'prepareValue');
    $values = ['value' => '1/10'];
    $method(0, $values);
    $this->assertSame($values['numerator'], '1');
    $this->assertSame($values['denominator'], '10');
  }

  /**
   * @covers ::prepareValue
   */
  public function testPrepareValueDecimal() {
    $method = $this->getMethod('Drupal\fraction\Feeds\Target\FractionTarget', 'prepareTarget')->getClosure();
    $configuration = [
      'feed_type' => $this->getMock('Drupal\feeds\FeedTypeInterface'),
      'target_definition' => $method($this->getMockFieldDefinition()),
      'type' => 'decimal',
    ];
    $target = new FractionTarget($configuration, 'fraction', []);
    $method = $this->getProtectedClosure($target, 'prepareValue');
    $values = ['value' => 0.55];
    $method(0, $values);
    $this->assertSame($values['numerator'], '55');
    $this->assertSame($values['denominator'], '100');
  }

  /**
   * @covers ::buildConfigurationForm
   */
  public function testBuildConfigurationForm() {
    $method = $this->getMethod('Drupal\feeds\Feeds\Target\Text', 'prepareTarget')->getClosure();
    $configuration = [
      'feed_type' => $this->getMock('Drupal\feeds\FeedTypeInterface'),
      'target_definition' => $method($this->getMockFieldDefinition()),
    ];
    $target = new Text($configuration, 'text', [], $this->getMock('Drupal\Core\Session\AccountInterface'));
    $target->setStringTranslation($this->getStringTranslationStub());
    $form_state = new FormState();
    $form = $target->buildConfigurationForm([], $form_state);
    $this->assertSame(count($form), 1);
  }

}
