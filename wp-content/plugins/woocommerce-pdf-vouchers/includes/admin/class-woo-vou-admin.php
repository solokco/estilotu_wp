<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Admin Class
 *
 * Handles generic Admin functionality and AJAX requests.
 *
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
class WOO_Vou_Admin{
	
	var $scripts, $model, $render, $voumeta;
	
	public function __construct() {
		
		global $woo_vou_scripts,$woo_vou_model,
				$woo_vou_render, $woo_vou_admin_meta;
		
		$this->scripts 	= $woo_vou_scripts;
		$this->model 	= $woo_vou_model;
		$this->render 	= $woo_vou_render;
		$this->voumeta	= $woo_vou_admin_meta;
	}
	
	/**
	 * Adding Submenu Page
	 * 
	 * Handles to adding submenu page for
	 * voucher extension
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_admin_submenu() {
		
		global $current_user, $woo_vou_vendor_role;
		
		$main_menu_slug = WOO_VOU_MAIN_MENU_NAME;

		//Current user role
		$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
		$user_role	= array_shift( $user_roles );

		//get voucher admins
		$voucher_admins	= woo_vou_assigned_admin_roles();

		if( in_array( $user_role, $voucher_admins ) || in_array( $user_role, $woo_vou_vendor_role ) ) {

			if ( current_user_can( 'manage_woocommerce' ) ) { // administrator or shop manager

				//voucher codes page
				$voucher_page = add_submenu_page( $main_menu_slug , __( 'Voucher Codes', 'woovoucher'), __( 'Voucher Codes', 'woovoucher' ), 'read', 'woo-vou-codes', array( $this, 'woo_vou_codes_page' ) );

			} else {

				$main_menu_slug = 'woo-vou-codes';
				//add WooCommerce Page
				add_menu_page( __( 'WooCommerce', 'woovoucher' ),__( 'WooCommerce', 'woovoucher' ), 'read', $main_menu_slug, '' );
				add_submenu_page( $main_menu_slug , __( 'Voucher Codes', 'woovoucher'), __( 'Voucher Codes', 'woovoucher' ), 'read', $main_menu_slug, array( $this, 'woo_vou_codes_page' ) );
			}

			//add check voucher code page
			$check_voucher_page = add_submenu_page( $main_menu_slug, __( 'Check Voucher Code', 'woovoucher' ),__( 'Check Voucher Code', 'woovoucher' ), 'read', 'woo-vou-check-voucher-code', array( $this, 'woo_vou_check_voucher_code_page' ) );
		}
	}

	/**
	 * Add Page to See Used Voucher for
	 * all Products
	 * 
	 * Handles to list the products for which vouchers
	 * used
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_codes_page() {
		include_once( WOO_VOU_ADMIN . '/forms/woo-vou-codes-page.php' );
	}
	
	/**
	 * Check Voucher Code Page for
	 * all Products
	 * 
	 * Handles to check voucher code page
	 * for all voucher codes and manage codes
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_check_voucher_code_page() {
		
		include_once( WOO_VOU_ADMIN . '/forms/woo-vou-check-code.php' );
		
	}
	
	/**
	 * Import Codes From CSV
	 * 
	 * Handle to import voucher codes from CSV Files
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_import_codes() {
		
		//import csv file code for voucher code importing to textarea
		if( ( isset( $_FILES['woo_vou_csv_file']['tmp_name'] ) && !empty( $_FILES['woo_vou_csv_file']['tmp_name'] ) ) ) {
			
			$filename		= $_FILES['woo_vou_csv_file']['tmp_name'];
			$deletecode		= isset( $_POST['woo_vou_delete_code'] ) && !empty( $_POST['woo_vou_delete_code'] ) ? $_POST['woo_vou_delete_code'] : '';
			$existingcode	= isset( $_POST['woo_vou_existing_code'] ) && !empty( $_POST['woo_vou_existing_code'] ) ? $_POST['woo_vou_existing_code'] : '';
			$csvseprator	= isset( $_POST['woo_vou_csv_sep'] ) && !empty( $_POST['woo_vou_csv_sep'] ) ? $_POST['woo_vou_csv_sep'] : ',';
			$csvenclosure	= isset( $_POST['woo_vou_csv_enc'] ) ? $_POST['woo_vou_csv_enc'] : '';
			
			$importcodes	= '';
			
			$importcodes	= '';
			$pattern_data	= array();
			
			if( !empty( $existingcode ) && $deletecode != 'y' ) { // check existing code and existing code not remove
				$pattern_data = explode( ',', $existingcode );
				$pattern_data = array_map( 'trim', $pattern_data );
			}
			
			if ( !empty( $filename ) && ( $handle = fopen( $filename, "r") ) !== FALSE) {
				
				if( !empty($csvenclosure) ) {
					
					while (($data = fgetcsv($handle, 1000, $csvseprator, $csvenclosure)) !== FALSE) { // check all row of csv
						
						foreach ( $data as $key => $value ) { // check all column of particular row
							
							if( !empty($value) && !in_array( $value, $pattern_data) ) { // cell value is not empty and avoid duplicate code
								
								$pattern_data[] = str_replace( ',', '', $value );
							}
					    }
					}
				} else {
					
					while (($data = fgetcsv($handle, 1000, $csvseprator)) !== FALSE) { // check all row of csv
						
						foreach ( $data as $key => $value ) { // check all column of particular row
							
							if( !empty($value) && !in_array( $value, $pattern_data) ) { // cell value is not empty and avoid duplicate code
								
								$pattern_data[] = str_replace( ',', '', $value );
							}
					    }
					}
				}
				
			    fclose( $handle );
			    unset( $_FILES['woo_vou_csv_file'] );
			}
			
		    $import_code = implode( ', ', $pattern_data ); // all pattern codes
			
			echo $import_code;
			exit;
		}
	}
	
	/**
	 * Import Random Code using AJAX
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_import_code() {
		
		$noofvoucher 	= !empty($_POST['noofvoucher']) ? $_POST['noofvoucher'] : 0;
		$codeprefix 	= !empty($_POST['codeprefix']) ? $_POST['codeprefix'] : '';
		$codeseperator 	= !empty($_POST['codeseperator']) ? $_POST['codeseperator'] : '';
		$pattern 		= !empty($_POST['codepattern']) ? $_POST['codepattern'] : '';
		$existingcode	= !empty($_POST['existingcode']) ? $_POST['existingcode'] : '';
		$deletecode		= !empty($_POST['deletecode']) ? $_POST['deletecode'] : '';
		
		$pattern_prefix = $codeprefix . $codeseperator; // merge prefix with seperator
		
		$pattern_data	= array();
		if( !empty( $existingcode ) && $deletecode != 'y' ) { // check existing code and existing code not remove
			$pattern_data	= explode( ',', $existingcode );
			$pattern_data	= array_map( 'trim', $pattern_data );
		}
		
		for ( $j = 0; $j < $noofvoucher; $j++ ) { // no of codes are generate
			
			$pattern_string = $pattern_prefix . $this->model->woo_vou_get_pattern_string( $pattern );
			
			while ( in_array( $pattern_string, $pattern_data) ) { // avoid duplicate pattern code
				$pattern_string = $pattern_prefix . $this->model->woo_vou_get_pattern_string( $pattern );
			}
			
			$pattern_data[] = str_replace( ',', '', $pattern_string );
		}
		$import_code	= implode( ', ', $pattern_data ); // all pattern codes
		
		echo $import_code;
		exit;
	}
	
	/**
	 * Add Popup For import Voucher Code
	 * 
	 * Handels to show import voucher code popup
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_import_footer() {
		
		global $post;
		
		//Check product post type page
		if( isset( $post->post_type ) && $post->post_type == WOO_VOU_MAIN_POST_TYPE ) {
			
			include_once( WOO_VOU_ADMIN . '/forms/woo-vou-import-code-popup.php' );
		}
	}
	
	/**
	 * Add Custom meta boxs  for voucher templates post tpye
	 * 
	 * Handles to add custom meta boxs in voucher templates
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_editor_meta_box() {
		
		global $wp_meta_boxes;
		
		// add metabox for edtior
		add_meta_box( 'woo_vou_page_voucher' ,__( 'Voucher', 'woovoucher' ), array( $this, 'woo_vou_editor_control' ), WOO_VOU_POST_TYPE, 'normal', 'high', 1 );
		
		// add metabox for style options
		add_meta_box( 'woo_vou_pdf_options' ,__( 'Voucher Options', 'woovoucher' ), array( $this, 'woo_vou_pdf_options_page' ), WOO_VOU_POST_TYPE, 'normal', 'high' );
	}
	
	/**
	 * Add Custom Editor
	 * 
	 * Handles to add custom editor
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function woo_vou_editor_control() {
		
		include( WOO_VOU_ADMIN . '/forms/woo-vou-editor.php' );
	}
	
	/**
	 * Add Style Options
	 * 
	 * Handles to add Style Options
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function woo_vou_pdf_options_page() {
		
		include( WOO_VOU_ADMIN . '/forms/woo-vou-meta-options.php' );
	}
	
	/**
	 * Save Voucher Meta Content
	 * 
	 * Handles to saving voucher meta on update voucher template post type
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function woo_vou_save_metadata( $post_id ) {
		
		global $post_type;
		
		$prefix = WOO_VOU_META_PREFIX;
		
		$post_type_object = get_post_type_object( $post_type );
		
		// Check for which post type we need to add the meta box
		$pages = array( WOO_VOU_POST_TYPE );
		
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )                // Check Autosave
		|| ( ! isset( $_POST['post_ID'] ) || $post_id != $_POST['post_ID'] )        // Check Revision
		|| ( ! in_array( $post_type, $pages ) )              // Check if current post type is supported.
		|| ( ! check_admin_referer( WOO_VOU_PLUGIN_BASENAME, 'at_woo_vou_meta_box_nonce') )      // Check nonce - Security
		|| ( ! current_user_can( $post_type_object->cap->edit_post, $post_id ) ) )       // Check permission
		{
			return $post_id;
		}
		
		$metacontent	= isset( $_POST['woo_vou_meta_content'] ) ? $_POST['woo_vou_meta_content'] : '';
		$metacontent	= trim( $metacontent );
		update_post_meta( $post_id, $prefix . 'meta_content', $metacontent ); // updating the content of page builder editor
		
		//Update Editor Status
		if( isset( $_POST[ $prefix . 'editor_status' ] ) ) {
			update_post_meta( $post_id, $prefix . 'editor_status', $_POST[ $prefix . 'editor_status' ] );
		}
		
		//Update Background Style
		if( isset( $_POST[ $prefix . 'pdf_bg_style' ] ) ) {
			update_post_meta( $post_id, $prefix . 'pdf_bg_style', $_POST[ $prefix . 'pdf_bg_style' ] );
		}
		
		//Update Background Pattern
		if( isset( $_POST[ $prefix . 'pdf_bg_pattern' ] ) ) {
			update_post_meta( $post_id, $prefix . 'pdf_bg_pattern', $_POST[ $prefix . 'pdf_bg_pattern' ] );
		}
		
		//Update Background Image
		if( isset( $_POST[ $prefix . 'pdf_bg_img' ] ) ) {
			update_post_meta( $post_id, $prefix . 'pdf_bg_img', $_POST[ $prefix . 'pdf_bg_img' ] );
		}
		
		//Update Background Color
		if( isset( $_POST[ $prefix . 'pdf_bg_color' ] ) ) {
			update_post_meta( $post_id, $prefix . 'pdf_bg_color', $_POST[ $prefix . 'pdf_bg_color' ] );
		}
		
		//Update PDF View
		if( isset( $_POST[ $prefix . 'pdf_view' ] ) ) {
			update_post_meta( $post_id, $prefix . 'pdf_view', $_POST[ $prefix . 'pdf_view' ] );
		}
		
		//Update PDF Size
		if( isset( $_POST[ $prefix . 'pdf_size' ] ) ) {
			update_post_meta( $post_id, $prefix . 'pdf_size', $_POST[ $prefix . 'pdf_size' ] );
		}
		
		//Update Margin Top
		if( isset( $_POST[ $prefix . 'pdf_margin_top' ] ) ) {
			update_post_meta( $post_id, $prefix . 'pdf_margin_top', $_POST[ $prefix . 'pdf_margin_top' ] );
		}
		
		//Update Margin Bottom
		if( isset( $_POST[ $prefix . 'pdf_margin_bottom' ] ) ) {
			
			update_post_meta( $post_id, $prefix . 'pdf_margin_bottom', $_POST[ $prefix . 'pdf_margin_bottom' ] );
		}
		//Update Margin Left
		if( isset( $_POST[ $prefix . 'pdf_margin_left' ] ) ) {
			update_post_meta( $post_id, $prefix . 'pdf_margin_left', $_POST[ $prefix . 'pdf_margin_left' ] );
		}
		//Update Margin Right
		if( isset( $_POST[ $prefix . 'pdf_margin_right' ] ) ) {
			update_post_meta( $post_id, $prefix . 'pdf_margin_right', $_POST[ $prefix . 'pdf_margin_right' ] );
		}
	}
	
	/**
	 * Custom column
	 * 
	 * Handles the custom columns to voucher listing page
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_manage_custom_column( $column_name, $post_id ) {
		
		global $wpdb, $post;
		
		$prefix = WOO_VOU_META_PREFIX;
		
		switch( $column_name ) {
			
			case 'voucher_preview' :
								$preview_url = $this->woo_vou_get_preview_link( $post_id );
								echo '<a href="' . $preview_url . '" class="woo-vou-pdf-preview">' . __( 'View Preview', 'woovoucher' ) . '</a>';
								break;
		}
	}
	
	/**
	 * Add New Column to voucher listing page
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function woo_vou_add_new_columns($new_columns) {
 		
 		unset($new_columns['date']);
 		
 		$new_columns['voucher_preview'] = __( 'View Preview', 'woovoucher' );
		$new_columns['date']			= _x( 'Date', 'column name', 'woovoucher' );
		
		return $new_columns;
	}

	/**
	 * Get Preview Link
	 *
	 * Handles to get preview link
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function woo_vou_get_preview_link( $postid ) {
		
		$preview_url = add_query_arg( array( 'post_type' => WOO_VOU_POST_TYPE, 'woo_vou_pdf_action' => 'preview', 'voucher_id' => $postid ), admin_url( 'edit.php' ) );
		
		return $preview_url;
	}
	
	/**
	 * Add New Action For Create Duplicate
	 * 
	 * Handles to add new action for 
	 * Create Duplicate link of that voucher
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_dupd_action_new_link_add( $actions, $post ) {
		
		//check current user can have administrator rights
		//post type must have vouchers post type
		if ( ! current_user_can( 'manage_options' ) || $post->post_type != WOO_VOU_POST_TYPE ) 
			return $actions;
			
		// add new action for create duplicate
		$args = array( 'action'	=>	'woo_vou_duplicate_vou', 'woo_vou_dupd_vou_id' => $post->ID );
		$dupdurl = add_query_arg( $args, admin_url( 'edit.php' ) );
		$actions['woo_vou_duplicate_vou'] = '<a href="' . wp_nonce_url( $dupdurl, 'duplicate-vou_' . $post->ID ) . '" title="' . __( 'Make a duplicate from this voucher', 'woovoucher' )
										. '" rel="permalink">' .  __( 'Duplicate', 'woovoucher' ) . '</a>';
		
		// return all actions
		return $actions ;
		
	}
	
	/**
	 * Add Preview Button
	 * 
	 * Handles to add preview button within
	 * Publish meta box
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function woo_vou_add_preview_button() {
		
		global $typenow, $post;
		
		if ( ! current_user_can( 'manage_options' )
			|| ! is_object( $post )
			|| $post->post_type != WOO_VOU_POST_TYPE ) {
				return;
		}
		
		if ( isset( $_GET['post'] ) ) {
			
			$args = array( 'action'	=>	'woo_vou_duplicate_vou', 'woo_vou_dupd_vou_id' => absint( $_GET['post'] ) );
			$dupdurl = add_query_arg( $args, admin_url( 'edit.php' ) );
			$notifyUrl = wp_nonce_url( $dupdurl, 'duplicate-vou_' . $_GET['post'] );
			?>
			<div id="duplicate-action"><a class="submitduplicate duplication" href="<?php echo esc_url( $notifyUrl ); ?>"><?php _e( 'Copy to a new draft', 'woovoucher' ); ?></a></div>
			<?php
		}
		
		$preview_url = $this->woo_vou_get_preview_link( $post->ID );
		echo '<a href="' . $preview_url . '" class="button button-secondary button-large woo-vou-pdf-preview-button" >' . __( 'Preview', 'woovoucher' ) . '</a>';
	}
	
	/**
	 * Duplicate Voucher
	 * 
	 * Handles to creating duplicate voucher
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	function woo_vou_duplicate_process() {
		
		//check the duplicate create action is set or not and order id is not empty
		if( isset($_GET['action']) && !empty($_GET['action']) && $_GET['action'] == 'woo_vou_duplicate_vou'
			&& isset( $_GET['woo_vou_dupd_vou_id'] ) && !empty($_GET['woo_vou_dupd_vou_id'])) {
			
			// get the vou id
			$vou_id = $_GET['woo_vou_dupd_vou_id'];
			
			//check admin referer	
			check_admin_referer( 'duplicate-vou_' . $vou_id );
			
			// create duplicate voucher
			$this->model->woo_vou_dupd_create_duplicate_vou( $vou_id );
		}
	}

	/**
	 * Vouchers Lists display based on menu order with ascending order
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_edit_posts_orderby( $orderby_statement ) {
		
		global $wpdb;
		
		 //Check post type is woovouchers & sorting not applied by user
		if( isset( $_GET['post_type'] ) && $_GET['post_type'] == WOO_VOU_POST_TYPE && !isset( $_GET['orderby'] ) ) {
			
			$orderby_statement =  "{$wpdb->posts}.menu_order ASC, {$wpdb->posts}.post_date DESC";
		}
		return $orderby_statement;
	}
	
	/**
	 * Save Metabox Data
	 * 
	 * Handles to save metabox details
	 * to database
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_product_save_data( $post_id, $post ) {
		
		global $post_type;
		
		// get prefix
		$prefix = WOO_VOU_META_PREFIX;
		
		//is downloadable
		$is_downloadable 		= get_post_meta( $post_id, '_downloadable', true );
		
		// Getting product type
		$product_type 			= !empty($_POST['product-type']) ? $_POST['product-type'] : '';
		
		// Enable Voucher Codes
		$woo_vou_enable			= !empty( $_POST[ $prefix.'enable' ] ) ? 'yes' : '';
		
		// get Pdf template
		$woo_vou_pdf_template   = isset($_POST[$prefix.'pdf_template']) ? $_POST[$prefix.'pdf_template'] : ''; 
		
		// Usability
		$woo_vou_using_type		= isset( $_POST[$prefix.'using_type'] ) ? $_POST[$prefix.'using_type'] : '';
		
		// get logo
		$woo_vou_logo 			= isset($_POST[$prefix.'logo']) ? $_POST[$prefix.'logo'] : ''; 
		
		// get address
		$woo_vou_address_phone  = isset($_POST[$prefix.'address_phone']) ? $_POST[$prefix.'address_phone'] : ''; 
		
		// get website
		$woo_vou_website  		= isset($_POST[$prefix.'website']) ? $_POST[$prefix.'website'] : ''; 
		
		// get redeem instructions
		$woo_vou_how_to_use 	= isset($_POST[$prefix.'how_to_use']) ? $_POST[$prefix.'how_to_use'] : ''; 
		
		// enable recipient name
		$enable_recipient_name		= !empty( $_POST[ $prefix.'enable_recipient_name' ] ) ? 'yes' : '';
		$recipient_name_max_length	= !empty( $_POST[ $prefix.'recipient_name_max_length' ] ) && is_numeric( $_POST[$prefix.'recipient_name_max_length'] ) ? trim(round ( $_POST[ $prefix.'recipient_name_max_length' ] ) ) : '';
		$recipient_name_label		= !empty( $_POST[ $prefix.'recipient_name_label' ] ) ? trim( $_POST[ $prefix.'recipient_name_label' ] ) : '';
		$recipient_name_is_required	= !empty( $_POST[ $prefix.'recipient_name_is_required' ] ) ? 'yes' : '';
		
		// enable recipient email
		$woo_vou_recipient_email		= !empty( $_POST[ $prefix.'enable_recipient_email' ] ) ? 'yes' : '';
		$recipient_email_label			= !empty( $_POST[ $prefix.'recipient_email_label' ] ) ? trim( $_POST[ $prefix.'recipient_email_label' ] ) : '';
		$recipient_email_is_required	= !empty( $_POST[ $prefix.'recipient_email_is_required' ] ) ? 'yes' : '';
		
		// enable recipient message
		$enable_recipient_message		= !empty( $_POST[ $prefix.'enable_recipient_message' ] ) ? 'yes' : '';
		$recipient_message_max_length	= !empty( $_POST[ $prefix.'recipient_message_max_length' ] )  && is_numeric( $_POST[$prefix.'recipient_message_max_length'] ) ? trim(round ( $_POST[ $prefix.'recipient_message_max_length' ] ) ) : '';
		$recipient_message_label		= !empty( $_POST[ $prefix.'recipient_message_label' ] ) ? trim( $_POST[ $prefix.'recipient_message_label' ] ) : '';
		$recipient_message_is_required	= !empty( $_POST[ $prefix.'recipient_message_is_required' ] ) ? 'yes' : '';
		
		// enable recipient gift date field
		$woo_vou_recipient_gift_date	= !empty( $_POST[ $prefix.'enable_recipient_giftdate' ] ) ? 'yes' : '';
		$recipient_giftdate_label		= !empty( $_POST[ $prefix.'recipient_giftdate_label' ] ) ? trim( $_POST[ $prefix.'recipient_giftdate_label' ] ) : '';
		$recipient_giftdate_is_required	= !empty( $_POST[ $prefix.'recipient_giftdate_is_required' ] ) ? 'yes' : '';
		
		// enable pdf template selection
		$woo_vou_pdf_template_selection	   = !empty( $_POST[ $prefix.'enable_pdf_template_selection' ] ) ? 'yes' : '';
		$pdf_template_selection_label	   = !empty( $_POST[ $prefix.'pdf_template_selection_label' ] ) ? trim( $_POST[ $prefix.'pdf_template_selection_label' ] ) : '';
		$pdf_template_selection_is_required = !empty( $_POST[ $prefix.'pdf_template_selection_is_required' ] ) ? 'yes' : '';
		$pdf_template_selection	            = !empty( $_POST[ $prefix.'pdf_template_selection' ] ) ? $_POST[ $prefix.'pdf_template_selection' ] : '';
		
		$disable_redeem_day	            = !empty( $_POST[ $prefix.'disable_redeem_day' ] ) ? $_POST[ $prefix.'disable_redeem_day' ] : '';		
		
		// Check if downloadable is on or variable product then set voucher enable option otherwise not set
		if( $is_downloadable == 'yes' || $product_type == 'variable' ) {
			
			$enable_voucher = $woo_vou_enable;
			
		} else {
			$enable_voucher =  '';
		}
		
		// Getting downloadable variable
		//$variable_is_downloadable = !empty($_POST['variable_is_downloadable']) ? $_POST['variable_is_downloadable'] : array();
		
		update_post_meta( $post_id, $prefix.'enable', $enable_voucher );
		
		//Recipient Name Detail Update
		update_post_meta( $post_id, $prefix.'enable_recipient_name', $enable_recipient_name );
		update_post_meta( $post_id, $prefix.'recipient_name_max_length', $recipient_name_max_length );
		update_post_meta( $post_id, $prefix.'recipient_name_label', $recipient_name_label );
		update_post_meta( $post_id, $prefix.'recipient_name_is_required', $recipient_name_is_required );
		
		//Recipient Email Detail Update
		update_post_meta( $post_id, $prefix.'enable_recipient_email', $woo_vou_recipient_email );
		update_post_meta( $post_id, $prefix.'recipient_email_label', $recipient_email_label );
		update_post_meta( $post_id, $prefix.'recipient_email_is_required', $recipient_email_is_required );
		
		//Recipient Message Detail Update
		update_post_meta( $post_id, $prefix.'enable_recipient_message', $enable_recipient_message );
		update_post_meta( $post_id, $prefix.'recipient_message_max_length', $recipient_message_max_length );
		update_post_meta( $post_id, $prefix.'recipient_message_label', $recipient_message_label );
		update_post_meta( $post_id, $prefix.'recipient_message_is_required', $recipient_message_is_required );
		
		//Recipient Email Detail Update
		update_post_meta( $post_id, $prefix.'enable_recipient_giftdate', $woo_vou_recipient_gift_date );
		update_post_meta( $post_id, $prefix.'recipient_giftdate_label', $recipient_giftdate_label );
		update_post_meta( $post_id, $prefix.'recipient_giftdate_is_required', $recipient_giftdate_is_required );
		
		//Pdf Template Selection Detail Update
		update_post_meta( $post_id, $prefix.'enable_pdf_template_selection', $woo_vou_pdf_template_selection );
		update_post_meta( $post_id, $prefix.'pdf_template_selection_label', $pdf_template_selection_label );
		update_post_meta( $post_id, $prefix.'pdf_template_selection_is_required', $pdf_template_selection_is_required );
		update_post_meta( $post_id, $prefix.'pdf_template_selection', $pdf_template_selection );
		
		update_post_meta( $post_id, $prefix.'disable_redeem_day', $disable_redeem_day ); // disbale reedem days
		
		// PDF Template
		update_post_meta( $post_id, $prefix.'pdf_template', $woo_vou_pdf_template );
		
		// Vendor User
		update_post_meta( $post_id, $prefix.'vendor_user', $_POST[$prefix.'vendor_user'] );

		$secondary_vendor_users = isset( $_POST[$prefix.'sec_vendor_users'] ) ? $_POST[$prefix.'sec_vendor_users'] : '';
		// Secondary Vendor Users
		$secondary_vendor_users = isset( $_POST[$prefix.'sec_vendor_users'] ) && !empty( $_POST[$prefix.'sec_vendor_users'] ) ? $_POST[$prefix.'sec_vendor_users'] : '';		
		update_post_meta( $post_id, $prefix.'sec_vendor_users', $secondary_vendor_users );

		//expire type
		if( isset( $_POST[$prefix.'exp_type'] ) ) {
			update_post_meta( $post_id, $prefix.'exp_type', $_POST[$prefix.'exp_type'] );
		}
		
		update_post_meta( $post_id, $prefix.'days_diff', $_POST[$prefix.'days_diff'] );
		
		$custom_days	=  !empty( $_POST[$prefix.'custom_days']) && is_numeric( $_POST[$prefix.'custom_days'] ) ? trim(round ( $_POST[$prefix.'custom_days'] ) ) : '';
		update_post_meta( $post_id, $prefix.'custom_days', $custom_days );
		
		
		// Product Start Date
		$product_start_date = $_POST[$prefix.'product_start_date'];
		
		if( !empty( $product_start_date ) ) {
			$product_start_date = strtotime( $this->model->woo_vou_escape_slashes_deep( $product_start_date ) );
			$product_start_date = date('Y-m-d H:i:s',$product_start_date);
		}
		update_post_meta( $post_id, $prefix.'product_start_date', $product_start_date );
		
		// Expiration Date
		$product_exp_date = $_POST[$prefix.'product_exp_date'];
		
		if(!empty($product_exp_date)) {
			$product_exp_date = strtotime( $this->model->woo_vou_escape_slashes_deep( $product_exp_date ) );
			$product_exp_date = date('Y-m-d H:i:s',$product_exp_date);
		}
		update_post_meta( $post_id, $prefix.'product_exp_date', $product_exp_date );
		
		
		// Start Date
		$start_date = $_POST[$prefix.'start_date'];
		
		if( !empty( $start_date ) ) {
			$start_date = strtotime( $this->model->woo_vou_escape_slashes_deep( $start_date ) );
			$start_date = date('Y-m-d H:i:s',$start_date);
		}
		update_post_meta( $post_id, $prefix.'start_date', $start_date );
		
		// Expiration Date
		$exp_date = $_POST[$prefix.'exp_date'];
		
		if(!empty($exp_date)) {
			$exp_date = strtotime( $this->model->woo_vou_escape_slashes_deep( $exp_date ) );
			$exp_date = date('Y-m-d H:i:s',$exp_date);
		}
		update_post_meta( $post_id, $prefix.'exp_date', $exp_date );
		
		// Voucher Codes
		$voucher_codes = isset( $_POST[$prefix.'codes'] ) ? $this->model->woo_vou_escape_slashes_deep( $_POST[$prefix.'codes'] ) : '';
		update_post_meta( $post_id, $prefix.'codes', $voucher_codes );
		
		
		$usability = $woo_vou_using_type;
		
		if( isset( $_POST[$prefix.'vendor_user'] ) && !empty( $_POST[$prefix.'vendor_user'] ) && $usability == '') {//if vendor user is set and usability is default 
			
			$usability = get_user_meta( $_POST[$prefix.'vendor_user'], $prefix.'using_type', true );
		}
		
		// If usability is default then take it from setting
		if( $usability == '' ) {
			$usability = get_option('vou_pdf_usability');
		}
		
		update_post_meta( $post_id, $prefix.'using_type', $usability );
		
		// vendor's Logo
		update_post_meta( $post_id, $prefix.'logo', $woo_vou_logo );
		
		// Vendor's Address
		update_post_meta( $post_id, $prefix.'address_phone', $this->model->woo_vou_escape_slashes_deep( $woo_vou_address_phone, true, true ) );
		
		// Website URL
		update_post_meta( $post_id, $prefix.'website', $this->model->woo_vou_escape_slashes_deep( $woo_vou_website ) );
		
		// Redeem Instructions
		update_post_meta( $post_id, $prefix.'how_to_use', $this->model->woo_vou_escape_slashes_deep( $woo_vou_how_to_use, true, true ) );
		
		// update available products count on bases of entered voucher codes
		if( isset( $_POST[$prefix.'codes'] ) && $enable_voucher == 'yes' ) {
			
			$voucount = '';
			$vouchercodes = trim( $_POST[$prefix.'codes'], ',' );
			if( !empty( $vouchercodes ) ) {
				$vouchercodes = explode( ',', $vouchercodes );
				$voucount = count( $vouchercodes );
			}
			
			
			
			if( empty( $usability ) ) {// using type is only one time
				
				$avail_total = empty( $voucount ) ? '0' : $voucount;
				
				
				// Getting variable product id
				$variable_post_id = (!empty($_POST['variable_post_id'])) ? $_POST['variable_post_id'] : array();
				
				// If product is variable and id's are not blank then update their quantity with blank
				if( $product_type == 'variable' && !empty($variable_post_id) ) {
					
					// set flag false
					$variable_code_flag = false;
					
					foreach ( $variable_post_id as $variable_post ) {
						
						
						$variable_is_downloadable = get_post_meta( $variable_post, '_downloadable', true );
						
						$variable_codes = get_post_meta( $variable_post, $prefix.'codes', true );
						
						if($variable_is_downloadable == 'yes' && !empty($variable_codes) ) { 
							
							// if variation is set as downloadable and vochers codes set at variation level
							$variable_code_flag = true;
						
						}
					}
					
					if($variable_code_flag == true) {
						
						// mark this product as variable voucher so we consider it to take vouchers from variations 
						update_post_meta( $post_id, $prefix.'is_variable_voucher', '1' );
						
					} else {
						
						update_post_meta( $post_id, $prefix.'is_variable_voucher', '' );	
					}
					
					// default variable auto enable is true
					$variable_auto_enable	= true;
					
					// get auto download option
					$disable_variations_auto_downloadable	= get_option( 'vou_disable_variations_auto_downloadable' );
					if( $disable_variations_auto_downloadable == 'yes' ) { // if disable option
						$variable_auto_enable = false;
					}
					
					// disable auto enable
					$auto_enable	= apply_filters( 'woo_vou_auto_enable_downloadable_variations', $variable_auto_enable, $post_id );
					
					foreach ( $variable_post_id as $variable_post ) {
						
						if( $variable_code_flag != true) { // if there no voucher codes set on variation level
							
							// get voucher codes
							$var_vou_codes	= get_post_meta( $variable_post, $prefix.'codes', true );
							
							if( $auto_enable || !empty( $var_vou_codes ) ) {
								
								// update variation manage stock as no
								update_post_meta( $variable_post, '_manage_stock', 'no' );
								
								// Update variation stock qty with blank
								update_post_meta( $variable_post, '_stock', '' );
								
								// Update variation downloadable with yes
								update_post_meta( $variable_post, '_downloadable', 'yes' );
							}
							
						} else {
							
							//update manage stock with yes
							update_post_meta( $variable_post, '_manage_stock', 'yes' );
							
							$variable_voucount = '';
							$variable_codes = get_post_meta( $variable_post, $prefix.'codes', true );
							
							$vouchercodes = trim( $variable_codes, ',' );
							if( !empty( $vouchercodes ) ) {
								$vouchercodes = explode( ',', $vouchercodes );
								$variable_voucount = count( $vouchercodes );
							} 
							 
							$variable_avail_total = empty( $variable_voucount ) ? '0' : $variable_voucount;
							//update available count on bases of 
							//update_post_meta( $variable_post, '_stock', $variable_avail_total );
							wc_update_product_stock( $variable_post,  $variable_avail_total );
							
						}
					}
					
				}
				
				//update manage stock with yes
				update_post_meta( $post_id, '_manage_stock', 'yes' );
				
				//update available count on bases of 
				//update_post_meta( $post_id, '_stock', $avail_total );
				wc_update_product_stock( $post_id,  $avail_total );
				
			}
			
		}
		
		//update location and map links
		$availlocations = array();
		if( isset( $_POST[$prefix.'locations'] ) ) {
			
			$locations = $_POST[$prefix.'locations'];
			$maplinks = $_POST[$prefix.'map_link'];
			for ( $i = 0; $i < count( $locations ); $i++ ){
				if( !empty( $locations[$i] ) || !empty( $maplinks[$i])) { //if location or map link is not empty then
					$availlocations[$i][$prefix.'locations'] = $this->model->woo_vou_escape_slashes_deep( $locations[$i], true, true );
					$availlocations[$i][$prefix.'map_link'] = $this->model->woo_vou_escape_slashes_deep( $maplinks[$i] );
				}
			}
		}
		
		//update location and map links
		update_post_meta( $post_id, $prefix. 'avail_locations', $availlocations );
		
	}	
		
	/**
	 * Display Voucher Data within order meta
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_display_voucher_data() {
		
		include( WOO_VOU_ADMIN . '/forms/woo-vou-meta-history.php' );
	}
	
	/**
	 * Add Voucher Details meta box within Order
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_order_meta_boxes() {

		add_meta_box('woo-vou-order-voucher-details', __( 'Voucher Details', 'woovoucher' ), array( $this, 'woo_vou_display_voucher_data' ), WOO_VOU_MAIN_SHOP_POST_TYPE, 'normal', 'default' );
		
	}
	
	/**
	 * Delete order meta and all order detail whene order delete.
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.4.1
	 */
	public function woo_vou_order_delete( $order_id = '' ){
		
		$prefix		= WOO_VOU_META_PREFIX;		
		
		if( !empty( $order_id ) ) { // check if order id is not empty
			
			$post_type = get_post_type( $order_id ); // get	post type from order id
			
			if( $post_type == 'shop_order' ){ // check if post type is shop_order
				
				$args = array(
							'post_type'		=> WOO_VOU_CODE_POST_TYPE,
							'post_status'	=> 'any',
							'meta_query' 	=> array(
													array(
														'key' 	=> $prefix.'order_id',
														'value' => $order_id
													)
								)
				 );
				
				// get posts from order id
				$posts = get_posts($args);
				
				if( !empty( $posts ) ){ // check if get any post
					
					foreach ( $posts as $post ){
						
						wp_delete_post( $post->ID, true );
					}
				}
			}
		}
	}
	
