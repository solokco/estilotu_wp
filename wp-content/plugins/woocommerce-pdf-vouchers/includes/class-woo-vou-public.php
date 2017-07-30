<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Public Pages Class
 * 
 * Handles all the different features and functions
 * for the front end pages.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
class WOO_Vou_Public {

	public $model;

	public function __construct() {

		global $woo_vou_model;

		$this->model = $woo_vou_model;
	}

	/**
	 * Handles to update voucher details in order data
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_product_purchase( $order_id ) {

		//Get Prefix
		$prefix		= WOO_VOU_META_PREFIX;

		$changed	= false;
		$voucherdata = $vouchermetadata = $recipient_order_meta	= array();

		//Get user data from order
		$userdata    	= $this->model->woo_vou_get_payment_user_info( $order_id );

		//Get buyers information
		$userfirstname 	= isset( $userdata['first_name'] ) ? trim( $userdata['first_name'] ) : '';
		$userlastname 	= isset( $userdata['last_name'] ) ? trim( $userdata['last_name'] ) : '';
		$useremail 		= isset( $userdata['email'] ) ? $userdata['email'] : '';
		$buyername		= str_replace(' ', '_', $userfirstname);		

		// Check woocommerce order class
		if( class_exists( 'WC_Order' ) ) {

			$order = new WC_Order( $order_id );
			$order_items = $order->get_items();

			//Get Order Date
			$order_date	= isset( $order->order_date ) ? $order->order_date : '';

			if ( is_array( $order_items ) ) {

				// Check cart details
				foreach ( $order_items as $item_id => $item ) {

					//get product id
					$productid	= $item['product_id'];

					//get product quantity
					$productqty = $item['qty'];

					// Taking variation id
					$variation_id = !empty( $item['variation_id'] ) ? $item['variation_id'] : '';

					// If product is variable product take variation id else product id
					$data_id = ( !empty( $variation_id ) ) ? $variation_id : $productid;

					//Get voucher code from item meta "Now we store voucher codes in item meta fields"
					$codes_item_meta	= wc_get_order_item_meta( $item_id, $prefix.'codes' );

					if( empty( $codes_item_meta ) ) {// If voucher data are not empty so code get executed once only

						//voucher codes
						$vou_codes		= $this->model->woo_vou_get_voucher_code( $productid , $variation_id );

						//vendor user
						$vendor_user	= get_post_meta( $productid, $prefix.'vendor_user', true );

						//Secondary vendors
						$sec_vendor_users	= get_post_meta( $productid, $prefix.'sec_vendor_users', true );

						//get vendor detail
						$vendor_detail	= $this->model->woo_vou_get_vendor_detail( $productid , $vendor_user );

						//using type of voucher
						$using_type		= isset( $vendor_detail['using_type'] ) ? $vendor_detail['using_type'] : '';

						$allow_voucher_flag = true;

						// if using type is one time and voucher code is empty or quantity is zero
						if( empty( $using_type ) && (empty( $vou_codes ) )  ) { // || $avail_total_codes == '0'
							$allow_voucher_flag	= false;
						}

						//check enable voucher & is downlable & total codes are not empty
						if( $this->model->woo_vou_check_enable_voucher( $productid, $variation_id ) && $allow_voucher_flag == true ) {

							// start date
							$start_date	= get_post_meta( $productid, $prefix.'start_date', true );
							if( !empty( $start_date ) ) {
								//format start date
								$start_date	= date( 'Y-m-d H:i:s', strtotime( $start_date ) );
							} else {
								$start_date = '';	
							}
							
							//manual expiration date
							$manual_expire_date	= get_post_meta( $productid, $prefix.'exp_date', true );
							if( !empty( $manual_expire_date ) ) {								
								//expiry data
								$exp_date	= date( 'Y-m-d H:i:s', strtotime( $manual_expire_date ) );
							} else {								
								$exp_date	= '';
							}
							
							// Disable redeem days
							$disable_redeem_days = get_post_meta( $productid, $prefix.'disable_redeem_day', true );
							if ( empty ( $disable_redeem_days ) ) {
								$disable_redeem_days = '';
							}

							//get expiration tpe
							$exp_type 		 = get_post_meta( $productid, $prefix.'exp_type', true );

							//custom days
							$custom_days 	 = '';

							if( $exp_type == 'based_on_purchase' ){ //If expiry type based in purchase

								//get days difference
								$days_diff	= get_post_meta( $productid, $prefix.'days_diff', true );

								if( $days_diff == 'cust' ) {
									$custom_days	= get_post_meta( $productid, $prefix.'custom_days', true );
									$custom_days	= isset( $custom_days ) ? $custom_days : '';
									if( !empty( $custom_days ) ) {
										$add_days 	= '+'.$custom_days.' days';
										$exp_date 	= date( 'Y-m-d H:i:s', strtotime( $order_date . $add_days ) );
									} else {
										$exp_date 	= date( 'Y-m-d H:i:s', current_time('timestamp') );
									}
								} else {
									$custom_days = $days_diff;
									$add_days 	= '+'.$custom_days.' days';
									$exp_date 	= date( 'Y-m-d H:i:s', strtotime( $order_date . $add_days ) );
								}
							}

							//voucher code
							$vouchercodes = $vou_codes;
							$vouchercodes = trim( $vouchercodes, ',' );

							//explode all voucher codes
							$salecode = !empty( $vouchercodes ) ? explode( ',', $vouchercodes ) : array();

							// trim code
							foreach ( $salecode as $code_key => $code ) {
								$salecode[$code_key] = trim( $code );
							}

							$allcodes = '';

							//if voucher useing type is more than one time then generate voucher codes
							if( !empty( $using_type ) ) { 

								//if user buy more than 1 quantity of voucher
								if( isset( $productqty ) && $productqty > 1 ) {
									for( $i = 1; $i <= $productqty; $i++ ) {

										$voucode = $code_prefix = '';

										//make voucher code
										$randcode	= array_rand( $salecode );
										
										if( !empty( $salecode[$randcode] ) && trim( $salecode[$randcode] ) != '' ) {
											$code_prefix = trim( $salecode[$randcode] );
										}

										$vou_argument	= array(
																'buyername' 	=> $buyername,
																'code_prefix'	=> $code_prefix,
																'order_id'		=> $order_id,
																'data_id'		=> $data_id,
																'item_id'		=> $item_id,
																'counter'		=> $i
															);
										$voucode	= woo_vou_unlimited_voucher_code_pattern( $vou_argument );
										$allcodes .= $voucode.', ';
									}
								} else {
									
									$voucode = $code_prefix = '';
									
									//make voucher code when user buy single quantity
									$randcode = array_rand( $salecode );
									
									if( !empty( $salecode[$randcode] ) && trim( $salecode[$randcode] ) != '' ) {
										$code_prefix = trim( $salecode[$randcode] );
									}
									
									//voucher codes arguments for create unlinited voucher
									$vou_argument	= array(
															'buyername'		=> $buyername,
															'code_prefix'	=> $code_prefix,
															'order_id'		=> $order_id,
															'data_id'		=> $data_id,
															'item_id'		=> $item_id
														);
									
									$voucode	= woo_vou_unlimited_voucher_code_pattern( $vou_argument );
									
									$allcodes .= $voucode.', ';
								}
							} else {
								for ( $i = 0; $i < $productqty; $i++ ) {

									//get first voucher code
									$voucode = $salecode[$i];

									//unset first voucher code to remove from all codes
									unset( $salecode[$i] );
									$allcodes .= $voucode.', ';
								}

								//after unsetting first code make one string for other codes
								$lessvoucodes = implode( ',', $salecode );
								$this->model->woo_vou_update_voucher_code( $productid , $variation_id, $lessvoucodes );
								
								//Reduce stock quantity when order created and voucher deducted
								$this->model->woo_vou_update_product_stock( $productid , $variation_id, $salecode );
							}

							$allcodes = trim( $allcodes, ', ' );

							//add voucher codes item meta "Now we store voucher codes in item meta fields"
							//And Remove "order_details" array from here
							wc_add_order_item_meta( $item_id, $prefix.'codes', $allcodes );

							//Append for voucher meta data into order
							$productvoumetadata = array(
															'user_email'		=>	$useremail,
															'pdf_template'		=>	$vendor_detail['pdf_template'],
															'vendor_logo'		=>	$vendor_detail['vendor_logo'],
															'start_date'		=>	$start_date,
															'exp_date'			=>	$exp_date,
															'exp_type'			=>	$exp_type,
															'custom_days'		=>	$custom_days,
															'using_type'		=>	$using_type,
															'vendor_address'	=>	$vendor_detail['vendor_address'],
															'website_url'		=>	$vendor_detail['vendor_website'],
															'redeem'			=>	$vendor_detail['how_to_use'],
															'avail_locations'	=>	$vendor_detail['avail_locations']
														);

							$vouchermetadata[$productid] = apply_filters( 'woo_vou_meta_order_voucher_detail', $productvoumetadata, $order_id, $item_id, $productid );

							$all_vou_codes	= !empty( $allcodes ) ? explode( ', ', $allcodes ) : array();

							foreach ( $all_vou_codes as $vou_code ) {

								$vou_code = trim( $vou_code, ',' );
								$vou_code = trim( $vou_code );

								//Insert voucher details into custom post type with seperate voucher code
								$vou_codes_args = array(
															'post_title'	=>	$order_id,
															'post_content'	=>	'',
															'post_status'	=>	'pending',
															'post_type'		=>	WOO_VOU_CODE_POST_TYPE,
															'post_parent'	=>	$productid
														);

								if( !empty( $vendor_user ) ) { // Check vendor user is not empty
									$vou_codes_args['post_author'] = $vendor_user;
								}

								$vou_codes_id	= wp_insert_post( $vou_codes_args );

								if( $vou_codes_id ) { // Check voucher codes id is not empty

									// update buyer first name
									update_post_meta( $vou_codes_id, $prefix.'first_name', $userfirstname );
									// update buyer last name
									update_post_meta( $vou_codes_id, $prefix.'last_name', $userlastname );
									// update order id
									update_post_meta( $vou_codes_id, $prefix.'order_id', $order_id );
									// update order date
									update_post_meta( $vou_codes_id, $prefix.'order_date', $order_date );
									// update start date
									update_post_meta( $vou_codes_id, $prefix.'start_date', $start_date );
									// update expires date
									update_post_meta( $vou_codes_id, $prefix.'exp_date', $exp_date );
									// update disable redeem days
									update_post_meta( $vou_codes_id, $prefix.'disable_redeem_day', $disable_redeem_days );
									// update purchased codes
									update_post_meta( $vou_codes_id, $prefix.'purchased_codes', $vou_code );
									//update secondary vendors
									$sec_vendors	= !empty( $sec_vendor_users ) ? implode( ',', $sec_vendor_users ) : '';
									update_post_meta( $vou_codes_id, $prefix.'sec_vendor_users', $sec_vendors );

									$vou_from_variation	= get_post_meta( $productid, $prefix.'is_variable_voucher', true );

									if( !empty( $vou_from_variation ) ) {

										// update purchased codes
										update_post_meta( $vou_codes_id, $prefix.'vou_from_variation', $data_id );
									}
									
									do_action( 'woo_vou_update_voucher_code_meta', $vou_codes_id, $order_id, $item_id, $productid );
								}
							}
						}
					}
				}

				//Get custom meta data of order
				$custom_metadata	= get_post_custom( $order_id );

				if( !isset( $custom_metadata['multiple_pdf'] ) ) { // Multipdf is already updated

					//update If setting is set for multipdf or not
					$multiple_pdf	= get_option( 'multiple_pdf' );

					//update multipdf option in ordermeta
					update_post_meta( $order_id, $prefix . 'multiple_pdf', $multiple_pdf );
				}

				if( !empty( $vouchermetadata ) ) { // Check voucher meta data are not empty
					//update voucher order details with all meta data
					update_post_meta( $order_id, $prefix.'meta_order_details', $vouchermetadata );
				}
			}
		}
	}

	/**
	 * Add custom email notification to woocommerce
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.4
	 */
	public function woo_vou_add_email_notification( $email_actions ) {

		$email_actions[]	= 'woo_vou_vendor_sale_email';
		$email_actions[]	= 'woo_vou_gift_email';

		return apply_filters( 'woo_vou_add_email_notification', $email_actions );
	}

	/**
	 * Display Download Voucher Link
	 * 
	 * Handles to display product voucher link for user
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_downloadable_files( $downloadable_files, $product ) {

		global $post, $vou_order, $woo_vou_item_id;

		$prefix	= WOO_VOU_META_PREFIX;

		$pdf_downloadable_files	= array();

		// Taking variation id
		$variation_id = !empty($product->variation_id) ? $product->variation_id : $product->id;

		$order_id = $this->model->woo_vou_get_orderid_for_page(); // Getting order id

		//Get Order id on shop_order page
		// this is called when we make order complete from the backend
		if( is_admin() && !empty( $post->post_type ) && $post->post_type == 'shop_order' ) {

			$order_id	= isset( $post->ID ) ? $post->ID : '';
		}

		if( empty( $order_id ) ) { // Return download files if order id not found
			return $downloadable_files;
		}
		
		if( empty( $woo_vou_item_id ) ) {
			return $downloadable_files;
		}

		//Get vouchers download files
		$pdf_downloadable_files	= $this->woo_vou_get_vouchers_download_key( $order_id, $variation_id, $woo_vou_item_id );

		//Mearge existing download files with vouchers file
		if( !empty( $downloadable_files ) ) {
			$downloadable_files	= array_merge( $downloadable_files, $pdf_downloadable_files );
		} else {
			$downloadable_files	= $pdf_downloadable_files;
		}

		return apply_filters( 'woo_vou_downloadable_files', $downloadable_files, $product );
	}

	/**
	 * Download Process
	 *
	 * Handles to product process
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_download_process( $email, $order_key, $product_id, $user_id, $download_id, $order_id ) {

		if( !empty( $_GET['item_id'] ) ) {

			$item_id	= $_GET['item_id'];

			//Generate PDF
			$this->model->woo_vou_generate_pdf_voucher( $email, $product_id, $download_id, $order_id, $item_id );

			// Added support for dwnload pdf count
			$downlod_data	= $this->model->woo_vou_get_download_data( array( 
														'product_id'  => $product_id,
														'order_key'   => wc_clean( $_GET['order'] ),
														'email'       => sanitize_email( str_replace( ' ', '+', $_GET['email'] ) ),
														'download_id' => wc_clean( isset( $_GET['key'] ) ? preg_replace( '/\s+/', ' ', $_GET['key'] ) : '' )
													));
			
			$this->model->woo_vou_count_download( $downlod_data );
			exit;
		}
	}

	/**
	 * Insert pdf voucher files
	 * 
	 * Handles to insert pdf voucher
	 * files in database
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_insert_downloadable_files( $order_id ) {

		$prefix	= WOO_VOU_META_PREFIX;

		$downloadable_files	= array();

		//Get Order
		$order = new WC_Order( $order_id );

		if ( sizeof( $order->get_items() ) > 0 ) { //Get all items in order

			foreach ( $order->get_items() as $item_id => $item ) {

				//Get product from Item ( It is required otherwise multipdf voucher link not work and global $woo_vou_item_id will not work )
				$_product	= $order->get_product_from_item( $item );

				// Taking variation id
				$variation_id = !empty($item['variation_id']) ? $item['variation_id'] : '';

				if ( $_product && $_product->exists()) { // && $_product->is_downloadable()

					//get product id from prduct data
					$product_id	= isset( $_product->id ) ? $_product->id : '';

					// If product is variable product take variation id else product id
					$data_id = ( !empty($variation_id) ) ? $variation_id : $product_id;

					if( $this->model->woo_vou_check_enable_voucher( $product_id, $variation_id ) ) {//Check voucher is enabled or not

						//Get vouchers downlodable pdf files
						$downloadable_files	= $this->woo_vou_get_vouchers_download_key( $order_id, $data_id, $item_id );

						foreach ( array_keys( $downloadable_files ) as $download_id ) {

							//Insert pdf vouchers in downloadable table
							wc_downloadable_file_permission( $download_id, $data_id, $order );
						}
					}
				}
			}
		}

		// Status update from pending to publish when voucher is get completed or processing
		$args	= array( 
						'post_status'	=> array( 'pending' ),
						'meta_query'	=> array(
												array(
													'key'	=> $prefix . 'order_id',
													'value'	=> $order_id,
												)
											)
					);

		// Get vouchers code of this order
		$purchased_vochers	= $this->model->woo_vou_get_voucher_details( $args );

		if( !empty( $purchased_vochers ) ) { // If not empty voucher codes

			//For all possible vouchers
			foreach ( $purchased_vochers as $vocher ) {

				// Get voucher data
				$current_post = get_post( $vocher['ID'], 'ARRAY_A' );
				//Change voucher status
				$current_post['post_status'] = 'publish';
				//Update voucher post
				wp_update_post( $current_post );
			}
		}
	}

	/**
	 * Get downloadable vouchers files
	 * 
	 * Handles to get downloadable vouchers files
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_get_vouchers_download_key( $order_id = '', $product_id = '', $item_id = '' ) {

		$prefix	= WOO_VOU_META_PREFIX;
		$downloadable_files	= array();

		//Get mutiple pdf option from order meta
		$multiple_pdf = empty( $order_id ) ? '' : get_post_meta( $order_id, $prefix . 'multiple_pdf', true );

		if( !empty( $order_id ) ) {

			if( $multiple_pdf == 'yes' ) { //If multiple pdf is set

				$vouchercodes	= $this->model->woo_vou_get_multi_voucher_key( $order_id, $product_id, $item_id );

				foreach ( $vouchercodes as $codes ) {

					$downloadable_files[$codes] = array(
															'name' => woo_vou_voucher_download_text( $product_id ),
															'file' => get_permalink( $product_id )
														);
				}
			} else {

				// Set our vocher download file in download files
				$downloadable_files['woo_vou_pdf_1'] = array(
																'name' => woo_vou_voucher_download_text( $product_id ),
																'file' => get_permalink( $product_id )
															);
			}
		}

		return apply_filters( 'woo_vou_get_vouchers_download_key', $downloadable_files, $order_id, $product_id, $item_id );
	}
	
	/**
	 * Set Order As Global Variable
	 * 
	 * Handles to set order as global variable
	 * when order links displayed in email
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_email_before_order_table( $order ) {

		global $vou_order;

		//Get Order_id from order data
		$order_id	= isset( $order->id ) ? $order->id : '';
		//Create global varible for order
		$vou_order	= $order_id;
	}

	/**
	 * Allow admin access to vendor user
	 *
	 * Handles to allow admin access to vendor user
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_prevent_admin_access( $prevent_access ) {

		global $current_user, $woo_vou_vendor_role;

		//Get User roles
		$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
		$user_role	= array_shift( $user_roles );

		if( in_array( $user_role, $woo_vou_vendor_role ) ) { // Check vendor user role

			$prevent_access = false;	
		}
		
		return apply_filters( 'woo_vou_prevent_admin_access', $prevent_access );
	}

	/**
	 * Check Voucher Code
	 * 
	 * Handles to check voucher code
	 * is valid or invalid via ajax
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_check_voucher_code() {

		global $current_user, $woo_vou_vendor_role;

		$prefix				= WOO_VOU_META_PREFIX;
		$product_name		= '';
		$product_id			= '';
		$expiry_Date		= '';
		$response['expire']	= false;
		$vou_code_args		= array();
		$used_code_args		= array();

		if( !empty( $_POST['voucode'] ) ) { // Check voucher code is not empty

			//Voucher Code
			$voucode = strtolower( $_POST['voucode'] );
			$voucode = trim( $voucode );

			//Get User roles
			$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
			$user_role	= array_shift( $user_roles );

			//voucher admin roles
			$admin_roles	= woo_vou_assigned_admin_roles();

			if( !in_array( $user_role, $admin_roles ) ) {// voucher admin can redeem all codes

				$vou_code_args['author']	= $current_user->ID;
				$used_code_args['author']	= $current_user->ID;
			}

			// arguments for get purchased an used voucher code detail
			$vou_code_args['fields']		= 'ids';
			$vou_code_args['meta_query']	= array(
													array(
														'key' 		=> $prefix . 'purchased_codes',
														'value' 	=> $voucode
													),
													array(
														'key'     	=> $prefix . 'used_codes',
														'compare' 	=> 'NOT EXISTS'
													)
												);

			// this always return array
			$voucodedata = $this->model->woo_vou_get_voucher_details( $vou_code_args );

			// argunments array for used voucher code
			$used_code_args['fields']		= 'ids';
			$used_code_args['meta_query']	= array(
													array(
														'key' 		=> $prefix . 'used_codes',
														'value' 	=> $voucode
													)
												);

			// for used voucher code
			$usedcodedata = $this->model->woo_vou_get_voucher_details( $used_code_args );

			//Make meta args for secondary vendor
			$secvendor_args	= array(
										  'key'     => $prefix.'sec_vendor_users',
										  'value'   => $current_user->ID,
										  'compare'	=> 'LIKE'
									);

			//Argument for second query voucher code
			unset( $vou_code_args['author'] );
			$vou_code_args['meta_query'][] = $secvendor_args; 

			//Combined both result in main voucher code
			$voucodedata2	= $this->model->woo_vou_get_voucher_details( $vou_code_args );
			$voucodedata	= array_unique( array_merge( $voucodedata, $voucodedata2 ) );

			//Argument for second query voucher code
			unset( $used_code_args['author'] );
			$used_code_args['meta_query'][] = $secvendor_args; 

			//Combined both result in main voucher code
			$usedcodedata2	= $this->model->woo_vou_get_voucher_details( $used_code_args );
			$usedcodedata	= array_unique( array_merge( $usedcodedata, $usedcodedata2 ) );

			if( !empty( $voucodedata ) && is_array( $voucodedata ) ) { // Check voucher code ids are not empty

				$voucodeid = isset( $voucodedata[0] ) ? $voucodedata[0] : '';
				
				if( !empty( $voucodeid ) ) {

					//get vouchercodes data 
					$voucher_data	= get_post( $voucodeid );
					$order_id		= get_post_meta( $voucodeid , $prefix.'order_id' , true );
					$cart_details	= new Wc_Order( $order_id );
					$order_items	= $cart_details->get_items();

					foreach ( $order_items as $item_id => $download_data ) {

						$voucher_codes	= wc_get_order_item_meta( $item_id, $prefix.'codes' );
						$voucher_codes	= !empty( $voucher_codes ) ? explode(',',$voucher_codes) : array();
						$voucher_codes	= array_map( 'trim', $voucher_codes );
						$voucher_codes  = array_map( 'strtolower', $voucher_codes );

						if( in_array( $voucode, $voucher_codes ) ) {

							//get product data
							$product_name = $download_data['name'];
							$product_id  = $download_data['product_id'];
						}
					}
				}
				
				//voucher start date
				$start_Date = get_post_meta( $voucodeid , $prefix .'start_date' ,true );
				
				//voucher expired date
				$expiry_Date = get_post_meta( $voucodeid , $prefix .'exp_date' ,true );
				
				$response['success'] = apply_filters( 'woo_vou_voucher_code_valid_message', sprintf( __( 'Voucher code is valid and this voucher code has been bought for %s. ' . "\n" . 'If you would like to redeem voucher code, Please click on the redeem button below:', 'woovoucher' ), $product_name ), $product_name );								
				
				if( !empty( $product_id) ) {				
				$disable_redeem_days = get_post_meta( $voucodeid, $prefix.'disable_redeem_day', true );
				if( !empty($disable_redeem_days ) ) { // check days are selected					
					$current_day = date('l');
					
					if( in_array( $current_day, $disable_redeem_days ) ) { // check current day redeem is enable or not
							$message = implode(", ", $disable_redeem_days );

						 	$response['success'] = apply_filters( 'woo_vou_voucher_code_disabled_message', sprintf( __( 'Sorry, voucher code is not allowed to be used on %s. ' . "\n" ,'woovoucher'), $message ,$product_name ));
						 	$response['allow_redeem_expired_voucher'] = "no";
						 	$response['expire'] = true;
						}
					}
				}				
				
				if( isset( $start_Date ) && !empty( $start_Date ) ) {

					if( $start_Date > $this->model->woo_vou_current_date() ) {
						
						$response['before_start_date'] = true;
						$response['success'] = apply_filters( 'woo_vou_voucher_code_before_start_message', sprintf( __( 'Voucher code cannot be redeemed before %s for %s.' . "\n" ,'woovoucher'), $this->model->woo_vou_get_date_format( $start_Date , true ) ,$product_name ), $product_name, $start_Date );		
					}
				}
				
				if( isset( $expiry_Date ) && !empty( $expiry_Date ) ) {

					if( $expiry_Date < $this->model->woo_vou_current_date() ) {
						
						$response['expire'] = true;
						
						// check need to allow redeem for expired vouchers
						$allow_redeem_expired_voucher = get_option('vou_allow_redeem_expired_voucher');
						if( $allow_redeem_expired_voucher == "yes" )
							$response['allow_redeem_expired_voucher'] = "yes";
						else
							$response['allow_redeem_expired_voucher'] = "no";
							
						$response['success'] = apply_filters( 'woo_vou_voucher_code_expired_message', sprintf( __( 'Voucher code was expired on %s for %s. ' . "\n" ,'woovoucher'), $this->model->woo_vou_get_date_format( $expiry_Date , true ) ,$product_name ), $product_name, $expiry_Date );		
					}
				}
				
				$response['product_detail'] = $this->woo_vou_get_product_detail( $order_id, $voucode, $voucodeid );

			} else if (!empty( $usedcodedata ) && is_array( $usedcodedata ) ) { // Check voucher code is used or not

				$voucodeid = isset( $usedcodedata[0] ) ? $usedcodedata[0] : '';

				if( !empty( $voucodeid ) ) { //if voucher code id is not empty

					$voucher_data 		= get_post( $voucodeid );
					$order_id 			= get_post_meta( $voucodeid , $prefix.'order_id' , true );
					$cart_details 		= new Wc_Order( $order_id );
					$order_items 		= $cart_details->get_items();

					foreach ( $order_items as $item_id => $download_data ) {

						$voucher_codes	= wc_get_order_item_meta( $item_id, $prefix.'codes' );
						$voucher_codes	= !empty( $voucher_codes ) ? explode(',',$voucher_codes) : array();
						$voucher_codes	= array_map( 'trim', $voucher_codes );
						$voucher_codes	= array_map( 'strtolower', $voucher_codes );

						$check_code		= trim( $voucode );
						$check_code		= strtolower( $check_code );

						if( in_array( $check_code, $voucher_codes ) ) {

							//get product data
							$product_name 		= $download_data['name'];
						}
					}
				}

				// get used code date
				$used_code_date = get_post_meta( $voucodeid, $prefix.'used_code_date', true );
				$response['used'] = apply_filters( 'woo_vou_voucher_code_used_message', sprintf( __( 'Voucher code is invalid, was used on %s for %s.', 'woovoucher' ), $this->model->woo_vou_get_date_format( $used_code_date, true ), $product_name ), $product_name, $used_code_date );

			} else {
				$response['error'] = apply_filters( 'woo_vou_voucher_code_invalid_message', __( 'Voucher code doesn\'t exist.', 'woovoucher' ) );
			}

			if( isset( $_POST['ajax'] ) && $_POST['ajax'] == true ) {  // if request through ajax
				echo json_encode( $response );
				exit;	
			} else {
				return $response;
			}
		}
	}

	/**
	 * Get Product Detail From Order ID
	 * 
	 * Handles to get product detail
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.6.2
	 */
	public function woo_vou_get_product_detail( $order_id, $voucode, $voucodeid = '' ) {

		ob_start();
		require( WOO_VOU_ADMIN . '/forms/woo-vou-check-code-product-info.php' );
		$html = ob_get_clean();
		
		return apply_filters( 'woo_vou_get_product_detail', $html, $order_id, $voucode, $voucodeid );
	}

