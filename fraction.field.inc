<?php

/**
 * @file
 * Fraction Field API functions
 */

/**
 * Implements of hook_field_info().
 */
function fraction_field_info() {
  return array(
    'fraction' => array(
      'label' => t('Fraction'),
      'description' => t('This field stores a decimal in fraction form (with a numerator and denominator) for maximum precision.'),
      'settings' => array(),
      'instance_settings' => array('min' => '', 'max' => '', 'prefix' => '', 'suffix' => ''),
      'default_widget' => 'fraction_default',
      'default_formatter' => 'fraction_default',
      // Entity API integration.
      'property_type' => 'field_item_fraction',
      'property_callbacks' => array('fraction_property_info_callback'),
    ),
  );
}

/**
 * Callback to alter the property info of fraction fields.
 *
 * @see fraction_field_info().
 */
function fraction_property_info_callback(&$info, $entity_type, $field, $instance, $field_type) {
  $name = $field['field_name'];
  $property = &$info[$entity_type]['bundles'][$instance['bundle']]['properties'][$name];
  $property['type'] = ($field['cardinality'] != 1) ? 'list<field_item_fraction>' : 'field_item_fraction';
  $property['getter callback'] = 'entity_metadata_field_verbatim_get';
  $property['setter callback'] = 'entity_metadata_field_verbatim_set';
  $property['auto creation'] = 'fraction_default_values';
  $property['property info'] = fraction_data_property_info();
  unset($property['query callback']);
}

function fraction_default_values() {
  return array(
    'numerator' => 0,
    'denominator' => 100,
  );
}

/**
 * Defines info for the properties of the fraction field data structure.
 */
function fraction_data_property_info($name = NULL) {
  // Build an array of basic property information for the fraction field.
  $properties = array(
    'numerator' => array(
      'label' => t('Numerator'),
    ),
    'denominator' => array(
      'label' => t('Denominator'),
    ),
  );
  foreach ($properties as &$property) {
    $property += array(
      'type' => 'integer',
      'description' => !empty($name) ? t('!label of field %name', array('!label' => $property['label'], '%name' => $name)) : '',
      'getter callback' => 'entity_property_verbatim_get',
      'setter callback' => 'entity_property_verbatim_set',
    );
  }
  return $properties;
}

/**
 * Implements hook_field_instance_settings_form().
 */
function fraction_field_instance_settings_form($field, $instance) {
  $settings = $instance['settings'];

  $form['min'] = array(
    '#type' => 'textfield',
    '#title' => t('Minimum'),
    '#default_value' => $settings['min'],
    '#description' => t('The minimum value that should be allowed in this field. Leave blank for no minimum.'),
    '#element_validate' => array('element_validate_number'),
  );
  $form['max'] = array(
    '#type' => 'textfield',
    '#title' => t('Maximum'),
    '#default_value' => $settings['max'],
    '#description' => t('The maximum value that should be allowed in this field. Leave blank for no maximum.'),
    '#element_validate' => array('element_validate_number'),
  );
  $form['prefix'] = array(
    '#type' => 'textfield',
    '#title' => t('Prefix'),
    '#default_value' => $settings['prefix'],
    '#size' => 60,
    '#description' => t("Define a string that should be prefixed to the value, like '$ ' or '&euro; '. Leave blank for none. Separate singular and plural values with a pipe ('pound|pounds')."),
  );
  $form['suffix'] = array(
    '#type' => 'textfield',
    '#title' => t('Suffix'),
    '#default_value' => $settings['suffix'],
    '#size' => 60,
    '#description' => t("Define a string that should be suffixed to the value, like ' m', ' kb/s'. Leave blank for none. Separate singular and plural values with a pipe ('pound|pounds')."),
  );

  return $form;
}

/**
 * Implements hook_field_validate().
 *
 * Possible error codes:
 * - 'fraction_min': The value is less than the allowed minimum value.
 * - 'fraction_max': The value is greater than the allowed maximum value.
 */
