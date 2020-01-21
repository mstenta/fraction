<?php

/**
 * @file
 * Post update functions for Fraction.
 */

use Drupal\Core\Utility\UpdateException;

/**
 * Alter schema to make denominator signed.
 *
 * This is done so the field is leveled between MySQL and Postgres and migration
 * is possible between the two. When a integer is unsigned in Postges, Drupal
 * will create a bigint for it, while with MySQL, it would create a regular int.
 *
 * @see \Drupal\Core\Database\Driver\pgsql\Schema::processField()
 * @see https://www.drupal.org/project/fraction/issues/2729315
 */
function fraction_post_update_make_denominator_signed() {
  // Get all fraction fields in the system.
  $fraction_fields = \Drupal::service('entity_field.manager')->getFieldMapByFieldType('fraction');
  $entity_type_manager = \Drupal::entityTypeManager();
  $table_names = [];
  foreach ($fraction_fields as $entity_type => $fields) {
    $table_mapping = $entity_type_manager->getStorage($entity_type)->getTableMapping();
    foreach ($fields as $field_name => $field_info) {
      $table_name = $table_mapping->getFieldTableName($field_name);
      $column_name = $field_name . '_denominator';
      $max_denominator = \Drupal::database()->query('SELECT MAX(' . $column_name . ') FROM {' . $table_name . '}')->fetchField();
      // Max value on signed int for MySQL or int in PostgreSQL is 2147483648.
      if ($max_denominator > 2147483648) {
        // If the max value is bigger than the limits for MySQL/Postgres, warn
        // the user.
        throw new UpdateException('Fraction works with signed integer schema fields for denominator, some of your values in the database exceed this limit, please check the fraction field tables and review the data before running this update. See https://www.drupal.org/project/fraction/issues/2729315 for further details.');
      }
      else {
        $table_names[$field_name] = $table_name;
      }
    }
  }

  if (empty($table_names)) {
    return 'No fraction fields found to update.';
  }

  // Query all fraction field tables and get the MAX value.
  foreach ($table_names as $field_name => $table_name) {
    $column_name = $field_name . '_denominator';
    \Drupal::database()->schema()->changeField($table_name, $column_name, $column_name, [
      'description' => 'Fraction denominator value',
      'type' => 'int',
      'not null' => TRUE,
      'default' => 1,
    ]);
  }

  return 'Fraction fields updated: ' . implode(',', array_values($table_names));
}
