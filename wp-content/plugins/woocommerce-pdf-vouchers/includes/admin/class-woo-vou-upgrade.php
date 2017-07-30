<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Upgrade Class
 *
 * Handles generic Upgrade functionality and AJAX requests.
 *
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.0
 */
class WOO_Vou_Upgrade {
	
	var $scripts, $model, $render;
	
	public function __construct() {
		
		global $woo_vou_scripts,$woo_vou_model,
				$woo_vou_render, $woo_vou_admin_meta;
		
		$this->scripts 	= $woo_vou_scripts;
		$this->model 	= $woo_vou_model;
	}
	
	/**
	 * Adding Upgrade Submenu Page
	 * 
	 * Handles to adding upgrade submenu page
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.0
	 */
	public function woo_vou_upgrade_submenu() {
		
		//add page for upgrates 
		$vou_upgrades_screen = add_submenu_page( null, __( 'PDF Voucher Upgrades', 'woovoucher' ), __( 'PDF Voucher Upgrades', 'woovoucher' ), 'install_plugins', 'vou-upgrades', array( $this, 'woo_vou_upgrades_screen' ) );
	}
	
	/**
	 * Render Upgrades Screen
	 * 
	 * Handle to render upgrade screen
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.0
	 */
	function woo_vou_upgrades_screen() {
		?>
		<div class="wrap">
			<h2><?php echo __( 'PDF Vouchers - Upgrades', 'woovoucher' ); ?></h2>
			<div id="vou-upgrade-status">
				<p>
					<?php echo __( 'The upgrade process has started, please be patient. This could take several minutes. You will be automatically redirected when the upgrade is finished.', 'woovoucher' ); ?>
					<img src="<?php echo WOO_VOU_URL . '/includes/images/ajax-loader.gif'; ?>" id="vou-upgrade-loader"/>
				</p>
			</div>
			<script type="text/javascript">
				jQuery( document ).ready( function() {
					// Trigger upgrades on page load
					var data = { action: 'woo_vou_trigger_upgrades' };
					jQuery.post( ajaxurl, data, function (response) {
						if( response == 'complete' ) {
							jQuery('#vou-upgrade-loader').hide();
							document.location.href = 'index.php'; // Redirect to the welcome page
						}
					});
				});
			</script>
		</div><?php
	}
	
	/**
	 * Display Upgrade Notices
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.0
	 */
	function woo_vou_show_upgrade_notices() {
	
		$woo_vou_plugin_version = get_option( 'woo_vou_plugin_version' );
	
		if ( ! $woo_vou_plugin_version ) {
			// 2.2.1 is the first version to use this option so we must add it
			$woo_vou_plugin_version = '2.2.1';
		}
	
		//Get Valid version number
		$woo_vou_plugin_version = preg_replace( '/[^0-9.].*/', '', $woo_vou_plugin_version );
	
		if ( version_compare( $woo_vou_plugin_version, '2.3.0', '<' ) ) {
			printf(
				'<div class="updated"><p>' . esc_html__( 'WooCommerce Pdf Voucher needs to upgrade the order database, click %shere%s to start the upgrade.', 'woovoucher' ) . '</p></div>',
				'<a href="' . esc_url( admin_url( 'index.php?page=vou-upgrades&vou-upgrade=upgrade_db' ) ) . '">',
				'</a>'
			);
		}
	}
	
	/**
	 * Triggers all upgrade functions
	 *
	 * This function is usually triggered via AJAX
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.0
	 */
	function woo_vou_trigger_upgrades() {
	
		if( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( __( 'You do not have permission to do shop upgrades', 'woovoucher' ), __( 'Error', 'woovoucher' ), array( 'response' => 403 ) );
		}
		
		$woo_vou_plugin_version = get_option( 'woo_vou_plugin_version' );
		
		if ( ! $woo_vou_plugin_version ) {
			// 2.2.1 is the first version to use this option so we must add it
			$woo_vou_plugin_version = '2.2.1';
			add_option( 'woo_vou_plugin_version', $woo_vou_plugin_version );
		}
		
		if ( version_compare( $woo_vou_plugin_version, '2.3.0', '<' ) ) {
			$this->woo_vou_v230_upgrades();
		}
		
		update_option( 'woo_vou_plugin_version', WOO_VOU_PLUGIN_VERSION );
		
		if ( DOING_AJAX ) {
			die( 'complete' ); // Let AJAX know that the upgrade is complete
		}
	}
	
	/**
	 * Upgrades for PDF Voucher v2.3.0
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.0
	 */
	function woo_vou_v230_upgrades() {
		
		//Run upgarade old orders script
		$this->woo_vou_admin_run_udater_script();
		
		//Sleep for 10 seconds
		sleep( 10 );
	}
	
	/**
	 * Show Downloadable Option
	 * 
	 * Handle to show downloadable option for booking product type
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.0
	 */
	public function woo_vou_admin_run_udater_script() {
		
		$prefix = WOO_VOU_META_PREFIX;
		
		$args	= array( 
						'post_type'			=> WOO_VOU_MAIN_SHOP_POST_TYPE,
						'posts_per_page'	=> -1,
						'post_status'		=> 'any',
						'meta_query'		=> array(
													array(
														'key'		=> $prefix . 'order_details',
														'compare'	=> 'EXISTS',
													)
												)
					);
		
		//Get results
		$results	= new WP_Query( $args );
		
		// Get OLD Orders
		$orders		= isset( $results->posts ) ? $results->posts : '';
		
		if( !empty( $orders ) ) {//If or der is not empty
			
			foreach ( $orders as $shop_order ) {//For all old orders
				
				$order_id	= isset( $shop_order->ID ) ? $shop_order->ID : '';
				
				if( !empty( $order_id ) ) {// If order_id not empty
					
					$order_vouchers = array();
					
					$old_meta		= get_post_meta( $order_id, $prefix.'order_details', true );
					$order			= new WC_Order( $order_id );
					$order_items	= $order->get_items();
					
					if( !empty( $order_items ) ) { // If not empty items
						
						foreach ( $order_items as $item_key => $item_data ) {
							
							// If product is variable product take variation id else product id
							$data_id	= ( !empty( $item_data['variation_id'] ) ) ? $item_data['variation_id'] : $item_data['product_id'];
							
							//Order Vouchers Meta
							$order_vouchers[$item_key] = isset( $old_meta[$data_id]['codes'] ) ? $old_meta[$data_id]['codes'] : '';
							
						}
					}
					
					if( !empty( $order_vouchers ) ) {//If not empty old order meta
						
						foreach ( $order_vouchers as $order_item_key => $order_voucher ) {
							
							wc_update_order_item_meta( $order_item_key, $prefix.'codes', $order_voucher );
						}
					}
					
					//Delete older voucher codes post meta
					delete_post_meta( $order_id, $prefix.'order_details' );
				}
			}
		}
	}
	
	/**
	 * Adding Hooks
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function add_hooks() {
		
		//add submenu page
		add_action( 'admin_menu', array( $this, 'woo_vou_upgrade_submenu' ) );
		
		//Woocomerce PDF voucher updater script
		add_action( 'admin_notices', array( $this, 'woo_vou_show_upgrade_notices' ) );
		add_action( 'wp_ajax_woo_vou_trigger_upgrades', array( $this, 'woo_vou_trigger_upgrades' ) );
	}
}