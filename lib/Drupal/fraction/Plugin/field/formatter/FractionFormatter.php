<?php

/**
 * @file
 * Definition of Drupal\fraction\Plugin\field\formatter\FractionFormatter.
 */

namespace Drupal\fraction\Plugin\field\formatter;

use Drupal\field\Annotation\FieldFormatter;
use Drupal\Core\Annotation\Translation;
use Drupal\field\Plugin\Type\Formatter\FormatterBase;
use Drupal\Core\Entity\Field\FieldItemListInterface;
use Drupal\fraction\Fraction;

/**
 * Plugin implementation of the 'fraction' formatter.
 *
 * @FieldFormatter(
 *   id = "fraction",
 *   label = @Translation("Fraction"),
 *   field_types = {
 *     "fraction"
 *   },
 *   settings = {
 *     "separator" = "/",
 *   }
 * )
 */
class FractionFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, array &$form_state) {

    // Numerator and denominator separator.
    $elements['separator'] = array(
      '#type' => 'textfield',
      '#title' => t('Separator'),
      '#description' => t('Specify the separator to display between the numerator and denominator.'),
      '#default_value' => $this->getSetting('separator'),
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

    // Summarize the separator setting.
    $separator = $this->getSetting('separator');
    $summary[] = t('Separator: @separator', array('@separator' => $separator));

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {
    $elements = array();

    // Load the separator setting.
    $separator = $this->getSetting('separator');

    // Iterate through the items.
    foreach ($items as $delta => $item) {

      // Output fraction as a string.
      $elements[$delta] = array(
        '#markup' => fraction($item->numerator, $item->denominator)->toString($separator),
      );
    }

    return $elements;
  }
}
