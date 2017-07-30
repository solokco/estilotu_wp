<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * WPML Compability Class
 * 
 * Handles WPML Compability
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.8.0
 */
class WOO_Vou_Wpml {
	
	public $model;
	
	function __construct() {
		
		global $woo_vou_model;
		$this->model = $woo_vou_model;
	}
	
	/**
	 * Get original product id from translated product id
	 * 
	 * To add compability with WPML
	 * Handles to get original product id from translated product id.	 
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.0
	 */
	public function woo_vou_get_original_product_id( $product_id ) {
		
		// check wpml and woocommerce multilingual plugin is activated
		if( function_exists('icl_object_id') && class_exists('woocommerce_wpml') ) {
	  		
			global $sitepress;
	  		
			// get current language
			$current_language = $sitepress->get_current_language();			
			// get default language
	        $default_language = $sitepress->get_default_language();
	
	        // if current language and default language is different then only get product id of default language
	        if( $current_language != $default_language ) {
	        	$product_id = icl_object_id( $product_id, 'product', false, $default_language );	
	        }							
		}
		
		return $product_id;
	}
	
	public function woo_vou_get_original_id_from_translated_id( $product_id ) {
		
		// get original product/variation id from translated product/variation id
		$product_id = $this->woo_vou_get_original_product_id( $product_id );
		
		return $product_id;
	}
	
	/**
	 * Get original product id from translated product id and post_type
	 * 
	 * To add compability with WPML
	 * Handles to get original product id from translated product id and post_type 
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.2
	 */
	public function woo_vou_convert_get_originalid ( $translated_id, $post_type, $woo_vou_order_lang ) {
		
		// check wpml and woocommerce multilingual plugin is activated
		if( function_exists('icl_object_id') && class_exists('woocommerce_wpml') ) {
			
			global $sitepress;
	  		
			// get current language
			$current_language = $sitepress->get_current_language();
			
			if( !empty( $woo_vou_order_lang ) ) {
		
				// Get original product_id from translated_id and post_type
				$translated_id = icl_object_id( $translated_id, $post_type, false, $woo_vou_order_lang );
			}
		}
		return $translated_id;
	}
	
	/**
	 * Adding Hooks
	 * 
	 * Adding proper hooks for the WPML compability.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function add_hooks() {
		
		// Add filter to return original language product id before updating voucher code
		add_filter( 'woo_vou_before_update_voucher_code', array( $this, 'woo_vou_get_original_id_from_translated_id' ), 10 );
				
		// Add filter to return original language product id before getting voucher code
		add_filter( 'woo_vou_before_get_voucher_code', array( $this, 'woo_vou_get_original_id_from_translated_id' ), 10 );
		
		// Add filter to return order_id, in the language in which order was made
		add_filter( 'woo_vou_before_admin_vou_download_link', array( $this, 'woo_vou_convert_get_originalid' ), 10, 3 );
	}
}
?>