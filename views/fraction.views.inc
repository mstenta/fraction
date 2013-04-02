<?php

/**
 * @file
 * Views hooks.
 */

/**
 * Implements hook_field_views_data_alter().
 */
function fraction_field_views_data_alter(&$result, $field, $module) {

  // Only operate on fields defined by the Fraction module.
  if ($module != 'fraction') {
    return;
  }

  // Load field instance info.
  if (!empty($field['bundles'])) {
    foreach ($field['bundles'] as $entity_type => $bundles) {
      if (!empty($bundles)) {
        foreach ($bundles as $bundle) {
          $field_info_instance = field_info_instance($entity_type, $field['field_name'], $bundle);
        }
      }
    }
  }

  // Loop through the fields to add columns.
  foreach ($field['storage']['details']['sql'] as $type => $tables) {
    foreach ($tables as $table_name => $columns) {

      // Create a variable alias for the numerator column, because we'll copy some data from it.
      $numerator_column = $result[$table_name][$field['field_name'] . '_numerator'];

      // Create a new decimal column.
      $column_name = $field['field_name'] . '_decimal';
      $result[$table_name][$column_name] = array(
        'group' => t('Content'),
        'title' => t($field_info_instance['label'] . ' (decimal)'),
        'title short' => t($field_info_instance['label'] . ':decimal'),
        'help' => $numerator_column['help'],  // Copy from the numerator field.
      );

      // Allow sorting by the MySQL decimal equivalent of the fraction.
      $result[$table_name][$column_name]['sort'] = array(
        'handler' => 'fraction_handler_sort_decimal',
        'additional fields' => $numerator_column['sort']['additional fields'],
      );
    }
  }
}
