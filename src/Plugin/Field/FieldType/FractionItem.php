<?php

/**
 * @file
 * Contains \Drupal\fraction\Plugin\Field\FieldType\FractionItem.
 */

namespace Drupal\fraction\Plugin\Field\FieldType;

use Drupal\Core\Entity\Annotation\FieldType;
use Drupal\Core\Annotation\Translation;
use Drupal\field\FieldInterface;
use Drupal\field\Plugin\Type\FieldType\ConfigFieldItemBase;

/**
 * Plugin implementation of the 'fraction' field type.
 *
 * @FieldType(
 *   id = "fraction",
 *   label = @Translation("Fraction"),
 *   description = @Translation("This field stores a decimal in fraction form (with a numerator and denominator) for maximum precision."),
 *   default_widget = "fraction",
 *   default_formatter = "fraction"
 * )
 */
class FractionItem extends ConfigFieldItemBase {

  /**
   * Definitions of the contained properties.
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions() {
    if (!isset(static::$propertyDefinitions)) {
      static::$propertyDefinitions['numerator'] = array(
        'type' => 'integer',
        'label' => t('Numerator value'),
      );
      static::$propertyDefinitions['denominator'] = array(
        'type' => 'integer',
        'label' => t('Denominator value'),
      );
      static::$propertyDefinitions['fraction'] = array(
        'type' => 'fraction',
        'label' => t('Fraction'),
        'description' => t('A fraction object instance.'),
        'computed' => TRUE,
        'class' => '\Drupal\fraction\FractionProperty',
      );
    }
    return static::$propertyDefinitions;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldInterface $field) {
    return array(
      'columns' => array(
        'numerator' => array(
          'description' => 'Fraction numerator value',
          'type' => 'int',
          'size' => 'big',
          'not null' => TRUE,
          'default' => 0,
        ),
        'denominator' => array(
          'description' => 'Fraction denominator value',
          'type' => 'int',
          'unsigned' => TRUE,
          'not null' => TRUE,
          'default' => 1,
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $numerator = $this->get('numerator')->getValue();
    $denominator = $this->get('denominator')->getValue();
    return empty($numerator) || empty($denominator);
  }
}

