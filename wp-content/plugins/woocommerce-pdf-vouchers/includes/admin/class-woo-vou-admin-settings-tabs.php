<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Setting page Class
 * 
 * Handles Settings page functionality of plugin
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.6
 */
class WOO_Vou_Settings_Tabs {
	
	var $model, $render;
	public function __construct(){
		
		global $woo_vou_model,$woo_vou_render;
		
		$this->model	= $woo_vou_model;
		$this->render	= $woo_vou_render;
	}
	
	/**
	 * Settings Tab
	 * 
	 * Adds the Voucher tab to the WooCommerce settings page.
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.6
	 */ 
	public function woo_vou_add_settings_tab( $tabs ) {	
					
		$tabs['voucher'] = __( 'PDF Vouchers', 'woovoucher' );
			
		return $tabs;
	}
	
	/**
	 * Settings Tab Content
	 * 
	 * Adds the settings content to the Voucher tab.
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.6
	 */
	public function woo_vou_settings_tab() {
				
		woocommerce_admin_fields( $this->woo_vou_get_settings() );		
	}
	
	/**
	 * Update Settings
	 * 
	 * Updates the voucher options when being saved.
	 *
	 *  @package WooCommerce - PDF Vouchers
	 * @since 1.6
	 */
	public function woo_vou_update_settings() {
				
		woocommerce_update_options( $this->woo_vou_get_settings() );		
	}
	
