<?php
/**
 * Expired/Upcoming Product Template
 * 
 * Handles to load expired/upcoming product template
 * 
 * Override this template by copying it to yourtheme/woocommerece-pdf-vouchers/expired/expired.php
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.4.0
 */
?>

<div class="woo-vou-product-expired">
	<?php
		if( $expired == 'upcoming' ) {
			echo __( 'Upcoming product', 'woovoucher' );
		} else {
			echo __( 'Expired product', 'woovoucher' );
		}
	?>
</div>