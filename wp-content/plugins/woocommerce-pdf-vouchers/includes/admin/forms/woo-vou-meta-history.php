<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

global $woo_vou_model, $post;

//model class
$model			= $woo_vou_model;
$order_id		= isset( $post->ID ) ? $post->ID : '';

$allorderdata	= $model->woo_vou_get_all_ordered_data( $order_id );

//get cart details
$cart_details 	= new Wc_Order( $order_id );
$order_items	= $cart_details->get_items();

$woo_vou_order_lang = get_post_meta( $order_id, 'wpml_language', true );

$simple_product_post_type 		= 'product';
$variation_product_post_type 	= 'product_variation';

//get meta prefix
$prefix = WOO_VOU_META_PREFIX;

if( !empty( $order_items ) ) {// Check cart details are not empty

?>
	<table class="widefat woo-vou-history-table">
		<tr class="woo-vou-history-title-row">
			<th width="8%"><?php echo __( 'Logo', 'woovoucher' ); ?></th>
			<th width="17%"><?php echo __( 'Product Title', 'woovoucher' ); ?></th>
			<th width="15%"><?php echo __( 'Code', 'woovoucher' ); ?></th>
			<th width="45%"><?php echo __( 'Voucher Data', 'woovoucher' ); ?></th>
			<?php do_action( 'woo_vou_history_table_before_expires_title' ); ?>
			<th width="10%"><?php echo __( 'Expires', 'woovoucher' ); ?></th>
			<th width="5%"><?php echo __( 'Qty', 'woovoucher' ); ?></th>
		</tr><?php
		
		foreach ( $order_items as $item_id => $product_data ) {
			
			//Get product from Item ( It is required otherwise multipdf voucher link not work and global $woo_vou_item_id will not work )
			$_product	= apply_filters( 'woocommerce_order_item_product', $cart_details->get_product_from_item( $product_data ), $product_data );

			if( $product_data['variation_id'] > 0 ) {
				// Replace "variation_id" from product_data, if it's greater than 0
				$product_data['variation_id'] = apply_filters( 'woo_vou_before_admin_vou_download_link', $product_data['variation_id'], $variation_product_post_type, $woo_vou_order_lang );
			} else {
				// Else Replace "product_id" from product_data
				$product_data['product_id'] = apply_filters( 'woo_vou_before_admin_vou_download_link', $product_data['product_id'], $simple_product_post_type, $woo_vou_order_lang );
			}

			if( !$_product ) { //If product deleted
				$download_file_data = array();
			} else {
				//Get download files
				$download_file_data	= $cart_details->get_item_downloads( $product_data );
			}

			//Get product ID
			$product_id			= $product_data['product_id'];
			
			//get all voucher details from order meta
			$allvoucherdata = isset( $allorderdata[$product_id] ) ? $allorderdata[$product_id] : array();
			
			//Get product item meta
			$product_item_meta	= isset( $product_data['item_meta'] ) ? $product_data['item_meta'] : array();
			
			//Get voucher code from item meta "Now we store voucher codes in item meta fields"
			$codes_item_meta	= wc_get_order_item_meta( $item_id, $prefix.'codes' );
			
			if( !empty( $codes_item_meta ) ) { // Check Voucher Data are not empty ?>
				
				<tr>
					<td class="woo-vou-history-td"><img src="<?php echo $allvoucherdata['vendor_logo']['src'] ?>" alt="" width="70" height="30" /></td>
					<td class="woo-vou-history-td"><?php
							
							if( !empty( $_product ) ) {
									echo '<a href="'.esc_url( admin_url( 'post.php?post=' . absint( $product_id ) . '&action=edit' ) ).'">' . $product_data['name'] . '</a>';
							} else {
								echo $product_data['name'];
							}
							
							//echo $model->woo_vou_display_product_item_name( $product_item_meta );
							echo $model->woo_vou_display_product_item_name( $product_data, $_product );
							
							foreach ( $download_file_data as $key => $download_file ){
								
								$check_key = strpos( $key, 'woo_vou_pdf_' );
								
								if( !empty( $download_file ) && $check_key !== false ) {
									
									//Get download URL
									$download_url	= $download_file['download_url'];
									
									//Remove order query arguments
									$download_url	= remove_query_arg( 'order', $download_url );
									
									//add arguments array
									$add_arguments	= array(
															'woo_vou_admin'		=> true,
															'woo_vou_order_id'	=> $order_id,
															'item_id'	=> $item_id
														);
									
									//PDF Download URL
									$download_url	= add_query_arg( $add_arguments, $download_url );
									
									echo '<div><a href="'.$download_url.'" target="_blank">'.$download_file['name'].'</a></div>';
								}
							}
						?>
					</td>
					<td class="woo-vou-history-td"><?php echo $codes_item_meta;?></td>
					<td class="woo-vou-history-td">
						<p><strong><?php _e( 'Vendor\'s Address', 'woovoucher' ); ?></strong></p>
						<p><?php echo !empty( $allvoucherdata['vendor_address'] ) ? nl2br( $allvoucherdata['vendor_address'] ) : __( 'N/A', 'woovoucher' ); ?></p>
						<p><strong><?php _e( 'Site URL', 'woovoucher' ); ?></strong></p>
						<p><?php echo !empty( $allvoucherdata['website_url'] ) ? $allvoucherdata['website_url'] : __( 'N/A', 'woovoucher' ); ?></p>
						<p><strong><?php _e( 'Redeem Instructions', 'woovoucher' ); ?></strong></p>
						<p><?php echo !empty( $allvoucherdata['redeem'] ) ? nl2br( $allvoucherdata['redeem'] ) : __( 'N/A', 'woovoucher' ); ?></p><?php
						
						if( !empty( $allvoucherdata['avail_locations'] ) ) {
							
							echo '<p><strong>' . __( 'Locations', 'woovoucher' ) . '</strong></p>';
							
							foreach ( $allvoucherdata['avail_locations'] as $location ) {
								
								if( !empty( $location[$prefix.'locations'] ) ) {
									
									if( !empty( $location[$prefix.'map_link'] ) ) {
										echo '<p><a target="_blank" style="text-decoration: none;" href="' . $location[$prefix.'map_link'] . '">' . $location[$prefix.'locations'] . '</a></p>';
									} else {
										echo '<p>' . $location[$prefix.'locations'] . '</p>';
									}
								}
							}
						}?>
					</td>
					<?php do_action( 'woo_vou_history_table_before_expires', $codes_item_meta ); ?>
					<td class="woo-vou-history-td"><?php echo !empty( $allvoucherdata['exp_date'] ) ? $model->woo_vou_get_date_format( $allvoucherdata['exp_date'], true ) : __( 'N/A', 'woovoucher' ); ?></td>
					<td class="woo-vou-history-td"><?php echo $product_data['qty']; ?></td>
				</tr><?php 
			}
		}?>
	</table><?php
	do_action( 'woo_vou_after_history_table', $codes_item_meta );
}