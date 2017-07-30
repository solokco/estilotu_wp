<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Gift Notification
 * 
 * Type : Plain
 * 
 * $first_name			: displays the first name of customer
 * $last_name			: displays the last name of customer
 * $recipient_name		: displays the recipient name
 * $voucher_link		: displays the voucher download link
 * $recipient_message	: displays the recipient message
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.4
 */

//echo $email_heading . "\n\n";

echo __( "Hello,", 'woovoucher' ) . "\n\n";

echo __( "Hi there. You've been sent a voucher!", 'woovoucher' ) . "\n\n";

echo $recipient_message . "\n\n";

echo sprintf( __( "You can find your voucher: %s", 'woovoucher' ), $voucher_link ) . "\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );