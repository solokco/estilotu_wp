<?php
/**
 * Purchased Voucher Code
 * 
 * The html markup for the purchased voucher code popup
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

global $woo_vou_model;

$model = $woo_vou_model;

$prefix = WOO_VOU_META_PREFIX;

$postid =  apply_filters( 'woo_vou_edit_product_id', $postid, get_post( $postid ) );

// Get Voucher Details by post id
$purchasedcodes = $model->woo_vou_get_purchased_codes_by_product_id( $postid );?>

<div class="woo-vou-popup-content woo-vou-purchased-codes-popup">
	<div class="woo-vou-header">
		<div class="woo-vou-header-title"><?php echo __( 'Purchased Voucher Codes', 'woovoucher' );?></div>
		<div class="woo-vou-popup-close">
			<a href="javascript:void(0);" class="woo-vou-close-button">
				<img src="<?php echo WOO_VOU_URL .'includes/images/tb-close.png';?>" alt="<?php echo __( 'Close','woovoucher' );?>">
			</a>
		</div>
	</div><?php
	
	$generatpdfurl = add_query_arg( array( 
											'woo-vou-used-gen-pdf'	=>	'1',
											'product_id'			=>	$postid
										));
	$exportcsvurl = add_query_arg( array( 
											'woo-vou-used-exp-csv'	=>	'1',
											'product_id'			=>	$postid
										));

	//purchase codes table columns
	$purchasedcodes_columns	= apply_filters( 'woo_vou_product_purchasedcodes_columns', array(
													'voucher_code'	=> __( 'Voucher Code', 'woovoucher' ),
													'buyer_name'	=> __( 'Buyer\'s Name', 'woovoucher' ),
													'order_date'	=> __( 'Order Date', 'woovoucher' ),
													'order_id'		=> __( 'Order ID', 'woovoucher' )
												), $postid );?>
	
	<div class="woo-vou-popup used-codes">
		<div>
			<a href="<?php echo $exportcsvurl;?>" id="woo-vou-export-csv-btn" class="button-secondary" title="<?php echo __( 'Export CSV', 'woovoucher' );?>"><?php echo __('Export CSV','woovoucher');?></a>
			<a href="<?php echo $generatpdfurl;?>" id="woo-vou-pdf-btn" class="button-secondary" title="<?php echo __( 'Generate PDF', 'woovoucher' );?>"><?php echo __( 'Generate PDF', 'woovoucher' );?></a>
		</div>
		
		<table class="form-table" border="1">
			<tbody>
				<tr><?php
				
					if( !empty( $purchasedcodes_columns ) ) {
						foreach ( $purchasedcodes_columns as $column_key => $column ) {?>
							
							<th scope="row"><?php echo $column;?></th><?php
						}
					}?>
				</tr><?php 
				if( !empty( $purchasedcodes ) &&  count( $purchasedcodes ) > 0 ) { 
					
					foreach ( $purchasedcodes as $key => $voucodes_data ) { 
						
						//voucher order id
						$orderid 		= $voucodes_data['order_id'];
						
						if( !empty( $purchasedcodes_columns ) ) {?>
							<tr><?php 
							foreach ( $purchasedcodes_columns as $column_key => $column ) {
								
								$column_value = '';
								
								switch( $column_key ) {
									
									case 'voucher_code' : // voucher code purchased
										$column_value	= $voucodes_data['vou_codes'];
										break;
									case 'buyer_name' : // buyer's name who has purchased voucher code
										$column_value	= $voucodes_data['buyer_name'];
										break;
									case 'order_date' : // voucher order date
										$orderdate 		= $voucodes_data['order_date'];
										$column_value 	= !empty( $orderdate ) ? $model->woo_vou_get_date_format( $orderdate, true ) : '';
										break;
									case 'order_id' : 
										$column_value 	= $orderid;
										break;
								}
								
								$column_value = apply_filters( 'woo_vou_product_purchasedcodes_column_value', $column_value, $voucodes_data, $postid );?>
								
								<td><?php echo $column_value;?></td><?php 
							}?>
							</tr><?php
						}
					}
				} else { ?>
					<tr>
						<td colspan="4"><?php echo __( 'No voucher codes purchased yet.','woovoucher' );?></td>
					</tr><?php 
				}?>
			</tbody>
		</table>
	</div><!--.woo-vou-popup-->
</div><!--.woo-vou-purchased-codes-popup-->
<div class="woo-vou-popup-overlay woo-vou-purchased-codes-popup-overlay"></div>