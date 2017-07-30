<?php
/**
 * Plugin Name: WooCommerce MercadoPago
 * Plugin URI: https://github.com/claudiosmweb/woocommerce-mercadopago
 * Description: MercadoPago gateway for Woocommerce.
 * Author: Claudio Sanches
 * Author URI: http://claudiosmweb.com/
 * Version: 2.0.9
 * License: GPLv2 or later
 * Text Domain: woocommerce-mercadopago
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WC_MercadoPago' ) ) :

/**
 * WooCommerce MercadoPago main class.
 */
class WC_MercadoPago {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	const VERSION = '2.0.9';

	/**
	 * Instance of this class.
	 *
	 * @var object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin.
	 */
	private function __construct() {
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Checks with WooCommerce is installed.
		if ( class_exists( 'WC_Payment_Gateway' ) ) {
			// Include the WC_MercadoPago_Gateway class.
			include_once 'includes/class-wc-mercadopago-gateway.php';

			add_filter( 'woocommerce_payment_gateways', array( $this, 'add_gateway' ) );
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'woocommerce_missing_notice' ) );
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return object A single instance of this class.
	 */
	public static function get_instance() {
		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Load the plugin text domain for translation.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-mercadopago', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Add the gateway to WooCommerce.
	 *
	 * @param   array $methods WooCommerce payment methods.
	 *
	 * @return  array          Payment methods with MercadoPago.
	 */
	public function add_gateway( $methods ) {
		$methods[] = 'WC_MercadoPago_Gateway';

		return $methods;
	}

	/**
	 * WooCommerce fallback notice.
	 *
	 * @return  string
	 */
	public function woocommerce_missing_notice() {
		echo '<div class="error"><p>' . sprintf( __( 'WooCommerce MercadoPago Gateway depends on the last version of %s to work!', 'woocommerce-mercadopago' ), '<a href="http://wordpress.org/plugins/woocommerce/">' . __( 'WooCommerce', 'woocommerce-mercadopago' ) . '</a>' ) . '</p></div>';
	}

	/**
	 * Backwards compatibility with version prior to 2.1.
	 *
	 * @return object Returns the main instance of WooCommerce class.
	 */
	public static function woocommerce_instance() {
		if ( function_exists( 'WC' ) ) {
			return WC();
		} else {
			global $woocommerce;
			return $woocommerce;
		}
	}

	/**
	 * Action links.
	 *
	 * @param array $links Action links.
	 *
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$plugin_links = array();

		if ( defined( 'WC_VERSION' ) && version_compare( WC_VERSION, '2.1', '>=' ) ) {
			$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=mercadopago' ) ) . '">' . __( 'Settings', 'woocommerce-mercadopago' ) . '</a>';
		} else {
			$plugin_links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_mercadopago_gateway' ) ) . '">' . __( 'Settings', 'woocommerce-mercadopago' ) . '</a>';
		}

		return array_merge( $plugin_links, $links );
	}
}

add_action( 'plugins_loaded', array( 'WC_MercadoPago', 'get_instance' ) );

/**
 * Adds support to legacy IPN.
 */
function wcmercadopago_legacy_ipn() {
	if ( isset( $_GET['topic'] ) && ! isset( $_GET['wc-api'] ) ) {
		if ( isset( $_SERVER['REQUEST_URI'] ) && false === strpos( $_SERVER['REQUEST_URI'], 'wc-api' ) ) {
			$woocommerce = WC_MercadoPago::woocommerce_instance();
			$woocommerce->payment_gateways();

			do_action( 'woocommerce_api_wc_mercadopago_gateway' );
		}
	}
}

add_action( 'init', 'wcmercadopago_legacy_ipn' );

endif;
