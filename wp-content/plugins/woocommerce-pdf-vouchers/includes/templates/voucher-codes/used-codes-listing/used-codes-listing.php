<?php

/**
 * Used Codes Listing
 * 
 * Template for Used Codes Listing
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.8.1
 **/

global $woo_vou_model;

//model class
$model = $woo_vou_model;

$prefix = WOO_VOU_META_PREFIX;

$page = isset( $_POST['paging'] ) ? $_POST['paging'] : '1';

$generatepdfurl_args = array(
	'woo-vou-voucher-gen-pdf'	=> '1',
	'woo_vou_action'			=> 'used',
);
if( isset( $_GET['woo_vou_start_date'] ) && !empty( $_GET['woo_vou_start_date'] ) )
	$generatepdfurl_args['woo_vou_start_date']	= $_GET['woo_vou_start_date'];
if( isset( $_GET['woo_vou_end_date'] ) && !empty( $_GET['woo_vou_end_date'] ) )
	$generatepdfurl_args['woo_vou_end_date']	= $_GET['woo_vou_end_date'];
if( isset( $_GET['woo_vou_post_id'] ) && !empty( $_GET['woo_vou_post_id'] ) )
	$generatepdfurl_args['woo_vou_post_id']	= $_GET['woo_vou_post_id'];
	
$generatepdfurl = add_query_arg( $generatepdfurl_args );

?>

<!-- Get generate pdf button -->
<a href="<?php echo $generatepdfurl; ?>" id="woo-vou-pdf-btn" class="woo-vou-btn-front woo-vou-grnpdf-btn" title="<?php echo __('Generate PDF','woovoucher'); ?>"><?php echo __( 'Generate PDF', 'woovoucher' ); ?></a>

<!-- hidden data to get in ajax -->
<input type="hidden" id="woo_vou_hid_start_date" value="<?php echo isset( $_GET['woo_vou_start_date'] ) ? $_GET['woo_vou_start_date'] : ''; ?>" />
<input type="hidden" id="woo_vou_hid_end_date" value="<?php echo isset( $_GET['woo_vou_end_date'] ) ? $_GET['woo_vou_end_date'] : ''; ?>" />
<input type="hidden" id="woo_vou_product_filter" value="<?php echo isset( $_GET['woo_vou_post_id'] ) ? $_GET['woo_vou_post_id'] : ''; ?>">
<input type="hidden" class="wpw-fp-bulk-paging" value="<?php echo $page; ?>" />

<div class="woo-vou-clear" ></div>

<!-- Table formation starts -->
<table class="woo-vou-used-codes-table wp-list-table widefat fixed striped purchasedvous">
	<thead>
		<tr class="woo-vou-used-codes-table-row-head">
				<?php 
						//do action to add header title of orders list before
						do_action('woo_vou_used_codes_header_before');
				?>
				<th width="12%" scope="col" id='code' class="manage-column column-code column-primary sortable asc"><?php _e( 'Voucher Code','woovoucher' );?></th>
				<th width="18%" scope="col" id='product_info' class="manage-column column-product_info"><?php _e( 'Product Information','woovoucher' );?></th>
				<th width="25%" scope="col" id='buyers_info' class="manage-column column-buyers_info"><?php _e( "Buyer's Information",'woovoucher' );?></th>
				<th width="25%" scope="col" id='order_info' class="manage-column column-order_info"><?php _e( 'Order Information','woovoucher' );?></th>
				<th width="20%" scope="col" id='order_info' class="manage-column column-order_info"><?php _e( 'Redeem Information','woovoucher' );?></th>
				<?php 
						//do action to add header title of orders list after
						do_action('woo_vou_used_codes_header_after');
				?>
		</tr>
	</thead>
	
	<tbody id="the-list" data-wp-lists='list:purchasedvou'>
	<?php	
	if( empty( $result_arr ) ) {
		echo "<tr><td colspan='5' class='woo-vou-no-record-message'>" . __( 'No used voucher codes yet.','woocommerce' ) . "</td></tr>";
	} else {
		foreach ( $result_arr as $key => $value ) {		
		?>
			<tr class="woo-vou-used-codes-row-body">
				<?php 
						//do action to add row for orders list before
						do_action( 'woo_vou_used_codes_row_before' ); 
				?>
				<td class="woo-vou-used-code-list-codes code column-code has-row-actions column-primary"  data-colname="Voucher Code"><?php 	echo $value['code']; ?> <button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td>
				<td class='product_info column-product_info' data-colname="Product Information"><?php	echo $model->woo_vou_display_product_info_html( $value['order_id'], $value['code'] );		?></td>
				<td class='product_info column-product_info' data-colname="Buyer's Information"><?php	echo $model->woo_vou_display_buyer_info_html( $value['buyers_info'] ); ?></td>
				<td class='product_info column-product_info' data-colname="Order Information"><?php	echo $model->woo_vou_display_order_info_html( $value['order_id'] ); ?></td>
				<td class='product_info column-product_info' data-colname="Redeem Information"><?php	echo $model->woo_vou_display_redeem_info_html( $value['ID'], $value['order_id'] ); ?></td>
				<?php 
						//do action to add row for orders list after
						do_action( 'woo_vou_used_codes_row_after' ); 
				?>
			</tr>
	<?php	} }  ?>
	</tbody>
	<tfoot>
		<tr class="woo-vou-used-codes-row-foot">
			<?php 
					//do action to add row in footer before
					do_action('woo_vou_used_codes_footer_before');
			?>
			<th width="12%" scope="col" id='code' class="manage-column column-code column-primary sortable asc"><?php _e( 'Voucher Code','woovoucher' );?></th>
			<th width="18%" scope="col" id='product_info' class="manage-column column-product_info"><?php _e( 'Product Information','woovoucher' );?></th>
			<th width="25%" scope="col" id='buyers_info' class="manage-column column-buyers_info"><?php _e( "Buyer's Information",'woovoucher' );?></th>
			<th width="25%" scope="col" id='order_info' class="manage-column column-order_info"><?php _e( 'Order Information','woovoucher' );?></th>
			<th width="20%" scope="col" id='order_info' class="manage-column column-order_info"><?php _e( 'Redeem Information','woovoucher' );?></th>
			<?php 
					//do action to add row in footer after
					do_action('woo_vou_used_codes_footer_after');
			?>
		</tr>
	</tfoot>
</table>
<!-- Code for paging starts -->
<div class="woo-vou-paging woo-vou-used-codes-paging">
	<div id="woo-vou-tablenav-pages" class="woo-vou-tablenav-pages">
		<?php echo $paging->getOutput(); ?>
	</div><!--.woo-vou-tablenav-pages-->
</div>
<!-- Code for paging ends -->
<div class="woo-vou-used-codes-loader woo-vou-usedcodes-loader">
	<img src="<?php echo WOO_VOU_IMG_URL;?>/loader.gif"/>
</div><!--.woo-vou-usedcodes-loader-->