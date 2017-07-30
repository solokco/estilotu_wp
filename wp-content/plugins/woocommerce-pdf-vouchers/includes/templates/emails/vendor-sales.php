<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Vendor Sale Notification
 * 
 * Type : HTML
 * 
 * $site_name		: displays the site name
 * $product_title	: displays the product title
 * $voucher_code	: displays the voucher code
 * $product_price	: displays the product price
 * $order_id		: displays the order id
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.4
 */
?>

<?php do_action( 'woocommerce_email_header', $email_heading ); ?>

<p><?php _e( 'Hello,', 'woovoucher' ); ?></p>

<p><?php echo sprintf( __( 'A new sale on %s', 'woovoucher' ), $site_name );?></p>

<p><?php echo sprintf( __( 'Product Title: %s', 'woovoucher' ), $product_title );?></p>

<p><?php echo sprintf( __( 'Voucher Code: %s', 'woovoucher' ), $voucher_code );?></p>

<p><?php //echo sprintf( __( 'You can find voucher: %s', 'woovoucher' ), $voucher_link );?></p>

<p><?php _e( 'Thank you', 'woovoucher' );?></p>

<?php do_action( 'woocommerce_email_footer' ); ?>