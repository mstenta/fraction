<?php

namespace Drupal\fraction\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'fraction' formatter.
 *
 * @FieldFormatter(
 *   id = "fraction",
 *   label = @Translation("Fraction"),
 *   field_types = {
 *     "fraction"
 *   }
 * )
 */
class FractionFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'separator' => '/',
      'prefix_suffix' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    // Numerator and denominator separator.
    $elements['separator'] = [
      '#type' => 'textfield',
      '#title' => t('Separator'),
      '#description' => t('Specify the separator to display between the numerator and denominator.'),
      '#default_value' => $this->getSetting('separator'),
      '#required' => TRUE,
      '#weight' => 0,
    ];

    $elements['prefix_suffix'] = [
      '#type' => 'checkbox',
      '#title' => t('Display prefix and suffix'),
      '#default_value' => $this->getSetting('prefix_suffix'),
      '#weight' => 10,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    // Summarize the separator setting.
    $separator = $this->getSetting('separator');
    $summary[] = t('Separator: @separator', [
      '@separator' => $separator,
    ]);

    if ($this->getSetting('prefix_suffix')) {
      $summary[] = t('Display with prefix and suffix.');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $settings = $this->getFieldSettings();

    // Load the separator setting.
    $separator = $this->getSetting('separator');

    // Iterate through the items.
    foreach ($items as $delta => $item) {
      $output = $item->fraction->toString($separator);

      // Account for prefix and suffix.
      if ($this->getSetting('prefix_suffix')) {
        $prefixes = isset($settings['prefix']) ? array_map(['Drupal\Core\Field\FieldFilteredMarkup', 'create'], explode('|', $settings['prefix'])) : [''];
        $suffixes = isset($settings['suffix']) ? array_map(['Drupal\Core\Field\FieldFilteredMarkup', 'create'], explode('|', $settings['suffix'])) : [''];
        $prefix = (count($prefixes) > 1) ? $this->formatPlural($item->value, $prefixes[0], $prefixes[1]) : $prefixes[0];
        $suffix = (count($suffixes) > 1) ? $this->formatPlural($item->value, $suffixes[0], $suffixes[1]) : $suffixes[0];
        $output = $prefix . $output . $suffix;
      }

      // Output fraction as a string.
      $elements[$delta] = [
        '#markup' => $output,
      ];
    }

    return $elements;
  }
}
