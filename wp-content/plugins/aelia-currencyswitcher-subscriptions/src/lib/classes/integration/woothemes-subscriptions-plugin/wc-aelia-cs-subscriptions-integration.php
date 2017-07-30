<?php
namespace Aelia\WC\CurrencySwitcher\Subscriptions;
if(!defined('ABSPATH')) exit; // Exit if accessed directly

use \WC_Aelia_CurrencySwitcher;
use \WC_Aelia_CurrencyPrices_Manager;
use \WC_Subscriptions_Product;
use \WC_Product;
use \WC_Product_Subscription;
use \WC_Product_Subscription_Variation;
use \WC_Subscriptions_Cart;

/**
 * Implements support for WooThemes Subscriptions plugin.
 */
class Subscriptions_Integration {
	const FIELD_SIGNUP_FEE_CURRENCY_PRICES = '_subscription_signup_fee_currency_prices';
	const FIELD_VARIATION_SIGNUP_FEE_CURRENCY_PRICES = '_subscription_variation_signup_fee_currency_prices';

	const FIELD_REGULAR_CURRENCY_PRICES = '_subscription_variation_regular_currency_prices';
	const FIELD_SALE_CURRENCY_PRICES = '_subscription_variation_sale_currency_prices';
	const FIELD_VARIATION_REGULAR_CURRENCY_PRICES = '_subscription_variation_regular_currency_prices';
	const FIELD_VARIATION_SALE_CURRENCY_PRICES = '_subscription_variation_sale_currency_prices';

	// @var WC_Aelia_CurrencyPrices_Manager The object that handles currency prices for the products.
	private $currencyprices_manager;

	// @var Shop's base currency. Used for caching.
	protected static $_base_currency;

	/**
	 * Logs a message.
	 *
	 * @param string message The message to log.
	 * @param bool debug Indicates if the message is for debugging. Debug messages
	 * are not saved if the "debug mode" flag is turned off.
	 * @since 1.3.0.160617
	 */
	public function log($message, $debug = true) {
		return WC_Aelia_CS_Subscriptions::instance()->log($message, $debug);
	}

	/**
	 * Fix for Subscriptions bug #1040.
	 * The currency to be used at checkout. Used to override the active currency
	 * when paying for a resubscription.
	 * @var string
	 * @since 1.2.13.151208
	 * @link https://github.com/Prospress/woocommerce-subscriptions/issues/1040
	 */
	protected $checkout_currency = '';

	/**
	 * Returns the instance of the Currency Switcher plugin.
	 *
	 * @return WC_Aelia_CurrencySwitcher
	 */
	protected function currency_switcher() {
		return WC_Aelia_CurrencySwitcher::instance();
	}

	/**
	 * Returns the instance of the settings controller loaded by the plugin.
	 *
	 * @return WC_Aelia_CurrencySwitcher_Settings
	 */
	protected function settings_controller() {
		return WC_Aelia_CurrencySwitcher::settings();
	}

	/**
	 * Returns the instance of the currency prices manager used by the Currency
	 * Switcher plugin.
	 *
	 * @return WC_Aelia_CurrencySwitcher_Settings
	 */
	protected function currencyprices_manager() {
		return WC_Aelia_CurrencyPrices_Manager::Instance();
	}

	/**
	 * Convenience method. Returns an array of the Enabled Currencies.
	 *
	 * @return array
	 */
	protected function enabled_currencies() {
		return WC_Aelia_CurrencySwitcher::settings()->get_enabled_currencies();
	}

	/**
	 * Returns an array of Currency => Price values containing the signup fees
	 * of a subscription, in each currency.
	 *
	 * @param int post_id The ID of the Post (subscription).
	 * @return array
	 */
	public static function get_subscription_signup_prices($post_id) {
		return WC_Aelia_CurrencyPrices_Manager::Instance()->get_product_currency_prices($post_id,
																																										self::FIELD_SIGNUP_FEE_CURRENCY_PRICES);
	}

	/**
	 * Returns an array of Currency => Price values containing the signup fees
	 * of a subscription variation, in each currency.
	 *
	 * @param int post_id The ID of the Post (subscription).
	 * @return array
	 */
	public static function get_subscription_variation_signup_prices($post_id) {
		return WC_Aelia_CurrencyPrices_Manager::Instance()->get_product_currency_prices($post_id,
																																										self::FIELD_VARIATION_SIGNUP_FEE_CURRENCY_PRICES);
	}

