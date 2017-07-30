<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Post Type Functions
 *
 * Handles all custom post types
 * functions
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0 
 */

/**
 * Register Post Type
 *
 * Handles to registers the Voucher 
 * post type
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_register_post_types() {
	
	//register Woocommerce voucher templates post type
	$voulabels = array(
					'name'					=> __( 'Voucher Templates', 'woovoucher' ),
					'singular_name'			=> __( 'Voucher Template', 'woovoucher' ),
					'add_new'				=> _x( 'Add New', WOO_VOU_POST_TYPE, 'woovoucher' ),
					'add_new_item'			=> sprintf( __( 'Add New %s' , 'woovoucher' ), __( 'Voucher Template' , 'woovoucher' ) ),
					'edit_item'				=> sprintf( __( 'Edit %s' , 'woovoucher' ), __( 'Voucher Template' , 'woovoucher' ) ),
					'new_item'				=> sprintf( __( 'New %s' , 'woovoucher' ), __( 'Voucher Template' , 'woovoucher' ) ),
					'all_items'				=> sprintf( __( '%s' , 'woovoucher' ), __( 'Voucher Templates' , 'woovoucher' ) ),
					'view_item'				=> sprintf( __( 'View %s' , 'woovoucher' ), __( 'Voucher Template' , 'woovoucher' ) ),
					'search_items'			=> sprintf( __( 'Search %a' , 'woovoucher' ), __( 'Voucher Templates' , 'woovoucher' ) ),
					'not_found'				=> sprintf( __( 'No %s Found' , 'woovoucher' ), __( 'Voucher Templates' , 'woovoucher' ) ),
					'not_found_in_trash'	=> sprintf( __( 'No %s Found In Trash' , 'woovoucher' ), __( 'Voucher Templates' , 'woovoucher' ) ),
					'parent_item_colon'		=> '',
					'menu_name' 			=> __( 'Voucher Templates' , 'woovoucher' ),
					'featured_image'        => __( 'Preview Image', 'woovoucher' ),
					'set_featured_image'    => __( 'Set preview image', 'woovoucher' ),
					'remove_featured_image' => __( 'Remove preview image', 'woovoucher' ),
					'use_featured_image'    => __( 'Use as preview image', 'woovoucher' ),
				);

	$vouargs = array(
				'labels'				=> $voulabels,
				'public' 				=> false,
			    'exclude_from_search'	=> true,
			    'show_ui' 				=> true, 
			    'show_in_menu' 			=> WOO_VOU_MAIN_MENU_NAME,
			    'query_var' 			=> false,
			    'rewrite' 				=> true,
			    'capability_type' 		=> 'post',
			    'hierarchical' 			=> false,
			    'supports' 				=> array( 'title', 'editor', 'thumbnail' )
		  	);
	register_post_type( WOO_VOU_POST_TYPE, $vouargs );
	
	//register Woocommerce voucher codes post type
	$voucodelabels = array(
					'name'					=> __( 'Voucher Codes', 'woovoucher' ),
					'singular_name'			=> __( 'Voucher Code', 'woovoucher' ),
					'add_new'				=> _x( 'Add New', WOO_VOU_CODE_POST_TYPE, 'woovoucher' ),
					'add_new_item'			=> sprintf( __( 'Add New %s' , 'woovoucher' ), __( 'Voucher Code' , 'woovoucher' ) ),
					'edit_item'				=> sprintf( __( 'Edit %s' , 'woovoucher' ), __( 'Voucher Code' , 'woovoucher' ) ),
					'new_item'				=> sprintf( __( 'New %s' , 'woovoucher' ), __( 'Voucher Code' , 'woovoucher' ) ),
					'all_items'				=> sprintf( __( '%s' , 'woovoucher' ), __( 'Voucher Codes' , 'woovoucher' ) ),
					'view_item'				=> sprintf( __( 'View %s' , 'woovoucher' ), __( 'Voucher Code' , 'woovoucher' ) ),
					'search_items'			=> sprintf( __( 'Search %a' , 'woovoucher' ), __( 'Voucher Codes' , 'woovoucher' ) ),
					'not_found'				=> sprintf( __( 'No %s Found' , 'woovoucher' ), __( 'Voucher Codes' , 'woovoucher' ) ),
					'not_found_in_trash'	=> sprintf( __( 'No %s Found In Trash' , 'woovoucher' ), __( 'Voucher Codes' , 'woovoucher' ) ),
					'parent_item_colon'		=> '',
					'menu_name' 			=> __( 'Voucher Codes' , 'woovoucher' )
				);

	$voucodeargs = array(
				'labels'				=> $voucodelabels,
				'public' 				=> false,
			    'exclude_from_search'	=> true,
			    'query_var' 			=> false,
			    'rewrite' 				=> false,
			    'capability_type' 		=> WOO_VOU_CODE_POST_TYPE,
			    'hierarchical' 			=> false,
			    'supports' 				=> array( 'title' )
			);
	register_post_type( WOO_VOU_CODE_POST_TYPE, $voucodeargs );
	
	// register WooCommerce partially redeem voucher codes post type
	$vou_partial_redeem_labels = array(
		'name'					=> __( 'Partially Redeem Voucher Codes', 'woovoucher' ),
		'singular_name'			=> __( 'Partially Redeem Voucher Code', 'woovoucher' ),
		'add_new'				=> _x( 'Add New', WOO_VOU_PARTIAL_REDEEM_POST_TYPE, 'woovoucher' ),
		'add_new_item'			=> sprintf( __( 'Add New %s' , 'woovoucher' ), __( 'Partially Redeem Voucher Code' , 'woovoucher' ) ),
		'edit_item'				=> sprintf( __( 'Edit %s' , 'woovoucher' ), __( 'Partially Redeem Voucher Code' , 'woovoucher' ) ),
		'new_item'				=> sprintf( __( 'New %s' , 'woovoucher' ), __( 'Partially Redeem Voucher Code' , 'woovoucher' ) ),
		'all_items'				=> sprintf( __( '%s' , 'woovoucher' ), __( 'Partially Redeem Voucher Codes' , 'woovoucher' ) ),
		'view_item'				=> sprintf( __( 'View %s' , 'woovoucher' ), __( 'Partially Redeem Voucher Code' , 'woovoucher' ) ),
		'search_items'			=> sprintf( __( 'Search %a' , 'woovoucher' ), __( 'Partially Redeem Voucher Codes' , 'woovoucher' ) ),
		'not_found'				=> sprintf( __( 'No %s Found' , 'woovoucher' ), __( 'Partially Redeem Voucher Codes' , 'woovoucher' ) ),
		'not_found_in_trash'	=> sprintf( __( 'No %s Found In Trash' , 'woovoucher' ), __( 'Partially Redeem Voucher Codes' , 'woovoucher' ) ),
		'parent_item_colon'		=> '',
		'menu_name' 			=> __( 'Partially Redeem Voucher Codes' , 'woovoucher' )
	);

	$vou_partial_redeem_args = array(
		'labels'				=> $vou_partial_redeem_labels,
		'public' 				=> false,
	    'exclude_from_search'	=> true,
	    'query_var' 			=> false,
	    'rewrite' 				=> false,
	    'capability_type' 		=> WOO_VOU_PARTIAL_REDEEM_POST_TYPE,
	    'hierarchical' 			=> false,
	    'supports' 				=> array( 'title' )
	);
	
	// finally register post type
	register_post_type( WOO_VOU_PARTIAL_REDEEM_POST_TYPE, $vou_partial_redeem_args );
}
//register custom post type
// we need to keep priority 100, because we need to execute this init action after all other init action called.
add_action( 'init', 'woo_vou_register_post_types' );

/**
 * Register Post Status
 * 
 * Handles to registers voucher post status
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.3
 */
function woo_vou_register_post_status() {
	
	register_post_status( WOO_VOU_REFUND_STATUS, array(
		'label'                     => _x( 'Refunded', 'Voucher status', 'woovoucher' ),
		'public'                    => false,
		'exclude_from_search'       => false,
		'show_in_admin_all_list'    => true,
		'show_in_admin_status_list' => true,
		'label_count'               => _n_noop( 'Refunded <span class="count">(%s)</span>', 'Refunded <span class="count">(%s)</span>', 'woovoucher' )
	) );
}
add_action( 'init', 'woo_vou_register_post_status', 9 );