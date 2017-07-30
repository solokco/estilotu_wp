<?php 
	
/**
 * Used Voucher Codes Template
 * 
 * Handles to return and display data for used voucher codes
 * 
 * Override this template by copying it to yourtheme/woocommerce/woocommerce-pdf-vouchers/voucher-codes/woo-vou-used-codes.php
 *
 * @package WooCommerce - PDF Vouchers
 * @since 2.8.1
 */
	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


if ( ! is_user_logged_in() ) { //check user is logged in or not

	echo __( 'You need to be logged in to your account to see your used voucher codes.', 'woocommerce' );
	return; 
}

global $product, $current_user, $woo_vou_vendor_role, $woo_vou_model;
$products_data	= $woo_vou_model->woo_vou_get_products_by_voucher( $args );
?>

<!-- Used Voucher codes list table -->
<h3><?php _e( 'Used Voucher Codes', 'woovoucher' ); ?></h3>

<!-- Start search used Voucher Codes through there dates -->
<form action="" method="GET" class="search-form">
	<select id="woo_vou_post_id" name="woo_vou_post_id" class="woo_vou_multi_select">
		<option value=""><?php _e( 'Show all products', 'woovoucher' ); ?></option><?php
		if( !empty( $products_data ) ) {
			foreach ( $products_data as $product_data ) {
				echo '<option value="' . $product_data['ID'] . '" ' . selected( isset( $_GET['woo_vou_post_id'] ) ? $_GET['woo_vou_post_id'] : '', $product_data['ID'], false ) . '>' . $product_data['post_title'] . '</option>';
			}
		}?>
	</select>
	<input type="text" id="woo_vou_start_date" name="woo_vou_start_date" class="woo-vou-meta-datetime" rel="MM dd, yy" placeholder="<?php _e( 'Redeem Start Date', 'woovoucher' ); ?>" value="<?php echo isset( $_GET['woo_vou_start_date'] ) ? $_GET['woo_vou_start_date'] : ''; ?>">
	<input type="text" id="woo_vou_end_date" name="woo_vou_end_date" class="woo-vou-meta-datetime" rel="MM dd, yy" placeholder="<?php _e( 'Redeem End Date', 'woovoucher' ); ?>" value="<?php echo isset( $_GET['woo_vou_end_date'] ) ? $_GET['woo_vou_end_date'] : ''; ?>">
	<input type="submit" value="Apply" class="woo-vou-btn-front woo-vou-apply-btn" id="woo-vou-filter-apply-btn"></input>
</form>
<!-- End search used Voucher Codes through there dates -->

<div class="woo-vou-usedvoucodes woo-vou-used-codes-html">
<?php
	
	if( isset( $_POST['woo_vou_start_date'] ) && !empty( $_POST['woo_vou_start_date'] ) )
		$_GET['woo_vou_start_date'] = $_POST['woo_vou_start_date'];
	if( isset( $_POST['woo_vou_end_date'] ) && !empty( $_POST['woo_vou_end_date'] ) )
		$_GET['woo_vou_end_date'] = $_POST['woo_vou_end_date'];
	if( isset( $_POST['woo_vou_post_id'] ) && !empty( $_POST['woo_vou_post_id'] ) )
		$_GET['woo_vou_post_id'] = $_POST['woo_vou_post_id'];	
		
		
	$perpage = apply_filters( 'woo_vou_used_voucher_codes_per_page', 10 );
	
	//Get Prefix
	$prefix		= WOO_VOU_META_PREFIX;

	// start paging
	$paging = new Woo_Vou_Pagination_Public( 'woo_vou_used_codes_ajax_pagination' );
	
	$args = $data = array();
	
	// Taking parameter
	$orderby 	= 'ID';
	$order		= 'DESC';

	$args = array(
					'paged'				=> isset( $_POST['paging'] ) ? $_POST['paging'] : null,
					'orderby'			=> $orderby,
					'order'				=> $order,
					'woo_vou_list'		=> true
				);

	$search_meta = 	array(
						array(
							'key' 		=> $prefix . 'used_codes',
							'value' 	=> '',
							'compare' 	=> '!='
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
		
		$search_meta =	array(
							'relation' => 'AND',
							($search_meta),
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
		
		$search_meta =	array(
							'relation' => 'AND',
							($search_meta),
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
		
		$search_meta =	array(
							'relation' => 'AND',
							($search_meta),
							array(
								array(
										'key'		=> $prefix.'used_code_date',
										'value'		=> date( "Y-m-d H:i:s", strtotime( $_GET['woo_vou_end_date'] ) ),
										'compare'	=> '<=',
									)
							)
						);
	}
	
	$args['meta_query']	= $search_meta;

	// Get count for used voucher codes from database without post per page param
	$count_data 	= $woo_vou_model->woo_vou_get_voucher_details( $args );
	
	// Specify paging params
	$paging->items( count( $count_data['data'] ) ); // Get total paging items
	$paging->limit( $perpage ); // limit entries per page
	
	if( isset( $_POST['paging'] ) ) {
		$paging->currentPage( $_POST['paging'] ); // gets and validates the current page
	}
	
	$paging->calculate(); // calculates what to show

	$paging->parameterName( 'paging' ); // Specify parameter name for paging
	
	$args['posts_per_page'] = $perpage; // Specify post per page param now
	
	//get used voucher codes data from database
	$woo_data   = $woo_vou_model->woo_vou_get_voucher_details( $args );
	$data		= isset( $woo_data['data'] ) ? $woo_data['data'] : '';

	if( !empty( $data ) ) {

		foreach ( $data as $key => $value ) {

			$user_id 	  = get_post_meta( $value['ID'], $prefix.'redeem_by', true );
			$user_detail  = get_userdata( $user_id );
			$user_profile = add_query_arg( array('user_id' => $user_id), admin_url('user-edit.php') );
			$display_name = isset( $user_detail->display_name ) ? $user_detail->display_name : '';

			if( !empty( $display_name ) ) {
				$display_name = '<a href="'.$user_profile.'">'.$display_name.'</a>';
			} else {
				$display_name = __( 'N/A', 'woovoucher' );
			}

			$data[$key]['ID'] 			= $value['ID'];
			$data[$key]['post_parent'] 	= $value['post_parent'];
			$data[$key]['code'] 		= get_post_meta( $value['ID'], $prefix.'used_codes', true );
			$data[$key]['redeem_by'] 	= $display_name;
			$data[$key]['first_name'] 	= get_post_meta( $value['ID'], $prefix.'first_name', true );
			$data[$key]['last_name'] 	= get_post_meta( $value['ID'], $prefix.'last_name', true );
			$data[$key]['order_id'] 	= get_post_meta( $value['ID'], $prefix.'order_id', true );
			$data[$key]['order_date'] 	= get_post_meta( $value['ID'], $prefix.'order_date', true );
			$data[$key]['product_title']= get_the_title( $value['post_parent'] );

			$order_id = $data[$key]['order_id'];

			$data[$key]['buyers_info'] = $woo_vou_model->woo_vou_get_buyer_information( $order_id );
		}
	}
	$result_arr	= !empty($data) ? $data : array();
	
	if( isset( $result_arr ) ) { //check if Array of Used Voucher Codes is empty
		
		// do action add something before used codes table
		do_action( 'woo_vou_used_codes_table_before', $result_arr );
				
		// start displaying the paging if needed
		// do action add used codes listing table
		do_action( 'woo_vou_used_voucher_codes_table', $result_arr, $paging );

		// do action add something after used codes table after	
		do_action( 'woo_vou_used_codes_table_after', $result_arr );
		
	} ?>
</div>