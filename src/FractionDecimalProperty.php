<?php

namespace Drupal\fraction;

use Drupal\Core\TypedData\TypedData;

/**
 * A computed property for storing the fraction decimal value for validation.
 */
class FractionDecimalProperty extends TypedData {

  /**
   * Cached value.
   *
   * @var string|null
   */
  protected $decimal = NULL;

  /**
   * Implements \Drupal\Core\TypedData\TypedDataInterface::getValue().
   */
  public function getValue($langcode = NULL) {
    // If a value is already available, return it.
    if ($this->decimal !== NULL) {
      return $this->decimal;
    }

    // Load the parent item.
    $item = $this->getParent();
    // Otherwise, create a Fraction object.
    $fraction = new Fraction($item->numerator, $item->denominator);
    // Fallback to precision 9 to ensure validation.
    $this->decimal = $fraction->toDecimal(9, TRUE);

    return $this->decimal;
  }

}