	/**
	 * Function for Add an extra fields in edit user page
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.5
	 */
	public function woo_vou_user_edit_profile_fields( $user ){
		
		global $woo_vou_vendor_role;
		
		//Get user role
		$user_roles	= isset( $user->roles ) ? $user->roles : array();
		$user_role = array_shift( $user_roles );
		
		//check if user role is vendor or not
		if( isset( $user_role ) && in_array( $user_role, $woo_vou_vendor_role) ) {
			
			include_once( WOO_VOU_ADMIN . '/forms/woo-vou-user-meta.php' );
		}
	}
	
	/**
	 * Function for update an user meta fields
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.5
	 */
	public function woo_vou_update_profile_fields( $user_id ){
		
		$prefix = WOO_VOU_META_PREFIX;
		
		// update pdf template to user meta
		if( isset( $_POST[ $prefix.'pdf_template' ] ) )
		update_user_meta( $user_id, $prefix.'pdf_template', $_POST[ $prefix.'pdf_template' ] );
		
		// update pdf template to user meta
		if( isset( $_POST[ $prefix.'using_type' ] ) )
		update_user_meta( $user_id, $prefix.'using_type', $_POST[ $prefix.'using_type' ] );
		
		// update vendor address to user meta
		if( isset( $_POST[ $prefix.'address_phone' ] ) )
		update_user_meta( $user_id, $prefix.'address_phone', trim ( $this->model->woo_vou_escape_slashes_deep ( $_POST[ $prefix.'address_phone' ], true, true ) ) );
		
		// update vendor address to user meta
		if( isset( $_POST[ $prefix.'siteurl_text' ] ) )
		update_user_meta( $user_id, $prefix.'website', trim ( $this->model->woo_vou_escape_slashes_deep ( $_POST[ $prefix.'siteurl_text' ] ) ) );
		
		// update vendor logo to user meta
		if( isset( $_POST[ $prefix.'logo' ] ) )
		update_user_meta( $user_id, $prefix.'logo', $_POST[ $prefix.'logo' ] );
		
		// update vendor Redeem Instructions to user meta
		if( isset( $_POST[ $prefix.'how_to_use' ] ) )
		update_user_meta( $user_id, $prefix.'how_to_use', trim ( $this->model->woo_vou_escape_slashes_deep ( $_POST[ $prefix.'how_to_use' ], true, true ) ) );
		
		//update location and map links
		$availlocations = array();
		if( isset( $_POST[$prefix.'locations'] ) ) {
			
			$locations = $_POST[$prefix.'locations'];
			$maplinks = $_POST[$prefix.'map_link'];
			for ( $i = 0; $i < count( $locations ); $i++ ){
				if( !empty( $locations[$i] ) || !empty( $maplinks[$i])) { //if location or map link is not empty then
					$availlocations[$i][$prefix.'locations'] = $this->model->woo_vou_escape_slashes_deep( $locations[$i], true, true );
					$availlocations[$i][$prefix.'map_link'] = $this->model->woo_vou_escape_slashes_deep( $maplinks[$i] );
				}
			}
		}
				
		//update location and map links
		update_user_meta( $user_id, $prefix. 'avail_locations', $availlocations );
		
		// update vendor sale email notification settings
		$vendor_sale_email_notification = isset( $_POST[ $prefix.'enable_vendor_sale_email_notification' ] ) && !empty( $_POST[ $prefix.'enable_vendor_sale_email_notification' ] ) ? "1" : "";		
		update_user_meta( $user_id, $prefix.'enable_vendor_sale_email_notification', $vendor_sale_email_notification );				
		
	}
	
