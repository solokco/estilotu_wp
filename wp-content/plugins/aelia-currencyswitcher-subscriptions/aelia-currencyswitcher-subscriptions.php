<?php if(!defined('ABSPATH')) exit; // Exit if accessed directly
/*
Plugin Name: WooCommerce Currency Switcher - Subscriptions Integration
Plugin URI: https://aelia.co/
Description: Subscriptions integration for Aelia Currency Switcher for WooCommerce
Author: Aelia
Author URI: https://aelia.co
Version: 1.3.7.170607
Text Domain: wc-aelia-cs-subscriptions
Domain Path: /languages
*/

require_once(dirname(__FILE__) . '/src/lib/classes/install/aelia-wc-cs-subscriptions-requirementscheck.php');
// If requirements are not met, deactivate the plugin
if(Aelia_WC_CS_Subscriptions_RequirementsChecks::factory()->check_requirements()) {
	require_once dirname(__FILE__) . '/src/plugin-main.php';

	// Check for plugin updates (only when in Admin pages)
	function wc_aelia_cs_subscriptions_check_for_updates() {
		$GLOBALS['wc-aelia-cs-subscriptions']->check_for_updates(__FILE__, 'aelia-currencyswitcher-subscriptions');
	}
	add_action('admin_init', 'wc_aelia_cs_subscriptions_check_for_updates');
}
