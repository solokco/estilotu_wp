<?php
/**
 * Plugin Name: WooCommerce - PDF Vouchers
 * Plugin URI:  http://wpweb.co.in/
 * Description: With Pdf Vouchers Extension, you can create unlimited vouchers, either for Local Businesses / Local Stores or even online stores. The sky is the limit.
 * Version: 2.9.3
 * Author: WPWeb
 * Author URI: http://wpweb.co.in
 * Text Domain: woovoucher
 * Domain Path: languages
 * 
 * @package WooCommerce - PDF Vouchers
 * @category Core
 * @author WPWeb
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Basic plugin definitions
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
if( !defined( 'WOO_VOU_PLUGIN_VERSION' ) ) {
	define( 'WOO_VOU_PLUGIN_VERSION', '2.9.3' ); //Plugin version number
}
if( !defined( 'WOO_VOU_DIR' ) ) {
	define( 'WOO_VOU_DIR', dirname( __FILE__ ) ); // plugin dir
}
if( !defined( 'WOO_VOU_URL' ) ) {
	define( 'WOO_VOU_URL', plugin_dir_url( __FILE__ ) ); // plugin url
}
if( !defined( 'WOO_VOU_ADMIN' ) ) {
	define( 'WOO_VOU_ADMIN', WOO_VOU_DIR . '/includes/admin' ); // plugin admin dir
}
if( !defined( 'WOO_VOU_IMG_DIR' ) ) {
	define( 'WOO_VOU_IMG_DIR', WOO_VOU_DIR.'/includes/images' ); // plugin image dir
}
if( !defined( 'WOO_VOU_IMG_URL' ) ) {
	define( 'WOO_VOU_IMG_URL', WOO_VOU_URL.'includes/images' ); // plugin image url
}
if( !defined( 'WOO_VOU_META_DIR' ) ) {
	define( 'WOO_VOU_META_DIR', WOO_VOU_DIR . '/includes/meta-boxes' ); // path to meta boxes
}
if( !defined( 'WOO_VOU_META_URL' ) ) {
	define( 'WOO_VOU_META_URL', WOO_VOU_URL . 'includes/meta-boxes' ); // path to meta boxes
}
if( !defined( 'WOO_VOU_META_PREFIX' ) ) {
	define( 'WOO_VOU_META_PREFIX', '_woo_vou_' ); // meta box prefix
}
if( !defined( 'WOO_VOU_ORDER_META_PREFIX' ) ) {
	define( 'WOO_VOU_ORDER_META_PREFIX', 'woo_vou_' ); // order meta data box prefix
}
if( !defined( 'WOO_VOU_POST_TYPE' ) ) {
	define( 'WOO_VOU_POST_TYPE', 'woovouchers' ); // custom post type voucher templates
}
if( !defined( 'WOO_VOU_CODE_POST_TYPE' ) ) {
	define( 'WOO_VOU_CODE_POST_TYPE', 'woovouchercodes' ); // custom post type voucher codes
}
if( !defined( 'WOO_VOU_PARTIAL_REDEEM_POST_TYPE' ) ) {
	define( 'WOO_VOU_PARTIAL_REDEEM_POST_TYPE', 'woovoupartredeem' ); // woocommerce partial redeem post type
}
if( !defined( 'WOO_VOU_MAIN_POST_TYPE' ) ) {
	define( 'WOO_VOU_MAIN_POST_TYPE', 'product' ); //woocommerce post type
}
if( !defined( 'WOO_VOU_MAIN_SHOP_POST_TYPE' ) ) {
	define( 'WOO_VOU_MAIN_SHOP_POST_TYPE', 'shop_order' ); //woocommerce post type
}
if( !defined( 'WOO_VOU_MAIN_MENU_NAME' ) ) {
	define( 'WOO_VOU_MAIN_MENU_NAME', 'woocommerce' ); //woocommerce main menu name
}
if( !defined( 'WOO_VOU_PLUGIN_BASENAME' ) ) {
	define( 'WOO_VOU_PLUGIN_BASENAME', basename( WOO_VOU_DIR ) ); //Plugin base name
}
if( !defined( 'WOO_VOU_PLUGIN_BASE_FILENAME' ) ) {
	define( 'WOO_VOU_PLUGIN_BASE_FILENAME', basename( __FILE__ ) ); //Plugin base file name
}
if ( ! defined( 'WOO_VOU_PLUGIN_KEY' ) ) {
	define( 'WOO_VOU_PLUGIN_KEY', 'woovouchers' );
}
if ( ! defined( 'WOO_VOU_REFUND_STATUS' ) ) {
	define( 'WOO_VOU_REFUND_STATUS', 'wpv-refunded' );
}

// Required Wpweb updater functions file
if ( ! function_exists( 'wpweb_updater_install' ) ) {
	require_once( 'includes/wpweb-upd-functions.php' );
}

//Get Vendor Role name
if( !defined( 'WOO_VOU_VENDOR_ROLE' ) ) {
	define( 'WOO_VOU_VENDOR_ROLE', 'woo_vou_vendors' ); //plugin vendor role
}
if( !defined( 'WOO_VOU_VENDOR_LEVEL' ) ) {
	define( 'WOO_VOU_VENDOR_LEVEL' , 'woo_vendor_options' ); //plugin vendor capability
}

$upload_dir		= wp_upload_dir();
$upload_path	= isset( $upload_dir['basedir'] ) ? $upload_dir['basedir'].'/' : ABSPATH;
$upload_url		= isset( $upload_dir['baseurl'] ) ? $upload_dir['baseurl'] : site_url();

// Pdf voucher upload dir for email
if( !defined( 'WOO_VOU_UPLOAD_DIR' ) ) {
	define( 'WOO_VOU_UPLOAD_DIR' , $upload_path . 'woocommerce_uploads/wpv-uploads/' ); // Voucher upload dir
}

// Pdf voucher upload url for email
if( !defined( 'WOO_VOU_UPLOAD_URL' ) ) {
	define( 'WOO_VOU_UPLOAD_URL' , $upload_url . '/woocommerce_uploads/wpv-uploads/' ); // Voucher upload url
}

global $woo_vou_vendor_role;

// loads the Misc Functions file
require_once ( WOO_VOU_DIR . '/includes/woo-vou-misc-functions.php' );

//Post type to handle custom post type
require_once( WOO_VOU_DIR . '/includes/woo-vou-post-types.php' );

//Pagination Class
require_once( WOO_VOU_DIR . '/includes/class-woo-vou-pagination-public.php' ); // front end pagination class

/**
 * Activation Hook
 * 
 * Register plugin activation hook.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
register_activation_hook( __FILE__, 'woo_vou_install' );

/**
 * Plugin Setup (On Activation)
 * 
 * Does the initial setup,
 * stest default values for the plugin options.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_install() {
	
	global $wpdb, $user_ID;
	
	//register post type
	woo_vou_register_post_types();
	
	//IMP Call of Function
	//Need to call when custom post type is being used in plugin
	flush_rewrite_rules();
	
	// Flush Cron Jobs
	wp_clear_scheduled_hook( 'woo_vou_flush_upload_dir_cron' );
	
	// Schedule Cron
	if ( !wp_next_scheduled('woo_vou_flush_upload_dir_cron') ) {
		wp_schedule_event( time(), 'twicedaily', 'woo_vou_flush_upload_dir_cron' );
	}
	
	// Flush Cron Jobs for gift notification email
	wp_clear_scheduled_hook( 'woo_vou_send_gift_notification' );
		
	// Schedule Cron for send gift notification email
	if ( !wp_next_scheduled('woo_vou_send_gift_notification') ) {
		wp_schedule_event( time(), 'daily', 'woo_vou_send_gift_notification' );
	}
	
	//Pdf cache Dir and create directory on activation
	woo_vou_create_cache_folder();
	
	//get option for when plugin is activating first time
	$woo_vou_set_option = get_option( 'woo_vou_set_option' );
	
	if( empty( $woo_vou_set_option ) ) { //check plugin version option
		
		//update default options
		woo_vou_default_settings();
		
		//update plugin version to option
		update_option( 'woo_vou_set_option', '1.0' );
		update_option( 'woo_vou_plugin_version', WOO_VOU_PLUGIN_VERSION );
	}
	
	//get option for when plugin is activating first time
	$woo_vou_set_option = get_option( 'woo_vou_set_option' );
	
	if( $woo_vou_set_option == '1.0' ) { //check set option for plugin is set 1.0
		
		//get vendor role
		$vendor_role = get_role( WOO_VOU_VENDOR_ROLE );
		if( empty( $vendor_role ) ) { //check vendor role
			
			$capabilities  = array(
										WOO_VOU_VENDOR_LEVEL	=> true,  // true allows add vendor level
										'read' 					=> true
									);
			add_role( WOO_VOU_VENDOR_ROLE,__( 'Voucher Vendor', 'woovoucher' ), $capabilities );
		} else {
			
			$vendor_role->add_cap( WOO_VOU_VENDOR_LEVEL );
		}
		
		$role = get_role( 'administrator' );
		$role->add_cap( WOO_VOU_VENDOR_LEVEL );
		
		//update plugin version to option
		update_option( 'woo_vou_set_option', '1.1.0' );
	} //check plugin set option value is 1.0
	
	$woo_vou_set_option = get_option( 'woo_vou_set_option' );
	
	if( $woo_vou_set_option == '1.1.0' ) {
		
		// update default order pdf name
		update_option( 'order_pdf_name', 'woo-voucher-{current_date}' );
		
		//update plugin version to option
		update_option( 'woo_vou_set_option', '1.1.1' );
	}
	
	$woo_vou_set_option = get_option( 'woo_vou_set_option' );
	
	if( $woo_vou_set_option == '1.1.1' ) {
		
		update_option( 'vou_pdf_usability', '0' );
		
		//update plugin version to option
		update_option( 'woo_vou_set_option', '1.2' );
	} // check plugin set option value is 1.1.1
	
	$woo_vou_set_option = get_option( 'woo_vou_set_option' );
	
	if( $woo_vou_set_option == '1.2' ) {
	
		//update plugin version to option
		update_option( 'woo_vou_set_option', '1.3' );
	}
	
	$woo_vou_set_option = get_option( 'woo_vou_set_option' );
	
	if( $woo_vou_set_option == '1.3' ) {
		
		// Get attachment pdf file name
		$attach_pdf_file_name = get_option( 'attach_pdf_name' );
		
		if( empty( $attach_pdf_file_name ) ) {
			// update default value for attchment pdf file name
			update_option( 'attach_pdf_name', 'woo-voucher-' );
		}					
		
		//update plugin version to option
		update_option( 'woo_vou_set_option', '1.4' );
	}
	
	$woo_vou_set_option = get_option( 'woo_vou_set_option' );
	
	if( $woo_vou_set_option == '1.4' ) {
		
		update_option( 'vou_allow_bcc_to_admin', 'no' );
		update_option( 'woo_vou_set_option', '1.5' );
	}
	
	$woo_vou_set_option = get_option( 'woo_vou_set_option' );
	
	if( $woo_vou_set_option == '1.5' ) {
		// future code will be done here.
	}
}

/**
 * Change pdf cache Dir and
 * create directory on activation
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.2
 */