	/**
	 * Function for product variable meta
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.5
	 */
	function woo_vou_product_variable_meta( $loop, $variation_data, $variation ) {
		
		include( WOO_VOU_ADMIN . '/forms/woo-vou-product-variable-meta.php' );
	}
	
	/**
	 * Function to save product variable meta
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.5
	 */
	function woo_vou_product_save_variable_meta( $variation_id, $i ) {
		
		
		if( !empty($variation_id) ) {
			
			$prefix = WOO_VOU_META_PREFIX;
			
			$variable_pdf_template = isset($_POST[$prefix.'variable_pdf_template'][$i]) ? $this->model->woo_vou_escape_slashes_deep($_POST[$prefix.'variable_pdf_template'][$i]) : '';
			
			$variable_pdt_code = isset($_POST[$prefix.'variable_codes'][$i]) ? $this->model->woo_vou_escape_slashes_deep($_POST[$prefix.'variable_codes'][$i]) : '';
			
			// Updating variable pdf template
			update_post_meta( $variation_id, $prefix.'pdf_template', $variable_pdf_template );
			
			// Updating variable voucher code
			update_post_meta( $variation_id, $prefix.'codes', $variable_pdt_code );
		}
	}
	
	/**
	 * Function to unlink the pdf voucher from the folder
	 * If the file is created before 2 hours
	 * File creation time is in UTC
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.0
	 */
	public function woo_vou_flush_upload_dir() {
		
		// Get pdf vouchers from the upload dir
		$vou_pdf_files = glob( WOO_VOU_UPLOAD_DIR . '*.pdf');
		
		if( !empty($vou_pdf_files) ) {
			
			foreach ( $vou_pdf_files as $vou_pdf_files_key => $vou_pdf_files_val ) {
				
				// If file exist in folder
				if( file_exists($vou_pdf_files_val) ) {
					
					// Getting voucher pdf creation time in UTC format
					$vou_time = date_i18n( 'Y-m-d H:i:s', filemtime($vou_pdf_files_val), 'gmt' );
					
					// Getting current time in UTC format
					$current_time = date_i18n( 'Y-m-d H:i:s', false, 'gmt' );
					
					// Getting time difference of file
					$timediff = round((strtotime($current_time) - strtotime($vou_time))/(3600), 1);
					
					// If file is created before 2 houes
					if( !empty($timediff) && $timediff > 2 ) {
						unlink($vou_pdf_files_val);
					}
				} // End of file exist
			}
		} // End of main if
	}
	
