<?php

/**
 * @file
 * Definition of Drupal\fraction\Fraction.
 */

namespace Drupal\fraction;

/**
 * A simple class for representing and acting upon a fraction.
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

    // Normalize negative fractions.
    // If the denominator is negative, invert the signs for both numbers.
    if ($value < 0) {
      $numerator = $this->getNumerator();
      $numerator = $numerator * -1;
      $this->setNumerator($numerator);
      $value = $value * -1;
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
  public function toDecimal($precision = 2) {

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
   * Calculate the fraction's greatest common divisor using Euclid's algorithm.
   *
   * @return
   *   Returns the greatest common divisor.
   */
  public function gcd() {

    // Get the numerator and denominator.
    $numerator = $this->getNumerator();
    $denominator = $this->getDenominator();

    // Make sure both numbers are positive.
    $a = str_replace('-', '', $numerator);
    $b = str_replace('-', '', $denominator);

    // Euclid's algorithm gives us the greatest common divisor.
    // Use BCMath's modulus function if available.
    if (function_exists('bcmod')) {
      while ($b != 0) {
        $t = $b;
        $b = bcmod($a, $b);
        $a = $t;
      }
    }
    else {
      while ($b != 0) {
        $t = $b;
        $b = $a % $b;
        $a = $t;
      }
    }

    return $a;
  }

  /**
   * Reduce the fraction to its simplest form.
   *
   * @return
   *   Returns this object.
   */
  public function reduce() {

    // Get the numerator and denominator.
    $numerator = $this->getNumerator();
    $denominator = $this->getDenominator();

    // Calculate the greatest common divisor.
    $gcd = $this->gcd();

    // Divide the numerator and denominator by the gcd.
    // Use BCMath division if available.
    if (function_exists('bcdiv')) {
      $numerator = bcdiv($numerator, $gcd);
      $denominator = bcdiv($denominator, $gcd);
    }
    else {
      $numerator = $numerator / $gcd;
      $denominator = $denominator / $gcd;
    }

    // Save the numerator and denominator.
    $this->setNumerator($numerator);
    $this->setDenominator($denominator);

    return $this;
  }

  /**
   * Reciprocate the fraction.
   *
   * @return
   *   Returns this object.
   */
  public function reciprocate() {

    // Get the numerator and denominator, but flipped.
    $numerator = $this->getDenominator();
    $denominator = $this->getNumerator();

    // Save the numerator and denominator.
    $this->setNumerator($numerator);
    $this->setDenominator($denominator);

    return $this;
  }

  /**
   * Add another fraction to this one.
   *
   * @param Fraction $fraction
   *   Another fraction object to add to this one.
   *
   * @return
   *   Returns this object.
   */
  public function add(Fraction $fraction) {

    // Get the numerator and denominator of each fraction.
    $numerator1 = $this->getNumerator();
    $denominator1 = $this->getDenominator();
    $numerator2 = $fraction->getNumerator();
    $denominator2 = $fraction->getDenominator();

    // Calculate the sum of the two fractions.
    // Use BCMath if available.
    if (function_exists('bcmul') && function_exists('bcadd')) {
      $denominator = bcmul($denominator1, $denominator2);
      $numerator = bcadd(bcmul($numerator1, $denominator2), bcmul($numerator2, $denominator1));
    }
    else {
      $denominator = $denominator1 * $denominator2;
      $numerator = $numerator1 * $denominator2 + $numerator2 * $denominator1;
    }

    // Save the numerator and denominator.
    $this->setNumerator($numerator);
    $this->setDenominator($denominator);

    return $this;
  }

  /**
   * Subtract another fraction from this one.
   *
   * @param Fraction $fraction
   *   Another fraction object to subtract this one.
   *
   * @return
   *   Returns this object.
   */
  function subtract(Fraction $fraction) {

    // Get the numerator and denominator of each fraction.
    $numerator1 = $this->getNumerator();
    $denominator1 = $this->getDenominator();
    $numerator2 = $fraction->getNumerator();
    $denominator2 = $fraction->getDenominator();

    // Calculate the difference of the two fractions.
    // Use BCMath if available.
    if (function_exists('bcmul') && function_exists('bcsub')) {
      $denominator = bcmul($denominator1, $denominator2);
      $numerator = bcsub(bcmul($numerator1, $denominator2), bcmul($numerator2, $denominator1));
    }
    else {
      $denominator = $denominator1 * $denominator2;
      $numerator = $numerator1 * $denominator2 - $numerator2 * $denominator1;
    }

    // Save the numerator and denominator.
    $this->setNumerator($numerator);
    $this->setDenominator($denominator);

    return $this;
  }

  /**
   * Multiply this fraction with another one.
   *
   * @param Fraction $fraction
   *   Another fraction object to multiply with.
   *
   * @return
   *   Returns this object.
   */
  function multiply(Fraction $fraction) {

    // Get the numerator and denominator of each fraction.
    $numerator1 = $this->getNumerator();
    $denominator1 = $this->getDenominator();
    $numerator2 = $fraction->getNumerator();
    $denominator2 = $fraction->getDenominator();

    // Calculate the product of the two fractions.
    // Use BCMath if available.
    if (function_exists('bcmul')) {
      $numerator = bcmul($numerator1, $numerator2);
      $denominator = bcmul($denominator1, $denominator2);
    }
    else {
      $numerator = $numerator1 * $numerator2;
      $denominator = $denominator1 * $denominator2;
    }

    // Save the numerator and denominator.
    $this->setNumerator($numerator);
    $this->setDenominator($denominator);

    return $this;
  }

  /**
   * Divide this fraction by another one.
   *
   * @param Fraction $fraction
   *   Another fraction object to divide by.
   *
   * @return
   *   Returns this object.
   */
  function divide(Fraction $fraction) {

    // Reciprocate the fraction.
    $fraction->reciprocate();

    // Get the numerator and denominator of each fraction.
    $numerator1 = $this->getNumerator();
    $denominator1 = $this->getDenominator();
    $numerator2 = $fraction->getNumerator();
    $denominator2 = $fraction->getDenominator();

    // Calculate the quotient of the two fractions.
    // Use BCMath if available.
    if (function_exists('bcmul')) {
      $numerator = bcmul($numerator1, $numerator2);
      $denominator = bcmul($denominator1, $denominator2);
    }
    else {
      $numerator = $numerator1 * $numerator2;
      $denominator = $denominator1 * $denominator2;
    }

    // Save the numerator and denominator.
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
