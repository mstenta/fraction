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
 *   },
 *   settings = {
 *     "precision" = "2",
 *   }
 * )
 */
class FractionDecimalWidget extends FractionWidget {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, array &$form_state) {

    // Decimal precision.
    $elements['precision'] = array(
      '#type' => 'textfield',
      '#title' => t('Decimal precision'),
      '#description' => t('Specify the number of digits after the decimal place to display when converting the fraction to a decimal.'),
      '#default_value' => $this->getSetting('precision'),
      '#required' => TRUE,
      '#weight' => 0,
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
    $summary[] = t('Precision: @precision', array('@precision' => $precision));

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, array &$form_state) {

    // Pass the element through the parent's formElement method.
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Hide the numerator and denominator fields.
    $element['numerator']['#type'] = 'hidden';
    $element['denominator']['#type'] = 'hidden';

    // Load the precision setting.
    $precision = $this->getSetting('precision');

    // Add a 'decimal' textfield for capturing the decimal value.
    $element['decimal'] = array(
      '#type' => 'textfield',
      '#default_value' => $items[$delta]->fraction->toDecimal($precision),
      '#size' => 15,
    );

    // Convert the decimal value to a fraction during validation.
    $element['#element_validate'][] = array($this, 'validateDecimal');

    return $element;
  }

  /**
   * Form element validation handler for $this->formElement().
   *
   * Convert the decimal value to a numerator and denominator.
   */
  public function validateDecimal(&$element, &$form_state, $form) {

    if (!empty($element['decimal']['#value'])) {

      // Convert the value to a fraction.
      $fraction = fraction_from_decimal($element['decimal']['#value']);

      // Set the numerator and denominator values for the form.
      $values = array(
        'decimal' => $element['decimal']['#value'],
        'numerator' => $fraction->getNumerator(),
        'denominator' => $fraction->getDenominator(),
      );
      form_set_value($element, $values, $form_state);
    }
  }
}
