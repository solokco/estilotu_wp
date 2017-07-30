<?php
/**
 * Handles the product variable meta HTML
 *
 * The html markup for the product variable
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

global $woo_vou_model;
	
$this->model			= $woo_vou_model;
$prefix					= WOO_VOU_META_PREFIX;

$variation_id 			= isset($variation->ID) ? $variation->ID : '';

if( empty( $variation_id) ) 
	$variation_id = isset( $variation['id'] ) ? $variation['id'] : '';

$woo_vou_variable_codes = get_post_meta( $variation_id, $prefix . 'codes', true ); // Getting voucher code

$voucher_data			= $this->model->woo_vou_get_vouchers(); // Getting All Voucher Templates
$woo_vou_pdf_template	= get_post_meta( $variation_id, $prefix . 'pdf_template', true ); // Getting Selected Voucher Template
?>

<div class="show_if_variation_downloadable" style="display:none;">
	<p>
		<label class="woo-vou-pdf-template-variation-label"><?php _e('PDF Template', 'woovoucher'); ?></label>
		
		<select style="width:180px;" class="chosen_select" name="<?php echo $prefix; ?>variable_pdf_template[<?php echo $loop; ?>]" id="woo-vou-pdf-variable-pdf-template-<?php echo $loop; ?>">
			<option value=""><?php _e('Please Select', 'woovoucher'); ?></option>
				<?php foreach ( $voucher_data as $voucher ) { ?>
					<option value="<?php echo $voucher['ID']; ?>" <?php if( $woo_vou_pdf_template == $voucher['ID'] ) echo "selected=selected"; ?>><?php echo $voucher['post_title']; ?></option>
				<?php } ?>
		</select>
	</p>
	
	<p>
		<label><?php _e('Voucher Codes', 'woovoucher'); ?>: <a data-tip="<?php _e( 'If you have a list of Voucher Codes you can copy and paste them in to this option. Make sure, that they are comma separated.', 'woovoucher' ); ?>" class="tips" href="#">[?]</a></label>
		<textarea style="width:100%" rows="2" placeholder="" id="woo-vou-variable-codes-<?php echo $loop; ?>" name="<?php echo $prefix; ?>variable_codes[<?php echo $loop; ?>]" class="short"><?php echo $woo_vou_variable_codes; ?></textarea>
	</p>
</div>