	/**
	 * Download Pdf by admin
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.0.3
	 */
	public function woo_vou_admin_voucher_pdf_download() {
		
		global $current_user;
		
		if( !empty( $_GET['download_file'] ) && !empty( $_GET['key'] ) 
			&& !empty( $_GET['woo_vou_admin'] ) && !empty( $_GET['woo_vou_order_id'] ) ) {
				
				if ( current_user_can( 'moderate_comments' ) ) {
					
					$product_id		= (int) $_GET['download_file'];
					$email			= sanitize_email( str_replace( ' ', '+', $_GET['email'] ) );
					$download_id	= isset( $_GET['key'] ) ? preg_replace( '/\s+/', ' ', $_GET['key'] ) : '';
					$order_id		= $_GET['woo_vou_order_id'];
					$item_id		= isset( $_GET['item_id'] ) ? $_GET['item_id'] : '';
					
					//Generate PDF
					$this->model->woo_vou_generate_pdf_voucher( $email, $product_id, $download_id, $order_id, $item_id );
					
				} else {
					
					wp_die( '<p>'.__( 'You are not allowed to access this URL.', 'woovoucher' ).'</p>' );
				}
				
				exit;
		}
	}
	
	/**
	 * Show Downloadable Option
	 * 
	 * Handle to show downloadable option for booking product type
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.1.3
	 */
	public function woo_vou_booking_product_type_options( $options ) {
		
		$options['downloadable']['wrapper_class'] .= ' show_if_booking';
		return $options;
	}
	
