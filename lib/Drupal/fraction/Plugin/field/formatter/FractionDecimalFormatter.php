<?php

/**
 * @file
 * Definition of Drupal\fraction\Plugin\field\formatter\FractionDecimalFormatter.
 */

namespace Drupal\fraction\Plugin\field\formatter;

use Drupal\field\Annotation\FieldFormatter;
use Drupal\Core\Annotation\Translation;
use Drupal\fraction\Plugin\field\formatter\FractionFormatter;
use Drupal\Core\Entity\Field\FieldItemListInterface;
use Drupal\fraction\Fraction;

/**
 * Plugin implementation of the 'fraction' formatter.
 *
 * @FieldFormatter(
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
class FractionDecimalFormatter extends FractionFormatter {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, array &$form_state) {

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
        '#markup' => fraction($item->numerator, $item->denominator)->toDecimal($precision),
      );
    }

    return $elements;
  }
}
