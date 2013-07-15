<?php

/**
 * @file
 * Fraction class
 */

class Fraction {

  /**
   * Numerator and denominator properties.
   */
  protected $numerator;
  protected $denominator;

  /**
   * Constructor.
   *
   * @param $numerator
   *   The fraction's numerator.
   * @param $denominator
   *   The fraction's denominator.
   */
  public function __construct($numerator, $denominator) {

    // Set the numerator and denominator.
    $this->setNumerator($numerator);
    $this->setDenominator($denominator);
  }

  /**
   * Set the numerator.
   *
   * @param $value
   *   The numerator value.
   *
   * @return
   *   Returns this object.
   */
  public function setNumerator($value) {

    // Cast the value as a string and save it.
    $this->numerator = (string) $value;

    return $this;
  }

  /**
   * Set the denominator.
   *
   * @param $value
   *   The denominator value.
   *
   * @return
   *   Returns this object.
   */
  public function setDenominator($value) {

    // Protect against division by zero.
    if (empty($value)) {
      $this->setNumerator(0);
      $value = 1;
    }

    // Cast the value as a string and save it.
    $this->denominator = (string) $value;

    return $this;
  }

  /**
   * Get the numerator.
   *
   * @return
   *   Returns the numerator value.
   */
  public function getNumerator() {
    return $this->numerator;
  }

  /**
   * Get the denominator.
   *
   * @return
   *   Returns the denominator value.
   */
  public function getDenominator() {
    return $this->denominator;
  }

  /**
   * Return a string representation of the fraction.
   *
   * @param $separator
   *   The separator to place between the numerator and denominator.
   *
   * @return
   *   Returns a string with the numerator, separator, and denominator.
   */
  public function toString($separator = '/') {

    // Get the numerator and denominator.
    $numerator = $this->getNumerator();
    $denominator = $this->getDenominator();

    // Concatenate with the separator and return.
    return $numerator . $separator . $denominator;
  }

  /**
   * Calculate the decimal equivalent of the fraction.
   *
   * @param $precision
   *   The desired decimal precision.
   *
   * @return
   *   Returns the decimal equivalent of the fraction as a PHP string.
   */
  public function toDecimal($precision = FRACTION_DEFAULT_PRECISION) {

    // Get the numerator and denominator.
    $numerator = $this->getNumerator();
    $denominator = $this->getDenominator();

    // Divide the numerator by the denominator (using BCMath if available).
    if (function_exists('bcdiv')) {

      // Divide the numerator and denominator, with extra precision.
      $value = bcdiv($numerator, $denominator, $precision + 1);

      // Return a decimal string rounded to the final precision.
      return $this->bcRound($value, $precision);
    }

    // If BCMath is not available, use normal PHP float division and rounding.
    else {
      return round($numerator / $denominator, $precision);
    }
  }

  /**
   * Calculates the numerator and denominator from a decimal value.
   *
   * @param $value
   *   The decimal value to start with.
   *
   * @return
   *   Returns this object.
   */
  public function fromDecimal($value) {

    // Calculate the precision by counting the number of decimal places.
    $precision = drupal_strlen(drupal_substr(strrchr($value, '.'), 1));

    // Create the denominator by raising 10 to the power of the precision.
    if (function_exists('bcpow')) {
      $denominator = bcpow(10, $precision);
    }
    else {
      $denominator = pow(10, $precision);
    }

    // Calculate the numerator by multiplying the value by the denominator.
    if (function_exists('bcmul')) {
      $numerator = bcmul($value, $denominator);
    }
    else {
      $numerator = $value * $denominator;
    }

    // Set the numerator and denominator.
    $this->setNumerator($numerator);
    $this->setDenominator($denominator);

    return $this;
  }

  /**
   * BCMath decimal rounding function.
   *
   * @param $value
   *   The value to round.
   * @param $precision
   *   The desired decimal precision.
   *
   * @return
   *   Returns a rounded decimal value, as a PHP string.
   */
  protected function bcRound($value, $precision) {
    if ($value[0] != '-') {
      return bcadd($value, '0.' . str_repeat('0', $precision) . '5', $precision);
    }
    return bcsub($value, '0.' . str_repeat('0', $precision) . '5', $precision);
  }
}

