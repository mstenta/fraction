<?php

/**
 * @file
 * Definition of Drupal\fraction\Plugin\Field\FieldFormatter\FractionDecimalFormatter.
 */

namespace Drupal\fraction\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'fraction' formatter.
 *
 * @FieldFormatter(
 *   id = "fraction_decimal",
 *   label = @Translation("Decimal"),
 *   field_types = {
 *     "fraction"
 *   }
 * )
 */
class FractionDecimalFormatter extends FractionFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'precision' => 2,
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
      '#description' => t('Specify the number of digits after the decimal place to display.'),
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
  public function viewElements(FieldItemListInterface $items) {
    $elements = array();

    // Load the precision setting.
    $precision = $this->getSetting('precision');

    // Iterate through the items.
    foreach ($items as $delta => $item) {

      // Output fraction as a decimal with a fixed precision.
      $elements[$delta] = array(
        '#markup' => $item->fraction->toDecimal($precision),
      );
    }

    return $elements;
  }
}

