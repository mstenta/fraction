<?php

/**
 * @file
 * Views hooks.
 */

/**
 * Implements hook_field_views_data_alter().
 */
function fraction_field_views_data_alter(&$result, $field) {

  // Only operate on fields defined by the Fraction module.
  if ($field->module != 'fraction') {
    return;
  }

  // Grab information about the entity type tables.
  $entity_manager = \Drupal::entityManager();
  $entity_type = $field->entity_type;
  $entity_info = $entity_manager->getDefinition($entity_type);

  // Look up the field's most used instance label.
  list($field_label, $other_labels) = field_views_field_label($field->entity_type, $field->name);

  // Build a list of additional fields for the sort and filter handlers.
  $additional_fields = array(
    $field->name . '_numerator',
    $field->name . '_denominator',
  );

  // Iterate through the results.
  foreach ($result as $table_alias => &$data) {

    // Determine the field name.
    $field_name = $field->name;
    if (strpos($table_alias, $entity_info['revision_table']) !== FALSE) {
      $field_name .= '-revision_id';
    }

    // Override the field handler so that we can provide our own custom
    // click sort method (that uses the fraction's decimal equivalent).
    $data[$field_name]['field']['id'] = 'fraction';

    // Make the field click-sortable.
    $data[$field_name]['field']['click sortable'] = TRUE;

    // Create a new decimal column with custom sort and filter handlers.
    $column_name = $field->name . '_decimal';
    $data[$column_name] = array(
      'group' => t($data[$field_name]['group']),
      'title' => t($field_label . ' (decimal)'),
      'title short' => t($field_label . ':decimal'),
      'help' => t('Decimal equivalent of Fraction field for sorting and filtering. ' . $data[$field_name]['help']),
      'sort' => array(
        'id' => 'fraction',
        'additional fields' => $additional_fields,
      ),
      'filter' => array(
        'id' => 'fraction',
        'additional fields' => $additional_fields,
      )
    );
  }
}