function fraction_field_validate($entity_type, $entity, $field, $instance, $langcode, $items, &$errors) {
  foreach ($items as $delta => $item) {
    $value = '';
    if (!empty($item['decimal']) && is_numeric($item['decimal'])) {
      $value = $item['decimal'];
    }
    if (!empty($item['numerator']) && is_numeric($item['numerator']) && !empty($item['denominator']) && is_numeric($item['denominator'])) {
      $fraction = fraction($item['numerator'], $item['denominator']);
      $value = $fraction->toDecimal(0, TRUE);
    }
    if ($value != '') {
      if (is_numeric($instance['settings']['min']) && $value < $instance['settings']['min']) {
        $errors[$field['field_name']][$langcode][$delta][] = array(
          'error' => 'fraction_min',
          'message' => t('%name: the value may be no less than %min.', array('%name' => $instance['label'], '%min' => $instance['settings']['min'])),
        );
      }
      if (is_numeric($instance['settings']['max']) && $value > $instance['settings']['max']) {
        $errors[$field['field_name']][$langcode][$delta][] = array(
          'error' => 'fraction_max',
          'message' => t('%name: the value may be no greater than %max.', array('%name' => $instance['label'], '%max' => $instance['settings']['max'])),
        );
      }
    }
  }
}

/**
 * Implements hook_field_is_empty().
 */
function fraction_field_is_empty($item, $field) {
  return empty($item['numerator']) || empty($item['denominator']);
}

/**
 * Implements of hook_field_info().
 */
function fraction_field_widget_info() {
  return array(
    'fraction_default' => array(
      'label' => t('Fraction'),
      'field types' => array('fraction'),
      'settings' => array(),
      'behaviors' => array(
        'multiple values' => FIELD_BEHAVIOR_DEFAULT,
        'default value' => FIELD_BEHAVIOR_DEFAULT,
      ),
    ),
    'fraction_decimal' => array(
      'label' => t('Decimal'),
      'field types' => array('fraction'),
      'settings' => array(
        'precision' => FRACTION_PRECISION_DEFAULT,
        'auto_precision' => TRUE,
      ),
      'behaviors' => array(
        'multiple values' => FIELD_BEHAVIOR_DEFAULT,
        'default value' => FIELD_BEHAVIOR_DEFAULT,
      ),
    ),
  );
}

/**
 * Implements hook_field_widget_settings_form().
 */
function fraction_field_widget_settings_form($field, $instance) {
  $form = array();
  $widget = $instance['widget'];
  $defaults = field_info_widget_settings($widget['type']);
  $settings = array_merge($defaults, $widget['settings']);

  if ($widget['type'] == 'fraction_decimal') {
    $form['precision'] = array(
      '#type' => 'textfield',
      '#title' => t('Precision'),
      '#description' => t('Specify the number of digits after the decimal place to display when converting the fraction to a decimal. When "Auto precision" is enabled, this value essentially becomes a minimum fallback precision.'),
      '#default_value' => $settings['precision'],
    );
    $form['auto_precision'] = array(
      '#type' => 'checkbox',
      '#title' => t('Auto precision'),
      '#description' => t('Automatically determine the maximum precision if the fraction has a base-10 denominator. For example, 1/100 would have a precision of 2, 1/1000 would have a precision of 3, etc.'),
      '#default_value' => $settings['auto_precision'],
    );
  }

  return $form;
}

/**
 * Implements hook_field_widget_form().
 */
function fraction_field_widget_form(&$form, &$form_state, $field, $instance, $langcode, $items, $delta, $element) {
  $numerator = isset($items[$delta]['numerator']) ? $items[$delta]['numerator'] : '';
  $denominator = isset($items[$delta]['denominator']) ? $items[$delta]['denominator'] : '';

  $settings = $instance['widget']['settings'];

  switch ($instance['widget']['type']) {

    // Default fraction widget: two textfields (numerator and denominator).
    case 'fraction_default':
      $element['#type'] = 'fieldset';
      $element['numerator'] = array(
        '#type' => 'textfield',
        '#title' => t('Numerator'),
        '#default_value' => $numerator,
        '#element_validate' => array('element_validate_integer'),
        '#size' => 15,
      );
      $element['denominator'] = array(
        '#type' => 'textfield',
        '#title' => t('Denominator'),
        '#default_value' => $denominator,
        '#element_validate' => array('element_validate_integer'),
        '#size' => 15,
      );
      break;

    // Decimal widget: a single textfield that accepts a decimal number.
    // The default value is converted to a decimal with the specified precision.
    case 'fraction_decimal':
      $default_value = '';
      if (!empty($items[$delta]) && !fraction_field_is_empty($items[$delta], $field)) {
        $auto_precision = !empty($settings['auto_precision']) ? TRUE : FALSE;
        $default_value = fraction($numerator, $denominator)->toDecimal($settings['precision'], $auto_precision);
      }
      $element['#element_validate'] = array('_fraction_decimal_widget_validate');
      $element['decimal'] = array(
        '#type' => 'textfield',
        '#title' => $element['#title'],
        '#description' => $element['#description'],
        '#default_value' => $default_value,
        '#element_validate' => array('element_validate_number'),
        '#size' => 15,
      );

      // Add prefix and suffix.
      if (!empty($instance['settings']['prefix'])) {
        $prefixes = explode('|', $instance['settings']['prefix']);
        $element['#field_prefix'] = field_filter_xss(array_pop($prefixes));
      }
      if (!empty($instance['settings']['suffix'])) {
        $suffixes = explode('|', $instance['settings']['suffix']);
        $element['#field_suffix'] = field_filter_xss(array_pop($suffixes));
      }
      break;
  }

  return $element;
}

/**
 * Validation function for the fraction_decimal widget to convert the decimal to a fraction.
 */
function _fraction_decimal_widget_validate($element, &$form_state) {

  // Search through the form values to find the current field value.
  $parents = $element['#parents'];
  $values = drupal_array_get_nested_value($form_state['values'], $parents);

  // Convert the value to a fraction.
  $fraction = fraction_from_decimal($values['decimal']);
  $values['numerator'] = $fraction->getNumerator();
  $values['denominator'] = $fraction->getDenominator();

  // Set the numerator and denominator values for the form.
  form_set_value($element, $values, $form_state);
}

/**
 * Implements hook_field_widget_error().
 */
function fraction_field_widget_error($element, $error, $form, &$form_state) {
  switch ($error['error']) {
    case 'fraction':
      form_error($element, $error['message']);
      break;
  }
}

/**
 * Implements of hook_field_info().
 */
function fraction_field_formatter_info() {
  return array(
    'fraction_default' => array(
      'label' => t('Fraction'),
      'field types' => array('fraction'),
      'settings' => array(
        'separator' => '/',
        'prefix_suffix' => TRUE,
      ),
    ),
    'fraction_decimal' => array(
      'label' => t('Decimal'),
      'field types' => array('fraction'),
      'settings' => array(
        'precision' => FRACTION_PRECISION_DEFAULT,
        'auto_precision' => TRUE,
        'prefix_suffix' => TRUE,
      ),
    ),
  );
}

/**
 * Implements hook_field_formatter_settings_summary().
 */
function fraction_field_formatter_settings_summary($field, $instance, $view_mode) {
  $display = $instance['display'][$view_mode];
  $settings = $display['settings'];

  $summary = array();

  if ($display['type'] == 'fraction_default') {
    $summary[] = t('Separator: @separator', array('@separator' => $settings['separator']));
  }

  elseif ($display['type'] == 'fraction_decimal') {
    $auto_precision = !empty($settings['auto_precision']) ? 'On' : 'Off';
    $summary[] = t('Precision: @precision', array('@precision' => $settings['precision']));
    $summary[] = t('Auto-precision: @auto_precision', array('@auto_precision' => $auto_precision));
  }

  if ($settings['prefix_suffix']) {
    $summary[] = t('Display with prefix and suffix.');
  }

  return implode('<br />', $summary);
}

/**
 * Implements hook_field_formatter_settings_form().
 */