	/**
	 * Returns an array of Currency => Price values containing the Regular
	 * Currency Prices of a subscription.
	 *
	 * @param int post_id The ID of the Post (subscription).
	 * @return array
	 */
	public function get_subscription_regular_prices($post_id) {
		return $this->currencyprices_manager()->get_product_currency_prices($post_id,
																																				WC_Aelia_CurrencyPrices_Manager::FIELD_REGULAR_CURRENCY_PRICES);
	}

	/**
	 * Returns an array of Currency => Price values containing the Sale Currency
	 * Prices of a subscription.
	 *
	 * @param int post_id The ID of the Post (subscription).
	 * @return array
	 */
	public function get_subscription_sale_prices($post_id) {
		return $this->currencyprices_manager()->get_product_currency_prices($post_id,
																																				WC_Aelia_CurrencyPrices_Manager::FIELD_SALE_CURRENCY_PRICES);
	}

	/**
	 * Returns the value of the meta from a subscription product.
	 *
	 * @param WC_Product product
	 * @param string meta_key
	 * @param string default_value
	 * @since 1.3.1.170405
	 */
	protected function get_subscription_meta($product, $meta_key, $default_value = '') {
		return WC_Subscriptions_Product::get_meta_data($product, $meta_key, $default_value, 'use_default_value');
	}

	/**
	 * Convenience method. Returns WooCommerce base currency.
	 *
	 * @return string
	 */
	public function base_currency() {
		if(empty(self::$_base_currency)) {
			self::$_base_currency = WC_Aelia_CurrencySwitcher::settings()->base_currency();
		}
		return self::$_base_currency;
	}

	/**
	 * Converts a subscription prices to the specific currency, taking
	 * into account manually entered prices.
	 *
	 * @param WC_Product product The subscription whose prices should
	 * be converted.
	 * @param string currency A currency code.
	 * @param array product_regular_prices_in_currency An array of manually entered
	 * product prices (one for each currency).
	 * @param array product_sale_prices_in_currency An array of manually entered
	 * product prices (one for each currency).
	 * @return WC_Product
	 */
	protected function convert_to_currency(WC_Product $product, $currency,
																				 array $product_regular_prices_in_currency,
																				 array $product_sale_prices_in_currency,
																				 array $product_signup_prices_in_currency) {
		// @since 1.3.1.170405
		$product_id = aelia_wc_version_is('>=', '3.0') ? $product->get_id() : $product->id;
		$product_base_currency = $this->currencyprices_manager()->get_product_base_currency($product_id);
		$shop_base_currency = $this->base_currency();

		// If subscription price and signup fee in shop's base currency were not passed,
		// retrieve them using the Subscription plugin's function. The sale price uses
		// a standard field, and it's always passed by WooCommerce
		// @since 1.3.5.170425
		if(!isset($product_regular_prices_in_currency[$shop_base_currency])) {
			$product_regular_prices_in_currency[$shop_base_currency] = $this->get_subscription_meta($product, 'subscription_price');
		}
		if(!isset($product_signup_prices_in_currency[$shop_base_currency])) {
			$product_signup_prices_in_currency[$shop_base_currency] = $this->get_subscription_meta($product, 'subscription_sign_up_fee', 0);
		}

		// Take subscription price in the specific product base currency
		$base_subscription_price = isset($product_regular_prices_in_currency[$product_base_currency]) ? $product_regular_prices_in_currency[$product_base_currency] : null;
		// If a subscription price was not entered for the selected product base currency,
		// take the one in shop base currency
		if(!is_numeric($base_subscription_price)) {
			$base_subscription_price = isset($product_regular_prices_in_currency[$shop_base_currency]) ? $product_regular_prices_in_currency[$shop_base_currency] : null;

			// If a product doesn't have a price in the product-specific base currency,
			// then that base currency is not valid. In such case, shop's base currency
			// should be used instead
			$product_base_currency = $shop_base_currency;
		}

		// Take sale price in the specific product base currency
		$base_sale_price = isset($product_sale_prices_in_currency[$product_base_currency]) ? $product_sale_prices_in_currency[$product_base_currency] : null;
		// If a sale price was not entered for the selected product base currency,
		// take the one in shop base currency
		if(!is_numeric($base_sale_price)) {
			$base_sale_price = isset($product_sale_prices_in_currency[$shop_base_currency]) ? $product_sale_prices_in_currency[$shop_base_currency] : null;
		}

		// Take signup fee in the specific product base currency
		$base_subscription_sign_up_fee = isset($product_signup_prices_in_currency[$product_base_currency]) ? $product_signup_prices_in_currency[$product_base_currency] : null;
		// If a signup fee was not entered for the selected product base currency,
		// take the one in shop base currency
		if(!is_numeric($base_subscription_sign_up_fee)) {
			$base_subscription_sign_up_fee = isset($product_signup_prices_in_currency[$shop_base_currency]) ? $product_signup_prices_in_currency[$shop_base_currency] : null;
		}

		$product->regular_price = isset($product_regular_prices_in_currency[$currency]) ? $product_regular_prices_in_currency[$currency] : $this->currencyprices_manager()->convert_product_price_from_base($base_subscription_price, $currency, $product_base_currency, $product, 'regular_price');
		$product->sale_price = isset($product_sale_prices_in_currency[$currency]) ? $product_sale_prices_in_currency[$currency] : $this->currencyprices_manager()->convert_product_price_from_base($base_sale_price, $currency, $product_base_currency, $product, 'sale_price');
		$product->subscription_sign_up_fee = isset($product_signup_prices_in_currency[$currency]) ? $product_signup_prices_in_currency[$currency] : $this->currencyprices_manager()->convert_product_price_from_base($base_subscription_sign_up_fee, $currency, $product_base_currency, $product, 'signup_fee');

		// Debug
		//var_dump(
		//	"BASE",
		//	$base_subscription_price,
		//	$base_sale_price,
		//	$base_subscription_sign_up_fee,
		//	"CONVERTED",
		//	$product->regular_price,
		//	$product->sale_price,
		//	$product->subscription_sign_up_fee
		//);

		if(is_numeric($product->sale_price) && $product->is_on_sale()) {
			$product->price = $product->sale_price;
		}
		else {
			$product->price = $product->regular_price;
		}
		$product->subscription_price = $product->price;

		// @since 1.3.1.170405
		if(aelia_wc_version_is('>=', '3.0')) {
			$product->set_regular_price($product->regular_price);
			$product->set_sale_price($product->sale_price);
			$product->set_price($product->price);
		}

		// Debug
		//var_dump(
		//	$product->subscription_price,
		//	$product->sale_price,
		//	$product->subscription_sign_up_fee
		//);die();

		return $product;
	}

