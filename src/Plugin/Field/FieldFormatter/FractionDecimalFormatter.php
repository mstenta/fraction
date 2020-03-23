<?php

namespace Drupal\fraction\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'fraction_decimal' formatter.
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
    return [
      'precision' => 0,
      'auto_precision' => TRUE,
      'separator' => NULL,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    // Decimal precision.
    $elements['precision'] = [
      '#type' => 'number',
      '#title' => $this->t('Decimal precision'),
      '#description' => $this->t('Specify the number of digits after the decimal place to display. When "Auto precision" is enabled, this value essentially becomes a minimum fallback precision.'),
      '#default_value' => $this->getSetting('precision'),
      '#required' => TRUE,
      '#weight' => 0,
    ];

    // Auto precision.
    $elements['auto_precision'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Auto precision'),
      '#description' => $this->t('Automatically determine the maximum precision if the fraction has a base-10 denominator. For example, 1/100 would have a precision of 2, 1/1000 would have a precision of 3, etc.'),
      '#default_value' => $this->getSetting('auto_precision'),
      '#weight' => 1,
    ];

    // Separator.

    $elements['separator'] = [
      '#type' => 'radios',
      '#title' => $this->t('Decimal separator'),
      '#description' => $this->t('Specify the decimal separator that should be used.'),
      '#options' => array(
        NULL => $this->t('Period'),
        ',' => $this->t('Comma'),
      ),
      '#default_value' => $this->getSetting('separator'),
      '#weight' => 2,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary() ?? [];

    // Summarize the precision setting.
    $precision = $this->getSetting('precision');
    $auto_precision = !empty($this->getSetting('auto_precision')) ? 'On' : 'Off';
    $separator = !empty($this->getSetting('separator')) ? $this->t('Comma') : $this->t('Period');
    $summary[] = $this->t('Precision: @precision, Auto-precision: @auto_precision, Separator: @separator', [
      '@precision' => $precision,
      '@auto_precision' => $auto_precision,
      '@separator' => $separator,
    ]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    // Output fraction as a decimal with a fixed or automatic precision.
    $precision = $this->getSetting('precision');
    $auto_precision = !empty($this->getSetting('auto_precision')) ? TRUE : FALSE;
    $separator = $this->getSetting('separator');

    // Iterate through the items.
    foreach ($items as $delta => $item) {
      $output = $item->fraction->toDecimal($precision, $auto_precision, $separator);

      $elements[$delta] = [
        '#markup' => $this->viewOutput($item, $output),
      ];
    }

    return $elements;
  }

}