function fraction_field_formatter_settings_form($field, $instance, $view_mode, $form, &$form_state) {
  $display = $instance['display'][$view_mode];
  $settings = $display['settings'];

  $element = array();

  if ($display['type'] == 'fraction_default') {

    // Numerator and denominator separator.
    $element['separator'] = array(
      '#type' => 'textfield',
      '#title' => t('Separator'),
      '#description' => t('Specify the separator to display between the numerator and denominator.'),
      '#default_value' => $settings['separator'],
      '#required' => TRUE,
    );
  }

  elseif ($display['type'] == 'fraction_decimal') {

    // Decimal precision.
    $element['precision'] = array(
      '#type' => 'textfield',
      '#title' => t('Decimal precision'),
      '#description' => t('Specify the number of digits after the decimal place to display. When "Auto precision" is enabled, this value essentially becomes a minimum fallback precision.'),
      '#default_value' => $settings['precision'],
      '#required' => TRUE,
      '#element_validate' => array('element_validate_integer'),
    );

    // Auto precision.
    $element['auto_precision'] = array(
      '#type' => 'checkbox',
      '#title' => t('Auto precision'),
      '#description' => t('Automatically determine the maximum precision if the fraction has a base-10 denominator. For example, 1/100 would have a precision of 2, 1/1000 would have a precision of 3, etc.'),
      '#default_value' => $settings['auto_precision'],
    );
  }

  $element['prefix_suffix'] = array(
    '#type' => 'checkbox',
    '#title' => t('Display prefix and suffix.'),
    '#default_value' => $settings['prefix_suffix'],
  );

  return $element;
}

/**
 * Implements hook_field_formatter_view().
 */
function fraction_field_formatter_view($entity_type, $entity, $field, $instance, $langcode, $items, $display) {
  $settings = $display['settings'];
  $element = array();

  // Iterate through the items.
  foreach ($items as $delta => $item) {

    // Start with empty output.
    $output = '';

    // Merge the item with default values, in case it's empty.
    $defaults = array(
      'numerator' => 0,
      'denominator' => 1,
    );
    $item = array_merge($defaults, $item);

    // Build output based on display type.
    switch ($display['type']) {

      // This formatter simply outputs the field as a fraction ([numerator]/[denominator]).
      case 'fraction_default':
        $output = fraction($item['numerator'], $item['denominator'])->toString(field_filter_xss($settings['separator']));
        break;

      // This formatter outputs the field as a decimal value with a fixed or automatic precision.
      case 'fraction_decimal':
        $auto_precision = !empty($settings['auto_precision']) ? TRUE : FALSE;
        $output = fraction($item['numerator'], $item['denominator'])->toDecimal($settings['precision'], $auto_precision);
        break;
    }

    // Add prefix and suffix, if desired.
    if ($settings['prefix_suffix']) {
      $prefixes = isset($instance['settings']['prefix']) ? array_map('field_filter_xss', explode('|', $instance['settings']['prefix'])) : array('');
      $suffixes = isset($instance['settings']['suffix']) ? array_map('field_filter_xss', explode('|', $instance['settings']['suffix'])) : array('');
      $prefix = (count($prefixes) > 1) ? format_plural($item['value'], $prefixes[0], $prefixes[1]) : $prefixes[0];
      $suffix = (count($suffixes) > 1) ? format_plural($item['value'], $suffixes[0], $suffixes[1]) : $suffixes[0];
      $output = $prefix . $output . $suffix;
    }

    // Create the markup element.
    $element[$delta] = array(
      '#type' => 'markup',
      '#markup' => $output,
    );
  }

  return $element;
}

/**
 * Implements hook_field_load().
 */
function fraction_field_load($entity_type, $entities, $field, $instances, $langcode, &$items, $age) {

  // Iterate through entities and their items.
  foreach ($entities as $id => $entity) {
    foreach ($items[$id] as $delta => $item) {

      // Load the numerator and denominator values.
      $numerator = $items[$id][$delta]['numerator'];
      $denominator = $items[$id][$delta]['denominator'];

      // Create a new Fraction object and add it to the field.
      $items[$id][$delta]['fraction'] = fraction($numerator, $denominator);
    }
  }
}