function woo_vou_create_cache_folder() {
	
	$files	= array(
				array(
					'base' 		=> WOO_VOU_UPLOAD_DIR,
					'file' 		=> '.htaccess',
					'content' 	=> 'deny from all'
				),
				array(
					'base' 		=> WOO_VOU_UPLOAD_DIR,
					'file' 		=> 'index.html',
					'content' 	=> ''
				)
			);
	
	foreach ( $files as $file ) {
		if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
			if ( $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ) ) {
				fwrite( $file_handle, $file['content'] );
				fclose( $file_handle );
			}
		}
	}
}

/**
 * Default Settings
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_default_settings() {
	
	// Create default templates
	$default_templates = woo_create_default_templates();
	
	// Get default template page id
	$default_template_page_id = isset( $default_templates['default_template'] ) ? $default_templates['default_template'] : '';
	
	$options = array(
					'vou_site_logo'				=> '',
					'vou_pdf_name'				=> __( 'woo-purchased-voucher-codes-{current_date}', 'woovoucher' ),
					'vou_csv_name'				=> __( 'woo-purchased-voucher-codes-{current_date}', 'woovoucher' ),
					//'vou_pdf_template_selection'=> '',
					'vou_pdf_template'			=> $default_template_page_id,
					'vou_char_support'			=> '',
					'vou_attach_mail'			=> ''
				);
	
	foreach ($options as $key => $value) {
		update_option( $key, $value );
	}
}

/**
 * Check if current page is edit page.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_is_edit_page() {
	
	global $pagenow;
	
	return in_array( $pagenow, array( 'post.php', 'post-new.php', 'user-edit.php', 'profile.php' ) );
}

/**
 * Load Text Domain
 * 
 * This gets the plugin ready for translation.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.3
 */
