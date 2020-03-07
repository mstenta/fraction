<?php

namespace Drupal\fraction\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\MapDataDefinition;
use Drupal\fraction\Fraction;

/**
 * Plugin implementation of the 'fraction' field type.
 *
 * @FieldType(
 *   id = "fraction",
 *   label = @Translation("Fraction (two integers)"),
 *   description = @Translation("This field stores a decimal in fraction form (with a numerator and denominator) for maximum precision."),
 *   category = @Translation("Number"),
 *   default_widget = "fraction",
 *   default_formatter = "fraction"
 * )
 */
class FractionItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    return [
      'prefix' => '',
      'suffix' => '',
    ] + parent::defaultFieldSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    $settings = $this->getSettings();

    $element['prefix'] = [
      '#type' => 'textfield',
      '#title' => t('Prefix'),
      '#default_value' => $settings['prefix'],
      '#size' => 60,
      '#description' => t("Define a string that should be prefixed to the value, like '$ ' or '&euro; '. Leave blank for none. Separate singular and plural values with a pipe ('pound|pounds')."),
    ];
    $element['suffix'] = [
      '#type' => 'textfield',
      '#title' => t('Suffix'),
      '#default_value' => $settings['suffix'],
      '#size' => 60,
      '#description' => t("Define a string that should be suffixed to the value, like ' m', ' kb/s'. Leave blank for none. Separate singular and plural values with a pipe ('pound|pounds')."),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'numerator' => [
          'description' => 'Fraction numerator value',
          'type' => 'int',
          'size' => 'big',
          'not null' => TRUE,
          'default' => 0,
        ],
        'denominator' => [
          'description' => 'Fraction denominator value',
          'type' => 'int',
          'not null' => TRUE,
          'default' => 1,
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $property_definitions['numerator'] = DataDefinition::create('integer')
      ->setLabel(t('Numerator value'));
    $property_definitions['denominator'] = DataDefinition::create('integer')
      ->setLabel(t('Denominator value'));
    $property_definitions['fraction'] = MapDataDefinition::create()
      ->setLabel(t('Fraction'))
      ->setDescription(t('A fraction object instance.'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\fraction\FractionProperty');
    return $property_definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $numerator = $this->get('numerator')->getValue();
    $denominator = $this->get('denominator')->getValue();
    return ((string) $numerator !== '0' && empty($numerator)) || empty($denominator);
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    // Generate random decimal (float) with a max of 9 decimal places and then
    // convert it to fraction.
    $divisor = pow(10, rand(0, 9));
    $number = mt_rand(1, 20 * $divisor) / $divisor;
    $fraction = Fraction::createFromDecimal($number);
    return [
      'numerator' => $fraction->getNumerator(),
      'denominator' => $fraction->getDenominator(),
    ];
  }

}
