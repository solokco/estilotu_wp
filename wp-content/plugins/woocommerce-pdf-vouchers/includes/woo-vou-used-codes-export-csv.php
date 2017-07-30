<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Export to CSV for Voucher
 * 
 * Handles to Export to CSV on run time when 
 * user will execute the url which is sent to
 * user email with purchase receipt
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */

function woo_vou_code_export_to_csv(){
	
	
	$prefix = WOO_VOU_META_PREFIX;	
	
	if( isset( $_GET['woo-vou-used-exp-csv'] ) && !empty( $_GET['woo-vou-used-exp-csv'] ) 
		&& $_GET['woo-vou-used-exp-csv'] == '1'
		&& isset($_GET['product_id']) && !empty($_GET['product_id'] ) ) {
		
		global $current_user,$woo_vou_model, $post;
		
		//model class
		$model = $woo_vou_model;
	
		$postid = $_GET['product_id']; 
		
		$exports = '';
		
		// Check action is used codes
		if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) {
		
		 	//Get Voucher Details by post id
		 	$voucodes = $model->woo_vou_get_used_codes_by_product_id( $postid );
		 	
			$vou_file_name = 'woo-used-voucher-codes-{current_date}';
			
		} else{
			
		 	//Get Voucher Details by post id
		 	$voucodes = $model->woo_vou_get_purchased_codes_by_product_id( $postid );
		 	
			$vou_csv_name = get_option( 'vou_csv_name' );
			$vou_file_name = !empty( $vou_csv_name )? $vou_csv_name : 'woo-purchased-voucher-codes-{current_date}';
		}
		$columns = array(	
							__( 'Voucher Code', 'woovoucher' ),
							__( 'Buyer\'s Name', 'woovoucher' ),
							__( 'Order Date', 'woovoucher' ),
							__( 'Order ID', 'woovoucher' ),							
					     );
					     
		if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) {
			
			$new_columns	= array( __('Redeem By', 'woovoucher' ) );
			$columns 		= array_merge ( $columns , $new_columns );
			
		}
		
		
        // Put the name of all fields
		foreach ($columns as $column) {
			
			$exports .= '"'.$column.'",';
		}
		$exports .="\n";
		
		if( !empty( $voucodes ) &&  count( $voucodes ) > 0 ) { 
												
			foreach ( $voucodes as $key => $voucodes_data ) { 
			
				//voucher order id
				$orderid 		= $voucodes_data['order_id'];
				
				//voucher order date
				$orderdate 		= $voucodes_data['order_date'];
				$orderdate 		= !empty( $orderdate ) ? $model->woo_vou_get_date_format( $orderdate, true ) : '';
				
				//buyer's name who has purchased/used voucher code				
				$buyername 		=  $voucodes_data['buyer_name'];
				
				//voucher code purchased/used
				$voucode 		= $voucodes_data['vou_codes'];
				
				//this line should be on start of loop
				$exports .= '"'.$voucode.'",';
				$exports .= '"'.$buyername.'",';
				$exports .= '"'.$orderdate.'",';
				$exports .= '"'.$orderid.'",';
				
				if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) {
					
					$user_id 	 	= $voucodes_data['redeem_by'];
					$user_detail 	= get_userdata( $user_id );
					$redeem_by 		= isset( $user_detail->display_name ) ? $user_detail->display_name : 'N/A';
					
					$exports .= '"'.$redeem_by.'",';
				}
				ob_start();
								
				$added_column = ob_get_clean();

				$exports .= $added_column;
				
				$exports .="\n";
			}
		} 
		
		$vou_file_name = str_replace( '{current_date}', date('d-m-Y'), $vou_file_name );
		
		// Output to browser with appropriate mime type, you choose ;)
		header("Content-type: text/x-csv");
		header("Content-Disposition: attachment; filename=".$vou_file_name.".csv");
		echo $exports;
		exit;
		
	}
	
	// generate csv for voucher code
	if( isset( $_GET['woo-vou-voucher-exp-csv'] ) && !empty( $_GET['woo-vou-voucher-exp-csv'] ) 
		&& $_GET['woo-vou-voucher-exp-csv'] == '1' ) 
	{	
		global $current_user,$woo_vou_model, $post, $woo_vou_vendor_role;
		
		//model class
		$model = $woo_vou_model;
	
		$exports = '';
		
		// Check action is used codes
		if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) {
		
			$args = array();
		
			$args['meta_query'] = array(
											array(
														'key'		=> $prefix.'used_codes',
														'value'		=> '',
														'compare'	=> '!=',
													)
										);
			//Get user role
			$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
			$user_role	= array_shift( $user_roles );
			
			//voucher admin roles
			$admin_roles	= woo_vou_assigned_admin_roles();

			if( !in_array( $user_role, $admin_roles ) ) {// voucher admin can redeem all codes
				$args['author'] = $current_user->ID;
			}

			if( isset( $_GET['woo_vou_post_id'] ) && !empty( $_GET['woo_vou_post_id'] ) ) {
				$args['post_parent'] = $_GET['woo_vou_post_id'];
			}
			
			if( isset( $_GET['woo_vou_user_id'] ) && !empty( $_GET['woo_vou_user_id'] ) ) {
				
				$args['meta_query'] =	array(
								'relation' => 'AND',
								($args['meta_query']),
								array(
									array(
											'key'		=> $prefix.'redeem_by',
											'value'		=> $_GET['woo_vou_user_id'],
											'compare'	=> '=',
										)
								)
							);
			}
			
			if( isset( $_GET['woo_vou_start_date'] ) && !empty( $_GET['woo_vou_start_date'] ) ) {
				
				$args['meta_query'] =	array(
								'relation' => 'AND',
								($args['meta_query']),
								array(
									array(
											'key'		=> $prefix.'used_code_date',
											'value'		=> date( "Y-m-d H:i:s", strtotime( $_GET['woo_vou_start_date'] ) ),
											'compare'	=> '>=',
										)
								)
							);
			}
			
			if( isset( $_GET['woo_vou_end_date'] ) && !empty( $_GET['woo_vou_end_date'] ) ) {
				
				$args['meta_query'] =	array(
								'relation' => 'AND',
								($args['meta_query']),
								array(
									array(
											'key'		=> $prefix.'used_code_date',
											'value'		=> date( "Y-m-d H:i:s", strtotime( $_GET['woo_vou_end_date'] ) ),
											'compare'	=> '<=',
										)
								)
							);
			}
			
			if( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
				
				//$args['s'] = $_GET['s'];
				$args['meta_query'] = array(
											'relation' => 'AND',
											($args['meta_query']),
											array(
												'relation'	=> 'OR',
												array(
															'key'		=> $prefix.'used_codes',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'first_name',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'last_name',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'order_id',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'order_date',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
											)
										);
			}
			
		 	//Get Voucher Details by post id
		 	$voucodes = $model->woo_vou_get_voucher_details( $args );
		 	
		 	$vou_file_name = 'woo-used-voucher-codes-{current_date}';
			
		} 
		// Check action is partially used codes
		else if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'partially' ) {
		
			$args = array();
		
			$args['meta_query'] = array(
									array(
										'key'		=> $prefix.'redeem_by',
										'value'		=> '',
										'compare'	=> '!=',
										)
									);
			//Get user role
			$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
			$user_role	= array_shift( $user_roles );
			
			//voucher admin roles
			$admin_roles	= woo_vou_assigned_admin_roles();

			if( !in_array( $user_role, $admin_roles ) ) {// voucher admin can redeem all codes
				$args['author'] = $current_user->ID;
			}

			if( isset( $_GET['woo_vou_post_id'] ) && !empty( $_GET['woo_vou_post_id'] ) ) {
							
				$args['meta_query'] = array(
								array(
										'key'		=> $prefix.'product_id',
										'value'		=> $_GET['woo_vou_post_id'],										
									)
								);	
			}
			
			if( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
				
				//$args['s'] = $_GET['s'];
				$args['meta_query'] = array(
											'relation' => 'AND',
											($args['meta_query']),
											array(
												'relation'	=> 'OR',
												array(
													'key'		=> $prefix.'purchased_codes',
													'value'		=> $_GET['s'],
													'compare'	=> 'LIKE',
													)
												)
											);
			}
			
		 	//Get partially redeem voucher codes details
		 	$voucodes = $model->woo_vou_get_partially_redeem_details( $args );
		 	
		 	$vou_file_name = 'woo-partially-used-voucher-codes-{current_date}';
			
		} else {
			
		 	$args = array();	 			 	
		 	
		 	if( isset( $_GET['vou-data'] ) && $_GET['vou-data'] == 'expired') {
		 		
	 			$args['meta_query'] = array(
										array(
												'key' 		=> $prefix . 'purchased_codes',
												'value'		=> '',
												'compare' 	=> '!='
											),
										array(
													'key'     	=> $prefix . 'used_codes',
													'compare' 	=> 'NOT EXISTS'
											 ),
										array(
												'key' =>  $prefix .'exp_date',
												'compare' => '<=',
	                  							'type'    => 'DATE',
	                  							'value' => $model->woo_vou_current_date()
											)										    
									);
									
				$vou_file_name = 'woo-expired-voucher-codes-{current_date}';
		 		
		 	} else {
		 		
				$args['meta_query'] = array(
											array(
													'key' 		=> $prefix . 'purchased_codes',
													'value'		=> '',
													'compare' 	=> '!='
												),
											array(
														'key'     	=> $prefix . 'used_codes',
														'compare' 	=> 'NOT EXISTS'
												 ),
											array(
												'relation' => 'OR', // Optional, defaults to "AND"
												array(
													'key'     => $prefix .'exp_date',
													'value'   => '',
													'compare' => '='
												),
												array(
													'key' =>  $prefix .'exp_date',
													'compare' => '>=',
		                  							'type'    => 'DATE',
		                  							'value' => $model->woo_vou_current_date()
												)
										   )	 
										);
										
				$vou_csv_name  = get_option( 'vou_csv_name' );
				$vou_file_name = !empty( $vou_csv_name )? $vou_csv_name : 'woo-purchased-voucher-codes-{current_date}';
		 	}
			//Get user role
			$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
			$user_role	= array_shift( $user_roles );

			//voucher admin roles
			$admin_roles	= woo_vou_assigned_admin_roles();
	
			if( !in_array( $user_role, $admin_roles ) ) {// voucher admin can redeem all codes
				$args['author'] = $current_user->ID;
			}

			if( isset( $_GET['woo_vou_post_id'] ) && !empty( $_GET['woo_vou_post_id'] ) ) {
				$args['post_parent'] = $_GET['woo_vou_post_id'];
			}
			
			if( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {
				
				//$args['s'] = $_GET['s'];
				$args['meta_query'] = array(
												'relation'	=> 'OR',
												array(
															'key'		=> $prefix.'purchased_codes',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'first_name',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'last_name',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'order_id',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
												array(
															'key'		=> $prefix.'order_date',
															'value'		=> $_GET['s'],
															'compare'	=> 'LIKE',
														),
											);
			}
			
		 	//Get Voucher Details by post id
		 	$voucodes = $model->woo_vou_get_voucher_details( $args );
		 	
		}
		$columns = array(	
							__( 'Voucher Code', 'woovoucher' ),
							__( 'Product Information', 'woovoucher' ),
							__( 'Buyer\'s Information', 'woovoucher' ),
							__( 'Order Information', 'woovoucher' ),							
					     );
					     
		if( isset( $_GET['woo_vou_action'] ) && ( $_GET['woo_vou_action'] == 'used' || $_GET['woo_vou_action'] == 'partially' ) ) {
			
			$new_columns	= array( __('Redeem Information', 'woovoucher' ) );
			$columns 		= array_merge ( $columns , $new_columns );
			
		}	
		
		$csv_type	= isset( $_GET['woo_vou_action'] ) ? $_GET['woo_vou_action'] : 'purchased';		
		
		$columns	= apply_filters( 'woo_vou_generate_csv_columns', $columns, $csv_type );
		
        // Put the name of all fields
		foreach ($columns as $column) {
			
			$exports .= '"'.$column.'",';
		}
		$exports .="\n";
		
		if( !empty( $voucodes ) &&  count( $voucodes ) > 0 ) { 
												
			foreach ( $voucodes as $key => $voucodes_data ) { 
				
				if(isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'partially') { 
					//voucher order id
					$orderid 		= get_post_meta( $voucodes[$key]['post_parent'], $prefix.'order_id', true );
					
					//voucher order date
					$orderdate 		= get_post_meta( $voucodes[$key]['post_parent'], $prefix.'order_date', true );
					$orderdate 		= !empty( $orderdate ) ? $model->woo_vou_get_date_format( $orderdate, true ) : '';
					
					//voucher code purchased/used
					$voucode 		= get_post_meta( $voucodes[$key]['post_parent'], $prefix.'purchased_codes', true );
					
					// Get user details
					$user_id 	 	= get_post_meta( $voucodes_data['ID'], $prefix.'redeem_by', true );
					$user_detail 	= get_userdata( $user_id );
					$redeem_by 		= isset( $user_detail->display_name ) ? $user_detail->display_name : 'N/A';
					
					// Get redeem Date
					$redeem_date	= get_post_meta( $voucodes_data['ID'], $prefix.'used_code_date', true );
					$redeem_date 	= !empty( $redeem_date ) ? $model->woo_vou_get_date_format( $redeem_date, true ) : '';
					
					// Get Redeem by information 
					$redeeminfo 	=	$model->woo_vou_display_redeem_info_html( $voucodes_data['ID'], $orderid ,'csv', 'partially' ); 
					$redeeminfo = strip_tags( $redeeminfo);	
					// Get buyer information 
					$buyer_details		 = $model->woo_vou_get_buyer_information( $orderid );
					$buyer_details_html  = 'Name: '.$buyer_details['first_name'].' '.$buyer_details['last_name']."\n";
					$buyer_details_html .= 'Email: '.$buyer_details['email']."\n";
					$buyer_details_html .= 'Address: '.$buyer_details['address_1'].' '.$buyer_details['address_2']."\n";
					$buyer_details_html .= $buyer_details['city'].' '.$buyer_details['state'].' '.$buyer_details['country'].' - '.$buyer_details['postcode']."\n";
					$buyer_details_html .= 'Phone: '.$buyer_details['phone'];
				
					$buyerinfo = $buyer_details_html;			
				
				} else {
					//voucher order id
					$orderid 		= get_post_meta( $voucodes_data['ID'], $prefix.'order_id', true );
					
					//voucher order date
					$orderdate 		= get_post_meta( $voucodes_data['ID'], $prefix.'order_date', true );
					$orderdate 		= !empty( $orderdate ) ? $model->woo_vou_get_date_format( $orderdate, true ) : '';
					
					//voucher code purchased/used
					$voucode 		= get_post_meta( $voucodes_data['ID'], $prefix.'purchased_codes', true );
					
					// Get user information 
					$user_id 	 	= get_post_meta( $voucodes_data['ID'], $prefix.'redeem_by', true );
					$user_detail 	= get_userdata( $user_id );
					$redeem_by 		= isset( $user_detail->display_name ) ? $user_detail->display_name : 'N/A';
					
					// Get voucher redeem date
					$redeem_date	= get_post_meta( $voucodes_data['ID'], $prefix.'used_code_date', true );
					$redeem_date 	= !empty( $redeem_date ) ? $model->woo_vou_get_date_format( $redeem_date, true ) : '';
					
					// get voucher Redeem by information 
					$redeeminfo = $model->woo_vou_display_redeem_info_html( $voucodes_data['ID'], $orderid, '' );
					$redeeminfo = strip_tags( $redeeminfo);	
					
					// Get Buyer information 
					$buyer_details		 = $model->woo_vou_get_buyer_information( $orderid );
					$buyer_details_html  = 'Name: '.$buyer_details['first_name'].' '.$buyer_details['last_name']."\n";
					$buyer_details_html .= 'Email: '.$buyer_details['email']."\n";
					$buyer_details_html .= 'Address: '.$buyer_details['address_1'].' '.$buyer_details['address_2']."\n";
					$buyer_details_html .= $buyer_details['city'].' '.$buyer_details['state'].' '.$buyer_details['country'].' - '.$buyer_details['postcode']."\n";
					$buyer_details_html .= 'Phone: '.$buyer_details['phone'];
					
					$buyerinfo = $buyer_details_html;			
				}
					
				// get order detail
				$order = new WC_Order( $orderid );
				// get Buyer id, if buyer is guest then user id will be zero
				$user_id = $order->user_id;								
				
				// Get Product information 
				$product_info = $woo_vou_model->woo_vou_display_product_info_html( $orderid, $voucode, 'csv' );
				
				// Get order information 
				$order_info = $woo_vou_model->woo_vou_display_order_info_html( $orderid, 'csv' );
				
				//this line should be on start of loop
				$exports .= '"'.$voucode.'",';
				$exports .= '"'.$product_info.'",';
				$exports .= '"'.$buyerinfo.'",';
				$exports .= '"'.$order_info.'",';
				

				if( isset( $_GET['woo_vou_action'] ) && ( $_GET['woo_vou_action'] == 'used' || $_GET['woo_vou_action'] == 'partially' ) ) {
					$exports .= '"'.$redeeminfo.'",';
				}
				$exports = apply_filters( 'woo_vou_generate_csv_add_column_after', $exports, $orderid, $voucode );
				
				$exports .="\n";
			}
		}
		
		$vou_file_name = str_replace( '{current_date}', date('d-m-Y'), $vou_file_name );

		
		// Output to browser with appropriate mime type, you choose ;)
		
		header("Content-type: text/x-csv");
		header("Content-Disposition: attachment; filename=".$vou_file_name.".csv");
		echo $exports;
		exit;
		
	}
}
add_action( 'init', 'woo_vou_code_export_to_csv' );