	/**
	 * Add Email Class In Woocommerce
	 * 
	 * Handle to add email class to wocommerce
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.4
	 */
	public function woo_vou_add_email_classes( $email_classes ) {
		
		//Include vendor sale notification email class file
		require_once ( WOO_VOU_ADMIN . '/class-woo-vou-vendor-sale.php' );
		$email_classes['Woo_Vou_Vendor_Sale'] = new Woo_Vou_Vendor_Sale();

		//Include gift notification email class file
		require_once ( WOO_VOU_ADMIN . '/class-woo-vou-gift-notification.php' );
		$email_classes['Woo_Vou_Gift_Notification'] = new Woo_Vou_Gift_Notification();

		return $email_classes;
	}
	
	/**
	 * Delete voucher codes
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.6.3
	 */
	public function woo_vou_delete_vou_codes(){
		
		// check if action is not blank and page is woo voucher code
		if( isset( $_GET['action'] ) && $_GET['action'] == 'delete' && isset( $_GET['page'] ) && $_GET['page'] == 'woo-vou-codes' && isset( $_GET['vou-data'] ) && ( $_GET['vou-data'] == 'purchased' || $_GET['vou-data'] == 'used' || $_GET['vou-data'] == 'expire' ) ) {
			
			// get redirect url
			$redirect_url = add_query_arg( array( 'page' => 'woo-vou-codes', 'vou-data' => $_GET['vou-data'] ), admin_url( 'admin.php' ) );
		
			if( isset( $_GET['code_id'] ) && !empty(  $_GET['code_id'] ) && !get_post_status( $_GET['order_id'] ) ) {
			
				$delete_post = wp_delete_post( $_GET['code_id'] );
				
				if( $delete_post )
				$redirect_url = add_query_arg( array( 'message' => '1' ), $redirect_url );
			}
			wp_redirect( $redirect_url ); 
			exit;
		}
	}		
	
	/**
	 * Send Gift notification email using cron jobs
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.1
	 */
	public function woo_vou_send_gift_notification_email() {

		global $vou_order;
		
		//Get prefix
		$prefix = WOO_VOU_META_PREFIX;

		// get all orders	
		$woo_vou_get_all_orders = get_posts( array(
			'numberposts' => -1,
	        'post_type'   => 'shop_order',
	        'post_status' => array( 'wc-processing', 'wc-completed' )
		) );

		// loop through orders
		foreach ( $woo_vou_get_all_orders as $woo_vou_order ) {
			
			// order id
			$order_id = $vou_order = $woo_vou_order->ID;						
			
			// get order details
			$cart_details 	= new WC_Order( $order_id );
			
			// get order items
			$order_items    = $cart_details->get_items();
			
			if ( $cart_details->status == 'processing' && get_option( 'woocommerce_downloads_grant_access_after_payment' ) == 'no' ) {
				continue;
			}
			
			// record the fact that the vouchers have been sent
			if( get_post_meta( $order_id, $prefix . 'recipient_email_sent', true ) ) {
				continue;
			}

			if( !empty( $order_items ) ) { //if item is empty
	
				foreach ( $order_items as $product_item_key => $product_data ) {															
										
					$product_id			= isset( $product_data['product_id'] ) ? $product_data['product_id'] : '';
					$variation_id		= isset( $product_data['variation_id'] ) ? $product_data['variation_id'] : '';
					
					//Initilize recipient detail
					$recipient_details	= array();
	
					//Get product item meta
					$product_item_meta	= isset( $product_data['item_meta'] ) ? $product_data['item_meta'] : array();
					
					// get recipient details
					$recipient_details	= $this->model->woo_vou_get_recipient_data( $product_item_meta );
					
					$recipient_details['recipient_giftdate'] = apply_filters( 'woo_vou_replace_giftdate', $recipient_details['recipient_giftdate'], $order_id, $product_item_key );

					// if empty recipient giftdate then check next items
					if( empty( $recipient_details['recipient_giftdate'] ) ) {
						continue;
					}
					
					// today date
					$woo_vou_today_date			= $this->model->woo_vou_current_date('Y-m-d');					
					
					// recipient gift date
					$woo_vou_order_gift_date 	= $recipient_details['recipient_giftdate'];

					// if today date and gift date is same then send gift notification email
					if ( strtotime( $woo_vou_today_date ) == strtotime( $woo_vou_order_gift_date ) ) {
						
						$first_name		= isset( $cart_details->billing_first_name ) ? $cart_details->billing_first_name : '';
						$last_name		= isset( $cart_details->billing_last_name ) ? $cart_details->billing_last_name : '';
						
						$_product = apply_filters( 'woocommerce_order_item_product', $cart_details->get_product_from_item( $product_data ), $product_data );
								
						if( !$_product ) { //If product deleted
							$download_file_data = array();
						} else {
							//Get download files
							$download_file_data	= $cart_details->get_item_downloads( $product_data );
						}																		
						
						$links		= array();
						$i			= 0;
						$attach_key	= array();
						
						foreach ( $download_file_data as $key => $download_file ) {
		
							$check_key		= strpos( $key, 'woo_vou_pdf_' );
		
							if( !empty( $download_file ) && $check_key !== false ) {
		
								$attach_keys[]	= $key;
								$i++;
								$links[] = '<small><a href="' . esc_url( $download_file['download_url'] ) . '">' . sprintf( __( 'Download file%s', 'woovoucher' ), ( count( $download_file_data ) > 1 ? ' ' . $i . ': ' : ': ' ) ) . esc_html( $download_file['name'] ) . '</a></small>';
							}
						}
		
						$recipient_details['recipient_voucher']	= '<br/>' . implode( '<br/>', $links );
						
						// added filter to send extra emails on diferent email ids by other extensions
						$woo_vou_extra_emails = false;
						$woo_vou_extra_emails = apply_filters( 'woo_vou_pdf_recipient_email', $woo_vou_extra_emails, $product_id );
		
						if( ( isset( $recipient_details['recipient_email'] ) && !empty( $recipient_details['recipient_email'] ) ) || 
							( !empty( $woo_vou_extra_emails ) ) ) {
		
							$recipient_name		= isset( $recipient_details['recipient_name'] ) ? $recipient_details['recipient_name'] : '';
							$recipient_email	= isset( $recipient_details['recipient_email'] ) ? $recipient_details['recipient_email'] : '';
							$recipient_message	= isset( $recipient_details['recipient_message'] ) ? '"'.nl2br( $recipient_details['recipient_message'] ).'"' : '';
							$recipient_voucher	= isset( $recipient_details['recipient_voucher'] ) ? $recipient_details['recipient_voucher'] : '';
		
							// Get Extra email if passed through filter
							$woo_vou_extra_emails	= !empty( $woo_vou_extra_emails ) ? $woo_vou_extra_emails : '';
		
							$attachments = array();
							
							if( get_option( 'vou_attach_mail' ) == 'yes' ) { //If attachment enable
		
								//Get product/variation ID
								$product_id	= !empty( $product_data['variation_id'] ) ? $product_data['variation_id'] : $product_data['product_id'];
		
								if( !empty( $attach_keys ) ) {//attachments keys not empty
		
									foreach ( $attach_keys as $attach_key ) {
		
										$attach_pdf_file_name = get_option( 'attach_pdf_name' );
										$attach_pdf_file_name = !empty( $attach_pdf_file_name ) ? $attach_pdf_file_name : 'woo-voucher-';
		
										// Replacing voucher pdf name with given value
										$orderdvoucode_key = str_replace('woo_vou_pdf_', $attach_pdf_file_name, $attach_key );
		
										//Voucher attachment path
										$vou_pdf_path 	= WOO_VOU_UPLOAD_DIR . $orderdvoucode_key . '-' . $product_id . '-' . $product_item_key . '-' . $order_id; // Voucher pdf path
										$vou_pdf_name	= $vou_pdf_path . '.pdf';
		
										// If voucher pdf exist in folder
										if( file_exists($vou_pdf_name) ) {
		
											// Adding the voucher pdf in attachment array
											$attachments[] = apply_filters( 'woo_vou_gift_email_attachments', $vou_pdf_name, $order_id, $product_data );
										} else { // If voucher pdf doesn't exist then we will generate that
											
											// Call function to generate Voucher PDF
											$attachments = apply_filters( 'woo_vou_gift_email_attachments', $woo_vou_public->woo_vou_attach_voucher_to_email( array(), 'customer_processing_order', $cart_details ), $order_id, $product_data );
										}
									}
								}
							}
							
							//Get All Data for gift notify
							$gift_data	= array(
								'first_name'			=> $first_name,
								'last_name'				=> $last_name,
								'recipient_name'		=> $recipient_name,
								'recipient_email'		=> $recipient_email,
								'recipient_message'		=> $recipient_message,
								'voucher_link'			=> $recipient_voucher,
								'attachments'			=> $attachments,
								'woo_vou_extra_emails'	=> $woo_vou_extra_emails,
							);
		
							// Fires when gift notify.
							do_action( 'woo_vou_gift_email', $gift_data );
						}
						
						//Update post meta for email attachment issue
						update_post_meta( $order_id, $prefix . 'recipient_email_sent', true );
					}
				}
										
				// Add action after gift email is sent
				do_action( 'woo_vou_after_gift_email', $order_id );
			}
		}
	}
	
