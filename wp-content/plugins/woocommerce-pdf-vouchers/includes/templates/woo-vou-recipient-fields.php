<?php

/**
 * Recipient Fields Template
 * 
 * Handles to load Recipient Fields template
 * 
 * Override this template by copying it to yourtheme/woocommerce/woocommerce-pdf-vouchers/woo-vou-recipient-fields.php
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.5.3
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product;

//Get Prefix
$prefix		= WOO_VOU_META_PREFIX;

?>

<div class="woo-vou-fields-wrapper<?php echo $product->is_type( 'variation' ) ? '-variation' : ''; ?>" id="woo-vou-fields-wrapper-<?php echo $variation_id; ?>">
	<table cellspacing="0" class="woo-vou-recipient-fields">
	  <tbody><?php 
		
	  	if( $enable_pdf_template_selection == 'yes' ) {
			
	  		$preview_images    				= array();
	  		$pdf_template_selection_label	= !empty( $pdf_template_selection_label ) ? $pdf_template_selection_label : __( 'Voucher Template' , 'woovoucher' ); 
	  		$pdf_template_selection_label  .= '<span class="woo-vou-gift-field-required"> *</span>'; ?>
			<tr>
				<td class="label" colspan="2">
					<label for="pdf_template_selection-<?php echo $variation_id; ?>"><?php echo $pdf_template_selection_label; ?></label>
				</td>
			</tr>
			<tr>
				<td class="value" colspan="2">
					<div class="woo-vou-preview-template-img-wrap">
						<?php
						if( !empty( $product_templates ) ){
							foreach( $product_templates as $key => $value ){
								
								$image = '';
								
								$image = wp_get_attachment_url( get_post_thumbnail_id( $value ) );
								
								if( empty( $image ) ){
									$image = WOO_VOU_IMG_URL.'/no-preview.png';
								}
						?>
								<img src="<?php echo $image; ?>" class="woo-vou-preview-template-img" data-id="<?php echo $value; ?>" title="<?php echo get_the_title( $value ); ?>">
						<?php
							}
						}
						?>
					</div>
					<input type="hidden" name="<?php echo $prefix.'pdf_template_selection['.$variation_id.']'; ?>" value="<?php echo $pdf_template_selection; ?>" class="woo-vou-preview-template-img-id">
				</td>
			</tr><?php
	  	}
	  	
	  	// Add Field after Template on Product Page
	  	do_action( 'woo_vou_add_field_on_product_page_after_template', $variation_id );
	  	
	  	if( $enable_recipient_name == 'yes' ) {
			
	  		$recipient_name_lable	= !empty( $recipient_name_lable ) ? $recipient_name_lable : __( 'Recipient Name' , 'woovoucher' );
	  		
	  		if( !empty( $recipient_name_required ) && $recipient_name_required == "yes" ) {
				$recipient_name_lable .= '<span class="woo-vou-gift-field-required"> *</span>';
			}
	  		$name_maxlength			= intval( $recipient_name_max_length ); ?>
			<tr>
				<td class="label">
					<label for="recipient_name-<?php echo $variation_id; ?>"><?php echo $recipient_name_lable; ?></label>
				</td>
				<td class="value">
					<input type="text" class="woo-vou-recipient-details" <?php if( !empty($name_maxlength) ) { echo 'maxlength="'.$name_maxlength.'"'; } ?> value="<?php echo $recipient_name; ?>" id="recipient_name-<?php echo $variation_id; ?>" name="<?php echo $prefix; ?>recipient_name[<?php echo $variation_id; ?>]">
				</td>
			</tr><?php
	  	}
	  	
	  	// Add Field after Name on Product Page
	  	do_action( 'woo_vou_add_field_on_product_page_after_name', $variation_id );
	  	
	  	if( $enable_recipient_email == 'yes' ) {
			
	  		$recipient_email_label = !empty( $recipient_email_label ) ? $recipient_email_label : __( 'Recipient Email' , 'woovoucher' );
	  		if( !empty( $recipient_email_required ) && $recipient_email_required == "yes" ) {
				$recipient_email_label .= '<span class="woo-vou-gift-field-required"> *</span>';
			}?>
			<tr>
				<td class="label">
					<label for="recipient_email-<?php echo $variation_id; ?>"><?php echo $recipient_email_label; ?></label>
				</td>
				<td class="value">
					<input type="text" class="woo-vou-recipient-details" value="<?php echo $recipient_email; ?>" id="recipient_email-<?php echo $variation_id; ?>" name="<?php echo $prefix; ?>recipient_email[<?php echo $variation_id; ?>]">
				</td>
			</tr><?php 
	  	}
	  	
	  	// Add Recipient field after email field
	  	do_action( 'woo_vou_add_field_on_product_page_after_email', $variation_id );
	  	
	  	if( $enable_recipient_message == 'yes' ) {
	
	  		$recipient_message_label	= !empty( $recipient_message_label ) ? $recipient_message_label : __( 'Message to Recipient' , 'woovoucher' );
	  		$msg_maxlength				= intval( $recipient_message_max_length );
	  		if( !empty( $recipient_message_required ) && $recipient_message_required == "yes" ) {
				$recipient_message_label .= '<span class="woo-vou-gift-field-required"> *</span>';
			}
	  		?>
			<tr>
				<td class="label">
					<label for="recipient_message-<?php echo $variation_id; ?>"><?php echo $recipient_message_label;?></label>
				</td>
				<td class="value">
					<textarea <?php if( !empty( $msg_maxlength ) ) { echo 'maxlength="'.$msg_maxlength.'"'; } ?> class="woo-vou-recipient-details" id="recipient_message-<?php echo $variation_id; ?>" name="<?php echo $prefix; ?>recipient_message[<?php echo $variation_id; ?>]"><?php echo $recipient_message; ?></textarea>
				</td>
			</tr><?php 
	  	}
	  	
	  	// Add recipient field on product page after message
	  	do_action( 'woo_vou_add_field_on_product_page_after_message', $variation_id );
	  	
	  	if( $enable_recipient_giftdate == 'yes' ) {
			
	  		$recipient_giftdate_label = !empty( $recipient_giftdate_label ) ? $recipient_giftdate_label : __( 'Recipient\'s Gift Date' , 'woovoucher' );
	  		if( !empty( $recipient_giftdate_required ) && $recipient_giftdate_required == "yes" ) {
				$recipient_giftdate_label .= '<span class="woo-vou-gift-field-required"> *</span>';
			}
	  		?>
			<tr>
				<td class="label">
					<label for="recipient_giftdate-<?php echo $variation_id; ?>"><?php echo $recipient_giftdate_label; ?></label>
				</td>
				<td class="value">
					<input type="text" class="woo-vou-recipient-details" value="<?php echo $recipient_giftdate; ?>" id="recipient_giftdate-<?php echo $variation_id; ?>" name="<?php echo $prefix; ?>recipient_giftdate[<?php echo $variation_id; ?>]">
				</td>
			</tr><?php 
	  	}
	  	
	  	// Add Recipient field after email field
	  	do_action( 'woo_vou_add_field_on_product_page_after_giftdate', $variation_id );
	  	
	  	?>
	  </tbody>
	</table>
</div>