	/**
	 * Tags cart items containing a product being renewed or resubscribed, to make
	 * it easier to distinguish them.
	 *
	 * @since 1.3.3.170413
	 */
	protected function tag_cart_resubscribes_and_renewals() {
		if(!empty(WC()->cart->cart_contents)) {
			foreach(WC()->cart->cart_contents as $cart_item) {
				// Skip cart items that don't have a product
				if(!is_object($cart_item['data'])) {
					continue;
				}
				// Tag products being resubscribed
				if(isset($cart_item['subscription_resubscribe'])) {
					$cart_item['data']->aelia_product_resubscribe = true;
				}

				// Tag products being renewed
				if(isset($cart_item['subscription_renewal'])) {
					$cart_item['data']->aelia_product_renewal = true;
				}

				// Tag products being switched
				// @since 1.3.6.170531
				if(isset($cart_item['subscription_switch'])) {
					$cart_item['data']->aelia_product_switch = true;
				}
			}
		}
	}
	/**
	 * Indicates if a product is being purchased as a renewal.
	 *
	 * @param WC_Product product
	 * @return bool
	 * @since 1.3.3.170413
	 */
	protected function is_renewal_or_resubscribe_purchase($product) {
		/* Look cart items containing a product being renewed or resubscribed.
		 * When one is found, attach a flag to the product, to indicate that it's a
		 * renewal/resubscribe. Since objects are passed by reference, if we "tack"
		 * the flag on the same product that we got as an argument for this method,
		 * then we will be able to retrieve it at the end of the function.
		 *
		 * Example
		 * 1. Instance of Product X passed as an argument. It might not have the flag.
		 * 2. Going through the cart, we find that one instance of Product X is being
		 *    purchased as a renewal. We add the flag to the instance of that product.
		 * 3. We check for the presence of the flag on the object passed as an argument.
		 *    If we attached that flag in step #2, we will find it against the object.
		 */
		$this->tag_cart_resubscribes_and_renewals();

		return !empty($product->aelia_product_renewal) ||
					 !empty($product->aelia_product_resubscribe) ||
					 !empty($product->aelia_product_switch);
	}

