<?php
/**
 * Templates Functions
 * 
 * Handles to manage templates of plugin
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.5.4
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

if( !function_exists( 'woo_vou_get_templates_dir' ) ) { 
	
	/**
	 * Returns the path to the pdf vouchers templates directory
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.5.4
	 */
	function woo_vou_get_templates_dir() {
		
		return apply_filters( 'woo_vou_get_templates_dir', WOO_VOU_DIR . '/includes/templates/' );
	}
}

if( !function_exists( 'woo_vou_get_template' ) ) {
	
	/**
	 * Get other templates
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.5.4
	 */
	function woo_vou_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
		
		$plugin_absolute	= woo_vou_get_templates_dir();
		
		if ( ! $template_path ) {
			$template_path = WC()->template_path() . WOO_VOU_PLUGIN_BASENAME . '/';
		}

		wc_get_template( $template_name, $args, $template_path, $plugin_absolute );
	}
}

if( !function_exists( 'woo_vou_recipient_fields_content' ) ) {
	
	/**
	 * Recipient Fields
	 * 
	 * Handles to display Recipient Fields
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.5.4
	 */
	function woo_vou_recipient_fields_content() {
		
		global $product , $woo_vou_model;
		
		// store product in reset variable
		$reset_product	= $product;
		
		//Initilize products
		$products = array();
		
		if ( $product->is_type( 'variable' ) ) { //for variable product
			foreach ( $product->get_children() as $variation_product_id ) {
				$products[] = wc_get_product( $variation_product_id );
			}
		} else {
			$products[] = $product;
		}
	
		foreach ( $products as $product ) {//For all products
	
			//Get prefix
			$prefix			= WOO_VOU_META_PREFIX;
			
			//Get product ID
			$product_id			= isset( $product->id ) ? $product->id : '';
			
			//Get variation ID
			$variation_id		= isset( $product->variation_id ) ? $product->variation_id : $product->id;
			
			//voucher enable or not
			$voucher_enable	= $woo_vou_model->woo_vou_check_enable_voucher( $product_id, $variation_id );
			
			if( $voucher_enable ) { // if voucher is enable
				
				//Get product recipient meta setting
				$recipient_data	= $woo_vou_model->woo_vou_get_product_recipient_meta( $product->id );
				
				//Recipient name fields
				$enable_recipient_name			= $recipient_data['enable_recipient_name'];
				$recipient_name_lable			= $recipient_data['recipient_name_lable'];
				$recipient_name_max_length		= $recipient_data['recipient_name_max_length'];
				$recipient_name_required 		= $recipient_data['recipient_name_is_required'];
				
				//Recipient email fields
				$enable_recipient_email			= $recipient_data['enable_recipient_email'];
				$recipient_email_label			= $recipient_data['recipient_email_label'];
				$recipient_email_required 		= $recipient_data['recipient_email_is_required'];
				
				//Recipient message fields
				$enable_recipient_message		= $recipient_data['enable_recipient_message'];
				$recipient_message_label		= $recipient_data['recipient_message_label'];
				$recipient_message_max_length	= $recipient_data['recipient_message_max_length'];
				$recipient_message_required 	= $recipient_data['recipient_message_is_required'];
				
				//Recipient gift date
				$enable_recipient_giftdate		= $recipient_data['enable_recipient_giftdate'];
				$recipient_giftdate_label		= $recipient_data['recipient_giftdate_label'];
				$recipient_giftdate_required 	= $recipient_data['recipient_giftdate_is_required'];
				
				//Pdf Template Selection fields
				$enable_pdf_template_selection	= $recipient_data['enable_pdf_template_selection'];
				$pdf_template_selection_label	= $recipient_data['pdf_template_selection_label'];
				
				// check if enable Recipient Detail
				if( $enable_recipient_email == 'yes' || $enable_recipient_name == 'yes' || $enable_recipient_message == 'yes' || $enable_recipient_giftdate == 'yes' || $enable_pdf_template_selection == 'yes' ) {
					
					$recipient_name		= isset( $_POST[$prefix.'recipient_name'][$variation_id] ) ? $woo_vou_model->woo_vou_escape_attr( $_POST[$prefix.'recipient_name'][$variation_id] ) : '';
					$recipient_email	= isset( $_POST[$prefix.'recipient_email'][$variation_id] ) ? $woo_vou_model->woo_vou_escape_attr( $_POST[$prefix.'recipient_email'][$variation_id] ) : '';
					$recipient_message	= isset( $_POST[$prefix.'recipient_message'][$variation_id] ) ? $woo_vou_model->woo_vou_escape_attr( $_POST[$prefix.'recipient_message'][$variation_id] ) : '';
					$recipient_giftdate = isset( $_POST[$prefix.'recipient_giftdate'][$variation_id] ) ? $_POST[$prefix.'recipient_giftdate'][$variation_id] : '';
					
					$pdf_template_selection	= isset( $_POST[$prefix.'pdf_template_selection'][$variation_id] ) ? $woo_vou_model->woo_vou_escape_attr( $_POST[$prefix.'pdf_template_selection'][$variation_id] ) : '';
					
	  				$product_templates = array();
	  		
	  				$product_templates = get_post_meta( $product->id, $prefix.'pdf_template_selection', true );
	  		
	  				if( empty( $product_templates ) ){
	  					$product_templates = get_option( 'vou_pdf_template_selection' );
	  				}
	  		
			  		if( empty( $product_templates ) ){
						
			  			$args = array(
							'posts_per_page'   => -1,
							'orderby'          => 'date',
							'order'            => 'DESC',
							'post_type'        => WOO_VOU_POST_TYPE,
							'post_status'      => 'publish',
						);
						$posts_array = get_posts( $args ); 
			  			
						foreach( $posts_array as $key ){
							$product_templates[] = $key->ID;
						}
			  		}
					
					$args	= array(
								'enable_recipient_name' 	   =>	$enable_recipient_name,
								'recipient_name_lable'		   =>	$recipient_name_lable,
								'recipient_name_max_length'	   => 	$recipient_name_max_length,
								'recipient_name_required'	   => 	$recipient_name_required,
								'variation_id'				   =>	$variation_id,
								'recipient_name'			   =>	$recipient_name,
								'enable_recipient_email'	   =>	$enable_recipient_email,
								'recipient_email_label'		   =>	$recipient_email_label,
								'recipient_email'			   =>	$recipient_email,
								'recipient_email_required'	   =>	$recipient_email_required,
								'enable_recipient_message'	   =>	$enable_recipient_message,
								'recipient_message_label'	   =>	$recipient_message_label,
								'recipient_message_max_length' =>	$recipient_message_max_length,
								'recipient_message'			   =>	$recipient_message,
								'recipient_message_required'   =>	$recipient_message_required,
								'enable_recipient_giftdate'	   =>	$enable_recipient_giftdate,
								'recipient_giftdate_label'	   =>	$recipient_giftdate_label,
								'recipient_giftdate'		   =>	$recipient_giftdate,
								'recipient_giftdate_required'  =>	$recipient_giftdate_required,
								'enable_pdf_template_selection'=>	$enable_pdf_template_selection,
								'pdf_template_selection_label' =>   $pdf_template_selection_label,
								'pdf_template_selection'	   =>	$pdf_template_selection,
								'product_templates'	   		   =>	$product_templates,
					);
					
					woo_vou_get_template( 'woo-vou-recipient-fields.php', $args );
				}
			}
		}
		
		// restore product
		$product	= $reset_product;
	}
}