	/**
	 * Save Voucher Code
	 * 
	 * Handles to save voucher code
	 * via ajax
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_save_voucher_code() {

		$prefix = WOO_VOU_META_PREFIX;

		global $woo_vou_vendor_role, $current_user;

		if( !empty( $_POST['voucode'] ) ) { // Check voucher code is not empty

			//Voucher Code
			$voucode = $_POST['voucode'];
			
			// Get partial redeem global settings
			$enable_partial_redeem = get_option( 'vou_enable_partial_redeem' );
			
			if( $enable_partial_redeem == "yes" ) {
				
				// redeem Amount
				$redeem_amount = isset( $_POST['vou_partial_redeem_amount'] ) && !empty( $_POST['vou_partial_redeem_amount'] ) ? $_POST['vou_partial_redeem_amount'] : '';
				// redeem Method  
				$redeem_method = isset( $_POST['vou_redeem_method'] ) && !empty( $_POST['vou_redeem_method'] ) ? $_POST['vou_redeem_method'] : '';
				// total price
				$total_price = isset( $_POST['vou_code_total_price'] ) && !empty( $_POST['vou_code_total_price'] ) ? $_POST['vou_code_total_price'] : '';
				// redeemed price
				$total_redeemed_price = isset( $_POST['vou_code_total_redeemed_price'] ) && !empty( $_POST['vou_code_total_redeemed_price'] ) ? $_POST['vou_code_total_redeemed_price'] : '';
				// remaining redeem price
				$remaining_redeem_price = isset( $_POST['vou_code_remaining_redeem_price'] ) && !empty( $_POST['vou_code_remaining_redeem_price'] ) ? $_POST['vou_code_remaining_redeem_price'] : '';
				
				// in case if javascript validation fail then this will prevent from redeem wrong amount
				if( $redeem_method == 'partial' && ( $redeem_amount == '' || $redeem_amount > $remaining_redeem_price ) ) {
					return;
				}
			}
						
			//Check voucher code
			$args	= array();

			//Get User roles
			$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();

			//Get user id
			$user_id	= isset( $current_user->ID ) ? $current_user->ID : '';

			//Get user role
			$user_role	= array_shift( $user_roles );

			//get voucher admin roles
			$admin_roles	= woo_vou_assigned_admin_roles();

			if( !in_array( $user_role, $admin_roles ) ) {// voucher admin can redeem all voucher codes
				$args['author'] = $user_id;
			}
			
			$args['fields']		= 'ids';
			$args['meta_query']	= array(
										array(
												'key' 		=> $prefix . 'purchased_codes',
												'value' 	=> $voucode
											),
										array(
												'key'     	=> $prefix . 'used_codes',
												'compare' 	=> 'NOT EXISTS'
										)
									);

			$voucodedata = $this->model->woo_vou_get_voucher_details( $args );

			//Make meta args for secondary vendor
			$secvendor_args	= array(
										  'key'     => $prefix.'sec_vendor_users',
										  'value'   => $user_id,
										  'compare'	=> 'LIKE'
									);

			//Argument for second query voucher code
			unset( $args['author'] );
			$args['meta_query'][] = $secvendor_args; 

			//Combined both result in main voucher code
			$voucodedata2	= $this->model->woo_vou_get_voucher_details( $args );
			$voucodedata	= array_unique( array_merge( $voucodedata, $voucodedata2 ) );

			if( !empty( $voucodedata ) && is_array( $voucodedata ) ) { // Check voucher code ids are not empty
				
				//current date
				$today = $this->model->woo_vou_current_date();								
												
				// if partial redeem is enabled then process parial redeem
				if( $enable_partial_redeem == "yes" && !empty( $redeem_method ) && $redeem_method == 'partial' ) {
					
					foreach ( $voucodedata as $voucodeid ) {

						$this->model->woo_vou_save_partialy_redeem_voucher_code( $voucodeid, $redeem_amount, $voucode );	
						
						if( $redeem_amount == $remaining_redeem_price ) { // need to save full redeem data
							
							// update used codes
							update_post_meta( $voucodeid, $prefix.'used_codes', $voucode );
		
							// update redeem by
							update_post_meta( $voucodeid, $prefix.'redeem_by', $user_id );
		
							// update used code date
							update_post_meta( $voucodeid, $prefix.'used_code_date', $today );
		
							//after redeem voucher code
							do_action( 'woo_vou_redeemed_voucher_code', $voucodeid );		
						}					
						
						// break is neccessary so if 2 code found then only 1 get marked as completed.
						break;
					}
										
				} else {															
	
					foreach ( $voucodedata as $voucodeid ) {
						
						if( $redeem_method == 'full' ) {														
							
							$this->model->woo_vou_save_partialy_redeem_voucher_code( $voucodeid, $remaining_redeem_price ,$voucode );									
						}
	
						// update used codes
						update_post_meta( $voucodeid, $prefix.'used_codes', $voucode );
	
						// update redeem by
						update_post_meta( $voucodeid, $prefix.'redeem_by', $user_id );
	
						// update used code date
						update_post_meta( $voucodeid, $prefix.'used_code_date', $today );
	
						//after redeem voucher code
						do_action( 'woo_vou_redeemed_voucher_code', $voucodeid );
						
						// break is neccessary so if 2 code found then only 1 get marked as completed.
						break;
					}
				}
			}

			if( isset( $_POST['ajax'] ) && $_POST['ajax'] == true ) { // if request through ajax
				echo 'success';
				exit;
			} else {
				return 'success';
			}
		}
	}

	/**
	 * Display Check Code Html
	 * 
	 * Handles to display check code html for user and admin
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_check_code_content() { ?>

		<table class="form-table woo-vou-check-code">
			<tr>
				<th>
					<label for="woo_vou_voucher_code"><?php _e( 'Enter Voucher Code', 'woovoucher' ) ?></label>
				</th>
				<td>
					<input type="text" id="woo_vou_voucher_code" name="woo_vou_voucher_code" value="" />
					<input type="button" id="woo_vou_check_voucher_code" name="woo_vou_check_voucher_code" class="button-primary" value="<?php _e( 'Check It', 'woovoucher' ) ?>" />
					<div class="woo-vou-loader woo-vou-check-voucher-code-loader"><img src="<?php echo WOO_VOU_IMG_URL;?>/ajax-loader.gif"/></div>
					<div class="woo-vou-voucher-code-msg"></div>
				</td>
			</tr>
			<tr class="woo-vou-voucher-code-submit-wrap">
				<th>
				</th>
				<td>
					<?php 
						echo apply_filters('woo_vou_voucher_code_submit','<input type="submit" id="woo_vou_voucher_code_submit" name="woo_vou_voucher_code_submit" class="button-primary" value="'.__( "Redeem", "woovoucher" ).'"/>'
						);
					?>
					<div class="woo-vou-loader woo-vou-voucher-code-submit-loader"><img src="<?php echo WOO_VOU_IMG_URL;?>/ajax-loader.gif"/></div>
				</td>
			</tr>
			<?php do_action( 'woo_vou_inner_check_code_table' ); ?>
		</table><?php
		
		do_action( 'woo_vou_after_check_code_content' );
	}

	/**
	 * Add Capability to vendor role
	 * 
	 * Handle to add capability to vendor role
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_initilize_role_capabilities() {

		global $woo_vou_vendor_role;

		$class_exist	= apply_filters( 'woo_vou_initilize_role_capabilities', class_exists( 'WC_Vendors' ) );

		//Return if class not exist 
		if( !$class_exist ) return;

		foreach ( $woo_vou_vendor_role as $vendor_role ) {

			//get vendor role
			$vendor_role_obj = get_role( $vendor_role );

			if( !empty( $vendor_role_obj ) ) { // If vendor role is exist 

				if( !$vendor_role_obj->has_cap( WOO_VOU_VENDOR_LEVEL ) ) { //If capabilty not exist

					//Add vucher level capability to vendor roles
					$vendor_role_obj->add_cap( WOO_VOU_VENDOR_LEVEL );
				}
			}
		}
	}

	/**
	 * Set Order Product As Global Variable
	 * 
	 * Handles to set order product as global variable
	 * when complete order mail fired or Order Details page is at front side
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.6
	 */
	function woo_vou_order_item_product( $product, $item ) {

		global $woo_vou_order_item;

		$woo_vou_order_item = $item; // Making global of order product item

		return $product;
	}

	/**
	 * Restore Voucher Code
	 * 
	 * Handles to restore voucher codes
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.6.2
	 */
	public function woo_vou_restore_voucher_codes( $order_id, $old_status, $new_status ) {

		//Get prefix
		$prefix	= WOO_VOU_META_PREFIX;

		if( $new_status == 'cancelled' ) { //If status cancelled, failed
			$this->model->woo_vou_restore_order_voucher_codes( $order_id );
		}

		if( $new_status == 'refunded' ) { //If status refunded
			$this->model->woo_vou_refund_order_voucher_codes( $order_id );
		}
	}

	/**
	 * Display Recipient HTML
	 * 
	 * Handles to display the Recipient HTML for user
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.0
	 */
	public function woo_vou_before_add_to_cart_button() { 
		
		do_action( 'woo_vou_product_recipient_fields' );
	}

	/**
	 * add to cart in item data
	 * 
	 * Handles to add to cart in item data
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.0
	 */
	public function woo_vou_woocommerce_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {

		$data_id = !empty( $variation_id ) ? $variation_id : $product_id;

		//Get prefix
		$prefix	= WOO_VOU_META_PREFIX;

		if( isset( $_POST[$prefix.'recipient_name'] ) ) {//If recipient name is set
			$cart_item_data[$prefix.'recipient_name']	= $this->model->woo_vou_escape_slashes_deep( $_POST[$prefix.'recipient_name'][$data_id] );
		}

		if( isset( $_POST[$prefix.'recipient_email'] ) ) {//If recipient email is set
			$cart_item_data[$prefix.'recipient_email']	= $this->model->woo_vou_escape_slashes_deep( $_POST[$prefix.'recipient_email'][$data_id] );
		}

		if( isset( $_POST[$prefix.'recipient_message'] ) ) {//If recipient message is set
			$cart_item_data[$prefix.'recipient_message']= $this->model->woo_vou_escape_slashes_deep( $_POST[$prefix.'recipient_message'][$data_id] );
		}
		
		if( isset( $_POST[$prefix.'recipient_giftdate'] ) ) {//If recipient message is set
			$cart_item_data[$prefix.'recipient_giftdate']= $_POST[$prefix.'recipient_giftdate'][$data_id];
		}
		
		if( isset( $_POST[$prefix.'pdf_template_selection'] ) ) {//If pdf template is set
			$cart_item_data[$prefix.'pdf_template_selection']= $this->model->woo_vou_escape_slashes_deep( $_POST[$prefix.'pdf_template_selection'][$data_id] );
		}

  		return $cart_item_data;
	}

	/**
	 * get to cart in item data to display in cart page
	 * 
	 * Handles to get to cart in item data to display in cart page
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.0
	 */
	public function woo_vou_woocommerce_get_item_data( $data, $item ) {
		
		//Get prefix
		$prefix	= WOO_VOU_META_PREFIX;

		//Get Product ID
		$product_id	= isset( $item['product_id'] ) ? $item['product_id'] : '';

		//Get product recipient meta setting
		$recipient_data	= $this->model->woo_vou_get_product_recipient_meta( $product_id );

		//recipient name lable
		$recipient_name_lable			= $recipient_data['recipient_name_lable'];

		//recipient email lable
		$recipient_email_label			= $recipient_data['recipient_email_label'];

		//recipient message lable
		$recipient_message_label		= $recipient_data['recipient_message_label'];
		
		//recipient message lable
		$recipient_giftdate_label		= $recipient_data['recipient_giftdate_label'];
		
		//pdf template selection label
		$pdf_template_selection_label	= $recipient_data['pdf_template_selection_label'];

		if( !empty( $item[$prefix.'recipient_name'] ) ) {

			$data[] = array(
							'name'		=> $recipient_name_lable,
							'display'	=> $item[$prefix.'recipient_name'],
							'hidden'	=> false,
							'value'		=> ''
						);
		}

		if( !empty( $item[$prefix.'recipient_email'] ) ) {

			$data[] = array(
							'name'		=> $recipient_email_label,
							'display'	=> $item[$prefix.'recipient_email'],
							'hidden'	=> false,
							'value'		=> ''
						);
		}

		if( !empty( $item[$prefix.'recipient_message'] ) ) {

			$data[] = array(
							'name'		=> $recipient_message_label,
							'display'	=> $item[$prefix.'recipient_message'],
							'hidden'	=> false,
							'value'		=> ''
						);
		}
		
		if( !empty( $item[$prefix.'recipient_giftdate'] ) ) {

			$data[] = array(
							'name'		=> $recipient_giftdate_label,
							'display'	=> $item[$prefix.'recipient_giftdate'],
							'hidden'	=> false,
							'value'		=> ''
						);
		}
		
		if( !empty( $item[$prefix.'pdf_template_selection'] ) ) {
			
			$data[] = array(
							'name'		=> $pdf_template_selection_label,
							'display'	=> $item[$prefix.'pdf_template_selection'],
							'hidden'	=> true,
							'value'		=> ''
						);
			
			// enable display
			$enable_template_display	= woo_vou_enable_template_display_features();
			
			if( $enable_template_display ) { // if enabling the display template selection
				
				// pdf template preview image
				$pdf_template_preview_img		= wp_get_attachment_url( get_post_thumbnail_id( $item[$prefix.'pdf_template_selection'] ) );
				
				if( empty( $pdf_template_preview_img ) ) { // if preview image not available
					$pdf_template_preview_img = WOO_VOU_IMG_URL.'/no-preview.png';
				}
				
				$pdf_template_preview_img_title	= get_the_title( $item[$prefix.'pdf_template_selection'] );
				
				$data[] = array(
								'name'		=> $pdf_template_selection_label,
								'display'	=> '<img src="'.$pdf_template_preview_img.'" style="width:50px !important;height:50px !important;cursor:pointer;" title="'.$pdf_template_preview_img_title.'">',
								'hidden'	=> false,
								'value'		=> ''
							);
			}
		}
		
		return $data;
	}

	/**
	 * add to cart in item data from session
	 * 
	 * Handles to add to cart in item data from session
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.0
	 */
	public function woo_vou_get_cart_item_from_session( $cart_item, $values ){

		//Get prefix
		$prefix	= WOO_VOU_META_PREFIX;
		
		if( !empty( $values[$prefix.'recipient_name'] ) ) {//Recipient Name
			$cart_item[$prefix.'recipient_name']	= $values[$prefix.'recipient_name'];
		}
		
		if( !empty( $values[$prefix.'recipient_email'] ) ) {//Recipient Email
			$cart_item[$prefix.'recipient_email']	= $values[$prefix.'recipient_email'];
		}
		
		if( !empty( $values[$prefix.'recipient_message'] ) ) {//Recipient Message
			$cart_item[$prefix.'recipient_message']	= $values[$prefix.'recipient_message'];
		}
		
		if( !empty( $values[$prefix.'recipient_giftdate'] ) ) {//Recipient Message
			$cart_item[$prefix.'recipient_giftdate']	= $values[$prefix.'recipient_giftdate'];
		}
		
		if( !empty( $values[$prefix.'pdf_template_selection'] ) ) {//PDF Template Selection
			$cart_item[$prefix.'pdf_template_selection']	= $values[$prefix.'pdf_template_selection'];
		}
		
		return $cart_item;
	}
	
	/**
	 * add cart item to the order.
	 * 
	 * Handles to add cart item to the order.
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.0
	 */
	public function woo_vou_add_order_item_meta( $item_id, $values ){
		
		//Get prefix
		$prefix		= WOO_VOU_META_PREFIX;
		
		//Initilize recipients labels
		$woo_vou_recipient_labels	= array();
		
		//Get product ID
		$_product_id	= isset( $values['product_id'] ) ? $values['product_id'] : '';
		
		$recipient_labels	= $this->model->woo_vou_get_product_recipient_meta( $_product_id );
		
		if( !empty( $values[$prefix.'recipient_name'] ) ) {//Add recipient name field
			
			wc_add_order_item_meta( $item_id, $prefix.'recipient_name', array(
																			'label'	=> $recipient_labels['recipient_name_lable'],
																			'value'	=> $values[$prefix.'recipient_name']
																		) );
			
			wc_add_order_item_meta( $item_id, $recipient_labels['recipient_name_lable'], $values[$prefix.'recipient_name'] );
		}
		
		if( !empty( $values[$prefix.'recipient_email'] ) ) {//Add recipient email field
			
			wc_add_order_item_meta( $item_id, $prefix.'recipient_email', array(
																			'label'	=> $recipient_labels['recipient_email_label'],
																			'value'	=> $values[$prefix.'recipient_email']
																		) );
			
			wc_add_order_item_meta( $item_id, $recipient_labels['recipient_email_label'], $values[$prefix.'recipient_email'] );
		}
		
		if( !empty( $values[$prefix.'recipient_message'] ) ) {//Add recipient message field
			
			wc_add_order_item_meta( $item_id, $prefix.'recipient_message', array(
																			'label'	=> $recipient_labels['recipient_message_label'],
																			'value'	=> $values[$prefix.'recipient_message']
																		) );
			
			wc_add_order_item_meta( $item_id, $recipient_labels['recipient_message_label'], $values[$prefix.'recipient_message'] );
		}
		
		if( !empty( $values[$prefix.'recipient_giftdate'] ) ) {//Add recipient giftdate field
			
			wc_add_order_item_meta( $item_id, $prefix.'recipient_giftdate', array(
																			'label'	=> $recipient_labels['recipient_giftdate_label'],
																			'value'	=> $values[$prefix.'recipient_giftdate']
																		) );
			
			wc_add_order_item_meta( $item_id, $recipient_labels['recipient_giftdate_label'], $values[$prefix.'recipient_giftdate'] );						
		}
		
		if( !empty( $values[$prefix.'pdf_template_selection'] ) ) {//Add pdf template selection field
			
			wc_add_order_item_meta( $item_id, $prefix.'pdf_template_selection', array(
																			'label'	=> $recipient_labels['pdf_template_selection_label'],
																			'value'	=> $values[$prefix.'pdf_template_selection']
																		) );
			
			// check if template display is anable or not
			$enable_display_template	= woo_vou_enable_template_display_features();
			
			if( $enable_display_template ) { // if enable template preview image display
				
				//pdf template preview image
				$pdf_template_preview_img		= wp_get_attachment_url( get_post_thumbnail_id( $values[$prefix.'pdf_template_selection'] ) );
				
				if( empty( $pdf_template_preview_img ) ){
					$pdf_template_preview_img = WOO_VOU_IMG_URL.'/no-preview.png';
				}
				
				$pdf_template_preview_img_title	= get_the_title( $values[$prefix.'pdf_template_selection'] );
																			
				wc_add_order_item_meta( $item_id, $recipient_labels['pdf_template_selection_label'], '<img src="'.$pdf_template_preview_img.'" style="width:50px !important;height:50px !important;cursor:pointer;" title="'.$pdf_template_preview_img_title.'">' );
			}
		}
	}
	
	/**
	 * This is used to ensure any required user input fields are supplied
	 * 
	 * Handles to This is used to ensure any required user input fields are supplied
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.0
	 */
	public function woo_vou_add_to_cart_validation( $valid, $product_id, $quantity, $variation_id = '', $variations = array(), $cart_item_data = array() ) {
		
		//Get prefix
		$prefix		 = WOO_VOU_META_PREFIX;		
		$_product_id = $variation_id ? $variation_id : $product_id;
		$product	 = wc_get_product( $_product_id );
		
		//voucher enable or not
		$voucher_enable	= $this->model->woo_vou_check_enable_voucher( $product_id, $variation_id );
		
		if( $voucher_enable ) {//If voucher enable
			
			//Get product recipient meta setting
			$recipient_data	= $this->model->woo_vou_get_product_recipient_meta( $product_id );
			
			if( isset( $_POST[$prefix.'recipient_name'][$_product_id] ) ) {//Strip recipient name
				$_POST[$prefix.'recipient_name'][$_product_id]	= $this->model->woo_vou_escape_slashes_deep( trim( $_POST[$prefix.'recipient_name'][$_product_id] ) );
			}
			if( isset( $_POST[$prefix.'recipient_email'][$_product_id] ) ) {//Strip recipient email
				$_POST[$prefix.'recipient_email'][$_product_id]	= $this->model->woo_vou_escape_slashes_deep( trim( $_POST[$prefix.'recipient_email'][$_product_id] ) );
			}
			if( isset( $_POST[$prefix.'recipient_message'][$_product_id] ) ) {//Strip recipient message
				$_POST[$prefix.'recipient_message'][$_product_id]	= $this->model->woo_vou_escape_slashes_deep( trim( $_POST[$prefix.'recipient_message'][$_product_id] ) );
			}
			if( isset( $_POST[$prefix.'pdf_template_selection'][$_product_id] ) ) {//Strip pdf template selection
				$_POST[$prefix.'pdf_template_selection'][$_product_id]	= $this->model->woo_vou_escape_slashes_deep( trim( $_POST[$prefix.'pdf_template_selection'][$_product_id] ) );
			}
			
			//recipient name field validation
			if( $recipient_data['enable_recipient_name'] == 'yes' && $recipient_data['recipient_name_is_required'] == 'yes' && empty( $_POST[$prefix.'recipient_name'][$_product_id] ) ) {
				wc_add_notice( '<p class="woo-vou-recipient-error">' . __( "Field", 'woovoucher' ).' '.$recipient_data['recipient_name_lable'].' '.__( "is required.", 'woovoucher' ) . '</p>', 'error' );
				$valid = false;
			}
			
			// Check if Email is Selected or not in Delivery Choice
			$rec_email_err_enable = apply_filters( 'woo_vou_recipient_email_error_enable', $_product_id );
			
			//recipient email field validation
			if( $recipient_data['enable_recipient_email'] == 'yes' && $recipient_data['recipient_email_is_required'] == 'yes' && empty( $_POST[$prefix.'recipient_email'][$_product_id] ) && $rec_email_err_enable ) {
				wc_add_notice( '<p class="woo-vou-recipient-error">' . __( "Field", 'woovoucher' ).' '.$recipient_data['recipient_email_label'].' '.__( "is required.", 'woovoucher' ) . '</p>', 'error' );
				$valid = false;
			}
			
			//recipient email valid email validation
			if ( !empty( $_POST[$prefix.'recipient_email'][$_product_id] ) && !is_email( $_POST[$prefix.'recipient_email'][$_product_id] ) ){
				wc_add_notice( '<p class="woo-vou-recipient-error">' . __( "Please Enter Valid", 'woovoucher' ).' '.$recipient_data['recipient_email_label'].'.</p>', 'error' );
				$valid = false;
			}
			
			//recipient message validation
			if( $recipient_data['enable_recipient_message'] == 'yes' && $recipient_data['recipient_message_is_required'] == 'yes' && empty( $_POST[$prefix.'recipient_message'][$_product_id] ) ) {
				wc_add_notice( '<p class="woo-vou-recipient-error">' . __( "Field", 'woovoucher' ).' '.$recipient_data['recipient_message_label'].' '.__( "is required.", 'woovoucher' ) . '</p>', 'error' );
				$valid = false;
			}
			
			//recipient email valid email validation
			if( $recipient_data['enable_recipient_giftdate'] == 'yes' && $recipient_data['recipient_giftdate_is_required'] == 'yes' && empty( $_POST[$prefix.'recipient_giftdate'][$_product_id] ) && $rec_email_err_enable ) {
				wc_add_notice( '<p class="woo-vou-recipient-error">' . __( "Field", 'woovoucher' ).' '.$recipient_data['recipient_giftdate_label'].' '.__( "is required.", 'woovoucher' ) . '</p>', 'error' );
				$valid = false;
			}

			//recipient gift date validation
			if ( !empty( $_POST[$prefix.'recipient_giftdate'][$_product_id] ) ){
				if( ( $_POST[$prefix.'recipient_giftdate'][$_product_id] != date( 'd-M-Y', strtotime( $_POST[$prefix.'recipient_giftdate'][$_product_id] ) ) ) ) {
					wc_add_notice( '<p class="woo-vou-recipient-error">' . __( "Please Enter Valid", 'woovoucher' ).' '.$recipient_data['recipient_giftdate_label'].'.</p>', 'error' );
					$valid = false;
				} elseif ( strtotime( $this->model->woo_vou_current_date('d-M-Y') ) > strtotime($_POST[$prefix.'recipient_giftdate'][$_product_id]) ) {
					wc_add_notice( '<p class="woo-vou-recipient-error">' . __( "Please Enter Valid", 'woovoucher' ).' '.$recipient_data['recipient_giftdate_label'].'.</p>', 'error' );
					$valid = false;
				}
			}
			
			//pdf template selection validation
			if( $recipient_data['enable_pdf_template_selection'] == 'yes' && empty( $_POST[$prefix.'pdf_template_selection'][$_product_id] ) ) {
				wc_add_notice( '<p class="woo-vou-recipient-error">' . __( "Field", 'woovoucher' ).' '.$recipient_data['pdf_template_selection_label'].' '.__( "is required.", 'woovoucher' ) . '</p>', 'error' );
				$valid = false;
			}
		}
		
		return $valid;
	}
	