	public function __construct() {
		$this->set_hooks();
	}

	/**
	 * Indicates if we are editing an order.
	 *
	 * @return bool
	 * @since 1.3.1.170405
	 */
	protected static function editing_order() {
		if(!empty($_GET['action']) && ($_GET['action'] == 'edit') && !empty($_GET['post'])) {
			$post = get_post($_GET['post']);

			if(!empty($post) && ($post->post_type == 'shop_order')) {
				return $post->ID;
			}
		}
		return false;
	}

	/**
	 * Indicates if a product is supported by this integratoin.
	 *
	 * @param WC_Product product
	 * @return bool
	 * @since 1.3.3.170413
	 */
	protected function is_supported_subscription_product($product) {
		return in_array(get_class($product), array(
			'WC_Product_Subscription',
			'WC_Product_Subscription_Variation',
			'WC_Product_Variable_Subscription',
			// Legacy products, introduced in Subscriptions 2.2
			'WC_Product_Subscription_Legacy',
			'WC_Product_Subscription_Variation_Legacy',
			'WC_Product_Variable_Subscription_Legacy',
		));
	}

	/**
	 * Set the hooks required by the class.
	 */
	protected function set_hooks() {
		if(WC_Aelia_CS_Subscriptions::is_frontend() || self::editing_order()) {
			// Price conversion
			add_filter('wc_aelia_currencyswitcher_product_convert_callback', array($this, 'wc_aelia_currencyswitcher_product_convert_callback'), 10, 2);
			add_filter('woocommerce_subscriptions_product_price', array($this, 'woocommerce_subscriptions_product_price'), 10, 2);
			add_filter('woocommerce_subscriptions_product_sign_up_fee', array($this, 'woocommerce_subscriptions_product_sign_up_fee'), 10, 2);

			// Coupon types
			add_filter('wc_aelia_cs_coupon_types_to_convert', array($this, 'wc_aelia_cs_coupon_types_to_convert'), 10, 1);
		}

		// Product edit/add hooks
		add_action('woocommerce_process_product_meta_subscription', array($this, 'woocommerce_process_product_meta_subscription'), 10);
		add_action('woocommerce_process_product_meta_variable-subscription', array($this, 'woocommerce_process_product_meta_variable_subscription'), 10);

		// WC 2.4+
		add_action('woocommerce_ajax_save_product_variations', array($this, 'woocommerce_ajax_save_product_variations'));

		// Admin UI
		add_action('woocommerce_product_options_general_product_data', array($this, 'woocommerce_product_options_general_product_data'), 20);
		add_filter('woocommerce_product_after_variable_attributes', array($this, 'woocommerce_product_after_variable_attributes'), 20);

		// Cart hooks
		add_action('wc_aelia_currencyswitcher_recalculate_cart_totals_before', array($this, 'wc_aelia_currencyswitcher_recalculate_cart_totals_before'), 10);

		add_filter('wc_aelia_currencyswitcher_prices_type_field_map', array($this, 'wc_aelia_currencyswitcher_prices_type_field_map'), 10, 2);
		//add_action('wc_aelia_currencyswitcher_recalculate_cart_totals_after', array($this, 'wc_aelia_currencyswitcher_recalculate_cart_totals_after'), 10);


		// Subscriptions 2.0 - Fix bug #1040
		// Fix checkout currency during renewals
		// NOTE: as of 15/12/2015 this patch should no longer be needed.
		//add_filter('woocommerce_order_again_cart_item_data', array($this, 'woocommerce_order_again_cart_item_data'), 10, 3);
		//add_filter('woocommerce_checkout_init', array($this, 'maybe_override_currency'), 10);
	}

	/**
	 * Converts all the prices of a given product in the currently selected
	 * currency.
	 *
	 * @param WC_Product product The product whose prices should be converted.
	 * @return WC_Product
	 */
	protected function convert_product_prices($product) {
		$selected_currency = $this->currency_switcher()->get_selected_currency();
		$base_currency = $this->settings_controller()->base_currency();

		$product = $this->currencyprices_manager()->convert_product_prices($product, $selected_currency);

		return $product;
	}

