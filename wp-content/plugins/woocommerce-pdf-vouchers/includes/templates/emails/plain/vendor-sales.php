<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Vendor Sale Notification
 * 
 * Type : Plain
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

//echo $email_heading . "\n\n";

echo __( "Hello,", 'woovoucher' ) . "\n\n";

echo sprintf( __( "A new sale on %s", 'woovoucher' ), $site_name ) . "\n\n";

echo sprintf( __( "Product Title: %s", 'woovoucher' ), $product_title ) . "\n\n";

echo sprintf( __( "Voucher Code: %s", 'woovoucher' ), $voucher_code ) . "\n\n";

//echo sprintf( __( 'You can find voucher: %s', 'woovoucher' ), $voucher_link ) . "\n\n";

echo __( "Thank you", 'woovoucher' );

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );