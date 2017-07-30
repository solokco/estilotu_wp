<?php
$prefix = WOO_VOU_META_PREFIX;

//get order
$order 			= new Wc_Order( $order_id );

//get order items
$order_items 	= $order->get_items();

//get order date
$order_date		= $order->order_date;

//get payment method
$payment_method	= $order->payment_method_title;

//get buyer details
$buyer_detail	= $this->model->woo_vou_get_buyer_information( $order_id );

//get buyer name
$buyername		= isset( $buyer_detail['first_name'] ) ? $buyer_detail['first_name'] : '';
$buyername		.= isset( $buyer_detail['last_name'] ) ? ' '.$buyer_detail['last_name'] : '';

// get partial redeem settings
$enable_partial_redeem = apply_filters( 'woo_vou_enable_partial_redeem_during_check_voucher', get_option( 'vou_enable_partial_redeem' ), $order_id, $voucode );

//product info key parameter
$product_info_columns	= apply_filters( 'woo_vou_check_vou_productinfo_fields', array(
													'item_name'		=> __( 'Item Name', 'woovoucher' ),
													'item_price'	=> __( 'Price', 'woovoucher' )
												), $order_id, $voucode );

// if partial redeem is enabled
if( $enable_partial_redeem == "yes" ) {

	// add redeemable price column
	$product_info_columns['redeemable_price'] = __( 'Redeemable Price', 'woovoucher' );
	
	// redeem information key parameter
	$redeem_info_columns	= apply_filters( 'woo_vou_check_vou_partial_redeem_info_fields', array(
													'item_name'		=> __( 'Item Name', 'woovoucher' ),
													'redeem_price'	=> __( 'Redeem Amount', 'woovoucher' ),
													'redeem_by'		=> __( 'Redeem By', 'woovoucher' ),
													'redeem_date'	=> __( 'Redeem Date', 'woovoucher' )
												), $order_id, $voucode );
}												

//product voucher information columns
$voucher_info_columns = apply_filters( 'woo_vou_check_vou_voucherinfo_fields', array(
													'logo' 			=> __( 'Logo', 'woovoucher' ),
													'voucher_data' 	=> __( 'Voucher Data', 'woovoucher' ),
													'expires' 		=> __( 'Expires', 'woovoucher' )
												), $order_id, $voucode );

//buyer info key parameter
$buyer_info_columns	= apply_filters( 'woo_vou_check_vou_buyerinfo_fields', array(
													'buyer_name'		=> __( 'Name', 'woovoucher' ),
													'buyer_email'		=> __( 'Email', 'woovoucher' ),
													'billing_address'	=> __( 'Billing Address', 'woovoucher' ),
													'shipping_address'	=> __( 'Shipping Address', 'woovoucher' ),
													'buyer_phone'		=> __( 'Phone', 'woovoucher' )
												), $order_id, $voucode );

//order info key parameter
$order_info_columns	= apply_filters( 'woo_vou_check_vou_orderinfo_fields', array(
													'order_id'			=> __( 'Order ID', 'woovoucher' ),
													'order_date'		=> __( 'Order Date', 'woovoucher' ),
													'payment_method'	=> __( 'Payment Method', 'woovoucher' ),
													'order_total'		=> __( 'Order Total', 'woovoucher' ),
													'order_discount'	=> __( 'Order Discount', 'woovoucher' ),
												), $order_id, $voucode );

$check_code	= trim( $voucode );
$item_array	= $this->model->woo_vou_get_item_data_using_voucher_code( $order_items, $check_code );

$item		= isset( $item_array['item_data'] ) ? $item_array['item_data'] : array();
$item_id	= isset( $item_array['item_id'] ) ? $item_array['item_id'] : array();

//Get product from Item ( It is required otherwise multipdf voucher link not work and global $woo_vou_item_id will not work )
$_product 	= $order->get_product_from_item( $item );

$product_id = isset( $_product->id ) ? $_product->id :''; // get product id
$billing_address	= $order->get_formatted_billing_address();
$shipping_address	= $order->get_formatted_shipping_address();

