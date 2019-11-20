<?php

namespace Drupal\fraction;

use Drupal\Core\TypedData\TypedData;

/**
 * A computed property for storing the fraction decimal value for validation.
 */
class FractionDecimalProperty extends TypedData {

  /**
   * Cached Fraction object.
   *
   * @var Fraction|null
   */
  protected $fraction = NULL;

  /**
   * Implements \Drupal\Core\TypedData\TypedDataInterface::getValue().
   */
  public function getValue($langcode = NULL) {
    // Load the parent item.
    $item = $this->getParent();

    // Otherwise, create a Fraction object.
    $this->fraction = fraction($item->numerator, $item->denominator);

    // Fallback to precision 9 to ensure validation.
    return $this->fraction->toDecimal(9, TRUE);
  }

}
