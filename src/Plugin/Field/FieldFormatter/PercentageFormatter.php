<?php

namespace Drupal\fraction\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Format fraction as percentage.
 *
 * @FieldFormatter(
 *   id = "fraction_percentage",
 *   label = @Translation("Percentage"),
 *   field_types = {
 *     "fraction"
 *   }
 * )
 */
class PercentageFormatter extends FractionDecimalFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $settings = $this->getFieldSettings();

    // Iterate through the items.
    foreach ($items as $delta => $item) {
      $percentage = clone $item->fraction;
      $percentage->multiply(fraction_from_decimal('100'));

      $auto_precision = !empty($this->getSetting('auto_precision'));
      $output = $percentage->toDecimal($this->getSetting('precision'), $auto_precision) . '%';

      // Account for prefix and suffix.
      if ($this->getSetting('prefix_suffix')) {
        $prefixes = isset($settings['prefix']) ? array_map(['Drupal\Core\Field\FieldFilteredMarkup', 'create'], explode('|', $settings['prefix'])) : [''];
        $suffixes = isset($settings['suffix']) ? array_map(['Drupal\Core\Field\FieldFilteredMarkup', 'create'], explode('|', $settings['suffix'])) : [''];
        $prefix = (count($prefixes) > 1) ? $this->formatPlural($item->value, $prefixes[0], $prefixes[1]) : $prefixes[0];
        $suffix = (count($suffixes) > 1) ? $this->formatPlural($item->value, $suffixes[0], $suffixes[1]) : $suffixes[0];
        $output = $prefix . $output . $suffix;
      }

      $elements[$delta] = [
        '#markup' => $output,
      ];
    }

    return $elements;
  }

}