// if partial redeem is enabled
if( $enable_partial_redeem == "yes" ) {
	
	// Get partially used voucher code data
	$args = $partially_redeemed_data = $redeemed_infos = array();
	$args = array(
				'woo_vou_list' => true,
				'post_parent' => $voucodeid
			);	
	
	//get partially used voucher codes data from database
	$redeemed_data 				= $this->model->woo_vou_get_partially_redeem_details( $args );
	$partially_redeemed_data	= isset( $redeemed_data['data'] ) ? $redeemed_data['data'] : '';
	$redeemed_data_cnt 			= isset( $redeemed_data['total'] ) ? $redeemed_data['total'] : '';

	if( !empty( $partially_redeemed_data ) ) {
		
		foreach ( $partially_redeemed_data as $key => $value ) {
	
			$user_id 	  = get_post_meta( $value['ID'], $prefix.'redeem_by', true );
			$user_detail  = get_userdata( $user_id );
			$display_name = isset( $user_detail->display_name ) ? $user_detail->display_name : '';
						
			$redeemed_amount	= get_post_meta( $value['ID'], $prefix . 'partial_redeem_amount', true );
			$redeem_date 		= get_post_meta( $value['ID'], $prefix . 'used_code_date', true );
			
			$redeemed_infos[$key] = array(
				"redeem_by"			=> $display_name,
				"redeem_amount" 	=> $redeemed_amount,
				"redeem_date" 		=> $redeem_date,
			);
		}
	}
	
	// get total price of voucher code
	if ( isset( $item['line_subtotal'] ) ) {			
		$vou_code_total_price = $item['line_subtotal'] / $item['qty'];
	}
	
	// get total redeemed price
	$vou_code_total_redeemed_price = $this->model->woo_vou_get_total_redeemed_price_for_vouchercode( $voucodeid );
	
	// get remaining price for redeem
	$vou_code_remaining_redeem_price = number_format( (float)($vou_code_total_price - $vou_code_total_redeemed_price), 2, '.', '' );
}
?>