	/**
	 * Callback to perform the conversion of subscription prices into selected currencu.
	 *
	 * @param callable $original_convert_callback The original callback passed to the hook.
	 * @param WC_Product The product to examine.
	 * @return callable
	 */
	public function wc_aelia_currencyswitcher_product_convert_callback($original_convert_callback, $product) {
		$method_keys = array(
			'WC_Product_Subscription' => 'subscription',
			// TODO Implement conversion of variable subscriptions
			'WC_Product_Subscription_Variation' => 'subscription_variation',
			'WC_Product_Variable_Subscription' => 'variable_subscription',

			'WC_Product_Subscription_Legacy' => 'subscription',
			'WC_Product_Subscription_Variation_Legacy' => 'subscription_variation',
			'WC_Product_Variable_Subscription_Legacy' => 'variable_subscription',
		);

		// Determine the conversion method to use
		$method_key = get_value(get_class($product), $method_keys, '');
		$convert_method = 'convert_' . $method_key . '_product_prices';

		if(!method_exists($this, $convert_method)) {
			return $original_convert_callback;
		}

		return array($this, $convert_method);
	}

	/**
	 * Converts the prices of a subscription product to the specified currency.
	 *
	 * @param WC_Product_Subscription product A subscription product.
	 * @param string currency A currency code.
	 * @return WC_Product_Subscription The product with converted prices.
	 */
	public function convert_subscription_product_prices(WC_Product_Subscription $product, $currency) {
		// @since 1.3.1.170405
		$product_id = aelia_wc_version_is('>=', '3.0') ? $product->get_id() : $product->id;
		$product = $this->convert_to_currency($product,
																					$currency,
																					$this->get_subscription_regular_prices($product_id),
																					$this->get_subscription_sale_prices($product_id),
																					self::get_subscription_signup_prices($product_id));

		return $product;
	}

	/**
	 * Converts the prices of a variable product to the specified currency.
	 *
	 * @param WC_Product_Variable product A variable product.
	 * @param string currency A currency code.
	 * @return WC_Product_Variable The product with converted prices.
	 */
	public function convert_variable_subscription_product_prices(WC_Product $product, $currency) {
		$product_children = $product->get_children();

		if(empty($product_children)) {
			return $product;
		}

		$variation_regular_prices = array();
		$variation_sale_prices = array();
		$variation_signup_prices = array();
		$variation_prices = array();

		$currencyprices_manager = $this->currencyprices_manager();
		foreach($product_children as $variation_id) {
			$variation = $this->load_subscription_variation_in_currency($variation_id, $currency);

			if(empty($variation)) {
				continue;
			}

			$variation_regular_prices[] = $variation->regular_price;
			$variation_sale_prices[] = $variation->sale_price;
			$variation_signup_prices[] = $variation->subscription_sign_up_fee;
			$variation_prices[] = $variation->price;

			//var_dump(
			//	$variation->regular_price,
			//	$variation->sale_price,
			//	$variation->subscription_sign_up_fee,
			//	$variation->price
			//);die();
		}

		$product->min_variation_regular_price = $currencyprices_manager->get_min_value($variation_regular_prices);
		$product->max_variation_regular_price = $currencyprices_manager->get_max_value($variation_regular_prices);

		$product->min_variation_sale_price = $currencyprices_manager->get_min_value($variation_sale_prices);
		$product->max_variation_sale_price = $currencyprices_manager->get_max_value($variation_sale_prices);

		$product->min_variation_price = $currencyprices_manager->get_min_value($variation_prices);
		$product->max_variation_price = $currencyprices_manager->get_max_value($variation_prices);

		$product->min_subscription_sign_up_fee = $currencyprices_manager->get_min_value($variation_signup_prices);
		$product->max_subscription_sign_up_fee = $currencyprices_manager->get_max_value($variation_signup_prices);

		$product->subscription_price = $product->min_variation_price;
		$product->price = $product->subscription_price;
		$product->subscription_sign_up_fee = $product->min_subscription_sign_up_fee;

		if(aelia_wc_version_is('>=', '3.0')) {
			$product->set_price($product->price);
		}

		if(!isset($product->max_variation_period)) {
			$product->max_variation_period = '';
		}
		if(!isset($product->max_variation_period_interval)) {
			$product->max_variation_period_interval = '';
		}

		//var_dump($product);

		return $product;
	}

	/**
	 * Converts the product prices of a variation.
	 *
	 * @param WC_Product_Variation $product A product variation.
	 * @param string currency A currency code.
	 * @return WC_Product_Variation The variation with converted prices.
	 */
	public function convert_subscription_variation_product_prices(WC_Product_Subscription_Variation $product, $currency) {
		// @since 1.3.1.170405
		$variation_id = aelia_wc_version_is('>=', '3.0') ? $product->get_id() : $product->variation_id;
		$product = $this->convert_to_currency($product,
																					$currency,
																					$this->currencyprices_manager()->get_variation_regular_prices($variation_id),
																					$this->currencyprices_manager()->get_variation_sale_prices($variation_id),
																					$this->get_subscription_variation_signup_prices($variation_id));

		//var_dump($product);

		return $product;
	}

	/**
	 * Indicates if a product requires conversion.
	 *
	 * @param WC_Product product The product to process.
	 * @param string currency The target currency for which product prices will
	 * be requested.
	 * @return bool
	 * @since 1.3.1.170405
	 */
	protected function product_requires_conversion($product, $currency) {
		// If the product is already in the target currency, it doesn't require
		// conversion
		return empty($product->currency) || ($product->currency != $currency);
	}

	/**
	 * Given a Variation ID, it loads the variation and returns it, with its
	 * prices converted into the specified currency.
	 *
	 * @param int variation_id The ID of the variation.
	 * @param string currency A currency code.
	 * @return WC_Product_Variation
	 */
	public function load_subscription_variation_in_currency($variation_id, $currency) {
		try {
			$variation = wc_get_product($variation_id);
		}
		catch(\Exception $e) {
			$variation = null;
			$err_msg = sprintf(__('Invalid subscription variation found. Variation ID: "%s". ' .
														'Variation will be skipped.', WC_Aelia_CS_Subscriptions::$text_domain),
												 $e->getMessage());
			$this->log($err_msg, false);
		}

		if(empty($variation)) {
			return false;
		}

		$variation = $this->convert_product_prices($variation, $currency);

		return $variation;
	}

	/**
	 * Converts the price of a subscription before it's used by WooCommerce.
	 *
	 * @param float subscription_price The original price of the subscription.
	 * @param WC_Subscription_Product product The subscription product.
	 * @return float
	 */
	public function woocommerce_subscriptions_product_price($subscription_price, $product) {
		if($this->is_supported_subscription_product($product)) {
			$selected_currency = $this->currencyprices_manager()->get_selected_currency();
			if($this->product_requires_conversion($product, $selected_currency)) {
				$product = $this->convert_product_prices($product, $selected_currency);
			}
			$subscription_price = $product->subscription_price;
		}

		return $subscription_price;
	}

	/**
	 * Returns a subscription signup fee, converted into the active currency.
	 *
	 * @param float subscription_sign_up_fee The original subscription signup fee.
	 * @param WC_Subscription_Product product The subscription product.
	 * @return float
	 */
	public function woocommerce_subscriptions_product_sign_up_fee($subscription_sign_up_fee, $product) {
		// Don't process signup fees for unsupported products or renewals
		if($this->is_supported_subscription_product($product) && !$this->is_renewal_or_resubscribe_purchase($product)) {
			$selected_currency = $this->currencyprices_manager()->get_selected_currency();
			if($this->product_requires_conversion($product, $selected_currency)) {
				$product = $this->convert_product_prices($product, $selected_currency);
			}
			$subscription_sign_up_fee = $product->subscription_sign_up_fee;
		}

		return $subscription_sign_up_fee;
	}

	/**
	 * Returns the path where the Admin Views can be found.
	 *
	 * @return string
	 */
	protected function admin_views_path() {
		return WC_Aelia_CS_Subscriptions::plugin_path() . '/views/admin';
	}

	/**
	 * Loads (includes) a View file.
	 *
	 * @param string view_file_name The name of the view file to include.
	 */
	private function load_view($view_file_name) {
		$file_to_load = $this->admin_views_path() . '/' . $view_file_name;

		if(!empty($file_to_load) && is_readable($file_to_load)) {
			include($file_to_load);
		}
	}

	/**
	 * Event handler fired when a subscription is being saved. It processes and
	 * saves the Currency Prices associated with the subscription.
	 *
	 * @param int post_id The ID of the Post (subscription) being saved.
	 */
	public function woocommerce_process_product_meta_subscription($post_id) {
		$subscription_signup_prices = $this->currencyprices_manager()->sanitise_currency_prices(get_value(self::FIELD_SIGNUP_FEE_CURRENCY_PRICES, $_POST));

		// D.Zanella - This code saves the subscription prices in the various currencies
		update_post_meta($post_id, self::FIELD_SIGNUP_FEE_CURRENCY_PRICES, json_encode($subscription_signup_prices));

		// Copy the currency prices from the fields dedicated to the variation inside the standard product fields
		$_POST[WC_Aelia_CurrencyPrices_Manager::FIELD_REGULAR_CURRENCY_PRICES] = $_POST[self::FIELD_REGULAR_CURRENCY_PRICES];
		$_POST[WC_Aelia_CurrencyPrices_Manager::FIELD_SALE_CURRENCY_PRICES] = $_POST[self::FIELD_SALE_CURRENCY_PRICES];


		$this->currencyprices_manager()->process_product_meta($post_id);
	}

	/**
	 * Event handler fired when a subscription is being saved. It processes and
	 * saves the Currency Prices associated with the subscription.
	 *
	 * @param int post_id The ID of the Post (subscription) being saved.
	 */
	public function woocommerce_process_product_meta_variable_subscription($post_id) {
		// Debug
		//var_dump($_POST);die();

		// Save the instance of the pricing manager to reduce calls to internal method
		$currencyprices_manager = $this->currencyprices_manager();

		// Retrieve all IDs, regular prices and sale prices for all variations. The
		// "all_" prefix has been added to easily distinguish these variables from
		// the ones containing the data of a single variation, whose names would
		// be otherwise very similar
		$all_variations_ids = get_value('variable_post_id', $_POST, array());
		$all_variations_signup_currency_prices = get_value(self::FIELD_VARIATION_SIGNUP_FEE_CURRENCY_PRICES, $_POST);

		// D.Zanella - This code saves the subscription prices for all variations in
		// the various currencies
		foreach($all_variations_ids as $variation_idx => $variation_id) {
			$variations_signup_currency_prices = $currencyprices_manager->sanitise_currency_prices(get_value($variation_idx, $all_variations_signup_currency_prices, null));
			update_post_meta($variation_id, self::FIELD_VARIATION_SIGNUP_FEE_CURRENCY_PRICES, json_encode($variations_signup_currency_prices));
		}

		// Copy the currency prices from the fields dedicated to the variation inside the standard product fields
		$_POST[WC_Aelia_CurrencyPrices_Manager::FIELD_VARIABLE_REGULAR_CURRENCY_PRICES] = $_POST[self::FIELD_VARIATION_REGULAR_CURRENCY_PRICES];
		$_POST[WC_Aelia_CurrencyPrices_Manager::FIELD_VARIABLE_SALE_CURRENCY_PRICES] = $_POST[self::FIELD_VARIATION_SALE_CURRENCY_PRICES];

		$currencyprices_manager->woocommerce_process_product_meta_variable($post_id);
	}

	/**
	 * Alters the view used to allow entering prices manually, in each currency.
	 *
	 * @param string file_to_load The view/template file that should be loaded.
	 * @return string
	 */
	public function woocommerce_product_options_general_product_data() {
		$this->load_view('simplesubscription_currencyprices_view.php');
	}

	/**
	 * Loads the view that allows to set the prices for a subscription variation.
	 *
	 * @param string file_to_load The original file to load.
	 * @return string
	 */
	public function woocommerce_product_after_variable_attributes() {
		$this->load_view('subscriptionvariation_currencyprices_view.php');
	}

	/**
	 * Intercepts the recalculation of the cart, ensuring that subscriptions
	 * subtotals are calculated correctly.
	 */
	public function wc_aelia_currencyswitcher_recalculate_cart_totals_before() {
		if(!WC_Subscriptions_Cart::cart_contains_subscription() &&
			 !WC_Subscriptions_Cart::cart_contains_subscription_renewal()) {
			// Cart doesn't contain subscriptions
			return;
		}

		// If cart contains subscriptions, force the full recalculation of totals and
		// subtotals. This is required for the Subscriptions plugin to recalculate
		// the subtotal in the mini-cart and display the correct amounts
		if(!defined('WOOCOMMERCE_CART')) {
			define('WOOCOMMERCE_CART', true);
		}
	}