	/**
	 * Handles to change status to 'on-hold', during checkout through 'COD',
	 * if PDF Voucher is enabled at product level
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.9.0
	 */
	public function woocommerce_cod_process_payment_order_status_func ( $order_status, $order ) {
		
		$prefix = WOO_VOU_META_PREFIX;
		
		foreach ( $order->get_items() as $item ) {
			$_product = $order->get_product_from_item( $item );

			if ( $_product && $_product->exists() ) {
				
				$woo_vou_pro_enable = get_post_meta( $_product->id, $prefix . 'enable', true );
				
				if ( !empty( $woo_vou_pro_enable ) && $woo_vou_pro_enable == 'yes' && $_product->is_downloadable() ) {
					
					$order_status = 'on-hold';
					break;
				}
			}
		}
		
		return $order_status;
	}
	
	/**
	 * Handles to insert html fieldsa on
	 * coupon add / edit page
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.9.2
	 */
	public function woo_vou_coupon_options () {
		
		global $post;
		
		$prefix = WOO_VOU_META_PREFIX;
		
		//disable redeem voucher
		$redeem_days = array( 
			'monday' => __( 'Monday', 'woovoucher' ), 
			'tuesday' => __( 'Tuesday', 'woovoucher' ), 
			'wednesday' => __( 'Wednesday', 'woovoucher' ),
			'thursday' => __( 'Thursday', 'woovoucher' ), 
			'friday' => __( 'Friday', 'woovoucher' ),
			'saturday' => __( 'Saturday', 'woovoucher' ),
			'sunday' => __( 'Sunday', 'woovoucher' )
		);
		
		// Start date
		woocommerce_wp_text_input( array( 'id' => $prefix . 'start_date', 'label' => __( 'Coupon start date', 'woovoucher' ), 'placeholder' => _x( 'YYYY-MM-DD', 'placeholder', 'woovoucher' ), 'description' => '', 'class' => 'date-picker', 'custom_attributes' => array( 'pattern' => "[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" ) ) );

		// Restriction days
		?>
		<p class="form-field"><label for="product_categories"><?php _e( 'Choose which days coupon can not be used', 'woocommerce' ); ?></label>
		<select id="product_rest_days" name="<?php echo $prefix; ?>product_rest_days[]" style="width: 50%;"  class="wc-enhanced-select" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select days', 'woovoucher' ); ?>">
			<?php
			
				$rest_days_meta = (array) get_post_meta( $post->ID, $prefix . 'product_rest_days', true );
				
				if ( $redeem_days ) foreach ( $redeem_days as $redeem_day_key => $redeem_day_val ) {
					echo '<option value="' . $redeem_day_key . '"' . selected( in_array( $redeem_day_key, $rest_days_meta ), true, false ) . '>' . $redeem_day_val . '</option>';
				}
			?>
		</select> <?php echo wc_help_tip( __( 'If you want to restrict use of Coupon Code for specific days, you can select days here. Leave it blank for no restriction.', 'woocommerce' ) ); ?></p>
		<?php
	}
	