<div class="woo_vou_product_details"><?php
	
	// if partial redeem enabled then show partial redeem option
	if( $enable_partial_redeem == "yes" ) {

		if( !empty( $redeemed_infos ) && is_array( $redeemed_infos ) ) {
				
			do_action( 'woo_vou_before_redeeminfo', $voucodeid, $item_id, $order_id ); ?>
		
			<h2><?php echo __( 'Redeem Information', 'woovoucher' );?></h2>
			
			<div class="woo_pdf_vou_main">
				<div class="woo_pdf_pro_tit">
					<?php
					// Get product columns
					$product_col = count( $redeem_info_columns );
					$product_col = 'col-' . $product_col;
					
					foreach ( $redeem_info_columns as $col_key => $column ) { ?>
						<div class="<?php echo $product_col; ?> woo_vou_padding"><?php echo $column;?></div><?php
					} ?>
				</div>
				<?php
				foreach( $redeemed_infos as $key => $redeemed_info ) {
					
					if( $key != ( $redeemed_data_cnt - 1 ) )
						$margin_bottom_0 = 'woo-vou-margin-bottom-0';
					else
						$margin_bottom_0 = '';
						
					echo '<div class="woo_pdf_vou_pro_lst ' . $margin_bottom_0 . '">';
					
					foreach ( $redeem_info_columns as $col_key => $column ) { ?>
					
						<div class="<?php echo $product_col; ?> woo_vou_padding"><?php

							$column_value = $sku_value	= '';

							switch ( $col_key ) {

								case 'item_name' : 									
									$column_value = '<div class="woo_pdf_res_vou"> ' . __( 'Item Name', 'woovoucher' ) . '</div>'; 
									
									if ( $_product && $_product->get_sku() ) {
										$sku_value	= esc_html( $_product->get_sku() ).' - ';
									}
									if ( $_product ) {
										$column_value .= $sku_value.'<a target="_blank" href="'. get_permalink( $_product->id ) . '">' . esc_html( $item['name'] ) . '</a>';
									} else {
										$column_value .= $sku_value.esc_html( $item['name'] );
									}

									//Get product item meta
									$product_item_meta = isset( $item['item_meta'] ) ? $item['item_meta'] : array();
									$column_value .= $this->model->woo_vou_display_product_item_name( $item, $_product, true );
									break;

								case 'redeem_price' :
									$column_value = '<div class="woo_pdf_res_vou">' . __( 'Price', 'woovoucher' ) . '</div>'; 
									
									if ( isset( $redeemed_info['redeem_amount'] ) ) {
										$column_value .= wc_price( $redeemed_info['redeem_amount'] , array( 'currency' => $order->get_order_currency() ) ) ;											
									}
									break;
									
								case 'redeem_by' :
									$column_value = '<div class="woo_pdf_res_vou">' . __( 'Redeem By', 'woovoucher' ) . '</div>'; 
									
									if ( isset( $redeemed_info['redeem_by'] ) ) {
										$column_value .= $redeemed_info['redeem_by'] ;
									}
									break;
									
								case 'redeem_date' :
									$column_value = '<div class="woo_pdf_res_vou">' . __( 'Redeem Date', 'woovoucher' ) . '</div>'; 
									
									if ( isset( $redeemed_info['redeem_date'] ) ) {
										$column_value .= $this->model->woo_vou_get_date_format( $redeemed_info['redeem_date'], true ) ;
									}
									break;
									
								default:
									$column_value .= '';
							}

							echo apply_filters( 'woo_vou_check_partial_voucher_column_value', $column_value, $col_key, $voucodeid, $item_id, $order_id );

							?>
					</div><?php 
				} 
				echo '</div>';
			}
			echo '</div>';
		} 
	}
	
	do_action( 'woo_vou_before_productinfo', $voucodeid, $item_id, $order_id );
	
	if( !empty( $product_info_columns ) ) { //if product info is not empty ?>
		
		<h2><?php echo __( 'Product Information', 'woovoucher' );?></h2>
		<div class="woo_pdf_vou_main">
			<div class="woo_pdf_pro_tit">
				<?php
				// Get product columns
				$product_col = count($product_info_columns);
				$product_col = 'col-'.$product_col;
				
				foreach ( $product_info_columns as $col_key => $column ) { ?>
					<div class="<?php echo $product_col; ?> woo_vou_padding"><?php echo $column;?></div><?php
				} ?>
			</div>
			<div class="woo_pdf_vou_pro_lst"><?php
				
				foreach ( $product_info_columns as $col_key => $column ) {?>

					<div class="<?php echo $product_col; ?> woo_vou_padding"><?php

						$column_value = $sku_value	= '';

						switch ( $col_key ) {

							case 'item_name' : 
							
							$column_value = '<div class="woo_pdf_res_vou">' . __( 'Item Name', 'woovoucher') . '</div>'; 
								
								if ( $_product && $_product->get_sku() ) {
									$sku_value	= esc_html( $_product->get_sku() ).' - ';
								}
								if ( $_product ) {
									$column_value .= $sku_value.'<a target="_blank" href="'. get_permalink( $_product->id ) . '">' . esc_html( $item['name'] ) . '</a>';
								} else {
									$column_value .= $sku_value.esc_html( $item['name'] );
								}

								//Get product item meta
								$product_item_meta = isset( $item['item_meta'] ) ? $item['item_meta'] : array();

								//Display product variations
								//$column_value .= $this->model->woo_vou_display_product_item_name( $product_item_meta, true );
								$column_value .= $this->model->woo_vou_display_product_item_name( $item, $_product, true );
								break;

							case 'item_price' :
								$column_value = '<div class="woo_pdf_res_vou">' . __( 'Price', 'woovoucher') . '</div>'; 
								
								if ( isset( $item['line_total'] ) ) {
									if ( isset( $item['line_subtotal'] ) && $item['line_subtotal'] != $item['line_total'] ) echo '<del>' . wc_price( $item['line_subtotal']/$item['qty'] ) . '</del> ';
									$column_value .= wc_price( ( $item['line_total'] + $item['line_tax'] )/$item['qty'] );
								}
								break;
								
							case 'redeemable_price' :
								$column_value = '<div class="woo_pdf_res_vou">' . __( 'Redeemable Price', 'woovoucher' ) . '</div>';
								$column_value .= wc_price( $vou_code_remaining_redeem_price );
								break;
								
							default:
								$column_value .= '';
						}

						echo apply_filters( 'woo_vou_check_voucher_column_value', $column_value, $col_key, $voucodeid, $item_id, $order_id );

						?>
					</div><?php 
				} ?>
			</div>
		</div><?php 
	}
	
	do_action( 'woo_vou_after_productinfo', $voucodeid, $item_id, $order_id );
	
	if( !empty( $voucher_info_columns ) ) { //if voucher info column is not empty ?>
		
		<h2><?php echo __( 'Voucher Information', 'woovoucher' ); ?></h2>
		
		<div class="woo_pdf_vou_main">
			<div class="woo_pdf_pro_tit">
				<?php
				// Get product columns
				$voucher_col = count($voucher_info_columns);
				$voucher_col = 'col-'.$voucher_col;
			
				foreach ( $voucher_info_columns as $col_key => $column ) { ?>
					
					<div class="<?php echo $voucher_col; ?> woo_vou_padding"><?php echo $column;?></div><?php
				}?>
			</div>
			<div class="woo_pdf_vou_pro_lst"><?php 
				
				// get orderdata
				$allorderdata	= $this->model->woo_vou_get_all_ordered_data( $order_id );						
				//get all voucher details from order meta
				$allvoucherdata = isset( $allorderdata[$_product->id] ) ? $allorderdata[$_product->id] : array();
				
				foreach ( $voucher_info_columns as $col_key => $column ) { ?>

					<div class="<?php echo $voucher_col; ?> woo_vou_padding">
						<?php
						$column_value = '';

						switch ( $col_key ) {

							case 'logo' :									
								if( !empty(  $allvoucherdata['vendor_logo']['src'] ) )
									$column_value .= '<div class="woo_pdf_res_vou">' . __( 'Logo', 'woovoucher') . '</div>' . '<img src="' . $allvoucherdata['vendor_logo']['src'] . '" alt="" width="70" height="70" />';
								else 
									$column_value .= '<div class="woo_pdf_res_vou">' . __( 'Logo', 'woovoucher') . '</div> &nbsp;';
								break;
							case 'voucher_data' : 
								ob_start(); ?>									
								<span><strong><?php _e( 'Vendor\'s Address', 'woovoucher' ); ?></strong></span><br />
								<span><?php echo !empty( $allvoucherdata['vendor_address'] ) ? nl2br( $allvoucherdata['vendor_address'] ) : __( 'N/A', 'woovoucher' ); ?></span><br />
								<span><strong><?php _e( 'Site URL', 'woovoucher' ); ?></strong></span><br />
								<span><?php echo !empty( $allvoucherdata['website_url'] ) ? $allvoucherdata['website_url'] : __( 'N/A', 'woovoucher' ); ?></span><br />
								<span><strong><?php _e( 'Redeem Instructions', 'woovoucher' ); ?></strong></span><br />
								<span><?php echo !empty( $allvoucherdata['redeem'] ) ? nl2br( $allvoucherdata['redeem'] ) : __( 'N/A', 'woovoucher' ); ?></span><br /><?php
								
								if( !empty( $allvoucherdata['avail_locations'] ) ) {
									
									echo '<span><strong>' . __( 'Locations', 'woovoucher' ) . '</strong></span><br />';
									
									foreach ( $allvoucherdata['avail_locations'] as $location ) {
										
										if( !empty( $location[$prefix.'locations'] ) ) {
											
											if( !empty( $location[$prefix.'map_link'] ) ) {
												echo '<span><a target="_blank" style="text-decoration: none;" href="' . $location[$prefix.'map_link'] . '">' . $location[$prefix.'locations'] . '</a></span><br />';
											} else {
												echo '<span>' . $location[$prefix.'locations'] . '</span><br />';
											}
										}
									}
								}
								$column_value = '<div class="woo_pdf_res_vou">'. __( 'Voucher Data', 'woovoucher') . '</div> <div class="woo_pdf_val">'. ob_get_clean() . '</div>';
								break;
							case 'expires' :
								$column_value = '<div class="woo_pdf_res_vou">' . __( 'Expires', 'woovoucher') . '</div>'; 
								$column_value .= !empty( $allvoucherdata['exp_date'] ) ? $this->model->woo_vou_get_date_format( $allvoucherdata['exp_date'], true ) : __( 'N/A', 'woovoucher' );	
							default:
								$column_value .= '';
						}

						echo apply_filters( 'woo_vou_check_voucher_column_value', $column_value, $col_key, $voucodeid, $item_id, $order_id );
						?>
					</div><?php
				}?>
			</div>
		</div><?php 
	}
	
	do_action( 'woo_vou_after_voucherinfo', $voucodeid, $item_id, $order_id );
	
	if( !empty( $buyer_info_columns ) ) { //if product info is not empty ?>
		
		<h2><?php echo __( 'Buyer Information', 'woovoucher' ); ?></h2>
		<div class="woo_pdf_vou_main">
			<div class="woo_pdf_vou_tit">
				<?php 
				// Get product columns
				$buyer_col = count($buyer_info_columns);
				$buyer_col = 'col-'.$buyer_col;
				
				foreach ( $buyer_info_columns as $col_key => $column ) { ?>
					<div class="<?php echo $buyer_col; ?> woo_vou_padding"><?php echo $column;?></div><?php
				} ?>
			</div>
			
			<div class="woo_pdf_vou_pro_lst">
				<?php
				foreach ( $buyer_info_columns as $col_key => $column ) { ?>
					
					<div class="<?php echo $buyer_col; ?> woo_vou_padding">
						<?php
						$column_value = '';
						
						switch ( $col_key ) { 
							
							case 'buyer_name' : 
								$column_value .= '<div class="woo_pdf_res_buyer">' . __( 'Name', 'woovoucher') . '</div>' . $order->billing_first_name;
								
								if( !empty( $order->billing_last_name ) ) {
									$column_value .=  $order->billing_last_name;
								}
								break;
							
							case 'buyer_email' : 
								$column_value .='<div class="woo_pdf_res_buyer">' . __( 'Email', 'woovoucher') . '</div>' .$order->billing_email;
								break;
							
							case 'billing_address' : 
								$column_value .= '<div class="woo_pdf_res_buyer">' . __( 'Billing Address', 'woovoucher') . '</div> <div class="woo_pdf_val">' . $billing_address . "</div>";
								break;
							
							case 'shipping_address' : 
								$column_value .= '<div class="woo_pdf_res_buyer">' . __( 'Shipping Address', 'woovoucher') . '</div> <div class="woo_pdf_val">' . $shipping_address . '</div>';
								break;
							
							case 'buyer_phone' : 
								$column_value .= '<div class="woo_pdf_res_buyer">' . __( 'Phone', 'woovoucher') . '</div>' . $order->billing_phone;
								break;
							
							default:
								$column_value .= '';
						}
						
						echo apply_filters( 'woo_vou_check_voucher_column_value', $column_value, $col_key, $voucodeid, $item_id, $order_id );
						?>
					</div><?php 
				}?>
			</div>
		</div><?php 
	}
	
	do_action( 'woo_vou_after_buyerinfo', $voucodeid, $item_id, $order_id );
	
	if( !empty( $order_info_columns ) ) { //if product info is not empty ?>
	
		<h2><?php echo __( 'Order Information', 'woovoucher' );?></h2>
		
		<div class="woo_pdf_vou_main">
			<div class="woo_pdf_vou_tit">
				<?php
				// Get product columns
				$order_col = count($order_info_columns);
				$order_col = 'col-'.$order_col;
				
				foreach ( $order_info_columns as $col_key => $column ) { ?>
					<div class="<?php echo $order_col; ?> woo_vou_padding"><?php echo $column;?></div><?php
				}?>
			</div>
			<div class="woo_pdf_vou_pro_lst">
				<?php
				foreach ( $order_info_columns as $col_key => $column ) {?>
					
					<div class="<?php echo $order_col; ?> woo_vou_padding"><?php
					
						$column_value = '';
						
						switch ( $col_key ) { 
							
							case 'order_id' :
								$column_value .= '<div class="woo_pdf_res_order">' . __( 'Order ID', 'woovoucher') . '</div>'. $order_id;
								break;
							
							case 'order_date' :
								$column_value .=  '<div class="woo_pdf_res_order">' . __( 'Order Date', 'woovoucher') . ' </div>' . $this->model->woo_vou_get_date_format( $order->post->post_date, true );
								break;
							
							case 'payment_method' : 
								$column_value .=  '<div class="woo_pdf_res_order">' . __( 'Payment Method', 'woovoucher') . '</div>' . $payment_method;
								break;
							
							case 'order_total':
								$column_value .=  '<div class="woo_pdf_res_order">' . __( 'Order Total', 'woovoucher') . '</div>' . esc_html( strip_tags( $order->get_formatted_order_total() ) );
								break;
							
							case 'order_discount' : 
								$column_value .=  '<div class="woo_pdf_res_order">' . __( 'Order Discount', 'woovoucher') . '</div>' . wc_price( $order->get_total_discount(), array( 'currency' => $order->get_order_currency() ) );
								break;
							
							default:
								$column_value .= '';
						}
						
						echo apply_filters( 'woo_vou_check_voucher_column_value', $column_value, $col_key, $voucodeid, $item_id, $order_id ); ?>
					</div><?php 
				}?>
			</div>
		</div><?php
	}
	
	do_action( 'woo_vou_after_orderinfo', $voucodeid, $item_id, $order_id ); 
	
	// if partial redeem enabled then show partial redeem option
	if( $enable_partial_redeem == "yes" ) { ?>
			
		<input type="hidden" value="<?php echo $vou_code_total_price; ?>" name="vou_code_total_price" id="vou_code_total_price" />
		<input type="hidden" value="<?php echo $vou_code_total_redeemed_price; ?>" name="vou_code_total_redeemed_price" id="vou_code_total_redeemed_price" />
		<input type="hidden" value="<?php echo $vou_code_remaining_redeem_price; ?>" name="vou_code_remaining_redeem_price" id="vou_code_remaining_redeem_price" />
		
		<div class="woo_pdf_vou_main">
			<h2><?php _e( 'Redeem Options', 'woovoucher' ); ?></h2>
			<div class="woo_pdf_vou_pro_lst woo-vou-margin-bottom-0">
				<div class="col-6 woo_vou_padding">
					<label for="vou_redeem_method"><?php _e( 'Redeem Method', 'woovoucher' ); ?></label>
				</div>
				<div class="col-2 woo_vou_padding">
					<select name="vou_redeem_method" id="vou_redeem_method">
						<option value="full"><?php _e( 'Full', 'woovoucher' ); ?></option>
						<option value="partial"><?php _e( 'Partial', 'woovoucher' ); ?></option>
					</select><br/>
					<?php
					$partially_redeemed = get_post_meta( $voucodeid, $prefix . 'redeem_method', true );
					if( !empty( $partially_redeemed ) && $partially_redeemed == 'partial' ) { ?>
						<span class="description"><?php echo sprintf( __( 'If you select %sFull%s method then it will redeem remaining amount. If you select %sPartial%s then you have option to enter the partial redeem amount.', 'woovoucher' ), '<b>', '</b>', '<b>', '</b>' ); ?></span>
					<?php } else { ?>
						<span class="description"><?php echo sprintf( __( 'If you select %sFull%s method then it will redeem full amount. If you select %sPartial%s then you have option to enter the partial redeem amount.', 'woovoucher' ), '<b>', '</b>', '<b>', '</b>' ); ?></span>
					<?php } ?>					
				</div>
			</div>			
			<div class="woo_pdf_vou_pro_lst woo-vou-partial-redeem-amount woo-vou-margin-bottom-0">
				<div class="col-6 woo_vou_padding">
					<label for="vou_partial_redeem_amount"><?php _e( 'Redeem Amount', 'woovoucher' ); ?></label>
				</div>
				<div class="col-2 woo_vou_padding">
					<input type="number" name="vou_partial_redeem_amount" id="vou_partial_redeem_amount" value="<?php echo $vou_code_remaining_redeem_price; ?>" min="1" max="<?php echo $vou_code_remaining_redeem_price; ?>"/><br />
					<span class="description"><?php _e( 'Enter the amount you want to redeem.', 'woovoucher' ); ?></span>
					<div class="woo-vou-voucher-code-msg woo-vou-voucher-code-error"></div>
				</div>
			</div>
		</div>
		<?php
		
		do_action( 'woo_vou_after_redeem_options', $voucodeid, $item_id, $order_id ); 		
	} ?>
		
</div>