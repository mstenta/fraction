<?php

/**
 * @file
 * Views hooks.
 */

/**
 * Implements hook_field_views_data_alter().
 */
function fraction_field_views_data_alter(&$data, $field) {

  // Only operate on fields defined by the Fraction module.
  if ($field->getTypeProvider() != 'fraction') {
    return;
  }

  // Get the field name.
  $field_name = $field->getName();

  // Grab information about the entity type tables.
  $entity_manager = \Drupal::entityManager();
  $entity_type = $field->getTargetEntityTypeId();
  $entity_info = $entity_manager->getDefinition($entity_type);

  // Look up the field's most used instance label.
  list($field_label, $other_labels) = views_entity_field_label($entity_type, $field_name);

  // Build a list of additional fields for the sort and filter handlers.
  $additional_fields = array(
    'numerator' => $field_name . '_numerator',
    'denominator' => $field_name . '_denominator',
  );

  // Iterate through the results.
  foreach ($data as $table_alias => &$definition) {

    // Determine the field name.
    if (strpos($table_alias, $entity_info->getRevisionTable()) !== FALSE) {
      $field_name .= '-revision_id';
    }

    // Override the field handler so that we can provide our own custom
    // click sort method (that uses the fraction's decimal equivalent).
    $definition[$field_name]['field']['id'] = 'fraction_field';

    // Make the field click-sortable.
    $definition[$field_name]['field']['click sortable'] = TRUE;

    // Create a new decimal column with custom sort and filter handlers.
    $column_name = $field_name . '_decimal';
    $definition[$column_name] = array(
      'group' => t($definition[$field_name]['group']->getUntranslatedString()),
      'title' => t($field_label . ' (decimal)'),
      'title short' => t($field_label . ':decimal'),
      'help' => t('Decimal equivalent of Fraction field for sorting and filtering. ' . $definition[$field_name]['help']),
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