if( !function_exists( 'woo_vou_check_qrcode_content' ) ) {
	
	/**
	 * Load qrcode template
	 * 
	 * Handles to load check voucher code using qrcode form
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.7.1
	 */
	function woo_vou_check_qrcode_content() {
		
		global $woo_vou_public;
			
		$redeem_response	= '';
		$voucodes           = '';
		$template_path      = '';
		
		if( !empty( $_POST['woo_vou_voucher_code_submit'] ) ) { // if form is submited
	
			// save voucher code
			$redeem_response = $woo_vou_public->woo_vou_save_voucher_code();
		}
		
		// if multiple voucher codes exist then split it
		$voucodes = explode( ",", $_GET['woo_vou_code'] );
		
		// pass arguments so we can use in tempelate 
		$args = array(
					'redeem_response' 	=>	$redeem_response,
					'voucodes' 	        =>	$voucodes
		);
		
		// call our function to go for tempelate
		woo_vou_get_template( 'qrcode/woo-vou-check-qrcode.php', $args );
	}
}

if( !function_exists( 'woo_vou_display_expiry_product' ) ) {
	
	/**
	 * expired/upcoming product
	 * 
	 * Handles to Remove add to cart product button and display expired/upcoming product
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.4.2
	 */
	function woo_vou_display_expiry_product() {
		
		global $product, $woo_vou_model;
		
		$expired = $woo_vou_model->woo_vou_check_product_is_expired( $product );
		
		if ( $expired == 'upcoming' ) {
			
	    	// remove add to cart button from single product page
	    	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	    	
	    	// get expired/upcoming template
			woo_vou_get_template( 'expired/expired.php', array( 'expired' => $expired ) );
			
	    } elseif ( $expired == 'expired' ) {
	    	
	    	// remove add to cart button from single product page
	    	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	    	
	    	// get expired/upcoming template
	    	woo_vou_get_template( 'expired/expired.php', array( 'expired' => $expired ) );
	    }
	}
}

if( !function_exists( 'woo_vou_used_voucher_codes_content' ) ) {
	
	/**
	 * Used Voucher Code
	 * 
	 * Handles to show used voucher codes on frontend
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.1
	 */
	function woo_vou_used_voucher_codes_content() {
		
		// Get used codes tempelate to get data
		woo_vou_get_template( 'voucher-codes/woo-vou-used-voucher-codes.php' );
	}
}

if( !function_exists( 'woo_vou_used_voucher_codes_listing_content' ) ) {

	/**
	 * Used Voucher Code listing table 
	 * 
	 * Handles to load listing for used voucher codes
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.1
	 */
	function woo_vou_used_voucher_codes_listing_content( $result_arr, $paging ) {
		
		//used codes listing template
		woo_vou_get_template( 'voucher-codes/used-codes-listing/used-codes-listing.php', array(	'result_arr' => $result_arr, 'paging' => $paging ) );
	}
}