	/**
 	 * Add plugin settings
 	 * 
 	 * Handles to add plugin settings
 	 * 
 	 * @package WooCommerce - PDF Vouchers
 	 * @since 1.6
 	 */
	public function woo_vou_get_settings() {
				
		$voucher_options	= array( '' => __( 'Please Select', 'woovoucher' ) );
		$voucher_data		= $this->model->woo_vou_get_vouchers();
		
		foreach ( $voucher_data as $voucher ) {
			
			if( isset( $voucher['ID'] ) && !empty( $voucher['ID'] ) ) { // Check voucher id is not empty
				
				$voucher_options[$voucher['ID']] = $voucher['post_title'];
				$multiple_voucher_options[$voucher['ID']] = $voucher['post_title'];
			}
		}
		
		// Usability options
		$usability_options = array(
									'0'	=> __('One time only', 'woovoucher'),
									'1'	=> __('Unlimited', 'woovoucher')
								);		
		
		//Setting 2 for pdf voucher
		$woo_vou_settings	= array(	
									array( 
										'name'	=>	__( 'General Options', 'woovoucher' ),
										'type'	=>	'title',
										'desc'	=>	'',
										'id'	=>	'vou_general_settings'
									),									
									array(
										'id'		=> 'vou_delete_options',
										'name'		=> __( 'Delete Options', 'woovoucher' ),
										'desc'		=> '',
										'type'		=> 'checkbox',
										'desc_tip'	=> '<p class="description">'.__( 'If you don\'t want to use the Pdf Voucher Plugin on your site anymore, you can check the delete options box. This makes sure, that all the settings and tables are being deleted from the database when you deactivate the plugin.','woovoucher' ).'</p>'
									),
									// Add Partial Redeem settings
									array(
										'id'		=> 'vou_enable_partial_redeem',
										'name'		=> __( 'Enable Partial Redemption', 'woovoucher' ),
										'desc'		=> __( 'Enable Partial Redemption', 'woovoucher' ),
										'type'		=> 'checkbox',
										'desc_tip'	=> '<p class="description">' . __( 'Check this box if you wanted to allow users to redeem partial voucher codes.', 'woovoucher' ) . '</p>'
									),
									array(
										'id'		=> 'vou_pdf_name',
										'name'		=> __( 'Export PDF File Name', 'woovoucher' ),
										'desc'		=> '<p class="description">'.__( 'Enter the PDF file name. This file name will be used when a PDF of purchased voucher codes is generated. The available tags are:','woovoucher' ).'<br /><code>{current_date}</code> - '.__('displays the current date', 'woovoucher' ).'</p>',
										'type'		=> 'vou_filename',
										'options'	=> '.pdf'
									),
									array(
										'id'		=> 'vou_csv_name',
										'name'		=> __( 'Export CSV File Name', 'woovoucher' ),
										'desc'		=> '<p class="description">'.__( 'Enter the CSV file name. This file name will be used when a CSV of purchased voucher codes is generated. The available tags are:','woovoucher' ).'<br /><code>{current_date}</code> - '.__('displays the current date', 'woovoucher' ).'</p>',
										'type'		=> 'vou_filename',
										'options'	=> '.csv'
									),
									array(
										'id'		=> 'order_pdf_name',
										'name'		=> __( 'Download PDF File Name', 'woovoucher' ),
										'desc'		=> '<p class="description">'.__( 'Enter the PDF file name. This file name will be used when users download a PDF of voucher codes on frontend. The available tags are:','woovoucher' ).'<br /><code>{current_date}</code> - '.__('displays the current date', 'woovoucher' ).'</p>',
										'type'		=> 'vou_filename',
										'options'	=> '.pdf'
									), 
									array(
										'id'		=> 'attach_pdf_name',
										'name'		=> __( 'Attachment PDF File Name', 'woovoucher' ),
										'desc'		=> '<p class="description">'.__( 'Enter the PDF file name. This file name will be used when users download a PDF of voucher codes from Email Attachment.','woovoucher' ).'</p>',
										'type'		=> 'vou_filename',
										'options'	=> '{unique_string}.pdf'
									),
									array(
										'name'   => __('Custom CSS', 'woovoucher'),
										'class'  => '',
										'css'   => 'width:100%;min-height:100px',
										'desc'   => __('Here you can enter your custom css for the  pdf vouchers. The css will be automatically added to the header, when you save it.', 'woovoucher'),
										'id'   => 'vou_custom_css',
										'type'   => 'vou_textarea',
										'default' => ''
									),
									array( 
										'type' 		=> 'sectionend',
										'id' 		=> 'vou_general_settings'
									),	
									array( 
										'name'	=>	__( 'Voucher Options', 'woovoucher' ),
										'type'	=>	'title',
										'desc'	=>	'',
										'id'	=>	'vou_voucher_settings'
									), 																
									array(
										'id'		=> 'vou_site_logo',
										'name'		=> __( 'Site Logo', 'woovoucher' ),
										'desc'		=> '<p class="description">'.__( 'Here you can upload a logo of your site. This logo will then be displayed on the Voucher as the Site Logo.', 'woovoucher' ).'</p>',
										'type'		=> 'vou_upload',
										'size'		=> 'regular'
									),
									/*array(
										'id'		=> 'vou_pdf_template_selection',
										'name'		=> __( 'PDF Template Selection:', 'woovoucher' ),
										'desc'		=> '<p class="description">'.__( '', 'woovoucher' ).'</p>',
										'type'		=> 'multiselect',
										'class'		=> 'wc-enhanced-select',
										'options'	=> $multiple_voucher_options
									),*/
									array(
										'id'		=> 'vou_pdf_template',
										'name'		=> __( 'PDF Template', 'woovoucher' ),
										'desc'		=> '<p class="description">'.__( 'Select PDF Template.', 'woovoucher' ).'</p>',
										'type'		=> 'select',
										'class'		=> 'wc-enhanced-select',
										'options'	=> $voucher_options
									),
									array(
										'id'		=> 'vou_pdf_usability',
										'name'		=> __( 'Usability', 'woovoucher' ),
										'desc'		=> '<p class="description">'.__( 'Choose how many times the same Voucher Code can be used by the users.', 'woovoucher' ).'</p>',
										'type'		=> 'select',
										'class'		=> 'wc-enhanced-select',
										'options'	=> $usability_options
									),
									array(
										'id'		=> 'multiple_pdf',
										'name'		=> __( 'Multiple voucher', 'woovoucher' ),
										'desc'		=> __( 'Enable 1 voucher per Pdf', 'woovoucher' ),
										'type'		=> 'checkbox',
										'desc_tip'	=> '<p class="description">'.__( 'Check this box if you want to generate 1 pdf for 1 voucher code instead of creating 1 combined pdf for all vouchers.', 'woovoucher' ).'</p>'
									),
									array(
										'id'		=> 'revoke_voucher_download_link_access',
										'name'		=> __( 'Remove Used / Expired voucher download link', 'woovoucher' ),
										'desc'		=> __( 'Remove Used / Expired voucher download link', 'woovoucher' ),
										'type'		=> 'checkbox',
										'desc_tip'	=> '<p class="description">' . sprintf(__( 'Check this box if you want to remove voucher download link, when voucher is %1$sused%2$s OR %1$sexpired%2$s.', 'woovoucher' ), '<strong>', '</strong>' ) . '</p>'
									),
									array(
										'id'		=> 'vou_attach_mail',
										'name'		=> __( 'Voucher Attachment', 'woovoucher' ),
										'desc'		=> __( 'Send voucher Pdf as attachment in mail', 'woovoucher' ),
										'type'		=> 'checkbox',
										'desc_tip'	=> '<p class="description">'.__( 'Check this box if you want to send pdf voucher as attachment in mail.', 'woovoucher' ).'</p>'
									),
									array(
										'id'		=> 'vou_allow_redeem_expired_voucher',
										'name'		=> __( 'Allow Redeem for Expired Vouchers', 'woovoucher' ),
										'desc'		=> __( 'Allow Redeem for Expired Vouchers', 'woovoucher' ),
										'type'		=> 'checkbox',
										'desc_tip'	=> '<p class="description">'.__( 'Check this box if you want to allow redeem voucher codes after expiration date.', 'woovoucher' ).'</p>'
									),
									array(
										'id'		=> 'vou_allow_bcc_to_admin',
										'name'		=> __( 'Send Emails to Admin', 'woovoucher' ),
										'desc'		=> __( 'Send Emails to Admin', 'woovoucher' ),
										'type'		=> 'checkbox',
										'desc_tip'	=> '<p class="description">'.__( 'Check this box if you want to send customer order email and gift notification email to admin.', 'woovoucher' ).'</p>'
									),
									// End Voucher Option section
									array( 
										'type' 		=> 'sectionend',
										'id' 		=> 'vou_voucher_settings'
									),
									// Start Misc Settings section
									array( 
										'name'	=>	__( 'Misc Settings', 'woovoucher' ),
										'type'	=>	'title',
										'desc'	=>	'',
										'id'	=>	'vou_misc_settings'
									),
									// Add "Auto Enable Voucher" to Misc Setting
									array(
										'id'		=> 'vou_enable_voucher',
										'name'		=> __( 'Auto Enable Voucher', 'woovoucher' ),
										'desc'		=> __( 'Auto Enable Voucher', 'woovoucher' ),
										'type'		=> 'checkbox',
										'desc_tip'	=> '<p class="description">' . __( 'Check this box to automatically "Enable Voucher Codes" when product is set as Downloadable.', 'woovoucher' ) . '</p>'
									),
									// Add "Disable Variation's Auto Downloadable" to Misc Setting
									array(
										'id'		=> 'vou_disable_variations_auto_downloadable',
										'name'		=> __( 'Disable Auto check Downloadable', 'woovoucher' ),
										'desc'		=> __( 'Disable Auto check Downloadable', 'woovoucher' ),
										'type'		=> 'checkbox',
										'desc_tip'	=> '<p class="description">' . __( 'Check this box if you don\'t wanted to tick all variation as downloadable when we "Enable Voucher Codes" at product level.', 'woovoucher' ) . '</p>'
									),
									array(
										'id'		=> 'vou_enable_preview_in_browser',
										'name'		=> __( 'Enable PDF Preview', 'woovoucher' ),
										'desc'		=> __( 'Enable PDF Preview', 'woovoucher' ),
										'type'		=> 'checkbox',
										'desc_tip'	=> '<p class="description">' . __( 'Check this box if you wanted to preview pdf in browser instead of download pdf.', 'woovoucher' ) . '</p>'
									),
									array(
										'id'		=> 'vou_enable_relative_path',
										'name'		=> __( 'Enable Relative Path', 'woovoucher' ),
										'desc'		=> __( 'Enable Relative Path', 'woovoucher' ),
										'type'		=> 'checkbox',
										'desc_tip'	=> '<p class="description">' . __( 'Check this box if you want to use relative path for images instead of absolute path.', 'woovoucher' ) . '</p>'
									),
									array(
										'id'		=> 'vou_char_support',
										'name'		=> __( 'Characters not displaying correctly?', 'woovoucher' ),
										'desc'		=> __( 'Enable characters support', 'woovoucher' ),
										'type'		=> 'checkbox',
										'desc_tip'	=> '<p class="description">'.__( 'Check this box to enable the characters support. Only do this if you have characters which do not display correctly (e.g. Greek characters).', 'woovoucher' ).'</p>'
									),
						);
				
				// apply filter to add more settings from plugins addons
				$woo_vou_settings = apply_filters( 'woo_vou_settings', $woo_vou_settings );
				
				//Add voucher setting if woocommerce vendor plugin activated
				if( class_exists( 'WC_Vendors' ) ) {
					
					$woo_vendor_setting	= array(
												array(
													'id'		=> 'vou_hide_vendor_options',
													'name'		=> __( 'Hide Vendor Options', 'woovoucher' ),
													'desc'		=> __( 'Hide Vendor Options', 'woovoucher' ),
													'type'		=> 'checkbox',
													'desc_tip'	=> '<p class="description">'.__( 'Check this box if you want to hide vendor specific settings from product meta box for vendor users.', 'woovoucher' ).'</p>'
												)
											);
					
					$woo_vou_settings = array_merge( $woo_vou_settings, $woo_vendor_setting );
				}

			//Setting 2 for pdf voucher
			$woo_vou_settings2 = array(										
									array( 
										'type' 		=> 'sectionend',
										'id' 		=> 'vou_voucher_settings'
									)
								);

			//Merge all vouvher settings
			$woo_vou_settings = array_merge( $woo_vou_settings, $woo_vou_settings2 );

		return apply_filters( 'woo_vou_get_settings', $woo_vou_settings );
	}
	
