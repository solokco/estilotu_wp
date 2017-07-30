<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Generate PDF for Voucher
 * 
 * Handles to Generate PDF on run time when 
 * user will execute the url which is sent to
 * user email with purchase receipt
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */

function woo_vou_generate_voucher_code_pdf() {

	$prefix = WOO_VOU_META_PREFIX;

	// Getting voucher character support
	$voucher_char_support = get_option( 'vou_char_support' );

	// Taking pdf fonts	
	$pdf_font = 'helvetica'; // This is default font

	if( !empty( $voucher_char_support ) ) {	// if character support is checked
		
		$pdf_font = apply_filters( 'woo_vou_pf_tcpdf_font', '' );
		
		if( empty( $pdf_font ) ){
			$pdf_font = 'freeserif';
		}	
	}

	if( isset( $_GET['woo-vou-used-gen-pdf'] ) && !empty( $_GET['woo-vou-used-gen-pdf'] )
		&& $_GET['woo-vou-used-gen-pdf'] == '1' 
		&& isset($_GET['product_id']) && !empty($_GET['product_id']) ) {

		global $current_user,$woo_vou_model, $post;

		//Create html for PDF
		$html = '';

		//model class
		$model = $woo_vou_model;

		$postid = $_GET['product_id'];

		if( !class_exists( 'TCPDF' ) ) { //If class not exist

			//include tcpdf file
			require_once WOO_VOU_DIR . '/includes/tcpdf/tcpdf.php';
		}

		// Check action is used codes
		if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) {

		 	//Get Voucher Details by post id
		 	$voucodes = $model->woo_vou_get_used_codes_by_product_id( $postid );

		 	$voucher_heading 	= __( 'Used Voucher Codes','woovoucher' );
		 	$voucher_empty_msg	= __( 'No voucher codes used yet.', 'woovoucher' );

			$vou_file_name = 'woo-used-voucher-codes-{current_date}';
		} else {

		 	//Get Voucher Details by post id
		 	$voucodes = $model->woo_vou_get_purchased_codes_by_product_id( $postid );

		 	$voucher_heading 	= __( 'Purchased Voucher Codes','woovoucher' );
		 	$voucher_empty_msg	= __( 'No voucher codes purchased yet.', 'woovoucher' );

			$vou_pdf_name = get_option( 'vou_pdf_name' );
			$vou_file_name = !empty( $vou_pdf_name )? $vou_pdf_name : 'woo-purchased-voucher-codes-{current_date}';
		}

		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// remove default header
		$pdf->setPrintHeader(false);

		// remove default footer
		$pdf->setPrintFooter(false);

		$pdf->AddPage( 'L', 'A4' );

		// Auther name and Creater name
		$pdf->SetTitle( utf8_decode(__('WooCommerce Voucher','woovoucher')) );
		$pdf->SetAuthor( utf8_decode( __('WooCommerce','woovoucher') ) );
		$pdf->SetCreator( utf8_decode( __('WooCommerce','woovoucher') ) );

		// Set margine of pdf (float left, float top , float right)
		$pdf->SetMargins( 8, 8, 8 );
		$pdf->SetX( 8 );

		// Font size set
		$pdf->SetFont( $pdf_font, '', 18 );
		$pdf->SetTextColor( 50, 50, 50 );

		$pdf->Cell( 270, 5, utf8_decode( $voucher_heading ), 0, 2, 'C', false );
		$pdf->Ln(5);
		$pdf->SetFont( $pdf_font, '', 12 );
		$pdf->SetFillColor( 238, 238, 238 );

		//voucher logo
		if( !empty( $voulogo ) ) {
			$pdf->Image( $voulogo, 95, 25, 20, 20 );
			$pdf->Ln(35);
		}

		$columns = array(
							array('name' => __( 'Voucher Code', 'woovoucher'), 'width' => 70),
							array('name' => __( 'Buyer\'s Name', 'woovoucher'), 'width' => 70),
							array('name' => __( 'Order Date', 'woovoucher'), 'width' => 50)
						);

		if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) { // if generate pdf for used code add and extra column

			$new_columns[]	= array('name' => __('Order ID', 'woovoucher'), 'width' => 35);
			$new_columns[]	= array('name' => __('Redeem By', 'woovoucher'), 'width' => 50);
			$columns 		= array_merge ( $columns , $new_columns );	
		} else {
			$new_columns[]	= array('name' => __('Order ID', 'woovoucher'), 'width' => 70);
			$columns 		= array_merge ( $columns , $new_columns );
		}

		$html .= '<table style="line-height:1.5;" border="1"><thead><tr style="line-height:2;font-weight:bold;background-color:#EEEEEE;">';

		// Table head Code
		foreach ($columns as $column) {

			$html .= '<th>'.$column['name'].'</th>';
		}

		$html .= '</tr></thead>';
		$html .= '<tbody>';

		if( !empty( $voucodes ) &&  count( $voucodes ) > 0 ) {
			foreach ( $voucodes as $key => $voucodes_data ) {

				$html .= '<tr>';

				//voucher order id
				$orderid 		= $voucodes_data['order_id'];

				//voucher order date
				$orderdate 		= $voucodes_data['order_date'];
				$orderdate 		= !empty( $orderdate ) ? $model->woo_vou_get_date_format( $orderdate, true ) : '';

				//buyer's name who has purchased/used voucher code
				$buyername 		=  $voucodes_data['buyer_name'];

				//voucher code purchased/used
				$voucode 		= $voucodes_data['vou_codes'];

				$html .= '<td>'.$voucode.'</td>';
				$html .= '<td>'.$buyername.'</td>';
				$html .= '<td>'.( $orderdate ).'</td>';

				if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) { // if generate pdf for used code add and extra column

					$user_id 	 	= $voucodes_data['redeem_by'];
					$user_detail 	= get_userdata( $user_id );
					$redeem_by 		= isset( $user_detail->display_name ) ? $user_detail->display_name : 'N/A';

					$html .= '<td>'.( $orderid ).'</td>';
					$html .= '<td>'.( $redeem_by ).'</td>';
				} else {

					$html .= '<td>'.( $orderid ).'</td>';
				}

				$html .= '</tr>';
			}
		} else {

			if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) { // if generate pdf for used code add and extra column
				$colspan = 5;
			} else {
				$colspan = 4;
			}

			$title = ( $voucher_empty_msg );
			$html .= '<tr><td colspan="'.$colspan.'">'.$title.'</td></tr>';
		}

		$html .= '</tbody>';
		$html .= '</table>';

		// output the HTML content
		$pdf->writeHTML( $html, true, 0, true, 0 );

		// reset pointer to the last page
		$pdf->lastPage();

		//voucher code
		$pdf->SetFont( $pdf_font, 'B', 14 );

		$vou_file_name = str_replace( '{current_date}', date('d-m-Y'), $vou_file_name );
		$pdf->Output( $vou_file_name . '.pdf', 'D' );
		exit;
	}

	// generate pdf for voucher code
	if( isset( $_GET['woo-vou-voucher-gen-pdf'] ) && !empty( $_GET['woo-vou-voucher-gen-pdf'] )
		&& $_GET['woo-vou-voucher-gen-pdf'] == '1' ) {

		$prefix = WOO_VOU_META_PREFIX;

		global $current_user,$woo_vou_model, $post, $woo_vou_vendor_role;

		//model class
		$model = $woo_vou_model;

		// include tcpdf library
		require_once WOO_VOU_DIR . '/includes/tcpdf/tcpdf.php';

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
			$user_role = array_shift( $user_roles );

			if( in_array( $user_role, $woo_vou_vendor_role ) ) { // Check vendor user role
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

		 	$voucher_heading 	= __( 'Used Voucher Codes','woovoucher' );
		 	$voucher_empty_msg	= __( 'No voucher codes used yet.', 'woovoucher' );

			$vou_file_name = 'woo-used-voucher-codes-{current_date}';
		} 
		// if its paritally voucher code tabs
		else if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'partially' ) {
			$args = array();

			$args['meta_query'] = array(
									array(
										'key' 		=> $prefix . 'redeem_by',
										'value'		=> '',
										'compare' 	=> '!='
											)
										);
			//Get user role
			$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
			$user_role = array_shift( $user_roles );

			if( in_array( $user_role, $woo_vou_vendor_role ) ) { // Check vendor user role
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

		 	//Get partially redeemed voucher codes details
		 	$voucodes = $model->woo_vou_get_partially_redeem_details( $args );

		 	$voucher_heading 	= __( 'Partially Used Voucher Codes','woovoucher' );
		 	$voucher_empty_msg	= __( 'No partially used voucher codes used yet.', 'woovoucher' );

			$vou_file_name = 'woo-partially-used-voucher-codes-{current_date}';
		} else {

			$args = array();
			
			if( isset( $_GET['vou-data'] ) && $_GET['vou-data'] == 'expired') {
		 		
		 			$args['meta_query'] = array(
											array(
											'key'			=> $prefix . 'purchased_codes',
											'value'			=> '',
											'compare'		=> '!='
										),
										array(
												'key'			=> $prefix . 'used_codes',
												'compare'		=> 'NOT EXISTS'
											),
										array(
													'key'		=> $prefix .'exp_date',
													'compare'	=> '<=',
		                  							//'type'		=> 'DATE',
		                  							'value'		=> $model->woo_vou_current_date()
											),
										array(
													'key'		=> $prefix .'exp_date',
													'value'		=> '',
													'compare'	=> '!='
											)
										);
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
	                  							//'type'    => 'DATE',
	                  							'value' => $model->woo_vou_current_date()
											)
									   )	
									);
			}

			//Get user role
			$user_roles	= isset( $current_user->roles ) ? $current_user->roles : array();
			$user_role = array_shift( $user_roles );

			//voucher admin roles
			$admin_roles	= woo_vou_assigned_admin_roles();

			if( !in_array( $user_role, $admin_roles ) ) {// voucher admin can redeem all codes
				$args['author'] = $current_user->ID;
			}

			if( isset( $_GET['woo_vou_post_id'] ) && !empty( $_GET['woo_vou_post_id'] ) ) {
				$args['post_parent'] = $_GET['woo_vou_post_id'];
			}

			if( isset( $_GET['s'] ) && !empty( $_GET['s'] ) ) {

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

		 	$voucher_heading 	= __( 'Purchased Voucher Codes','woovoucher' );
		 	$voucher_empty_msg	= __( 'No voucher codes purchased yet.', 'woovoucher' );

		 	$vou_pdf_name = get_option( 'vou_pdf_name' );
			$vou_file_name = !empty( $vou_pdf_name )? $vou_pdf_name : 'woo-purchased-voucher-codes-{current_date}';
		}
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// remove default header
		$pdf->setPrintHeader(false);

		// remove default footer
		$pdf->setPrintFooter(false);

		$pdf->AddPage( 'L', 'A4' );

		// Auther name and Creater name
		$pdf->SetTitle( utf8_decode(__('WooCommerce Voucher','woovoucher')) );
		$pdf->SetAuthor( utf8_decode( __('WooCommerce','woovoucher') ) );
		$pdf->SetCreator( utf8_decode( __('WooCommerce','woovoucher') ) );

		// Set margine of pdf (float left, float top , float right)
		$pdf->SetMargins( 8, 8, 8 );
		$pdf->SetX( 8 );

		// Font size set
		$pdf->SetFont( $pdf_font, '', 18 );
		$pdf->SetTextColor( 50, 50, 50 );
		$pdf->Ln(3);

		$pdf->Cell( 270, 5, utf8_decode( $voucher_heading ), 0, 2, 'C', false );
		$pdf->Ln(5);
		$pdf->SetFont( $pdf_font, '', 10 );
		$pdf->SetFillColor( 238, 238, 238 );

		//voucher logo
		if( !empty( $voulogo ) ) {
			$pdf->Image( $voulogo, 95, 25, 20, 20 );
			$pdf->Ln(35);
		}

		if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) { // if generate pdf for used code add and extra column
			$columns =  array(
								'vou_code' 		=> array('name' => __('Voucher Code', 'woovoucher'), 'width' => 12),
								'product_info' => array('name' => __('Product Information', 'woovoucher'), 'width' => 23),
								'buyer_info' 	=> array('name' => __('Buyer\'s Information', 'woovoucher'), 'width' => 26),
								'order_info' 	=> array('name' => __('Order Information', 'woovoucher'), 'width' => 24),								
								'redeem_by' 	=> array('name' => __('Redeem Information', 'woovoucher'), 'width' => 15)
						);
		} else if(isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'partially') { 
			$columns =  array(
								'vou_code' 		=> array('name' => __('Voucher Code', 'woovoucher'), 'width' => 12),
								'product_info' => array('name' => __('Product Information', 'woovoucher'), 'width' => 23),
								'buyer_info' 	=> array('name' => __('Buyer\'s Information', 'woovoucher'), 'width' => 26),
								'order_info' 	=> array('name' => __('Order Information', 'woovoucher'), 'width' => 24),								
								'redeem_by' 	=> array('name' => __('Redeem Information', 'woovoucher'), 'width' => 15)
						);
		} else {
			$columns =  array(
								'vou_code' 		=> array('name' => __('Voucher Code', 'woovoucher'), 'width' => 20),
								'product_info' => array('name' => __('Product Information', 'woovoucher'), 'width' => 25),
								'buyer_info' 	=> array('name' => __('Buyer\'s Information', 'woovoucher'), 'width' => 30),
								'order_info' 	=> array('name' => __('Order Information', 'woovoucher'), 'width' => 25),
								
						);
		}

		$pdf_type	= isset( $_GET['woo_vou_action'] ) ? $_GET['woo_vou_action'] : 'purchased';
		
		$columns	= apply_filters( 'woo_vou_generate_pdf_columns', $columns, $pdf_type );
		
		$html = '';
		$html .= '<table style="line-height:1.5;" border="1"><thead><tr style="line-height:2;font-weight:bold;background-color:#EEEEEE;">';

		// Table head Code
		foreach( $columns as $column ) {

			$html .= '<th width="'.$column['width'].'%" style="margin:10px;"> '.$column['name'].' </th>';
		}

		$html .= '</tr></thead>';
		$html .= '<tbody>';
		
		if( count( $voucodes ) > 0 ) {

			foreach ( $voucodes as $key => $voucodes_data ) {

				$html .= '<tr>';
				
				if(isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'partially') { 
									//voucher order id
					$orderid 		= get_post_meta( $voucodes[$key]['post_parent'], $prefix.'order_id', true );
	
					//voucher order date
					$orderdate 		= get_post_meta( $voucodes[$key]['post_parent'], $prefix.'order_date', true );
					$orderdate 		= !empty( $orderdate ) ? $model->woo_vou_get_date_format( $orderdate, true ) : '';
	
					//voucher code purchased/used
					$voucode 		= get_post_meta( $voucodes[$key]['post_parent'], $prefix.'purchased_codes', true );
	
					$redeeminfo = $model->woo_vou_display_redeem_info_html( $voucodes_data['ID'], $orderid , '' ,'partially' );
					$redeeminfo = strip_tags( $redeeminfo, '<table><tr><td>' );				
				
					$product_desc = $model->woo_vou_display_product_info_html( $orderid, $voucode, 'pdf' );
					$order_desc = $model->woo_vou_display_order_info_html( $orderid,'pdf' );
				} else {
									//voucher order id
					$orderid 		= get_post_meta( $voucodes_data['ID'], $prefix.'order_id', true );
	
					//voucher order date
					$orderdate 		= get_post_meta( $voucodes_data['ID'], $prefix.'order_date', true );
					$orderdate 		= !empty( $orderdate ) ? $model->woo_vou_get_date_format( $orderdate, true ) : '';
	
					
	
					//voucher code purchased/used
					$voucode 		= get_post_meta( $voucodes_data['ID'], $prefix.'purchased_codes', true );
	
					$redeeminfo = $model->woo_vou_display_redeem_info_html( $voucodes_data['ID'], $orderid, '' );
					$redeeminfo = strip_tags( $redeeminfo, '<table><tr><td>' );				
	
					$product_desc = $model->woo_vou_display_product_info_html( $orderid, $voucode, 'pdf' );
					$order_desc = $model->woo_vou_display_order_info_html( $orderid,'pdf' );
				}
				
				// get order detail
				$order = new WC_Order( $orderid );
	
				$buyer_details		= $model->woo_vou_get_buyer_information( $orderid );
				$buyerinfo	= $model->woo_vou_display_buyer_info_html( $buyer_details );
				
				$html .= '<td width="'.$columns['vou_code']['width'].'%"> '.$voucode.' </td>';
				$html .= '<td width="'.$columns['product_info']['width'].'%"> '.$product_desc.' </td>';
				$html .= '<td width="'.$columns['buyer_info']['width'].'%">'.$buyerinfo.' </td>';
				$html .= '<td width="'.$columns['order_info']['width'].'%"> '. $order_desc .' </td>';

				if( isset( $_GET['woo_vou_action'] ) && ( $_GET['woo_vou_action'] == 'used' ||  $_GET['woo_vou_action'] == 'partially' ) ) { // if generate pdf for used code add and extra column
					
					$html .= '<td width="'.$columns['redeem_by']['width'].'%"> '.( $redeeminfo ).' </td>';
				} 

				ob_start();
				do_action( 'woo_vou_generate_pdf_add_column_after', $orderid, $voucode, $pdf_type );
				$added_column = ob_get_clean();

				$html .= $added_column;

				$html .= '</tr>';
			}
		} else {

			if( isset( $_GET['woo_vou_action'] ) && $_GET['woo_vou_action'] == 'used' ) { // if generate pdf for used code add and extra column
				$colspan = 6;
			} else {
				$colspan = 5;
			}

			$title = ( $voucher_empty_msg );
			$html .= '<tr><td colspan="'.$colspan.'"> '.$title.' </td></tr>';
		}

		$html .= '</tbody>';
		$html .= '</table>';

		
		
		// output the HTML content
		$pdf->writeHTML( $html, true, 0, true, 0 );

		// reset pointer to the last page
		$pdf->lastPage();

		//voucher code
		$pdf->SetFont( $pdf_font, 'B', 10 );

		$vou_file_name = str_replace( '{current_date}', date('d-m-Y'), $vou_file_name );
		$pdf->Output( $vou_file_name . '.pdf', 'D' );
		exit;
	}
}
add_action( 'init', 'woo_vou_generate_voucher_code_pdf' );