	/**
	 * Adds coupon types related to subscriptions, which should be converted into
	 * the selected currency when used.
	 *
	 * @param array coupon_types The original array of coupon types passed by the
	 * Currency Switcher.
	 * @return array
	 */
	public function wc_aelia_cs_coupon_types_to_convert($coupon_types) {
		$coupon_types[] = 'sign_up_fee';
		$coupon_types[] = 'recurring_fee';

		return $coupon_types;
	}

	/**
	 * Alters the cart item associated to a renewal order, to keep track of the
	 * currency in which the checkout should be performed.
	 *
	 * @param array cart_item_data The cart item details.
	 * @param array line_item The item added to the cart.
	 * @param WC_Subscription subscription The original subscription being renewed.
	 * @since 1.2.13.151208
	 * @link https://github.com/Prospress/woocommerce-subscriptions/issues/1040
	 */
	public function woocommerce_order_again_cart_item_data($cart_item_data, $line_item, $subscription) {
		// Keep track of the original currency, it will be needed at checkout
		$cart_item_data['renewal_data_key'] = key($cart_item_data);
		$cart_item_data['checkout_currency'] = $subscription->order->get_order_currency();
		return $cart_item_data;
	}

	/**
	 * If necessary, replaces the currency active at checkout with the one from
	 * the order from which the resubscription was started.
	 *
	 * @since 1.2.13.151208
	 * @link https://github.com/Prospress/woocommerce-subscriptions/issues/1040
	 */
	public function maybe_override_currency() {
		$this->checkout_currency = $this->get_checkout_currency();
		// If a checkout currency was stored, use it
		if(!empty($this->checkout_currency)) {
			add_filter('woocommerce_currency', array($this, 'override_currency'), 10);
		}
	}

	/**
	 * Returns the currency to be used at checkout. This method inspects the cart
	 * contents to determine if there is a "resubscription" product in it. If there
	 * is, then the currency to be used at checkout is the one attached to the
	 * resubscription.
	 *
	 * @return string|null The currency from the original subscription, or null if
	 * there isn't one.
	 * @since 1.2.13.151208
	 * @link https://github.com/Prospress/woocommerce-subscriptions/issues/1040
	 */
	protected function get_checkout_currency() {
		$currency = null;
		foreach(WC()->cart->get_cart() as $item) {
			// If any of the items in the cart is a subscription renewal, it should
			// have a currency attached to it. That is the currency to use at checkout
			if(!empty($item) && !empty($item['checkout_currency'])) {
				$currency = $item['checkout_currency'];
				break;
			}
		}
		return $currency;
	}

	/**
	 * Overrides the active currency during checkout, when a resubscription is
	 * being processed.
	 *
	 * @param string currency The original currency.
	 * @return string The currency to be used at checkout.
	 * @since 1.2.13.151208
	 * @link https://github.com/Prospress/woocommerce-subscriptions/issues/1040
	 */
	public function override_currency($currency) {
		return $this->checkout_currency;
	}

	/**
	 * Handles the saving of variations data using the new logic introduced in
	 * WooCommerce 2.4.
	 *
	 * @param int product_id The ID of the variable product whose variations are
	 * being saved.
	 * @since 1.2.14.151215
	 * @since WC 2.4
	 */
	public function woocommerce_ajax_save_product_variations($product_id) {
		if(WC_Subscriptions_Product::is_subscription($product_id)) {
			$this->woocommerce_process_product_meta_variable_subscription($product_id);
		}
	}

	/**
	 * Alters the map of currency pricing fields to inform the Currency Switcher
	 * how to retrieve the prices in shop's base currency.
	 *
	 * @param array prices_type_field_map
	 * @return array
	 * @since 1.3.5.170425
	 */
	public function wc_aelia_currencyswitcher_prices_type_field_map($prices_type_field_map, $post_id = null) {
		// Subscription sign up fee
		$prices_type_field_map[self::FIELD_SIGNUP_FEE_CURRENCY_PRICES] = '_subscription_sign_up_fee';

		return $prices_type_field_map;
	}
}