	/**
	 * This is used to send an email after order completed to recipient user
	 * 
	 * Handles to send an email after order completed
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.0
	 */
	public function woo_vou_payment_process_or_complete( $order_id ){

		global $wpdb;

		//Get prefix
		$prefix	= WOO_VOU_META_PREFIX;

		//Get order
		$cart_details	= new Wc_Order( $order_id );

		if ( $cart_details->status == 'processing' && get_option( 'woocommerce_downloads_grant_access_after_payment' ) == 'no' ) {
			return;
		}

		// record the fact that the vouchers have been sent
		if( get_post_meta( $order_id, $prefix . 'recipient_email_sent', true ) ) {
			return;
		}
		
		$recipient_gift_email_send = true;
		
		$order_items	= $cart_details->get_items();
		$first_name		= isset( $cart_details->billing_first_name ) ? $cart_details->billing_first_name : '';
		$last_name		= isset( $cart_details->billing_last_name ) ? $cart_details->billing_last_name : '';

		if( !empty( $order_items ) ) {//if item is empty

			foreach ( $order_items as $product_item_key => $product_data ) {				
				
				$recipient_gift_email_send = true;
				
				//Get product from Item ( It is required otherwise multipdf voucher link not work and global $woo_vou_item_id will not work )
				$_product	= apply_filters( 'woocommerce_order_item_product', $cart_details->get_product_from_item( $product_data ), $product_data );
				
				$download_file_data	= $cart_details->get_item_downloads( $product_data );
				$product_id			= isset( $product_data['product_id'] ) ? $product_data['product_id'] : '';
				$variation_id		= isset( $product_data['variation_id'] ) ? $product_data['variation_id'] : '';

				//vendor sale notification
				$this->model->woo_vou_vendor_sale_notification( $product_id, $variation_id, $product_item_key, $product_data, $order_id, $cart_details );

				//Initilize recipient detail
				$recipient_details	= array();

				//Get product item meta
				$product_item_meta	= isset( $product_data['item_meta'] ) ? $product_data['item_meta'] : array();

				$recipient_details	= $this->model->woo_vou_get_recipient_data( $product_item_meta );

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

					$attachments		= array();
					
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
								}
							}
						}
					}
					
					// Get Recipient gift date
					$recipient_giftdate = apply_filters( 'woo_vou_replace_giftdate', $recipient_details['recipient_giftdate'], $order_id, $product_item_key );
										
					// check if gift date is set. If yes, then no need to send email right now.
					// Will send email on selected gift date
					if ( !empty( $recipient_giftdate ) ) {
						
						$recipient_gift_email_send = false;
						continue;
					} else {												
						
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
				}
			} //end foreach

			// Add action after gift email is sent
			do_action( 'woo_vou_after_gift_email', $order_id );
		}
		
		if( $recipient_gift_email_send ) {
			//Update post meta for email attachment issue
			update_post_meta( $order_id, $prefix . 'recipient_email_sent', true );
		}
	}

	/**
	 * Hide Recipient Itemmeta
	 * 
	 * Handle to hide recipient itemmeta
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.0
	 */
	public function woo_vou_hide_recipient_itemmeta( $item_meta = array() ) {

		$prefix	= WOO_VOU_META_PREFIX;

		$item_meta[]	= $prefix.'recipient_name';
		$item_meta[]	= $prefix.'recipient_email';
		$item_meta[]	= $prefix.'recipient_message';
		$item_meta[]	= $prefix.'recipient_giftdate';
		$item_meta[]	= $prefix.'recipient_gift_method';
		$item_meta[]	= $prefix.'pdf_template_selection';
		$item_meta[]	= $prefix.'codes';

		return $item_meta;
	}

	/**
	 * Handles the functionality to attach the voucher pdf in mail
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.0
	 */
	public function woo_vou_attach_voucher_to_email( $attachments, $status, $order ) {
		
		// Taking status array
		$vou_status = array( 'customer_processing_order', 'customer_completed_order', 'customer_invoice' );
		
		// Taking order status array
		$vou_order_status = array( 'wc-completed' );
		
		$order_status 				= !empty($order->post_status) ? $order->post_status : ''; // Order status
		$vou_attach_mail			= get_option( 'vou_attach_mail' ); // Getting voucher attach option
		$grant_access_after_payment	= get_option( 'woocommerce_downloads_grant_access_after_payment' ); // Woocommerce grant access after payment
		
		if( $vou_attach_mail == 'yes' && !empty($order) && ( (in_array($status, $vou_status) && in_array($order_status, $vou_order_status)) || ($status == 'customer_processing_order' && $grant_access_after_payment == 'yes' && $order_status != 'wc-on-hold') ) ) {
			
			$prefix				= WOO_VOU_META_PREFIX;
			$vou_attachments	= array();
			$order_id 			= !empty($order->id) ? $order->id : ''; // Taking order id
			$cart_details		= new Wc_Order( $order_id );
			$order_items		= $cart_details->get_items();
			
			if( !empty( $order_items ) ) {//not empty items
				
				//foreach items
				foreach ( $order_items as $item_id => $download_data ) {
					
					$product_id		= !empty($download_data['product_id']) ? $download_data['product_id'] : '';
					$variation_id	= !empty($download_data['variation_id']) ? $download_data['variation_id'] : '';
					
					//Get data id vriation id or product id
					$data_id		= !empty( $variation_id ) ? $variation_id : $product_id;
					
					//Check voucher enable or not
					$enable_voucher	= $this->model->woo_vou_check_enable_voucher( $product_id, $variation_id );
					
					if( $enable_voucher ) {
						
						// Get mutiple pdf option from order meta
						$multiple_pdf = !empty( $order_id ) ? get_post_meta( $order_id, $prefix . 'multiple_pdf', true ) : '';
						
						$orderdvoucodes = array();
						
						if( $multiple_pdf == 'yes' ) {
							$orderdvoucodes = $this->model->woo_vou_get_multi_voucher( $order_id, $data_id, $item_id );
						} else {
							$orderdvoucodes['woo_vou_pdf_1'] = '';
						}
						
						// If order voucher codes are not empty
						if( !empty($orderdvoucodes) ) {
							
							foreach ( $orderdvoucodes as $orderdvoucode_key => $orderdvoucode_val ) {
								
								if( !empty($orderdvoucode_key) ) {
									
									$attach_pdf_file_name = get_option( 'attach_pdf_name' );
									$attach_pdf_file_name = isset( $attach_pdf_file_name ) ? $attach_pdf_file_name : 'woo-voucher-';
									
									//Get Pdf Key
									$pdf_vou_key	= $orderdvoucode_key;
									
									// Replacing voucher pdf name with given value
									$orderdvoucode_key = str_replace('woo_vou_pdf_', $attach_pdf_file_name, $orderdvoucode_key);
									
									// Voucher pdf path and voucher name
									$vou_pdf_path 	= WOO_VOU_UPLOAD_DIR . $orderdvoucode_key . '-' . $data_id . '-' . $item_id . '-' . $order_id; // Voucher pdf path
									$vou_pdf_name	= $vou_pdf_path . '.pdf';
									
									// If voucher pdf does not exist in folder
									if( !file_exists($vou_pdf_name) ) {
										
										$pdf_args = array(
												'pdf_vou_key'	=> $pdf_vou_key,
												'pdf_name'		=> $vou_pdf_path,
												'save_file'		=> true
											);
										
										//Generatin pdf
										woo_vou_process_product_pdf( $data_id, $order_id, $item_id, $orderdvoucodes, $pdf_args );
									}
									
									// If voucher pdf exist in folder
									if( file_exists($vou_pdf_name) ) {
										$attachments[] = apply_filters( 'woo_vou_email_attachments', $vou_pdf_name, $order_id, $download_data ); // Adding the voucher pdf in attachment array
									}
								}
							}
						} // End of orderdvoucodes
					}
				}
			} // End of order item
		}
		
		return $attachments;
	}
	
	/**
	 * Update Cart for unique item
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.0.0
	 */
	public function woo_vou_add_to_cart_data( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		
		global $woocommerce;
		
		//Get voucher enable or not
		$enable_voucher		= $this->model->woo_vou_check_enable_voucher( $product_id, $variation_id );
		
		//enable voucher recipient
		$enable_recipient	= $this->model->woo_vou_check_enable_recipient( $product_id );
		
		if( $enable_voucher && $enable_recipient ) {//If enable voucher
			
			//exist item key
			$exist_item_key		= '';
			
			//get cart object
			$cart_object		= isset( $woocommerce->cart ) ? $woocommerce->cart : array();
			$cart_contents		= isset( $cart_object->cart_contents ) ? $cart_object->cart_contents : array();
			
			//current Item ID
			$current_item_id	= !empty( $variation_id ) ? $variation_id : $product_id;
			
			$sold_individually	= get_post_meta( $product_id, '_sold_individually', true );
			
			if( $sold_individually != 'yes' ) {// Sold Individually
			
				if( !empty( $cart_contents ) ) {//if empty cart content
					
					foreach ( $cart_contents as $item_key => $cart_content ) {
						
						$exist_item_id	= !empty( $cart_content['variation_id'] ) ? $cart_content['variation_id'] : $cart_content['product_id'];
						
						if( ( $cart_item_key != $item_key ) && ( $current_item_id == $exist_item_id ) ) {
							
							//Assign existing item key
							$exist_item_key	= $item_key;
							break;
						}
					}
				}
				
				//If product already add into cart
				if( !empty( $exist_item_key ) && !empty( $cart_contents[$exist_item_key] ) ) {
					
					//existing item data
					$exist_item_data	= $cart_contents[$exist_item_key];
					
					//get quantity
					$exist_quantity		= $exist_item_data['quantity'];
					
					//new quantity
					$new_quantity		= $quantity + $exist_quantity;
					
					//delete exist item
					$cart_object->set_quantity( $exist_item_key, 0 );
					
					//add new item with quantity
					$cart_object->set_quantity( $cart_item_key, $new_quantity );
				}
			}
		}
	}
	
	/**
	 * Check voucher code using qrcode and barcode
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.0.3
	 */
	public function woo_vou_check_qrcode() {
		
		if( isset( $_GET['woo_vou_code'] ) && !empty( $_GET['woo_vou_code'] ) ) {
			
			// Add action to add check voucher code from
			do_action( 'woo_vou_check_qrcode_content' );
		}
	}
	
	/**
	 * Add Voucher When Add Order Manually
	 * 
	 * Haldle to add voucher codes
	 * when add order manually from backend
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.2.1
	 */
	public function woo_vou_process_shop_order_manually( $order_id ) {
		
		if ( !empty( $_POST['order_item_id'] ) ) {//If order item are not empty
			
			//Process voucher code functionality
			$this->woo_vou_product_purchase( $order_id );
		}
	}
	
	/**
	 * Hide recipient variation from product name field
	 * 
	 * Handle to hide recipient variation from product name field
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.0
	 */
	public function woo_vou_hide_recipients_item_variations( $product_variations = array(), $product_item_meta = array() ) {
		
		$prefix	= WOO_VOU_META_PREFIX;
		
		$recipient_string	= '';
		
		//Get product ID
		$product_id					= isset( $product_item_meta['_product_id'] ) ? $product_item_meta['_product_id'] : '';
		
		//Get product recipient lables
		$product_recipient_lables	= $this->model->woo_vou_get_product_recipient_meta( $product_id );
		
		if( isset( $product_item_meta[$prefix.'recipient_name'] ) && !empty( $product_item_meta[$prefix.'recipient_name'][0] ) ) {
			if( is_serialized( $product_item_meta[$prefix.'recipient_name'][0] ) ) { // New recipient name field
				
				$recipient_name_fields	= maybe_unserialize( $product_item_meta[$prefix.'recipient_name'][0] );
				$recipient_name_lable	= isset( $recipient_name_fields['label'] ) ? $recipient_name_fields['label'] : $product_recipient_lables['recipient_name_lable'];
				
				if( isset( $product_variations[$recipient_name_lable] ) ) {
					unset( $product_variations[$recipient_name_lable] );
				}
			}
		}
		
		if( isset( $product_item_meta[$prefix.'recipient_email'] ) && !empty( $product_item_meta[$prefix.'recipient_email'][0] ) ) {
			if( is_serialized( $product_item_meta[$prefix.'recipient_email'][0] ) ) { // New recipient email field
				
				$recipient_email_fields	= maybe_unserialize( $product_item_meta[$prefix.'recipient_email'][0] );
				$recipient_email_lable	= isset( $recipient_email_fields['label'] ) ? $recipient_email_fields['label'] : $product_recipient_lables['recipient_email_label'];
				
				if( isset( $product_variations[$recipient_email_lable] ) ) {
					unset( $product_variations[$recipient_email_lable] );
				}
			}
		}
		
		if( isset( $product_item_meta[$prefix.'recipient_message'] ) && !empty( $product_item_meta[$prefix.'recipient_message'][0] ) ) {
			if( is_serialized( $product_item_meta[$prefix.'recipient_message'][0] ) ) { // New recipient message field
				
				$recipient_msg_fields	= maybe_unserialize( $product_item_meta[$prefix.'recipient_message'][0] );
				$recipient_msg_lable	= isset( $recipient_msg_fields['label'] ) ? $recipient_msg_fields['label'] : $product_recipient_lables['recipient_message_label'];
				
				if( isset( $product_variations[$recipient_msg_lable] ) ) {
					unset( $product_variations[$recipient_msg_lable] );
				}
			}
		}
		
		if( isset( $product_item_meta[$prefix.'pdf_template_selection'] ) && !empty( $product_item_meta[$prefix.'pdf_template_selection'][0] ) ) {
			if( is_serialized( $product_item_meta[$prefix.'pdf_template_selection'][0] ) ) { // New recipient message field
				
				$pdf_temp_selection_fields	= maybe_unserialize( $product_item_meta[$prefix.'pdf_template_selection'][0] );
				$pdf_temp_selection_lable	= isset( $pdf_temp_selection_fields['label'] ) ? $pdf_temp_selection_fields['label'] : $product_recipient_lables['pdf_template_selection_label'];
				
				if( isset( $product_variations[$pdf_temp_selection_lable] ) ) {
					unset( $product_variations[$pdf_temp_selection_lable] );
				}
			}
		}
		
		return $product_variations;
	}
	
	/**
	 * Set Global Item ID For Voucher Key Generater
	 * 
	 * Handle to Set global item id for voucher key generater
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.0
	 */
	public function woo_vou_set_global_item_id( $product, $item, $order ) {
		
		global $woo_vou_item_id;
		
		//Get prefix
		$prefix	= WOO_VOU_META_PREFIX;
		
		$product_item_meta = isset( $item['item_meta'] ) ? $item['item_meta'] : array();
		
		//Get voucher codes
		$voucher_codes	= isset( $product_item_meta[$prefix.'codes'][0] ) ? $product_item_meta[$prefix.'codes'][0] : '';
		
		if( !empty( $voucher_codes ) ) {
			
			//Get order items
			$order_items = $order->get_items();
			
			if ( !empty( $order_items ) ) { // If order not empty
				
				// Check cart details
				foreach ( $order_items as $item_id => $item ) {
					
					//Get voucher codes
					$codes	= wc_get_order_item_meta( $item_id, $prefix.'codes' );
					
					if( $codes == $voucher_codes ) {//If voucher code matches
						$woo_vou_item_id = $item_id;
						break;
					}
				}
			}
		}
		
		return $product;
	}
	
	/**
	 * Add Item Id In Download URL
	 * 
	 * Handle to add item id in generate pdf download URL
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.0
	 */
	public function woo_vou_add_item_id_in_download_pdf_url( $files, $item, $abs_order ) {
		
		global $woo_vou_item_id;
		
		if( !empty( $files ) ) { //If files not empty
			
			foreach ( $files as $file_key => $file_data ) {
				
				//Check key is for pdf voucher
				$check_key	= strpos( $file_key, 'woo_vou_pdf_' );
				
				if( $check_key !== false ) {
					
					//Get download URL
					$download_url	= isset( $files[$file_key]['download_url'] ) ? $files[$file_key]['download_url'] : '';
					
					//Add item id in download URL
					$download_url	= add_query_arg( array( 'item_id' => $woo_vou_item_id ), $download_url );
					
					//Store download URL agaiin
					$files[$file_key]['download_url']	= $download_url;
					
					// add filter to remove voucher download link
					$files = apply_filters( 'woo_vou_remove_download_link', $files, $file_key, $woo_vou_item_id );
				}
			}
		}
		
		return $files;
	}
	
	/**
	 * Adding Hooks
	 * 
	 * Adding proper hoocks for the discount codes
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.1
	 */
	public function woo_vou_my_pdf_vouchers_download_link( $downloads = array() ) {
		
		//get prefix
		$prefix	= WOO_VOU_META_PREFIX;
		
		if ( is_user_logged_in() ) {//If user is logged in
			
			//Get user ID
			$user_id	= get_current_user_id();
			
			//Get User Order Arguments
			$args	= array(
							'numberposts'	=> -1,
							'meta_key'		=> '_customer_user',
							'meta_value'	=> $user_id,
							'post_type'		=> WOO_VOU_MAIN_SHOP_POST_TYPE,
							'post_status'	=> array( 'wc-completed' ),
							'meta_query'	=> array( 
												array(
													'key'		=> $prefix . 'meta_order_details',
													'compare'	=> 'EXISTS',
												)
											)
						);
			
			//user orders
			$user_orders	= get_posts( $args );
			
			if( !empty( $user_orders ) ) {//If orders are not empty
				
				foreach ( $user_orders as $user_order ) {
					
					//Get order ID
					$order_id	= isset( $user_order->ID ) ? $user_order->ID : '';
					
					if( !empty( $order_id ) ) {//Order it not empty
						
						global $vou_order;
						
						//Set global order ID
						$vou_order = $order_id;
						
						//Get cart details
						$cart_details 	= new Wc_Order( $order_id );
						$order_items	= $cart_details->get_items();
						
						$order_date	= isset( $cart_details->order_date ) ? $cart_details->order_date : '';
						$order_date	= date( 'F j, Y', strtotime( $order_date ) );
						
						if( !empty( $order_items ) ) {// Check cart details are not empty
							
							foreach ( $order_items as $item_id => $product_data ) {
								
								//Get product from Item ( It is required otherwise multipdf voucher link not work and global $woo_vou_item_id will not work )
								$_product	= apply_filters( 'woocommerce_order_item_product', $cart_details->get_product_from_item( $product_data ), $product_data );
								
								if( !$_product ) {//If product deleted
									$download_file_data = array();
								} else {
									//Get download files
									$download_file_data	= $cart_details->get_item_downloads( $product_data );
								}
								
								//Get voucher codes
								$codes	= wc_get_order_item_meta( $item_id, $prefix.'codes' );
								
								if( !empty( $download_file_data ) && !empty( $codes ) ) {//If download exist and code is not empty
									
									foreach ( $download_file_data as $key => $download_file ) {
										
										//check download key is voucher key or not
										$check_key = strpos( $key, 'woo_vou_pdf_' );
										
										//get voucher number
										$voucher_number	= str_replace( 'woo_vou_pdf_', '', $key );
										
										if( empty( $voucher_number ) ) {//If empty voucher number
											
											$voucher_number	= 1;
										}
										
										if( !empty( $download_file ) && $check_key !== false ) {
											
											//Get download URL
											$download_url	= $download_file['download_url'];
											
											//add arguments array
											$add_arguments	= array( 'item_id' => $item_id );
											
											//PDF Download URL
											$download_url	= add_query_arg( $add_arguments, $download_url );

											//Get product ID
											$product_id	= isset( $_product->post->ID ) ? $_product->post->ID : '';

											//get product name
											$product_name	= isset( $_product->post->post_title ) ? $_product->post->post_title : '';
											
											//Download file arguments
											$download_args	= array(
																	'product_id'			=> $product_id,
																	'download_url'			=> $download_url,
																	'download_name'			=> $product_name . ' - ' . $download_file['name'] . ' ' . $voucher_number . ' ( ' . $order_date . ' )',
																	'downloads_remaining'	=> ''
																);
											
											//append voucher download to downloads array
											$downloads[]	= $download_args;
										}
									}
								}
							}
						}
						
						//reset global order ID
						$vou_order	= 0;
					}
				}
			}
		}
		
		return $downloads;
	}	
	
	/**
	 * Restore Voucher When Resume Order
	 * 
	 * Handle to restore old deduct voucher
	 * when item overwite in meta field
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.4.0
	 */
	public function woo_vou_resume_order_voucher_codes( $order_id ) {
		
		$this->model->woo_vou_restore_order_voucher_codes( $order_id );
	}
	
	/**
	 * Update product stock as per voucher codes when woocommerce deduct stock
	 * 
	 * As woocommrece reduce stock quantity on product purchase and so we have to update stock
	 * to no of voucher codes
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.4.0
	 */
	public function woo_vou_update_order_stock( $order ) {
		
		$prefix			= WOO_VOU_META_PREFIX;
			
		// loop for each item
		foreach ( $order->get_items() as $item ) {
			
			if ( $item['product_id'] > 0 ) {
				
				//Get product from Item ( It is required otherwise multipdf voucher link not work and global $woo_vou_item_id will not work )
				$_product = $order->get_product_from_item( $item );
				
				if ( $_product && $_product->exists() && $_product->managing_stock() ) {
					
					$product_id = $item['product_id'];
					$variation_id =  isset( $item['variation_id'] ) ? $item['variation_id'] : '';
					
					// check voucher is enabled for this product
					if( $this->model->woo_vou_check_enable_voucher( $product_id, $variation_id ) ) {
						
						//vendor user
						$vendor_user	= get_post_meta( $product_id, $prefix.'vendor_user', true );
		
						//get vendor detail
						$vendor_detail	= $this->model->woo_vou_get_vendor_detail( $product_id , $vendor_user );
		
						//using type of voucher
						$using_type		= isset( $vendor_detail['using_type'] ) ? $vendor_detail['using_type'] : '';
						
						// if using type is one time only
						if( empty( $using_type ) ) {
							
							//voucher codes
							$vou_codes	= $this->model->woo_vou_get_voucher_code( $product_id , $variation_id );
							
							// convert voucher code comma seperate string into array
							$vou_codes = !empty( $vou_codes ) ? explode( ',', $vou_codes ) : array();
							
							// update stock quanity
							$this->model->woo_vou_update_product_stock( $product_id, $variation_id, $vou_codes );
						}
					}
				}
			}
		}
	}
	
	/**
	 * expired/upcoming product on shop page
	 * 
	 * Handles to Remove add to cart product button on shop page when product is upcomming or expired
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.4.2
	 */
	public function woo_vou_shop_add_to_cart( $add_to_cart_html ) {
		
		global $product;					
		
		$expired = $this->model->woo_vou_check_product_is_expired( $product );
		
		if ( $expired == 'upcoming' || $expired == 'expired' ) {
	    	return ''; // do not display add to cart button
	    }
				
		return $add_to_cart_html;
	}
	
	/**
	 * Prevent product from being added to cart (free or priced) with ?add-to-cart=XXX
	 * When product expired or upcoming
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.4.0
	 */
	public function woo_vou_prevent_product_add_to_cart( $passed, $product_id ) {
		
		// Get complete product details from product id
		$product = wc_get_product( $product_id );
		
		$expired = $this->model->woo_vou_check_product_is_expired( $product );
		
		if ( $expired == 'upcoming' ) {
			wc_add_notice( __( 'You can not add upcoming products to cart.', 'woovoucher' ), 'error' );
			$passed = false;
		} elseif ( $expired == 'expired' ) {
			wc_add_notice( __( 'You can not add expired products to cart.', 'woovoucher' ), 'error' );
			$passed = false;
		}
		
		return $passed;
	}
	
	/**
	 * Valiate product added in cart is expired/upcoming
	 * 
	 * Handles to display error if proudct added in cart is expired/upcoming
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.4.0
	 */
	public function woo_vou_woocommerce_checkout_process() {
		
		// get added products in cart
		$cart_details = WC()->session->cart;
		if( !empty( $cart_details ) ) { // if cart is not empty
			
			foreach ( $cart_details as $key => $product_data ) {
				
				// get product id
				$product_id = $product_data['product_id'];
				
				// Get complete product details from product id
				$product = wc_get_product( $product_id );
				
				// check product is expired/upcoming
				$expired = $this->model->woo_vou_check_product_is_expired( $product );
				if ( $expired == 'upcoming' ) {
					wc_add_notice( sprintf( __( '%s is no longer available.', 'woovoucher' ), $product->post->post_title ), 'error' );
					return;
				} elseif ( $expired == 'expired' ) {
					wc_add_notice( sprintf( __( '%s is no longer available.', 'woovoucher' ), $product->post->post_title ), 'error' );
					return;
				}
			}
		}		
	}
	
	/**
	 * Remove voucher download link
	 * 
	 * Hanles to remove voucher download link if voucher is
	 * "used" 		- voucher code is redeemed
	 * "exipred" 	- voucher date is expired and its not used
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.6.4
	 */
	public function woo_vou_remove_voucher_download_link( $files, $file_key, $woo_vou_item_id ) {
		
		//get prefix
		$prefix	= WOO_VOU_META_PREFIX;
		
		$multiple_pdf	= get_option( 'multiple_pdf' );
		$revoke_voucher_download_link_access = get_option('revoke_voucher_download_link_access');
		
		// check multiple voucher and remove download voucher link is enabled
		if( $multiple_pdf == "yes" && $revoke_voucher_download_link_access == "yes" ) {
			
			// Get voucher codes
			$codes	= wc_get_order_item_meta( $woo_vou_item_id, $prefix . 'codes' );
			
			// get voucher code status
			$voucher_code_status = $this->model->woo_vou_get_voucher_code_status( $codes );
			if( $voucher_code_status === 'expired' || $voucher_code_status === 'used' ) {
				unset( $files[$file_key] );	// remove voucher download link
			}
		}
					
		return $files;
	}
	
	/**
	 * Allow To add Admin email in BCC
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.6.8
	 */
	public function woo_vou_allow_admin_to_bcc( $headers, $object ) {
		
		$admin_email		= get_option( 'admin_email' );
		$admin_premission	= get_option( 'vou_allow_bcc_to_admin' );
		
		if( $admin_premission == "yes" && !empty( $admin_email ) ) {
			
			switch( $object ) {
				case 'customer_processing_order':
				case 'customer_completed_order':
				case 'woo_vou_gift_notification':				
					$headers .= 'Bcc: ' . $admin_email . "\r\n";
					break;
				default:
			}
		}
		
		return apply_filters( 'woo_vou_allow_admin_to_bcc', $headers );
	}
	
	/**
	 * AJAX call 
	 * 
	 * Handles to show details of with ajax
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.8.1
	 */
	public function woo_vou_used_voucher_codes_ajax() {

		if ( is_user_logged_in() ) {
			ob_start();
			//do action to load used voucher codes html via ajax
			do_action( 'woo_vou_used_voucher_codes' );
			echo ob_get_clean();
			exit;
		} else {
			return __( 'You have no Used Voucher Codes yet.', 'woocommerce' );
		}
	}
	
	/**
	 * Validate Coupon
	 * 
	 * Handles to validate coupon on Cart and Checkout page
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.9.2
	 */
	public function woo_vou_validate_coupon ( $valid, $coupon ) {
		
		// Get prefix
		$prefix	= WOO_VOU_META_PREFIX;
		
		// Get coupon_id
		$coupon_id = $coupon->id;

		// Get Coupon's start date
		$coupon_start_date = get_post_meta( $coupon_id, $prefix . 'start_date', true );

		// Get coupon's restriction days
		$coupon_rest_days  = get_post_meta( $coupon_id, $prefix . 'product_rest_days', true );

		// Check start date validation
		if ( $coupon_start_date && current_time( 'timestamp' ) < strtotime( $coupon_start_date ) ) {

			throw new Exception( $error_code = $prefix . 'start_date_err' ); // throw error
			return false; // return false
		}

		// Check coupon restriction days
		if( !empty( $coupon_rest_days ) ) {

			// Get current day
			$current_day = strtolower( date('l') );

			// check current day redeem is enable or not
			if( in_array( $current_day, $coupon_rest_days ) ) {

				throw new Exception( $error_code = $prefix . 'day_err' ); // Throw error
				return false; // Return false
			}
		}
		
		// Return
		return $valid;
	}
	
	/**
	 * Error message
	 * 
	 * Handles to throw custom error message
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.9.2
	 */
	public function woo_vou_coupon_err_message ( $err, $err_code, $coupon ) {
		
		//get prefix
		$prefix	= WOO_VOU_META_PREFIX;

		// Get coupon id
		$coupon_id = $coupon->id;

		// Get coupon start date
		$coupon_start_date = get_post_meta( $coupon_id, $prefix . 'start_date', true );

		// Get coupon restriction days
		$coupon_rest_days  = get_post_meta( $coupon_id, $prefix . 'product_rest_days', true );

		// Check error code for start date
		if ( $err_code == $prefix . 'start_date_err' ) {

			$err = sprintf( __( 'This Coupon Code cannot be used before %s', 'woovoucher' ), $this->model->woo_vou_get_date_format( $coupon_start_date, true ) ); // Throw error message
		}

		// Check error for restriction days
		if ( $err_code == $prefix . 'day_err' ) {

			$message = implode(", ", $coupon_rest_days ); // Get all days

			$err = sprintf( __( 'Sorry, coupon Code cannot be used on %s.', 'woovoucher' ), $message ); // Throw error message
		}
		
		// Return error
		return $err;
	}
	
	/**
	 * Adding Hooks
	 * 
	 * Adding proper hoocks for the discount codes
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function add_hooks() {
		
		//add capabilities to user roles
		add_action( 'init', array( $this, 'woo_vou_initilize_role_capabilities' ), 100 );
		
		//add action to save voucher in order
		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'woo_vou_product_purchase' ) );

		//add action for add custom notifications
		add_filter( 'woocommerce_email_actions', array( $this, 'woo_vou_add_email_notification' ) );
	
		//add filter to merge voucher pdf with product files
		add_filter( 'woocommerce_product_files', array( $this, 'woo_vou_downloadable_files' ), 10, 2 );
		
		//insert pdf vouchers in woocommerce downloads fiels table
		add_action( 'woocommerce_grant_product_download_permissions', array( $this, 'woo_vou_insert_downloadable_files' ) );
		
		//add action to product process
		add_action( 'woocommerce_download_product', array( $this, 'woo_vou_download_process' ), 10, 6 );
		
		//add filter to add admin access for vendor role
		add_filter( 'woocommerce_prevent_admin_access', array( $this, 'woo_vou_prevent_admin_access' ) );
		
		//ajax call to edit all controls
		add_action( 'wp_ajax_woo_vou_check_voucher_code', array( $this, 'woo_vou_check_voucher_code') );
		add_action( 'wp_ajax_nopriv_woo_vou_check_voucher_code', array( $this, 'woo_vou_check_voucher_code' ) );
		
		//ajax call to save voucher code
		add_action( 'wp_ajax_woo_vou_save_voucher_code', array( $this, 'woo_vou_save_voucher_code') );
		add_action( 'wp_ajax_nopriv_woo_vou_save_voucher_code', array( $this, 'woo_vou_save_voucher_code' ) );
		
		// add action to add html for check voucher code
		add_action( 'woo_vou_check_code_content', array( $this, 'woo_vou_check_code_content' ) );
		
		// add action to set order as a global variable
		add_action( 'woocommerce_email_before_order_table', array( $this, 'woo_vou_email_before_order_table' ) );
		
		//filter to set order product data as a global variable
		add_filter( 'woocommerce_order_item_product', array( $this, 'woo_vou_order_item_product' ), 10, 2 );
		
		//restore voucher codes if order is failed or cancled
		add_action( 'woocommerce_order_status_changed', array( $this, 'woo_vou_restore_voucher_codes' ), 10, 3 );
		
		//add custom html to single product page before add to cart button
		add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'woo_vou_before_add_to_cart_button' ) );
		
		//add to cart in item data
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'woo_vou_woocommerce_add_cart_item_data' ), 10 , 3 );
		
		//check if item already added in cart
		//add_action( 'woocommerce_add_to_cart', array( $this, 'woo_vou_add_to_cart_data' ), 10, 6 );
		
		// add to cart in item data from session
		add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'woo_vou_get_cart_item_from_session'), 10, 2 );
		
		// get to cart in item data to display in cart page
		add_filter( 'woocommerce_get_item_data', array( $this, 'woo_vou_woocommerce_get_item_data' ), 10 , 2 );
		
		// add action to add cart item to the order.
		add_action( 'woocommerce_add_order_item_meta', array( $this, 'woo_vou_add_order_item_meta'), 10, 2 );
		
		//add filter to validate custom fields of product page
		add_filter( 'woocommerce_add_to_cart_validation',     array( $this, 'woo_vou_add_to_cart_validation'), 10, 6 );
		
		// add action when order status goes to complete
		add_action( 'woocommerce_order_status_completed_notification', array( $this, 'woo_vou_payment_process_or_complete'), 100 );
		add_action( 'woocommerce_order_status_pending_to_processing_notification', array( $this, 'woo_vou_payment_process_or_complete'), 100 );
		
		//add action to hide recipient in order meta
		add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'woo_vou_hide_recipient_itemmeta' ) );
		
		//filter to attach the voucher pdf in mail
		add_filter( 'woocommerce_email_attachments', array($this, 'woo_vou_attach_voucher_to_email'), 10, 3);
		
		//add action to check qrcode
		add_action( 'init', array( $this, 'woo_vou_check_qrcode' ) );
		
		//Add order manually from backend
		add_action( 'woocommerce_process_shop_order_meta', array( $this, 'woo_vou_process_shop_order_manually' ) );

		//Hide recipient variation from product name field
		add_filter( 'woo_vou_hide_recipient_variations', array( $this, 'woo_vou_hide_recipients_item_variations' ), 10, 2 );

		//Set global item id for voucher key generater
		add_filter( 'woocommerce_get_product_from_item', array( $this, 'woo_vou_set_global_item_id' ), 10, 3 );

		//Add Item ID in generated pdf download URL
		add_filter( 'woocommerce_get_item_downloads', array( $this, 'woo_vou_add_item_id_in_download_pdf_url' ), 10, 3 );

		//Add voucher download links to my account page
		add_action( 'woocommerce_customer_get_downloadable_products', array( $this, 'woo_vou_my_pdf_vouchers_download_link' ) );

		//restore old voucher code again when resume old order due to overwrite item
		add_action( 'woocommerce_resume_order', array( $this, 'woo_vou_resume_order_voucher_codes' ) );
		
		// add action to update stock as per no. of voucher codes
		add_action( 'woocommerce_reduce_order_stock', array( $this, 'woo_vou_update_order_stock' ) );
		
		// add filter to remove add to cart button on shop page for expire product
		add_action( 'woocommerce_loop_add_to_cart_link', array( $this, 'woo_vou_shop_add_to_cart' ), 10, 1 );
		
		// prevent add to cart product if some one try directly using url	
		add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'woo_vou_prevent_product_add_to_cart' ), 10, 2 );
		
		// add action on place order check product is expired/upcoming in checkout page
		add_action( 'woocommerce_checkout_process', array( $this, 'woo_vou_woocommerce_checkout_process' ), 10 );
		
		//ajax pagination for used voucher codes
		add_action( 'wp_ajax_woo_vou_used_codes_next_page', array( $this, 'woo_vou_used_voucher_codes_ajax' ) );
		add_action( 'wp_ajax_nopriv_woo_vou_used_codes_next_page', array( $this, 'woo_vou_used_voucher_codes_ajax' ) );
		
		// add filter to remove voucher download link
		add_filter( 'woo_vou_remove_download_link', array( $this, 'woo_vou_remove_voucher_download_link'), 10, 3 );
		
		// allow to add admin email in bcc
		add_filter( 'woocommerce_email_headers', array( $this, 'woo_vou_allow_admin_to_bcc' ), 10, 2 );
		
		// Add filter to validate extra fields
		add_filter( 'woocommerce_coupon_is_valid', array( $this, 'woo_vou_validate_coupon' ), 10, 2 );
		
		// Add filter to add custom coupon error message
		add_filter( 'woocommerce_coupon_error', array( $this, 'woo_vou_coupon_err_message' ), 10, 3 );
	}
}