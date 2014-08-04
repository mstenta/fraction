<?php

/**
 * @file
 * Definition of Drupal\fraction\Plugin\Field\FieldWidget\FractionWidget.
 */

namespace Drupal\fraction\Plugin\Field\FieldWidget;

use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Field\FieldItemListInterface;
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
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['#type'] = 'fieldset';

    $element['numerator'] = array(
      '#type' => 'textfield',
      '#title' => t('Numerator'),
      '#default_value' => isset($items[$delta]->numerator) ? $items[$delta]->numerator : NULL,
    );

    $element['denominator'] = array(
      '#type' => 'textfield',
      '#title' => t('Denominator'),
      '#default_value' => isset($items[$delta]->denominator) ? $items[$delta]->denominator : NULL,
    );

    // Add denominator validation.
    $element['#element_validate'][] = array($this, 'validateDenominator');

    return $element;
  }

  /**
   * Form element validation handler for $this->formElement().
   *
   * Validate the denominator.
   */
  public function validateDenominator(&$element, &$form_state, $form) {

    // If the denominator is empty, but the numerator isn't, print an error.
    if (empty($element['denominator']['#value']) && !empty($element['numerator']['#value'])) {
      form_error($element['denominator'], t('The denominator of a fraction cannot be zero or empty (if a numerator is provided).'));
    }
  }
}
