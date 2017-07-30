<?php

function bps_directories ()
{
	static $dirs = array ();

	if (count ($dirs))  return $dirs;

	if (function_exists ('bp_core_get_directory_page_ids'))
	{
		$bp_pages = bp_core_get_directory_page_ids ();
		if (isset ($bp_pages['members']))
		{
			$members = $bp_pages['members'];
// echo "-- debug: bp_core_get_directory_page_ids returns $members --\n";
			$members = bps_wpml_id ($members);
			$dirs[$members] = new stdClass;
			$dirs[$members]->label = get_the_title ($members);
			$dirs[$members]->link = parse_url (get_page_link ($members), PHP_URL_PATH);

			if (function_exists ('bp_get_member_types'))
			{
				$member_types = bp_get_member_types (array (), 'objects');
				foreach ($member_types as $type)  if ($type->has_directory == 1)
				{
					$dirs[$type->name] = new stdClass;
					$dirs[$type->name]->label = $dirs[$members]->label. ' - '. $type->labels['name'];
					$dirs[$type->name]->link = parse_url (bp_get_member_type_directory_permalink ($type->name), PHP_URL_PATH);
				}
			}
		}
	}

	if (!shortcode_exists ('bps_directory'))  return $dirs;

	$pages = get_pages ();
	foreach ($pages as $page)  if (has_shortcode ($page->post_content, 'bps_directory'))
	{
		$dirs[$page->ID] = new stdClass;
		$dirs[$page->ID]->label = $page->post_title;
		$dirs[$page->ID]->link = parse_url (get_page_link ($page->ID), PHP_URL_PATH);
	}

	return $dirs;
}

add_action ('wp_enqueue_scripts', 'bps_clear_directory', 1);
function bps_clear_directory ()
{
	global $bp;

	$dirs = bps_directories ();
	$current = parse_url ($_SERVER['REQUEST_URI'], PHP_URL_PATH);

	foreach ($dirs as $dir)  if ($dir->link == $current)
	{
		add_filter ('bp_directory_members_search_form', 'bps_idem');

		wp_enqueue_script ('bps-directory', plugins_url ('bps-directory.js', __FILE__), array ('bp-jquery-cookie'), BPS_VERSION);
		$_COOKIE['bp-members-scope'] = 'all';
		unset ($_COOKIE['bp-members-filter']);
		break;
	}
}

function bps_idem ($text) { return $text; }

add_shortcode ('bps_directory', 'bps_show_directory');
function bps_show_directory ($attr, $content)
{
	ob_start ();

	if (!function_exists ('bp_has_profile'))
	{
		printf ('<p class="bps_error">'. __('%s: The BuddyPress Extended Profiles component is not active.', 'bp-profile-search'). '</p>',
			'<strong>BP Profile Search '. BPS_VERSION. '</strong>');
	}
	else
	{
		$template = isset ($attr['template'])? $attr['template']: 'members/index';
		if (isset ($attr['order_by']))  bps_set_sort_options ($attr['order_by']);

		$found = bp_get_template_part ($template);
		if (!$found)  printf ('<p class="bps_error">'. __('%s: The directory template "%s" was not found.', 'bp-profile-search'). '</p>',
			'<strong>BP Profile Search '. BPS_VERSION. '</strong>', $template);
	}

	return ob_get_clean ();
}

function bps_set_sort_options ($options)
{
	global $bps_sort_options;

	$bps_sort_options = array ();
	list (, $fields) = bps_get_fields ();

	$options = explode (',', $options);
	foreach ($options as $option)
	{
		$option = trim (preg_replace ('/\s+/', ' ', $option));
		$option = explode (' ', $option);

		$code = $option[0];
		$order = isset ($option[1])? $option[1]: 'asc';

		if (!isset ($fields[$code]->sort_directory) ||
			!is_callable ($fields[$code]->sort_directory) ||
			!in_array ($order, array ('asc', 'desc', 'both')))  continue;

		if ($order == 'asc')
		{
			$bps_sort_options[$code] = $fields[$code]->name;
		}
		else if ($order == 'desc')
		{
			$bps_sort_options['-'. $code] = $fields[$code]->name;
		}
		else if ($order == 'both')
		{
			$bps_sort_options[$code] = $fields[$code]->name. " &#x21E1;";
			$bps_sort_options['-'. $code] = $fields[$code]->name. " &#x21E3;";
		}
	}

	add_action ('bp_members_directory_order_options', 'bps_display_sort_options');
}

function bps_display_sort_options ()
{
	global $bps_sort_options;

	$version = BPS_VERSION;
	echo "\n<!-- BP Profile Search $version -->\n";

	$sort_options = apply_filters ('bps_sort_options', $bps_sort_options);
	foreach ($sort_options as $code => $name)
	{
?>
		<option value='<?php echo esc_attr($code); ?>'><?php echo esc_html($name); ?></option>
<?php
	}

	echo "\n<!-- BP Profile Search - end -->\n";
}

add_filter ('bp_user_query_uid_clauses', 'bps_uid_clauses', 99, 2);
function bps_uid_clauses ($sql, $object)
{
	list (, $fields) = bps_get_fields ();

	$code = $object->query_vars['type']; 
	$order = 'ASC';
	if ($code[0] == '-')
	{
		$code = substr ($code, 1);
		$order = 'DESC';
	}

	if (isset ($fields[$code]->sort_directory) && is_callable ($fields[$code]->sort_directory))
	{
		$f = $fields[$code];
		$sql = call_user_func ($f->sort_directory, $sql, $object, $f, $order);
		add_action ('bp_directory_members_item', 'bps_directory_members_item');
	}

	return $sql;
}

function bps_directory_members_item ()
{
	global $members_template;

	list (, $fields) = bps_get_fields ();

	$code = $members_template->type;
	if ($code[0] == '-')  $code = substr ($code, 1);

	if (!isset ($fields[$code]->get_value) || !is_callable ($fields[$code]->get_value))  return;

	$f = $fields[$code];
	$name = $f->name;
	$value = call_user_func ($f->get_value, $f);
?>
	<div class="item-meta"><span class="activity"><?php echo esc_html("$name: $value"); ?></span></div>
<?php
}
