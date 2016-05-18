<?php

/**
 * @file
 * Definition of Drupal\fraction\Plugin\Field\FieldWidget\FractionDecimalWidget.
 */

namespace Drupal\fraction\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'fraction_decimal' widget.
 *
 * @FieldWidget(
 *   id = "fraction_decimal",
 *   label = @Translation("Decimal"),
 *   field_types = {
 *     "fraction"
 *   }
 * )
 */
class FractionDecimalWidget extends FractionWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'precision' => 0,
      'auto_precision' => TRUE,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    // Decimal precision.
    $elements['precision'] = array(
      '#type' => 'textfield',
      '#title' => t('Decimal precision'),
      '#description' => t('Specify the number of digits after the decimal place to display when converting the fraction to a decimal. When "Auto precision" is enabled, this value essentially becomes a minimum fallback precision.'),
      '#default_value' => $this->getSetting('precision'),
      '#required' => TRUE,
      '#weight' => 0,
    );

    // Auto precision.
    $elements['auto_precision'] = array(
      '#type' => 'checkbox',
      '#title' => t('Auto precision'),
      '#description' => t('Automatically determine the maximum precision if the fraction has a base-10 denominator. For example, 1/100 would have a precision of 2, 1/1000 would have a precision of 3, etc.'),
      '#default_value' => $this->getSetting('auto_precision'),
      '#weight' => 1,
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();

    // Summarize the precision setting.
    $precision = $this->getSetting('precision');
    $auto_precision = !empty($this->getSetting('auto_precision')) ? 'On' : 'Off';
    $summary[] = t('Precision: @precision, Auto-precision: @auto_precision', array('@precision' => $precision, '@auto_precision' => $auto_precision));

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    // Pass the element through the parent's formElement method.
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Hide the numerator and denominator fields.
    $element['numerator']['#type'] = 'hidden';
    $element['denominator']['#type'] = 'hidden';

    // Load the precision setting.
    $precision = $this->getSetting('precision');

    // Add a 'decimal' textfield for capturing the decimal value.
    // The default value is converted to a decimal with the specified precision.
    $auto_precision = !empty($this->getSetting('auto_precision')) ? TRUE : FALSE;
    $element['decimal'] = array(
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->fraction->toDecimal($precision, $auto_precision),
      '#size' => 15,
    );

    // Add decimal validation. This is also where we will convert the decimal
    // to a fraction.
    $element['#element_validate'][] = array($this, 'validateDecimal');

    return $element;
  }

  /**
   * Form element validation handler for $this->formElement().
   */
  public function validateDecimal(&$element, &$form_state, $form) {

    // Convert the value to a fraction.
    $fraction = fraction_from_decimal($element['decimal']['#value']);

    // Get the numerator and denominator.
    $numerator = $fraction->getNumerator();
    $denominator = $fraction->getDenominator();

    // Set the numerator and denominator values for the form.
    $values = array(
      'decimal' => $element['decimal']['#value'],
      'numerator' => $numerator,
      'denominator' => $denominator,
    );
    $form_state->setValueForElement($element, $values);
  }
}
