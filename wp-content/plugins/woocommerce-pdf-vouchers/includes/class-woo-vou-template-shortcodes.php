<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Shortcodes Class
 * 
 * Handles shortcodes functionality of plugin
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
class WOO_Vou_Template_Shortcodes {
	
	public $model;
	
	function __construct() {
		
		global $woo_vou_model;
		$this->model	= $woo_vou_model;
	}
	
	/**
	 * Download PDF form frontend OR backend - Replace shortcodes with value
	 * 
	 * Adding All Shortcodes with value in voucher template html
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function woo_vou_pdf_template_replace_shortcodes( $voucher_template_html, $orderid, $item_key, $items, $voucodes, $productid, $woo_vou_details ) {				
		
		// Creating order object for order id
		$woo_order = new WC_Order( $orderid );				
		//$items = $woo_order_details->get_items(); // no need to it. already passed in filter
		
		// Getting product name
		$woo_product_details = $this->model->woo_vou_get_product_details( $orderid, $items );		
			
		// get product
		$product = wc_get_product( $productid );				
		
		// Order id
		$woo_vou_details['orderid'] = $orderid;
		
		// get payment method title
		$woo_vou_details['payment_method']	= isset( $woo_order->payment_method_title ) ? $woo_order->payment_method_title : '';		 
		
		// Getting Buyer details
		$woo_vou_details['buyeremail'] 	= isset( $woo_order->billing_email ) ? $woo_order->billing_email : '';		
		$buyer_fname 	= isset( $woo_order->billing_first_name ) ? $woo_order->billing_first_name : '';
		$buyer_lname 	= isset( $woo_order->billing_last_name ) ? $woo_order->billing_last_name : '';
		$woo_vou_details['buyername'] = $buyer_fname .' '. $buyer_lname;			
		$woo_vou_details['buyerphone'] =  isset( $woo_order->billing_phone ) ? $woo_order->billing_phone : '';		
		
		// Get recipient data
		$recipient_data	= $this->model->woo_vou_get_recipient_data_using_item_key( $item_key );		
		$woo_vou_details['recipientname']		= isset( $recipient_data['recipient_name'] ) ? $recipient_data['recipient_name'] 	: '';
		$woo_vou_details['recipientemail']		= isset( $recipient_data['recipient_email'] ) ? $recipient_data['recipient_email'] 	: '';
		$woo_vou_details['recipientmessage']	= isset( $recipient_data['recipient_message'] ) ? $recipient_data['recipient_message'] 	: '';				
		
		// Get custom shortcode value here...		
		//$woo_vou_details['custom_shortcode'] = $custom_value;
		
		$voucher_template_html = woo_vou_replace_all_shortcodes_with_value( $voucher_template_html, $woo_vou_details );
	 	
	  	return $voucher_template_html;
	}
	
	/**
	 * Preview PDF - Replace shortcodes with value
	 * 
	 * Adding All Shortcodes with value in voucher template html
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function woo_vou_pdf_template_preview_replace_shortcodes( $voucher_template_html, $voucher_template_id ) {				
		
		global $pdf_voucodes, $woo_vou_model;
		
		$model = $woo_vou_model;
		
		// Check if relative path is enabled
		$vou_relative_path_option	= get_option('vou_enable_relative_path');
		$woo_vou_img_path			= !empty( $vou_relative_path_option ) && $vou_relative_path_option == 'yes' ? WOO_VOU_IMG_DIR : WOO_VOU_IMG_URL;
		
		$woo_vou_details = array();
		
		// site url
		$woo_vou_details['siteurl'] = 'www.bebe.com';
		
		// site logo
		$vousitelogohtml = '';
		$vou_site_url = get_option( 'vou_site_logo' );
		if( !empty( $vou_site_url ) ) {
			if( !empty( $vou_relative_path_option ) && $vou_relative_path_option == 'yes' ) {
				
				$vou_site_attachment_id = $model->woo_vou_get_attachment_id_from_url( $vou_site_url ); 			// Get attachment _id from attachment_url
				$vousitelogohtml = '<img src="' . get_attached_file( $vou_site_attachment_id ) . '" alt="" />'; // Get relative path and append in image tag
			} else {
				$vousitelogohtml = '<img src="' . $vou_site_url . '" alt="" />';
			}
		}
		$woo_vou_details['sitelogo'] = $vousitelogohtml;
		
		// vendor's logo
		$vou_url = $woo_vou_img_path . '/vendor-logo.png';
		$voulogohtml = '<img src="' . $vou_url . '" alt="" />';
		$woo_vou_details['vendorlogo'] = $voulogohtml;
		
		// Vendor address
		$vendor_address = __( 'Infiniti Mall Malad', 'woovoucher' ) . "\n\r" . __( 'GF 9 & 10, Link Road, Mindspace, Malad West', 'woovoucher' ) . "\n\r" . __( 'Mumbai, Maharashtra 400064', 'woovoucher' );
		$woo_vou_details['vendoraddress'] = nl2br( $vendor_address );		
		
		// Vendor Email
		$vendor_email 	= 'vendor_email@gmail.com';
		$woo_vou_details['vendoremail'] = nl2br( $vendor_email );
		
		// next month
		$nextmonth = mktime( date("H"),  date("i"), date("s"), date("m")+1,   date("d"),   date("Y") );
		$woo_vou_details['expiredate'] 		= $this->model->woo_vou_get_date_format( date('d-m-Y', $nextmonth ) );
		$woo_vou_details['expiredatetime'] 	= $this->model->woo_vou_get_date_format( date('d-m-Y H:i:s', $nextmonth ), true );
		
		// previous month
		$previousmonth = mktime( date("H"), date("i"), date("s"), date("m")-1,   date("d"),   date("Y") );
		$woo_vou_details['startdate'] 		= $this->model->woo_vou_get_date_format( date('d-m-Y', $previousmonth ) );
		$woo_vou_details['startdatetime'] 	= $this->model->woo_vou_get_date_format( date('d-m-Y H:i:s', $previousmonth ), true );
		
		// Redeem instruction
		$redeem_instruction = __( 'Redeem instructions :', 'woovoucher' );
		$redeem_instruction .= __( 'Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s.', 'woovoucher' );
		$woo_vou_details['redeem'] = $redeem_instruction;
		
		// vendor locations
		$locations = '<strong>' . __( 'DELHI:', 'woovoucher' ) . '</strong> ' . __( 'Dlf Promenade Mall & Pacific Mall', 'woovoucher' );
		$locations .= ' <strong>' . __( 'MUMBAI:', 'woovoucher' ) . '</strong> ' . __( 'Infiniti Mall, Malad & Phoenix MarketCity', 'woovoucher' );
		$locations .= ' <strong>' . __( 'BANGALORE:', 'woovoucher' ) . '</strong> ' . __( 'Phoenix MarketCity Mall', 'woovoucher' );
		$locations .= ' <strong>' . __( 'PUNE:', 'woovoucher' ) . '</strong> ' . __( 'Phoenix MarketCity Mall', 'woovoucher' );
		$woo_vou_details['location'] = $locations;
		
		// buyer information
		$woo_vou_details['buyername'] 	= __('WpWeb', 'woovoucher');
		$woo_vou_details['buyeremail'] = 'web101@gmail.com';
		$woo_vou_details['buyerphone'] = __( '9999999999','woovoucher' );
		
		// order & product related information
		$woo_vou_details['orderid'] = '101';
		$woo_vou_details['orderdate'] = date("d-m-Y");
		
		$woo_vou_details['productname']			= __('Test Product', 'woovoucher');
		$woo_vou_details['variationname']		= __('Test Variation', 'woovoucher');
		$woo_vou_details['productprice']		= '$'.number_format('10', 2);
		$woo_vou_details['regularprice']		= '$'.number_format('15', 2);
		$woo_vou_details['discounted_amount']  	= '$'.number_format('5', 2);
		$woo_vou_details['payment_method']		= 'Test Payment Method';
		$woo_vou_details['quantity']			= 1;
		$woo_vou_details['sku']    				= 'WooSKU';
		$woo_vou_details['productshortdesc']	= 'Product Short Description';	
		$woo_vou_details['productfulldesc']		= 'Product Full Description';
		
		// Voucher related information
		$codes 			= __( '[The voucher code will be inserted automatically here]', 'woovoucher' );
		$pdf_voucodes	= $codes;
		$woo_vou_details['codes'] = $codes;
		$woo_vou_details['recipientname']	= 'Test Name';
		$woo_vou_details['recipientemail']	= 'recipient@example.com';
		$woo_vou_details['recipientmessage']	= 'Test message';
		
		// Get custom shortcode value here...		
		// $woo_vou_details['custom_shortcode'] = $custom_value;
		
		$voucher_template_html = woo_vou_replace_all_shortcodes_with_value( $voucher_template_html, $woo_vou_details );				
		  
	  	return $voucher_template_html;	  	
	}
	
	/**
	 * Adding Hooks
	 * 
	 * Adding proper hoocks for the shortcodes.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function add_hooks() {
		
		// Add filter to replace all voucher template shortcodes
		add_filter( 'woo_vou_pdf_template_inner_html', array( $this, 'woo_vou_pdf_template_replace_shortcodes' ), 10, 7 );	
		
		// Add filter to replace all voucher template shortcodes in preview pdf
		add_filter( 'woo_vou_pdf_template_preview_html', array( $this, 'woo_vou_pdf_template_preview_replace_shortcodes' ), 10, 2 );		
	}
}
?>