	/**
	 * Adding Hooks
	 * 
	 * Adding proper hoocks for the shortcodes.
	 * 
	 * @package WooCommerce - PDF Vouchers
 	 * @since 1.6
	 */
	public function add_hooks() {
		
		//add Voucher tab to woocommerce setting page
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'woo_vou_add_settings_tab'), 99 );			
		
		//add Voucher tab content
		add_action( 'woocommerce_settings_tabs_voucher', array( $this, 'woo_vou_settings_tab') );
		
		//save custom update content
		add_action( 'woocommerce_update_options_voucher', array( $this, 'woo_vou_update_settings'), 100 );
		
		// Add a custom field types
		add_action( 'woocommerce_admin_field_vou_filename', array( $this->render, 'woo_vou_render_filename_callback' ) );
		add_action( 'woocommerce_admin_field_vou_upload', array( $this->render, 'woo_vou_render_upload_callback' ) );
		add_action( 'woocommerce_admin_field_vou_textarea', array( $this->render, 'woocommerce_admin_field_vou_textarea' ) );
		
		// save custom field types
		//add_action( 'woocommerce_update_option_vou_filename', array( $this->render, 'woo_vou_save_filename_field' ) );
		//add_action( 'woocommerce_update_option_vou_upload', array( $this->render, 'woo_vou_save_upload_field' ) );
		//add_action( 'woocommerce_update_option_vou_textarea', array( $this->render, 'woo_vou_save_vou_textarea_field' ) );
	}
}