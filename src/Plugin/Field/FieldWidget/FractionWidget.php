<?php

namespace Drupal\fraction\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'fraction' widget.
 *
 * @FieldWidget(
 *   id = "fraction",
 *   label = @Translation("Fraction"),
 *   field_types = {
 *     "fraction"
 *   }
 * )
 */
class FractionWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'min_numerator' => '',
        'max_numerator' => '',
        'min_denominator' => '',
        'max_denominator' => '',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {


    $elements['min_numerator'] = [
      '#type' => 'number',
      '#title' => $this->t('Denominator: Minimum'),
      '#default_value' => $this->getSetting('min_numerator'),
      '#description' => $this->t('The minimum value that should be allowed in this field. Leave blank for no minimum.'),
    ];
    $elements['max_numerator'] = [
      '#type' => 'number',
      '#title' => $this->t('Denominator: Maximum'),
      '#default_value' => $this->getSetting('max_numerator'),
      '#description' => $this->t('The maximum value that should be allowed in this field. Leave blank for no maximum.'),
    ];

    $elements['min_denominator'] = [
      '#type' => 'number',
      '#title' => $this->t('Numerator: Minimum'),
      '#default_value' => $this->getSetting('min_denominator'),
      '#description' => $this->t('The minimum value that should be allowed in this field. Leave blank for no minimum.'),
    ];
    $elements['max_denominator'] = [
      '#type' => 'number',
      '#title' => $this->t('Numerator: Maximum'),
      '#default_value' => $this->getSetting('max_denominator'),
      '#description' => $this->t('The maximum value that should be allowed in this field. Leave blank for no maximum.'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    if ($this->getSetting('min_numerator')) {
      $summary[] = $this->t('Min numerator: @min', [
        '@min' => $this->getSetting('min_numerator'),
      ]);
    }

    if ($this->getSetting('max_numerator')) {
      $summary[] = $this->t('Max numerator: @max', [
        '@max' => $this->getSetting('max_numerator'),
      ]);
    }

    if ($this->getSetting('min_denominator')) {
      $summary[] = $this->t('Min denominator: @min', [
        '@min' => $this->getSetting('min_denominator'),
      ]);
    }

    if ($this->getSetting('max_denominator')) {
      $summary[] = $this->t('Max denominator: @max', [
        '@max' => $this->getSetting('max_denominator'),
      ]);
    }


    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['#type'] = 'fieldset';

    $element['numerator'] = [
      '#type' => 'number',
      '#title' => $this->t('Numerator'),
      '#default_value' => isset($items[$delta]->numerator) ? $items[$delta]->numerator : NULL,
    ];

    $element['denominator'] = [
      '#type' => 'number',
      '#title' => $this->t('Denominator'),
      '#default_value' => isset($items[$delta]->denominator) ? $items[$delta]->denominator : NULL,
    ];

    // Set minimum and maximum.
    if (is_numeric($this->getSetting('min_numerator'))) {
      $element['numerator']['#min'] = $this->getSetting('min_numerator');
    }
    if (is_numeric($this->getSetting('max_numerator'))) {
      $element['numerator']['#max'] = $this->getSetting('max_numerator');
    }
    if (is_numeric($this->getSetting('min_denominator'))) {
      $element['denominator']['#min'] = $this->getSetting('min_denominator');
    }
    if (is_numeric($this->getSetting('max_denominator'))) {
      $element['denominator']['#max'] = $this->getSetting('max_denominator');
    }

    // Add validation.
    $element['#element_validate'][] = [$this, 'validateFraction'];

    return $element;
  }

  /**
   * Form element validation handler for $this->formElement().
   *
   * Validate the fraction.
   */
  public function validateFraction(&$element, &$form_state, $form) {

    // If the denominator is empty, but the numerator isn't, print an error.
    if (empty($element['denominator']['#value']) && !empty($element['numerator']['#value'])) {
      $form_state->setError($element, $this->t('The denominator of a fraction cannot be zero or empty (if a numerator is provided).'));
    }

    // Numerators must be between -9223372036854775808 and 9223372036854775807.
    // Explicitly perform a string comparison to ensure precision.
    if (!empty($element['numerator']['#value']) && ((string) $element['numerator']['#value'] < '-9223372036854775808' || (string) $element['numerator']['#value'] > '9223372036854775807')) {
      $form_state->setError($element, $this->t('The numerator of a fraction must be between -9223372036854775808 and 9223372036854775807.'));
    }

    // Denominators must be between 0 and 4294967295.
    // Explicitly perform a string comparison to ensure precision.
    if (!empty($element['denominator']['#value']) && ((string) $element['denominator']['#value'] < '0' || (string) $element['denominator']['#value'] > '4294967295')) {
      $form_state->setError($element, $this->t('The denominator of a fraction must be between 0 and 4294967295.'));
    }
  }

}