function woo_vou_load_text_domain() {
	
	// Set filter for plugin's languages directory
	$woo_vou_lang_dir	= dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	$woo_vou_lang_dir	= apply_filters( 'woo_vou_languages_directory', $woo_vou_lang_dir );
	
	// Traditional WordPress plugin locale filter
	$locale	= apply_filters( 'plugin_locale',  get_locale(), 'woovoucher' );
	$mofile	= sprintf( '%1$s-%2$s.mo', 'woovoucher', $locale );
	
	// Setup paths to current locale file
	$mofile_local	= $woo_vou_lang_dir . $mofile;
	$mofile_global	= WP_LANG_DIR . '/' . WOO_VOU_PLUGIN_BASENAME . '/' . $mofile;
	
	if ( file_exists( $mofile_global ) ) { // Look in global /wp-content/languages/woocommerce-pdf-vouchers folder
		load_textdomain( 'woovoucher', $mofile_global );
	} elseif ( file_exists( $mofile_local ) ) { // Look in local /wp-content/plugins/woocommerce-pdf-vouchers/languages/ folder
		load_textdomain( 'woovoucher', $mofile_local );
	} else { // Load the default language files
		load_plugin_textdomain( 'woovoucher', false, $woo_vou_lang_dir );
	}
}

/**
 * Add plugin action links
 *
 * Adds a Settings, Support and Docs link to the plugin list.
 *
 * @package WooCommerce - PDF Vouchers
 * @since 2.2.0
 */
