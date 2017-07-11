<?php

/**
 * Feeds integration code for the Fraction module.
 */

/**
 * Implements hook_feeds_processor_targets().
 */
function fraction_feeds_processor_targets($entity_type, $bundle_name) {
  $targets = array();

  // Add targets for each Fraction field instance.
  foreach (field_info_instances($entity_type, $bundle_name) as $name => $instance) {
    $info = field_info_field($name);
    if ($info['type'] == 'fraction') {
      $targets[$name] = array(
        'name' => check_plain($instance['label']) . ' (' . t('decimal') . ')',
        'callback' => 'fraction_feeds_set_target',
        'description' => t('The @label field of the entity.', array('@label' => $instance['label'])),
      );
      $targets[$name . ':fraction'] = array(
        'name' => check_plain($instance['label']) . ' (' . t('fraction') . ')',
        'callback' => 'fraction_feeds_set_target',
        'description' => t('The @label field of the entity (fraction).', array('@label' => $instance['label'])),
      );
      $targets[$name . ':numerator'] = array(
        'name' => check_plain($instance['label']) . ' (' . t('numerator') . ')',
        'callback' => 'fraction_feeds_set_target',
        'description' => t('The @label field of the entity (numerator value).', array('@label' => $instance['label'])),
      );
      $targets[$name . ':denominator'] = array(
        'name' => check_plain($instance['label']) . ' (' . t('denominator') . ')',
        'callback' => 'fraction_feeds_set_target',
        'description' => t('The @label field of the entity (denominator value).', array('@label' => $instance['label'])),
      );
    }
  }

  return $targets;
}

/**
 * Callback for mapping fraction fields in Feeds.
 */
function fraction_feeds_set_target(FeedsSource $source, $entity, $target, array $values, array $mapping) {
  $language = $mapping['language'];

  // Determine what type of value is being saved (decimal, fraction, numerator,
  // or denominator).
  $type = 'decimal';
  if (strpos($target, ':')) {
    $parts = explode(':', $target);
    $target = $parts[0];
    if ($parts[1] == 'fraction') {
      $type = 'fraction';
    }
    elseif ($parts[1] == 'numerator' || $parts[1] == 'denominator') {
      $type = $parts[1];
    }
  }

  // Load field info.
  $info = field_info_field($target);

  // Populate the field(s).
  $field = isset($entity->$target) ? $entity->$target : array($language => array());
  $delta = 0;
  foreach ($values as $value) {

    // Only store as many values as are allowed in the field config..
    if ($info['cardinality'] == $delta) {
      break;
    }

    // Load the value.
    if (is_object($value) && ($value instanceof FeedsElement)) {
      $value = $value->getValue();
    }

    // If the value is a fraction, pull out the numerator and denominator.
    if ($type == 'fraction') {
      $parts = explode('/', $value);
      if (!empty($parts[0]) && is_numeric($parts[0]) && !empty($parts[1]) && is_numeric($parts[1])) {
        $field[$language][$delta]['numerator'] = $parts[0];
        $field[$language][$delta]['denominator'] = $parts[1];
      }
    }

    // Or, if the value is numeric...
    elseif (is_numeric($value)) {

      // If the value is a decimal, convert to a fraction.
      if ($type == 'decimal') {
        $fraction = fraction_from_decimal($value);
        $field[$language][$delta]['numerator'] = $fraction->getNumerator();
        $field[$language][$delta]['denominator'] = $fraction->getDenominator();
      }

      // Otherwise...
      else {

        // If no values have been stored yet, and the value is not empty,
        // initialize both field values to ensure that both they are set to
        // sane defaults (in case one is omitted in the source).
        if (!isset($field[$language][$delta]) && !empty($value)) {
          $field[$language][$delta] = array(
            'numerator' => 0,
            'denominator' => 1,
          );
        }

        // Save the numerator/denominator. Do not allow empty denominator.
        if ($type == 'numerator') {
          $field[$language][$delta]['numerator'] = $value;
        }
        elseif ($type == 'denominator' && !empty($value)) {
          $field[$language][$delta]['denominator'] = $value;
        }
      }
    }

    $delta++;
  }

  $entity->$target = $field;
}