	/**
	 * Handles to save data in coupon's meta
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.9.2
	 */
	public function woo_vou_save_coupon_options ( $post_id ) {
		
		// Get prefix
		$prefix = WOO_VOU_META_PREFIX;
		
		// Get coupon start date from $_POST
		$woo_vou_start_date = wc_clean( $_POST[$prefix.'start_date'] );
		
		// Get restriction days from $_POST
		$woo_vou_rest_days 	= isset( $_POST[$prefix.'product_rest_days'] ) ? $_POST[$prefix.'product_rest_days'] : array();

		// Update coupon start date in coupon meta
		update_post_meta( $post_id, $prefix . 'start_date', $woo_vou_start_date );
		
		// Update restriction days in coupon meta
		update_post_meta( $post_id, $prefix . 'product_rest_days', $woo_vou_rest_days );
	}
	
	/**
	 * Adding Hooks
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function add_hooks() {
		
		if( woo_vou_is_edit_page() ) {
				
			//add content for import voucher codes in footer
			add_action( 'admin_footer', array($this, 'woo_vou_import_footer') );
		}
		
		//add filter to add settings
		//add_filter( 'woocommerce_general_settings', array( $this->model, 'woo_vou_settings') );
		
		//add action to import csv file for codes with Ajaxform
		add_action ( 'init',  array( $this, 'woo_vou_import_codes' ) );
		
		//add submenu page
		add_action( 'admin_menu', array( $this, 'woo_vou_admin_submenu' ) );
		
		//AJAX action for import code
		add_action( 'wp_ajax_woo_vou_import_code', array( $this, 'woo_vou_import_code') );
		add_action( 'wp_ajax_nopriv_woo_vou_import_code', array( $this, 'woo_vou_import_code') );
		
		//add new field to voucher listing page
		add_action( 'manage_'.WOO_VOU_POST_TYPE.'_posts_custom_column', array( $this, 'woo_vou_manage_custom_column' ), 10, 2 );
		add_filter( 'manage_edit-'.WOO_VOU_POST_TYPE.'_columns', array( $this, 'woo_vou_add_new_columns' ) );
		
		//add action to add custom metaboxes on voucher template post type
		add_action( 'add_meta_boxes', array( $this, 'woo_vou_editor_meta_box' ) );
		
		//saving voucher meta on update or publish voucher template post type
		add_action( 'save_post', array( $this, 'woo_vou_save_metadata' ) );
		
		//ajax call to edit all controls
		add_action( 'wp_ajax_woo_vou_page_builder', array( $this->render, 'woo_vou_page_builder') );
		add_action( 'wp_ajax_nopriv_woo_vou_page_builder', array( $this->render, 'woo_vou_page_builder' ) );
		
		//add filter to add new action "duplicate" on admin vouchers page
		add_filter( 'post_row_actions', array( $this , 'woo_vou_dupd_action_new_link_add' ), 10, 2 );
		
		//add action to add preview button after update button
		add_action( 'post_submitbox_start', array( $this, 'woo_vou_add_preview_button' ) ); 
		
		//add action to create duplicate voucher
		add_action( 'admin_init', array( $this, 'woo_vou_duplicate_process' ) );
		
		//add filter to display vouchers by menu order with ascending order
		add_filter( 'posts_orderby', array( $this, 'woo_vou_edit_posts_orderby' ) );
		
		// add metabox in products
		add_action( 'woocommerce_product_write_panel_tabs', array( $this->voumeta, 'woo_vou_product_write_panel_tab' ) );
		add_action( 'woocommerce_product_write_panels',     array( $this->voumeta, 'woo_vou_product_write_panel') );
		add_action( 'woocommerce_process_product_meta',     array( $this, 'woo_vou_product_save_data' ), 20, 2 );
		
		//add action to display voucher history
		add_action( 'add_meta_boxes', array( $this, 'woo_vou_order_meta_boxes' ), 35 );
		
		//add action to delete order meta when woocommerce order delete
		add_action( 'before_delete_post', array( $this, 'woo_vou_order_delete' ) );
		
		// add action to add an extra fields in edit user page
		add_action('edit_user_profile', array( $this, 'woo_vou_user_edit_profile_fields' ) );
		
		// add action to store user meta in database
		add_action('edit_user_profile_update', array( $this, 'woo_vou_update_profile_fields' ) );
		
		// Action for product variation meta
		add_action( 'woocommerce_product_after_variable_attributes', array($this, 'woo_vou_product_variable_meta'), 10, 3 );
		
		// Action to save product variation meta
		add_action( 'woocommerce_save_product_variation', array($this, 'woo_vou_product_save_variable_meta'), 10, 2 );
		
		// Action to flush the voucher upload dir
		add_action( 'woo_vou_flush_upload_dir_cron', array( $this, 'woo_vou_flush_upload_dir' ) );
		
		//File download access to admin
		add_action( 'init', array( $this, 'woo_vou_admin_voucher_pdf_download' ), 9 );
		
		//Add downloadable option for Woocommerce-Booking plugin
		add_filter( 'product_type_options', array( $this, 'woo_vou_booking_product_type_options' ) );
		
		//add action for email templates classes for woo pdf vouchers
		add_filter( 'woocommerce_email_classes', array( $this, 'woo_vou_add_email_classes' ) );
		
		// Add action for delete voucher codes
		add_action( 'admin_init', array( $this, 'woo_vou_delete_vou_codes' ) );
		
		// Add action to send gift notification email ( daily cron )
		add_action( 'woo_vou_send_gift_notification', array( $this, 'woo_vou_send_gift_notification_email' ) );
		
		// Add filter to change Order Status to 'on-hold', while checkout with COD, if PDF VOucher is enabled
		//add_filter( 'woocommerce_cod_process_payment_order_status', array( $this, 'woocommerce_cod_process_payment_order_status_func' ), 999, 2 );
		
		// Add action to add custom fields on coupon page
		add_action( 'woocommerce_coupon_options', array( $this, 'woo_vou_coupon_options' ) );
		
		// Add action to save custom fields on coupon page
		add_action( 'woocommerce_coupon_options_save', array( $this, 'woo_vou_save_coupon_options' ), 15 );
	}
}