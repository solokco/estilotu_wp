<?php 
/**
 * Panel HTML Class
 *
 * To handles some small panel HTML content for backend
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.4.1
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class WOO_Vou_Admin_Meta { 
	
	public $model;
	
	function __construct() {
		
		global $woo_vou_model;
		
		$this->model = $woo_vou_model;
		
	}
	
	/**
	 * WooCommerce custom product tab
	 * 
	 * Adds a new tab to the Product Data postbox in the admin product interface
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_product_write_panel_tab() {
		 
		echo "<li class=\"woo_vou_voucher_tab show_if_downloadable show_if_variable\"><a href=\"#woo_vou_voucher\">" . __( 'PDF Vouchers', 'woovoucher' ) . "</a></li>";
	}
	
	/**
	 * WooCommerce custom product tab data
	 * 
	 * Adds the panel to the Product Data postbox in the product interface
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_product_write_panel() {
		
		$prefix = WOO_VOU_META_PREFIX;
		
		global $current_user, $woo_vou_vendor_role;
		
		$voucher_options 	= array( '' => __( 'Please Select', 'woovoucher' ) );
		$voucher_data 		= $this->model->woo_vou_get_vouchers();
		foreach ( $voucher_data as $voucher ) {
			if( isset( $voucher['ID'] ) && !empty( $voucher['ID'] ) ) { // Check voucher id is not empty
				$voucher_options[$voucher['ID']] = $voucher['post_title'];
				$multiple_voucher_options[$voucher['ID']] = $voucher['post_title'];
			}
		}
		
		$vendors_options = array( '' => __( 'Please Select', 'woovoucher' ) );
		
		if( !empty( $woo_vou_vendor_role ) ) {
			
			foreach ( $woo_vou_vendor_role as $vonder_role ) {
				
				$vendors_data = get_users( array( 'role' => $vonder_role ) );
				
				if( !empty( $vendors_data ) ) { // Check vendor users are not empty
					
					foreach ( $vendors_data as $vendors ) {
						
						$vendors_options[$vendors->ID] = $vendors->display_name . ' (#' . $vendors->ID . ' &ndash; ' . sanitize_email( $vendors->user_email ) . ')';
					}
				}
			}
		}
		
		$based_on_purchase_opt = array(
										'7' 		=> '7 Days',
										'15' 		=> '15 Days',
										'30' 		=> '1 Month (30 Days)',
										'90' 		=> '3 Months (90 Days)',
										'180' 		=> '6 Months (180 Days)',
										'365' 		=> '1 Year (365 Days)',
										'cust'		=> 'Custom',
									);
		
		$using_type_opt 		= array(
										'' 	=> __( 'Default', 'woovoucher' ), 
										'0' => __( 'One time only', 'woovoucher' ), 
										'1' => __( 'Unlimited', 'woovoucher' )
									);					
		
		// Voucher Code Error
		$vou_codes_error_class	= ' woo-vou-display-none ';
		$codes_error_msg		= '<br/><span id="woo_vou_codes_error" class="woo-vou-codes-error ' . $vou_codes_error_class . '">' . __( 'Please enter atleast 1 voucher code.', 'woovoucher' ) . '</span>';
		$days_error_msg			= '<span id="woo_vou_days_error" class="woo-vou-days-error ' . $vou_codes_error_class . '">' . __( ' Please enter valid days.', 'woovoucher' ) . '</span>';
		$website_url_error_msg	= '<br/><span id="woo_vou_website_url_error" class="woo-vou-website-url-error woo-vou-codes-error ' . $vou_codes_error_class . '">' . __( ' Please enter valid url.', 'woovoucher' ) . '</span>';
		
		
		$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
		$user_role	= array_shift( $user_roles );
		
		$vendor_flag = false;
		if(!empty($user_role) && in_array( $user_role, $woo_vou_vendor_role )) {  // Check vendor user role
			
			$vendor_flag = true;
		}	
		$vou_hide_vendor_options = get_option('vou_hide_vendor_options');
	
		// display the custom tab panel
		echo '<div id="woo_vou_voucher" class="panel wc-metaboxes-wrapper woocommerce_options_panel tabs-content hide-all">';
			
			//Enable Voucher Code
			$this->woo_vou_add_checkbox( array( 'id' => $prefix . 'enable', 'label' => __('Enable Voucher Codes:', 'woovoucher' ), 'description' => __( 'To enable the Voucher for this Product check the "Enable Voucher Codes" check box.', 'woovoucher' ) ) );
			
			//action to add setting here
			do_action( 'woo_vou_add_meta_setting_after_voucher_enable', $this );
			
			//Recipient Name Detail
			$this->woo_vou_add_checkbox( array( 'id' => $prefix . 'enable_recipient_name', 'label' => __('Enable Recipient Name:', 'woovoucher' ), 'description' => __( 'To enable the Recipient Name on product page', 'woovoucher' ) ) );
			echo '<div class="recipient-detail-wrap">';
				$this->woo_vou_add_text( array( 'id' => $prefix . 'recipient_name_label', 'class' => 'woo_vou_recipient_text', 'wrap_class' => 'woo_vou_recipient_details', 'label' => __( 'Label:', 'woovoucher' ), 'description' => __( '', 'woovoucher' ) . $days_error_msg ) );
				$this->woo_vou_add_text( array( 'id' => $prefix . 'recipient_name_max_length', 'class' => 'woo_vou_recipient_text', 'wrap_class' => 'woo_vou_recipient_details', 'label' => __( 'Max Length:', 'woovoucher' ), 'description' => __( '', 'woovoucher' ) . $days_error_msg ) );
				$this->woo_vou_add_cust_checkbox( array( 'id' => $prefix . 'recipient_name_is_required', 'label' => __('Required:', 'woovoucher' ), 'description' => __( 'Make this field required in order to add a voucher product to the cart', 'woovoucher' ) ) );
			echo '</div>';
			
			do_action( 'woo_vou_add_meta_setting_after_recipient_name', $this );
			
			//Recipient Email Detail
			$this->woo_vou_add_checkbox( array( 'id' => $prefix . 'enable_recipient_email', 'label' => __('Enable Recipient Email:', 'woovoucher' ), 'description' => __( 'To enable the Recipient Email on product page', 'woovoucher' ) ) );
			echo '<div class="recipient-detail-wrap">';
				$this->woo_vou_add_text( array( 'id' => $prefix . 'recipient_email_label', 'class' => 'woo_vou_recipient_text', 'wrap_class' => 'woo_vou_recipient_details', 'label' => __( 'Label:', 'woovoucher' ), 'description' => __( '', 'woovoucher' ) . $days_error_msg ) );
				$this->woo_vou_add_cust_checkbox( array( 'id' => $prefix . 'recipient_email_is_required', 'label' => __('Required:', 'woovoucher' ), 'description' => __( 'Make this field required in order to add a voucher product to the cart', 'woovoucher' ) ) );
			echo '</div>';
			
			//action to add setting here
			do_action( 'woo_vou_add_meta_setting_after_recipient_email', $this );
			
			//Recipient Message Detail
			$this->woo_vou_add_checkbox( array( 'id' => $prefix . 'enable_recipient_message', 'label' => __('Enable Recipient Message:', 'woovoucher' ), 'description' => __( 'To enable the Recipient Message on product page', 'woovoucher' ) ) );
			echo '<div class="recipient-detail-wrap">';
				$this->woo_vou_add_text( array( 'id' => $prefix . 'recipient_message_label', 'class' => 'woo_vou_recipient_text', 'wrap_class' => 'woo_vou_recipient_details', 'label' => __( 'Label:', 'woovoucher' ), 'description' => __( '', 'woovoucher' ) ) );
				$this->woo_vou_add_text( array( 'id' => $prefix . 'recipient_message_max_length', 'class' => 'woo_vou_recipient_text', 'wrap_class' => 'woo_vou_recipient_details', 'label' => __( 'Max Length:', 'woovoucher' ), 'description' => __( '', 'woovoucher' ) ) );
				$this->woo_vou_add_cust_checkbox( array( 'id' => $prefix . 'recipient_message_is_required', 'label' => __(' Required:', 'woovoucher' ), 'description' => __( 'Make this field required in order to add a voucher product to the cart', 'woovoucher' ) ) );
			echo '</div>';
			
			do_action( 'woo_vou_add_meta_setting_after_recipient_message', $this );
			
			// Recipient's Date Detail for sending Gift Voucher
			$this->woo_vou_add_checkbox( array( 'id' => $prefix . 'enable_recipient_giftdate', 'label' => __('Enable Recipient Gift Date:', 'woovoucher' ), 'description' => __( 'To enable the Recipient\'s Gift Date Selection on product page', 'woovoucher' ) ) );
			echo '<div class="recipient-detail-wrap">';
				$this->woo_vou_add_text( array( 'id' => $prefix . 'recipient_giftdate_label', 'class' => 'woo_vou_recipient_text', 'wrap_class' => 'woo_vou_recipient_details', 'label' => __( 'Label:', 'woovoucher' ), 'description' => __( '', 'woovoucher' ) ) );
				$this->woo_vou_add_cust_checkbox( array( 'id' => $prefix . 'recipient_giftdate_is_required', 'label' => __(' Required:', 'woovoucher' ), 'description' => __( 'Make this field required in order to add a voucher product to the cart', 'woovoucher' ) ) );
			echo '</div>';
			
			do_action( 'woo_vou_add_meta_setting_after_recipient_giftdate', $this );
			
			//product start date time
			$this->woo_vou_add_datetime( array( 'id' => $prefix . 'product_start_date', 'label' => __('Product Start Date:', 'woovoucher'), 'std' => array(''), 'description' => __('If you want to make the Product valid for a specific time only, you can enter an start date here.', 'woovoucher'), 'format'=>'dd-mm-yy' ) );
			
			do_action( 'woo_vou_add_meta_setting_after_product_start_date', $this );
			
			//product expiration date time
			$this->woo_vou_add_datetime( array( 'id' => $prefix . 'product_exp_date', 'label' => __('Product End Date:', 'woovoucher'), 'std' => array(''), 'description' => __('If you want to make the Product valid for a specific time only, you can enter an end date here.', 'woovoucher'), 'format'=>'dd-mm-yy' ) );
			
			do_action( 'woo_vou_add_meta_setting_after_product_exp_date', $this );
			
			//Enable Template Selection
			$this->woo_vou_add_checkbox( array( 'id' => $prefix . 'enable_pdf_template_selection', 'label' => __('Enable Template Selection:', 'woovoucher' ), 'description' => __( 'To enable the PDF Template Selection on product page', 'woovoucher' ) ) );
			echo '<div class="recipient-detail-wrap">';
				$this->woo_vou_add_text( array( 'id' => $prefix . 'pdf_template_selection_label', 'class' => 'woo_vou_recipient_text', 'wrap_class' => 'woo_vou_recipient_details', 'label' => __( 'Label:', 'woovoucher' ), 'description' => __( '', 'woovoucher' ) ) );
				//$this->woo_vou_add_cust_checkbox( array( 'id' => $prefix . 'pdf_template_selection_is_required', 'label' => __(' Required:', 'woovoucher' ), 'description' => __( 'Make this field required in order to add a voucher product to the cart', 'woovoucher' ) ) );
				$this->woo_vou_add_select( array( 'id' => $prefix . 'pdf_template_selection', 'style' => 'width:96%; margin-bottom:20px;', 'class' => '_woo_vou_pdf_template_selection wc-enhanced-select', 'options' => $multiple_voucher_options, 'multiple' => true, 'label'=> __( 'Select Pdf Template:', 'woovoucher' ), 'description' => __( '', 'woovoucher' ), 'sign' => __( '', 'woovoucher' ) ) );
			echo '</div>';
			
			do_action( 'woo_vou_add_meta_setting_after_pdf_template_selection', $this );
			
			// if user is vendor and hide options set 
			if($vendor_flag == true  && $vou_hide_vendor_options == 'yes') { } else { 
			
				//PDF Template
				woocommerce_wp_select( array( 'id' => $prefix . 'pdf_template', 'style' => 'min-width:200px;', 'class' => 'wc-enhanced-select', 'options' => $voucher_options, 'label' => __( 'PDF Template:', 'woovoucher' ), 'description' => __( 'Select a PDF template. This setting modifies the global PDF template setting and overrides vendor\'s PDF template value. Leave it empty to use the global/vendor settings.', 'woovoucher' ) ) );
				do_action( 'woo_vou_add_meta_setting_after_pdf_template', $this );
			}
			
			if( $vendor_flag == true ) {  // Check vendor user role
				
				woocommerce_wp_hidden_input( array( 'id' => $prefix . 'vendor_user', 'value' => $current_user->ID ));
				
			} else {
			
				//Vendor User
   				woocommerce_wp_select( array( 'id' => $prefix . 'vendor_user', 'style' => 'min-width:200px;', 'class' => 'wc-enhanced-select', 'options' => $vendors_options, 'label' => __( 'Primary Vendor User:', 'woovoucher' ), 'description' => __( 'Please select the primary vendor user.', 'woovoucher' ) ) );

   				$sec_vendors_options	= $vendors_options;
   				unset( $sec_vendors_options[''] );

				//Secondary Vendor User
   				$this->woo_vou_add_select( array( 'id' => $prefix . 'sec_vendor_users', 'style' => 'min-width:250px;max-width: 75%;', 'class' => 'wc-enhanced-select', 'options' => $sec_vendors_options, 'multiple' => true, 'label' => __( 'Secondary Vendor Users:', 'woovoucher' ), 'description' => __( 'Please select the secondary vendor users. you can select multiple users as secondary vendor users.', 'woovoucher' ), 'sign' => __( '', 'woovoucher' ) ) );
			}
			
			do_action( 'woo_vou_add_meta_setting_after_vendor_user', $this );
			
   			if($vendor_flag == true  && $vou_hide_vendor_options == 'yes') { } else { 
   				
				//voucher's type to use it
				//woocommerce_wp_select( array( 'id' => $prefix . 'using_type', 'style' => 'min-width:200px;', 'class' => 'wc-enhanced-select',  'options' => $using_type_opt, 'label'=> __( 'Usability:', 'woovoucher' ), 'description' => sprintf( __( 'Choose how you wanted to use vouchers codes. %sif you set usability "%sone time only%s" then it will automatically set product quantity equal to number of voucher codes entered and it will automatically decrease quanity  by 1 when it get purchased. if you set usability "%sunlimited%s" then plugin will automatically generate unique voucher codes when product  purchased. %sThis setting modifies the global usability setting and overrides vendor\'s usability value. Leave it empty to use the global/vendor settings.', 'woovoucher' ), '<br />', '<b>', '</b>', '<b>', '</b>', '<br />' ) ) );
				$this->woo_vou_add_select( array( 'id' => $prefix . 'using_type', 'style' => 'min-width:200px;', 'class' => 'wc-enhanced-select',  'options' => $using_type_opt, 'label'=> __( 'Usability:', 'woovoucher' ), 'description' => sprintf( __( 'Choose how you wanted to use vouchers codes. %sif you set usability "%sone time only%s" then it will automatically set product quantity equal to number of voucher codes entered and it will automatically decrease quanity  by 1 when it get purchased. if you set usability "%sunlimited%s" then plugin will automatically generate unique voucher codes when product  purchased. %sThis setting modifies the global usability setting and overrides vendor\'s usability value. Leave it empty to use the global/vendor settings.', 'woovoucher' ), '<br />', '<b>', '</b>', '<b>', '</b>', '<br />' ) ) );
				do_action( 'woo_vou_add_meta_setting_after_using_type', $this );
			}
			//voucher's code comma seprated
			//woocommerce_wp_textarea_input( array( 'id' => $prefix . 'codes', 'label' => __( 'Voucher Codes:', 'woovoucher' ), 'description' => __( 'If you have a list of Voucher Codes you can copy and paste them in to this option. Make sure, that they are comma separated.', 'woovoucher' ) . $codes_error_msg ) );			
			$this->woo_vou_add_textarea_input( array( 'id' => $prefix . 'codes', 'label' => __( 'Voucher Codes:', 'woovoucher' ),'description' => __( 'If you have a list of Voucher Codes you can copy and paste them in to this option. Make sure, that they are comma separated', 'woovoucher' ) . $codes_error_msg ) );
			
			do_action( 'woo_vou_add_meta_setting_after_codes', $this );
			
			//import to csv field
			$this->woo_vou_add_importcsv( array( 'id' => $prefix . 'import_csv', 'btntext' => __( 'Generate / Import Codes', 'woovoucher' ), 'label' => __( 'Generate / Import Codes:', 'woovoucher' ), 'description' => __( 'Here you can import a csv file with voucher vodes or you can enter the prefix, pattern and extension will automatically create the voucher codes.', 'woovoucher' ) ) );
			
			do_action( 'woo_vou_add_meta_setting_after_import_csv', $this );
			
			//purchased voucher codes field
			$this->woo_vou_add_purchasedvoucodes( array( 'id' => $prefix . 'purchased_codes', 'btntext' => __( 'Purchased Voucher Codes', 'woovoucher' ), 'label' => __( 'Purchased Voucher Code:', 'woovoucher' ), 'description' => __( 'Click on the button to see a list of all purchased voucher vodes.', 'woovoucher' ) ) );
			
			do_action( 'woo_vou_add_meta_setting_after_purchased_codes', $this );
			
			//used voucher codes field
			$this->woo_vou_add_usedvoucodes( array( 'id' => $prefix . 'used_codes', 'btntext' => __( 'Used Voucher Codes', 'woovoucher' ), 'label' => __( 'Used Voucher Code:', 'woovoucher' ), 'description' => __( 'Click on the button to see a list of all used voucher vodes.', 'woovoucher' ) ) );
			
			do_action( 'woo_vou_add_meta_setting_after_used_codes', $this );
			
			//voucher expiration date type
			
			$expdate_types = apply_filters('woo_vou_exp_date_types',array( 'specific_date' => __( 'Specific Time', 'woovoucher' ), 'based_on_purchase' => __( 'Based on purchase', 'woovoucher' ) ));
			
			$this->woo_vou_add_radio( array( 'id' => $prefix . 'exp_type','options' => $expdate_types, 'default'=> array( 'specific_date' ), 'label'=> __( 'Expiration Date Type:', 'woovoucher' ), 'description' => __( 'Please select Expiration Date Type either specific time or set date based on purchased voucher date like After 7 days, 30 days, 1 year etc.', 'woovoucher' ) ) );
			
			do_action( 'woo_vou_add_meta_setting_after_exp_type', $this );
			
			//
			$this->woo_vou_add_select( array( 'id' => $prefix . 'days_diff', 'style' => 'min-width:200px;', 'class' => '_woo_vou_days_diff wc-enhanced-select', 'options' => $based_on_purchase_opt, 'label'=> __( 'Expiration Days:', 'woovoucher' ), 'description' => __( '', 'woovoucher' ), 'sign' => __( ' After purchase', 'woovoucher' ) ) );
			
			do_action( 'woo_vou_add_meta_setting_after_days_diff', $this );
			
			//voucher expiration date custom days
			$this->woo_vou_add_custom_text( array( 'id' => $prefix . 'custom_days', 'class' => 'custom-days-text', 'label' => __( 'Custom Days:', 'woovoucher' ), 'description' => __( '', 'woovoucher' ) . $days_error_msg  , 'sign' => __( ' Days after purchase', 'woovoucher' ) ) );
			
			do_action( 'woo_vou_add_meta_setting_after_custom_days', $this );
			
			//voucher start date time
			$this->woo_vou_add_datetime( array( 'id' => $prefix . 'start_date', 'label' => __('Start Date:', 'woovoucher'),'std' => array(''),'description' => __('If you want to make the Voucher Code(s) valid for a specific time only, you can enter an start date here.', 'woovoucher'),'format'=>'dd-mm-yy' ) );
			
			do_action( 'woo_vou_add_meta_setting_after_start_date', $this );
			
			//voucher expiration date time
			$this->woo_vou_add_datetime( array( 'id' => $prefix . 'exp_date', 'label' => __('Expiration Date:', 'woovoucher'),'std' => array(''),'description' => __('If you want to make the Voucher Code(s) valid for a specific time only, you can enter an expiration date here. If the Voucher Code never expires, then leave that option blank.', 'woovoucher'),'format'=>'dd-mm-yy' ) );
			
			do_action( 'woo_vou_add_meta_setting_after_exp_date', $this );
			
			//disable redeem voucher
			$redeem_days = array( 
				'Monday' => __( 'Monday', 'woovoucher' ), 
				'Tuesday' => __( 'Tuesday', 'woovoucher' ), 
				'Wednesday' => __( 'Wednesday', 'woovoucher' ),
				'Thursday' => __( 'Thursday', 'woovoucher' ), 
				'Friday' => __( 'Friday', 'woovoucher' ),
				'Saturday' => __( 'Saturday', 'woovoucher' ),
				'Sunday' => __( 'Sunday', 'woovoucher' )
			);
			
			$this->woo_vou_add_multiple_checkbox( array( 'id' => $prefix . 'disable_redeem_day','options' => $redeem_days, 'default'=> array( 'monday' ), 'label'=> __( 'Choose which days voucher can not  be used:', 'woovoucher' ), 'description' => __( 'If you want to restrict  use of Voucher Code(s)  for specific days, you can select days here. Leave it blank for no restriction. ', 'woovoucher' ) ) );						
			
			if( $vendor_flag == true  && $vou_hide_vendor_options == 'yes') { } else { 
				
				//add the vendor's logo
				$this->woo_vou_add_image( array( 'id' => $prefix . 'logo', 'label' => __( 'Vendor\'s Logo:', 'woovoucher' ), 'description' => __( 'Allows you to upload a logo of the vendor for which this Voucher is valid. The logo will also be displayed on the PDF document. Leave it empty to use the vendor logo from the vendor settings.', 'woovoucher' ) ) );
				
				do_action( 'woo_vou_add_meta_setting_after_logo', $this );
				
				//vendor's address
				//woocommerce_wp_textarea_input( array( 'id' => $prefix . 'address_phone', 'label' => __( 'Vendor\'s Address:', 'woovoucher' ), 'description' => __( 'Here you can enter the complete Vendor\'s address. This will be displayed on the PDF document sent to the customers so that they know where to redeem this Voucher. Limited HTML is allowed. Leave it empty to use address from the vendor settings.', 'woovoucher' ) ) );
				$this->woo_vou_add_textarea_input( array( 'id' => $prefix . 'address_phone', 'label' => __( 'Vendor\'s Address:', 'woovoucher' ),'description' => __( 'Here you can enter the complete Vendor\'s address. This will be displayed on the PDF document sent to the customers so that they know where to redeem this Voucher. Limited HTML is allowed. Leave it empty to use address from the vendor settings.', 'woovoucher' ) ) );
				
				do_action( 'woo_vou_add_meta_setting_after_address_phone', $this );
				
				//vendor's website
				//woocommerce_wp_text_input( array( 'id' => $prefix . 'website', 'class' => 'woo_vou_siteurl_text', 'label' => __( 'Website URL:', 'woovoucher' ), 'description' => __( 'Enter the Vendor\'s website URL here. This will be displayed on the PDF document sent to the customer. Leave it empty to use website URL from the vendor settings.', 'woovoucher' ).$website_url_error_msg ) );
				$this->woo_vou_text_input( array( 'id' => $prefix . 'website', 'class' => 'woo_vou_siteurl_text', 'wrap_class' => 'woo_vou_siteurl_text', 'label' => __( 'Website URL:', 'woovoucher' ), 'description' => __( 'Enter the Vendor\'s website URL here. This will be displayed on the PDF document sent to the customer. Leave it empty to use website URL from the vendor settings.', 'woovoucher' ).$website_url_error_msg ) );
				
				
				do_action( 'woo_vou_add_meta_setting_after_website', $this );
				
				//using instructions of voucher
				//woocommerce_wp_textarea_input( array( 'id' => $prefix . 'how_to_use', 'label' => __( 'Redeem Instructions:', 'woovoucher' ), 'description' => __( 'Within this option you can enter instructions on how this Voucher can be redeemed. This instruction will then be displayed on the PDF document sent to the customer after successful purchase. Limited HTML is allowed. Leave it empty to use Redeem Instructions from the vendor settings.', 'woovoucher' ) ) );
				$this->woo_vou_add_textarea_input( array( 'id' => $prefix . 'how_to_use', 'label' => __( 'Redeem Instructions:', 'woovoucher' ),'description' => __( 'Within this option you can enter instructions on how this Voucher can be redeemed. This instruction will then be displayed on the PDF document sent to the customer after successful purchase. Limited HTML is allowed. Leave it empty to use Redeem Instructions from the vendor settings.', 'woovoucher' ) ) );
				
				do_action( 'woo_vou_add_meta_setting_after_how_to_use', $this );
				
				//location fields
				$voucherlocations	= apply_filters( 'woo_vou_add_meta_location_fields', array( 
												'0'	=>	array( 'id' => $prefix. 'locations', 'class' => 'woo_vou_location', 'label'=> __( 'Location:', 'woovoucher' ), 'description' => __( 'Enter the address of the location where the Voucher Code can be redeemed. This will be displayed on the PDF document sent to the customer. Limited HTML is allowed.', 'woovoucher' )),
												'1'	=>	array( 'id' => $prefix. 'map_link', 'class' => 'woo_vou_location', 'label'=> __( 'Location Map Link:', 'woovoucher' ), 'description' => __( 'Enter a link to a Google Map for the location here. This will be displayed on the PDF document sent to the customer.', 'woovoucher' ))
											) );
				
				//locations for voucher block is available
				$this->woo_vou_add_repeater_block( array( 'id' => $prefix. 'avail_locations', 'label' => __( 'Locations:', 'woovoucher' ), 'description' => __( 'If the Vendor of the Voucher has more than one location where the Voucher can be redeemed, then you can add all the locations within this option.  Leave it empty to use locations from the vendor settings.', 'woovoucher' ), 'fields' => $voucherlocations ) );
			}
			
			do_action( 'woo_vou_add_meta_setting_after_location', $this );
		echo '</div>';
	}
	
	
	/**
	 * Show text Field.
	 *
	 * @param string $args
	 * @param string $echo 
	 * @since 2.8.2
	 * @access public
	 */
	function woo_vou_text_input( $args, $echo = true ){

		$html = '';	
		
		$new_field = array( 'type' => 'text', 'name' => 'Text Field' );
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );		
		
		$html = '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . '">';
		$html .= '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';		
		$html .= '<input type="text" name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" value="'. esc_attr( $meta ) . '" > ';

		if ( ! empty( $field['description'] ) ) {
	
			if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
				$html .= wc_help_tip( $field['description'] );
			} else {
				$html .= '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
			}
		}
		$html.= '</p>';
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}	
		
	}	
	
	/**
	 * Show textarea Field.
	 *
	 * @param string $args
	 * @param string $echo 
	 * @since 2.8.2
	 * @access public
	 */
	function woo_vou_add_textarea_input( $args, $echo = true ) {
		
		$html = '';	
		
		$new_field = array( 'type' => 'textarea', 'name' => 'Textarea Field' );
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );
		
		$html = '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . '">';
		$html .= '<label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label>';		
		$html .= '<textarea name="' . esc_attr( $field['id'] ) . '" id="' . esc_attr( $field['id'] ) . '" rows="2" cols="20" >' . esc_attr( $meta ) . '</textarea> ';

		if ( ! empty( $field['description'] ) ) {
	
			if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
				$html .= wc_help_tip( $field['description'] );
			} else {
				$html .= '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
			}
		}
		$html.= '</p>';
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	/**
	 * Show multiple Field Checkbox.
	 *
	 * @param string $field 
	 * @param string $meta 
	 * @since 2.7.2
	 * @access public
	 */
	function woo_vou_add_multiple_checkbox($args, $echo = true ) {
		
		$html = '';
		
		$new_field = array( 'type' => 'checkbox', 'name' => 'Radio Field' );
		$field = array_merge( $new_field, $args );
		
		$default_meta = isset( $field['default'] ) ? $field['default'] : '';
		
		$meta = woo_vou_meta_value( $field );
		$meta = !empty( $meta ) ? $meta : $default_meta;
		
		if( ! is_array( $meta ) ) {
			$meta = (array) $meta;
		}
	  
		$html .= '<p class="form-field ' . $field['id'] . '_field"><label style="display:block;" for="' . $field['id'] . '">' . $field['label'] . '</label>';
		
		foreach ( $field['options'] as $key => $value ) {
			$html .= "<input type='checkbox' id='{$field['id']}_{$key}' class='woo-vou-meta-radio' name='{$field['id']}[]' value='{$key}'" . checked( in_array( $key, $meta ), true, false ) . " /> <label for='{$field['id']}_{$key}' class='woo-vou-meta-multi-checkbox-label woo_vou_radio'>{$value}</label>";
		}
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';
			
		$html .= '</p>';
		
		$html = apply_filters( 'woo_vou_multiple_checkbox_html', $html, $args );
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	 }

	/**
	 * Show Field Checkbox.
	 *
	 * @param string $field 
	 * @param string $meta 
	 * @since 1.0
	 * @access public
	 */
	function woo_vou_add_checkbox( $args, $echo = true ) {
		
		$html = '';
		
		$new_field = array( 'type' => 'checkbox', 'name' => 'Checkbox Field' );
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );
		
		$html .= '<p class="form-field ' . $field['id'] . '_field"><label style="display:block;" for="' . $field['id'] . '">' . $field['label'] . '</label>';
		
		$html .= "<input type='checkbox' class='woo-vou-meta-checkbox' name='{$field['id']}' id='{$field['id']}'" . checked(!empty($meta), true, false) . " />";
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';
			
		$html .= '</p>';
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show Field Checkbox.
	 *
	 * @param string $field 
	 * @param string $meta 
	 * @since 1.0
	 * @access public
	 */
	function woo_vou_add_cust_checkbox( $args, $echo = true ) {
		
		$html = '';
		
		$new_field = array( 'type' => 'checkbox', 'name' => 'Checkbox Field' );
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );
		
		$html .= '<div class="form-field ' . $field['id'] . '_field" style="display: inline-block; "><label style="display: block; float: none; width: auto !important;" for="' . $field['id'] . '">' . $field['label'] . '</label>';
		
		$html .= "<input type='checkbox' style='float: left;' class='woo-vou-meta-checkbox' name='{$field['id']}' id='{$field['id']}'" . checked(!empty($meta), true, false) . " />";
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description"></span>';
			
		$html .= '</div>';
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show Image Field.
	 *
	 * @since 1.0
	 * @package WooCommerce - PDF Vouchers
	 */
	function woo_vou_add_image( $args, $echo = true ) {
		
		$html = '';
		
		$new_field = array( 'type' => 'image', 'name' => 'Image Field' );
		$field = array_merge( $new_field, $args );
		
		$html .= '<p class="form-field ' . $field['id'] . '_field"><label style="display:block;" for="' . $field['id'] . '">' . $field['label'] . '</label>';
		
		$html .= wp_nonce_field( "woo-vou-meta-delete-mupload_{$field['id']}", "nonce-delete-mupload_".$field['id'], false, false );
		
		$meta = woo_vou_meta_value( $field );
		
		if( is_array( $meta ) ) {
			if( isset( $meta[0] ) && is_array( $meta[0] ) ) {
				$meta = $meta[0];
			}
		}
		
		if( is_array( $meta ) && isset( $meta['src'] ) && $meta['src'] != '' ) {
			$html .= "<span class='mupload_img_holder'><img src='".$meta['src']."' style='width: 150px;' /></span>";
			$html .= "<input type='hidden' name='".$field['id']."[id]' id='".$field['id']."[id]' value='".$meta['id']."' />";
			$html .= "<input type='hidden' name='".$field['id']."[src]' id='".$field['id']."[src]' value='".$meta['src']."' />";
			$html .= "<input class='woo-vou-meta-delete_image_button button-secondary' type='button' rel='".$field['id']."' value='" . __( 'Delete Image', 'woovoucher' ) . "' />";
		} else {
			$html .= "<span class='mupload_img_holder'></span>";
			$html .= "<input type='hidden' name='".$field['id']."[id]' id='".$field['id']."[id]' value='' />";
			$html .= "<input type='hidden' name='".$field['id']."[src]' id='".$field['id']."[src]' value='' />";
			$html .= "<input class='woo-vou-meta-upload_image_button button-secondary' type='button' rel='".$field['id']."' value='" . __( 'Upload Image', 'woovoucher' ) . "' />";
		}
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';
			
		$html .= '</p>';
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show Field Import CSV.
	 *
	 * @since 1.0
	 * @package WooCommerce - PDF Vouchers
	 */
	function woo_vou_add_importcsv( $args, $echo = true ) {  
		
		$html = '';
		
		$new_field = array( 'type' => 'importcsv','name' => __( 'Import Voucher Codes Field', 'woovoucher' ));
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );
		
		$html .= '<p class="form-field ' . $field['id'] . '_field"><label style="display:block;" for="' . $field['id'] . '">' . $field['label'] . '</label>';
	
		$html .= '<input type="button" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['btntext'].'" class="woo-vou-meta-vou-import-data button-secondary">';
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';
		
		$html .= '</p>';
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show Field Purchased Voucher Code.
	 *
	 * @since 1.0.0
	 * @package WooCommerce - PDF Vouchers
	 */
	function woo_vou_add_purchasedvoucodes( $args, $echo = true ) {  
		
		global $post, $woo_vou_render;
		
		$html = '';
		
		$new_field = array( 'type' => 'purchasedvoucodes','name' => __( 'Purchased Voucher Codes Field', 'woovoucher' ));
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );
		
		$html .= '<p class="form-field ' . $field['id'] . '_field"><label style="display:block;" for="' . $field['id'] . '">' . $field['label'] . '</label>';
		
		$html .= '<input type="button" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['btntext'].'" class="woo-vou-meta-vou-purchased-data button-secondary">';
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';
		
		$html .= '</p>';
		
		$html .= $woo_vou_render->woo_vou_purchased_codes_popup( $post->ID );
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show Field Used Voucher Code.
	 *
	 * @since 1.1.0
	 * @package WooCommerce - PDF Vouchers
	 */
	function woo_vou_add_usedvoucodes( $args, $echo = true ) {  
		
		global $post, $woo_vou_render;
		
		$html = '';
		
		$new_field = array( 'type' => 'usedvoucodes','name' => __( 'Used Voucher Codes Field', 'woovoucher' ));
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );
		
		$html .= '<p class="form-field ' . $field['id'] . '_field"><label style="display:block;" for="' . $field['id'] . '">' . $field['label'] . '</label>';
		
		$html .= '<input type="button" name="'.$field['id'].'" id="'.$field['id'].'" value="'.$field['btntext'].'" class="woo-vou-meta-vou-used-data button-secondary">';
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';
			
		$html .= '</p>';
		
		$html .= $woo_vou_render->woo_vou_used_codes_popup( $post->ID );
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show Radio Field.
	 *
	 * @since 1.0
	 * @package WooCommerce - PDF Vouchers
	 */
	function woo_vou_add_radio( $args, $echo = true ) {
		
		$html = '';
		
		$new_field = array( 'type' => 'radio', 'name' => 'Radio Field' );
		$field = array_merge( $new_field, $args );
		
		$default_meta = isset( $field['default'] ) ? $field['default'] : '';
		
		$meta = woo_vou_meta_value( $field );
		$meta = !empty( $meta ) ? $meta : $default_meta;
		
		if( ! is_array( $meta ) ) {
			$meta = (array) $meta;
		}
	  
		$html .= '<p class="form-field ' . $field['id'] . '_field"><label style="display:block;" for="' . $field['id'] . '">' . $field['label'] . '</label>';
		
		foreach ( $field['options'] as $key => $value ) {
			$html .= "<input type='radio' id='{$field['id']}_{$key}' class='woo-vou-meta-radio' name='{$field['id']}' value='{$key}'" . checked( in_array( $key, $meta ), true, false ) . " /> <label for='{$field['id']}_{$key}' class='woo-vou-meta-radio-label woo_vou_radio'>{$value}</label>";
		}
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';
			
		$html .= '</p>';
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show select box Field.
	 *
	 * @since 1.0
	 * @package WooCommerce - PDF Vouchers
	 */
	public function woo_vou_add_select( $args, $echo = true ) {
		
		$html = '';
		
		$new_field = array( 'type' => 'select', 'name' => 'Select Field', 'multiple' => false, 'style' => '' );
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );
		
		if( ! is_array( $meta ) ) {
			$meta = (array) $meta;
		}
		
		$html .= '<div class="'. $field['id'] . '_field vou_clearfix"> <label id="custom_label" style="display:block;" for="' . $field['id'] . '"> ' . $field['label'] . '</label>';
		
		$html .= "<select id='{$field['id']}' class='woo-vou-meta-select {$field['class']} ".($field['multiple'] ? 'woo-vou-meta-multiple-select' : 'woo-vou-meta-single-select')."' name='{$field['id']}" . ( $field['multiple'] ? "[]' multiple='multiple'" : "'" ) . " style='" . esc_attr( $field['style'] ) . "'>";
		
		foreach ( $field['options'] as $key => $value ) {
			$html .= "<option value='{$key}'" . selected( in_array( $key, $meta ), true, false ) . ">{$value}</option>";
		}
		
		$html .= "</select>";	
		
		if ( isset( $field['sign'] ) && $field['sign'] )
			$html .= "<span class='custom-desc' style='padding: 5px;'>{$field['sign']}</span>";
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';
			
		$html .= '</div>';
		
		$html .= woo_vou_show_field_end( $field );
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show custom text
	 *
	 * @since 1.0
	 * @package WooCommerce - PDF Vouchers
	 */
	public function woo_vou_add_custom_text( $args, $echo = true ) {  
		
		$html = '';
		
		$new_field = array( 'type' => 'text', 'name' => 'Text Field' );
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );
		
		$html .= '<div class="'. $field['id'] . '_field">';
		
		$html .= "<input type='text' onkeypress='return woo_vou_is_number_key_per_page(event)' class='woo-vou-meta-text {$field['class']}' name='{$field['id']}' id='{$field['id']}' value='{$meta}' /> {$field['sign']}";
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description description-custom">' . $field['description'] . '</span>';
		
		$html .= '</div>';
		
		$html .= woo_vou_show_field_end( $field );
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show custom text
	 *
	 * @since 1.0
	 * @package WooCommerce - PDF Vouchers
	 */
	public function woo_vou_add_text( $args, $echo = true ) {  
		
		$html = '';
		
		$new_field = array( 'type' => 'text', 'name' => 'Text Field' );
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );
		
		$html .= '<div class="'. $field['id'] . '_field '. $field['wrap_class'] . '" style="display: inline-block;">';
		$html .= '<label style="display: block; float: none; width: auto !important;" for="' . $field['id'] . '">' . $field['label'] . '</label>';
		
		$html .= "<input type='text' class='woo-vou-meta-text {$field['class']}' name='{$field['id']}' id='{$field['id']}' value='{$meta}' />";
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description description-custom">' . $field['description'] . '</span>';
		
		$html .= '</div>';
		
		$html .= woo_vou_show_field_end( $field );
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Show Date Field.
	 *
	 * @since 1.0
	 * @package WooCommerce - PDF Vouchers
	 */
	function woo_vou_add_datetime( $args, $echo = true ) {
		
		$html = '';
		
		$new_field = array('type' => 'datetime','format'=>'d MM, yy','name' => 'Date Time Field');
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );
		
		if(isset($meta) && !empty($meta) && !is_array($meta)) { //check datetime value is set & not array & not empty
			$meta = date('d-m-Y h:i a',strtotime($meta));
		} else {
			$meta = '';
		}
		
		$html .= '<p class="form-field ' . $field['id'] . '_field"><label style="display:block;" for="' . $field['id'] . '">' . $field['label'] . '</label>';
	
		$html .= "<input type='text' class='woo-vou-meta-datetime' name='{$field['id']}' id='{$field['id']}' rel='{$field['format']}' value='{$meta}' size='30' />";
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';
		
		$html .= '</p>';
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
	
	/**
	 * Add Repeater Block
	 * 
	 * Handles to add repeater block
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function woo_vou_add_repeater_block( $args, $echo = true ) {
		
		global $post,$woo_vou_model;
		
		$new_field = array( 'type' => 'repeater', 'id'=> $args['id'], 'name' => 'Reapeater Field', 'fields' => array() );
		
		$field = array_merge( $new_field, $args );
		
		$meta = woo_vou_meta_value( $field );
		
		$html = '';
		
		$html .= '<p class="form-field ' . $field['id'] . '_field woo_vou_repeater"><label style="display:block;" for="' . $field['id'] . '">' . $field['label'] . '</label>';
		
		$html .= "<div class='woo-vou-meta-repeat' id='{$field['id']}'>";
		
		if( !empty( $meta ) && count( $meta ) > 0 ) {
			
			$row = '';
			
			for ( $i = 0; $i < ( count ( $meta ) ); $i++ ) {
			
				$row .= "	<div class='woo-vou-meta-repater-block'>
								<table class='repeater-table form-table'>
									<tbody>";
				
				for ( $k = 0; $k < count( $field['fields'] ); $k++ ) {
					
					$row .= '<p class="form-field ' . $field['fields'][$k]['id'] . '_field"><label style="display:block;" for="' . $field['fields'][$k]['id'] . '">' . $field['fields'][$k]['label'] . '</label>';
					
					if ( !empty ( $field['fields'][$k]['type'] ) ) {
						$row .= apply_filters( 'woo_vou_replace_meta_field_type', $field['fields'][$k], $woo_vou_model->woo_vou_escape_attr( $meta[$i][$field['fields'][$k]['id']] ) );
					} else {
						$row .= "<input type='text' name='{$field['fields'][$k]['id']}[]' class='woo-vou-meta-text regular-text woo-vou-repeater-text' value='{$woo_vou_model->woo_vou_escape_attr( $meta[$i][$field['fields'][$k]['id']] )}'/>";
					}
					
					if ( ! empty( $field['fields'][$k]['description'] ) ) {
						$row .=  '<span class="description">' . wp_kses_post( $field['fields'][$k]['description'] ) . '</span>';
					}
					
					$row .=  '</p>';
					
				}
				
				$row .= "			</tbody>
								</table>";
				if( $i > 0 ) {
					$showremove = "style='display:block;'";
				} else {
					$showremove = "style='display:none;'";
				}
				
				$row .= "	<img id='remove-{$args['id']}' class='woo-vou-repeater-remove' {$showremove} title='".__('Remove', 'woovoucher')."' alt='".__('Remove', 'woovoucher')."' src='".WOO_VOU_META_URL."/images/remove.png'>";
				
				$row .= "		</div><!--.woo-vou-meta-repater-block-->";
				
			}
			$html .= $row;
			
		} else {
			
			$row = '';
			$row .= "	<div class='woo-vou-meta-repater-block'>
								<table class='repeater-table form-table'>
									<tbody>";
					
					for ( $i = 0; $i < count ( $field['fields'] ); $i++ ) {
						
						$row .= '<p class="form-field ' . $field['fields'][$i]['id'] . '_field"><label style="display:block;" for="' . $field['fields'][$i]['id'] . '">' . $field['fields'][$i]['label'] . '</label>';
					
						
						if ( !empty ( $field['fields'][$i]['type'] ) ) {
							$row .= apply_filters( 'woo_vou_replace_meta_field_type', $field['fields'][$i] );
						} else {
							
							$row .= "	<input type='text' name='{$field['fields'][$i]['id']}[]' class='woo-vou-meta-text regular-text woo-vou-repeater-text'/>";
						}
						
						if ( ! empty( $field['fields'][$i]['description'] ) ) {
						$row .=  '<span class="description">' . wp_kses_post( $field['fields'][$i]['description'] ) . '</span>';
					}
					
					$row .=  '</p>';
						
					}
					
				$row .= "		</tbody>
							</table>";
					
				$row .= "	<img id='remove-{$args['id']}' class='woo-vou-repeater-remove' style='display:none;' title='".__('Remove', 'woovoucher')."' alt='".__('Remove', 'woovoucher')."' src='".WOO_VOU_META_URL."/images/remove.png'>";
				
				$row .= "		</div><!--.woo-vou-meta-repater-block-->";
			
			$html .= $row;
			
		}
		
		$html .= "	<img id='add-{$args['id']}' class='woo-vou-repeater-add' title='".__( 'Add','woovoucher')."' alt='".__( 'Add', 'woovoucher')."' src='".WOO_VOU_META_URL."/images/add.png'>";
		
		$html .= "	</div><!--.woo-vou-meta-repeat-->";
		
		if ( isset( $field['description'] ) && $field['description'] )
			$html .= '<span class="description">' . $field['description'] . '</span>';
			
		$html .= '</p>';
		
		if($echo) {
			echo $html;
		} else {
			return $html;
		}
	}
}