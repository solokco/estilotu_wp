<?php

function bps_escaped_form_data ()
{
	list ($form, $location) = bps_template_args ();

	$meta = bps_meta ($form);
	list ($x, $fields) = bps_get_fields ();

	$F = new stdClass;
	$F->id = $form;
	$F->location = $location;
	$F->header = bps_wpml ($form, '-', 'header', $meta['header']);
	$F->toggle = ($meta['toggle'] == 'Enabled');
	$F->toggle_text = bps_wpml ($form, '-', 'toggle form', $meta['button']);

	$dirs = bps_directories ();
	$F->action = $location == 'directory'?
		parse_url ($_SERVER['REQUEST_URI'], PHP_URL_PATH):
		$dirs[bps_wpml_id ($meta['action'])]->link;

	if (defined ('DOING_AJAX'))
		$F->action = parse_url ($_SERVER['HTTP_REFERER'], PHP_URL_PATH);

	$F->method = $meta['method'];
	$F->fields = array ();

	foreach ($meta['field_code'] as $k => $id)
	{
		if (empty ($fields[$id]))  continue;

		$f = clone $fields[$id];
		$mode = $meta['field_mode'][$k];
		if ($mode == 'range' || $mode == 'age_range')  { $f->display = 'range'; $f->type = bps_displayXsearch_form ($f); }

		$f->label = $f->name;
		$custom_label = bps_wpml ($form, $id, 'label', $meta['field_label'][$k]);
		if (!empty ($custom_label))
		{
			$f->label = $custom_label;
			$F->fields[] = bps_set_hidden_field ($f->code. '_label', $f->label);
		}

		$custom_desc = bps_wpml ($form, $id, 'comment', $meta['field_desc'][$k]);
		if ($custom_desc == '-')
			$f->description = '';
		else if (!empty ($custom_desc))
			$f->description = $custom_desc;

		if ($form != bps_active_form () || !isset ($f->filter))
		{
			$f->min = $f->max = $f->value = '';
			$f->values = array ();
		}

		$f = apply_filters ('bps_field_data_for_filters', $f);	// to be removed
		$f = apply_filters ('bps_field_data_for_search_form', $f);	// to be removed
		do_action ('bps_field_before_search_form', $f);

		if ($mode != '')  $f->code .= '_'. $mode;
		$F->fields[] = $f;
	}

	$F->fields[] = bps_set_hidden_field ('bp_profile_search', $form);

	$F = apply_filters ('bps_search_form_data', $F);  // to be removed
	do_action ('bps_before_search_form', $F);

	$F->toggle_text = esc_attr ($F->toggle_text);
	foreach ($F->fields as $f)
	{
		if (!is_array ($f->value))  $f->value = esc_attr (stripslashes ($f->value));
		if ($f->display == 'hidden')  continue;

		$f->label = esc_attr ($f->label);
		$f->description = esc_attr ($f->description);
		foreach ($f->values as $k => $value)  $f->values[$k] = esc_attr (stripslashes ($value));
		$options = array ();
		foreach ($f->options as $key => $label)  $options[esc_attr ($key)] = esc_attr ($label);
		$f->options = $options;
	}

	return $F;
}

function bps_escaped_filters_data ()
{
	$F = new stdClass;
	$F->action = parse_url ($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$F->fields = array ();

	list ($x, $fields) = bps_get_fields ();
	foreach ($fields as $field)
	{
		if (!isset ($field->filter))  continue;

		$f = clone $field;
		if ($f->filter == 'range' || $f->filter == 'age_range')  $f->display = 'range';

		if (empty ($f->label))  $f->label = $f->name;

		$f = apply_filters ('bps_field_data_for_filters', $f);	// to be removed
		$f = apply_filters ('bps_field_data_for_search_form', $f);	// to be removed
		do_action ('bps_field_before_filters', $f);
		$F->fields[] = $f;
	}

	$F = apply_filters ('bps_filters_data', $F);  // to be removed
	do_action ('bps_before_filters', $F);
	usort ($F->fields, 'bps_sort_fields');

	foreach ($F->fields as $f)
	{
		$f->label = esc_attr ($f->label);
		if (!is_array ($f->value))  $f->value = esc_attr (stripslashes ($f->value));
		foreach ($f->values as $k => $value)  $f->values[$k] = stripslashes ($value);

		foreach ($f->options as $key => $label)  $f->options[$key] = esc_attr ($label);
	}

	return $F;
}
