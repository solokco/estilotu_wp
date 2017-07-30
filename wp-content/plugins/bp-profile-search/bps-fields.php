<?php

function bps_get_fields ()
{
	static $groups = array ();
	static $fields = array ();

	if (count ($groups))  return array ($groups, $fields);

	$field_list = apply_filters ('bps_fields', array ());
	foreach ($field_list as $f)
	{
		$f = apply_filters ('bps_field_setup_data', $f);  // to be removed
		do_action ('bps_field_setup', $f);
		$groups[$f->group][] = array ('id' => $f->code, 'name' => $f->name);
		$fields[$f->code] = $f;
	}

	$request = bps_get_request ();
	bps_parse_request ($fields, $request);

	return array ($groups, $fields);
}

function bps_parse_request ($fields, $request)
{
	$j = 1;
	foreach ($request as $key => $value)
	{
		if ($value === '')  continue;

		$k = bps_match_key ($key, $fields);
		if ($k === false)  continue;

		$f = $fields[$k];
		$filter = ($key == $f->code)? '': substr ($key, strlen ($f->code) + 1);
		if (!bps_validate_filter ($filter, $f))  continue;

		switch ($filter)
		{
		default:
			$f->filter = $filter;
			$f->value = $value;
			$f->values = (array)$f->value;
			$f->min = $f->max = '';
			break;
		case 'range_min':
		case 'age_range_min':
			if (!is_numeric ($value))  break;
			$f->filter = rtrim ($filter, '_min');
			$f->min = $value;
			if ($f->type == 'datebox')  $f->min = (int)$f->min;
			if ($f->type == 'birthdate')  $f->min = (int)$f->min;
			if (!isset ($f->max))  $f->max = '';
			$f->value = '';
			$f->values = array ();
			break;
		case 'range_max':
		case 'age_range_max':
			if (!is_numeric ($value))  break;
			$f->filter = rtrim ($filter, '_max');
			$f->max = $value;
			if ($f->type == 'datebox')  $f->max = (int)$f->max;
			if ($f->type == 'birthdate')  $f->max = (int)$f->max;
			if (!isset ($f->min))  $f->min = '';
			$f->value = '';
			$f->values = array ();
			break;
		case 'label':
			$f->label = stripslashes ($value);
			break;
		}

		if (!isset ($f->order))  $f->order = $j++;
	}

	return true;
}

function bps_match_key ($key, $fields)
{
	foreach ($fields as $k => $f)
		if ($key == $f->code || strpos ($key, $f->code. '_') === 0)  return $k;

	return false;
}

function bps_validate_filter ($filter, $f)
{
	if ($filter == 'range_min' || $filter == 'range_max')  $filter = 'range';
	if ($filter == 'age_range_min' || $filter == 'age_range_max')  $filter = 'age_range';
	if ($filter == 'label')  return true;

	return isset ($f->filters[$filter]);
}

function bps_set_hidden_field ($code, $value)
{
	$new = new stdClass;
	$new->display = 'hidden';
	$new->code = $code;
	$new->value = $value;

	return $new;
}

function bps_sort_fields ($a, $b)
{
	return ($a->order <= $b->order)? -1: 1;
}