function woo_vou_add_plugin_links( $links ) {
	$plugin_links = array(
		'<a href="admin.php?page=wc-settings&tab=voucher">' . __( 'Settings', 'woovoucher' ) . '</a>',
		'<a href="http://support.wpweb.co.in/">' . __( 'Support', 'woovoucher' ) . '</a>',
		'<a href="http://wpweb.co.in/documents/woocommerce-pdf-vouchers/">' . __( 'Docs', 'woovoucher' ) . '</a>'
	);

	return array_merge( $plugin_links, $links );
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'woo_vou_add_plugin_links' );

//add action to load plugin
add_action( 'plugins_loaded', 'woo_vou_plugin_loaded' );

/**
 * Load Plugin
 * 
 * Handles to load plugin after
 * dependent plugin is loaded
 * successfully
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
function woo_vou_plugin_loaded() {
	
	//check Woocommerce is activated or not
	if( class_exists( 'Woocommerce' ) ) {
		
		// load first plugin text domain
		woo_vou_load_text_domain();

		/**
		 * Action To Initilize Vendors Role
		 * 
		 * Handles to action for initilize role
		 * 
		 * @package WooCommerce - PDF Vouchers
		 * @since 1.0.0
		 */
		add_action( 'init', 'woo_vou_add_other_role_to_vendor', 9 );

		/**
		 * Initilize Vendor Role
		 * 
		 * Handles to initilize vendor role
		 * 
		 * @package WooCommerce - PDF Vouchers
		 * @since 1.0.0
		 */
		function woo_vou_add_other_role_to_vendor() {

			//Initilize pdf voucher plugin
			woo_vou_vendor_initilize();
		}

		/**
		 * Deactivation Hook
		 * 
		 * Register plugin deactivation hook.
		 * 
		 * @package WooCommerce - PDF Vouchers
		 * @since 1.0.0
		 */
		register_deactivation_hook( __FILE__, 'woo_vou_uninstall' );
		
		/**
		 * Plugin Setup (On Deactivation)
		 * 
		 * Delete  plugin options.
		 * 
		 * @package WooCommerce - PDF Vouchers
		 * @since 1.0.0
		 */
		function woo_vou_uninstall() {
			
			global $wpdb;
			
			//IMP Call of Function
			//Need to call when custom post type is being used in plugin
			flush_rewrite_rules();
			
			// Flush Cron Jobs
			wp_clear_scheduled_hook( 'woo_vou_flush_upload_dir_cron' );
			
			// Flush Cron Jobs for gift notification email
			wp_clear_scheduled_hook( 'woo_vou_send_gift_notification' );
			
			// Getting delete option
			$woo_vou_delete_options = get_option( 'vou_delete_options' );
			
			// If option is set
			if( isset( $woo_vou_delete_options ) && !empty( $woo_vou_delete_options ) && $woo_vou_delete_options == 'yes' ) {
				
				// Delete vouchers data
				$post_types = array( 'woovouchers', 'woovouchercodes', 'woovoupartredeem' );
				
				foreach ( $post_types as $post_type ) {
					
					$args = array( 'post_type' => $post_type, 'post_status' => 'any', 'numberposts' => '-1' );
					$all_posts = get_posts( $args );
					foreach ( $all_posts as $post ) {
						wp_delete_post( $post->ID, true);
					}
				}
				
				//Items need to delete
				$options	= array(
								'vou_site_logo',
								'vou_pdf_name',
								'vou_csv_name',
								'order_pdf_name',
								'vou_pdf_usability',
								'multiple_pdf',
								//'vou_pdf_template_selection',
								'vou_pdf_template',
								'woo_vou_set_option',
								'vou_delete_options',
								'vou_char_support',
								'vou_attach_mail',
								'vou_allow_bcc_to_admin',
								'vou_disable_variations_auto_downloadable'
							);
				
				// Delete all options
				foreach ( $options as $option ) {
					delete_option( $option );
				}
			} // End of if
		}
		
		//global variables
		global $woo_vou_scripts,$woo_vou_model,$woo_vou_render,
				$woo_vou_shortcode,$woo_vou_admin,$woo_vou_public,
				$woo_vou_admin_meta,$woo_vou_settings_tabs,$woo_vou_upgrade, 
				$woo_vou_template_shortcodes,$woo_vou_wpml,$woo_vou_order_sms,
				$woo_vou_qtranslatex,$woo_vou_vendor_pro;
		
		//Model class handles most of functionalities of plugin
		include_once( WOO_VOU_DIR . '/includes/class-woo-vou-model.php' );
		$woo_vou_model = new WOO_Vou_Model();
		
		// Script Class to manage all scripts and styles
		include_once( WOO_VOU_DIR . '/includes/class-woo-vou-scripts.php' );
		$woo_vou_scripts = new WOO_Vou_Scripts();
		$woo_vou_scripts->add_hooks();
		
		//Render class to handles most of html design for plugin
		require_once( WOO_VOU_DIR . '/includes/class-woo-vou-renderer.php' );
		$woo_vou_render = new WOO_Vou_Renderer();
		
		// Admin meta class to handles most of html design for pdf voucher panel
		require_once( WOO_VOU_ADMIN . '/class-woo-vou-admin-meta.php' );
		$woo_vou_admin_meta = new WOO_Vou_Admin_Meta();
		
		//Shortcodes class for handling shortcodes
		require_once( WOO_VOU_DIR . '/includes/class-woo-vou-shortcodes.php' );
		$woo_vou_shortcode = new WOO_Vou_Shortcodes();
		$woo_vou_shortcode->add_hooks();
		
		//Public Class to handles most of functionalities of public side
		require_once( WOO_VOU_DIR . '/includes/class-woo-vou-public.php');
		$woo_vou_public = new WOO_Vou_Public();
		$woo_vou_public->add_hooks();
		
		//Admin Pages Class for admin side
		require_once( WOO_VOU_ADMIN . '/class-woo-vou-admin.php' );
		$woo_vou_admin = new WOO_Vou_Admin();
		$woo_vou_admin->add_hooks();
		
		//Admin Pages Class for admin side
		require_once( WOO_VOU_ADMIN . '/class-woo-vou-upgrade.php' );
		$woo_vou_upgrade = new WOO_Vou_Upgrade();
		$woo_vou_upgrade->add_hooks();
		
		//Settings Tab class for handling settings tab content
		require_once( WOO_VOU_ADMIN . '/class-woo-vou-admin-settings-tabs.php' );
		$woo_vou_settings_tabs = new WOO_Vou_Settings_Tabs();
		$woo_vou_settings_tabs->add_hooks();
		
		if( woo_vou_is_edit_page() ) {
			
			//include the meta functions file for metabox
			require_once ( WOO_VOU_META_DIR . '/woo-vou-meta-box-functions.php' );
			
		}
		
		//Export to CSV Process for used voucher codes
		require_once( WOO_VOU_DIR . '/includes/woo-vou-used-codes-export-csv.php' );
		
		//Generate PDF Process for voucher code and used voucher codes
		require_once( WOO_VOU_DIR . '/includes/woo-vou-used-codes-pdf.php' );
		require_once( WOO_VOU_DIR . '/includes/woo-vou-pdf-process.php' );
		
		//Loads the Templates Functions file
		require_once ( WOO_VOU_DIR . '/includes/woo-vou-template-functions.php' );
		
		//Load the Template Hook File
		require_once ( WOO_VOU_DIR . '/includes/woo-vou-template-hooks.php' );
		
		//Load the Voucher Template Custom Shortcodes File
		require_once ( WOO_VOU_DIR . '/includes/class-woo-vou-template-shortcodes.php' );		
		$woo_vou_template_shortcodes = new WOO_Vou_Template_Shortcodes();
		$woo_vou_template_shortcodes->add_hooks();
		
		// check wpml and woocommerce multilingual plugin is activated
		if( function_exists('icl_object_id') && class_exists('woocommerce_wpml') ) {
			require_once( WOO_VOU_DIR . '/includes/compatibility/class-woo-vou-wpml.php' );
			$woo_vou_wpml = new WOO_Vou_Wpml();
			$woo_vou_wpml->add_hooks();			
		}
		
		// check WC Order SMS Notification plugin is activated
		if( class_exists( 'Sat_WC_Order_SMS' ) ) {
			require_once( WOO_VOU_DIR . '/includes/compatibility/class-woo-vou-order-sms.php' );
			$woo_vou_order_sms = new WOO_Vou_Order_Sms();
			$woo_vou_order_sms->add_hooks();			
		}
		
		// check QTranslateX plugin is activated
		if( defined( 'QTX_VERSION' ) ) {
			require_once( WOO_VOU_DIR . '/includes/compatibility/class-woo-vou-qtranslate-x.php' );
			$woo_vou_qtranslatex = new WOO_Vou_QtranslateX();
			$woo_vou_qtranslatex->add_hooks();
		}
		
		// if WC Vendor Pro plugin is activated
		if( class_exists( 'WCVendors_Pro' ) ) {
			require_once( WOO_VOU_DIR . '/includes/compatibility/class-woo-vou-vendor-pro.php' );
			$woo_vou_vendor_pro = new WOO_Vou_Vendor_Pro();
			$woo_vou_vendor_pro->add_hooks();								
		}
		
	} //end if to check class Woocommerce is exist or not
	
} //end if to check plugin loaded is called or not

//check Social Updater is activated
if( class_exists( 'Wpweb_Upd_Admin' ) ) {
	
	// Plugin updates
	wpweb_queue_update( plugin_basename( __FILE__ ), WOO_VOU_PLUGIN_KEY );
	
	/**
	 * Include Auto Updating Files
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	require_once( WPWEB_UPD_DIR . '/updates/class-plugin-update-checker.php' ); // auto updating
	
	$WpwebWoovouUpdateChecker = new WpwebPluginUpdateChecker (
		'http://wpweb.co.in/Updates/WOOVouchers/license-info.php',
		__FILE__,
		WOO_VOU_PLUGIN_KEY
	);
	
	/**
	 * Auto Update
	 * 
	 * Get the license key and add it to the update checker.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function woo_vou_add_secret_key( $query ) {
		
		$plugin_key	= WOO_VOU_PLUGIN_KEY;
		
		$query['lickey'] = wpweb_get_plugin_purchase_code( $plugin_key );
		return $query;
	}
	
	$WpwebWoovouUpdateChecker->addQueryArgFilter( 'woo_vou_add_secret_key' );
} // end check WPWeb Updater is activated