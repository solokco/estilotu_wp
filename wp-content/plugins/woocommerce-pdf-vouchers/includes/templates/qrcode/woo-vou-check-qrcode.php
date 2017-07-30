<?php
/**
 * Check voucher code with Qrcode and Barcode
 * 
 * Handles to check voucher code with Qrcode and Barcode
 * 
 * Override this template by copying it to yourtheme/woocommerce/woocommerce-pdf-vouchers/qrcode/woo-vou-check-qrcode.php
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.7.1
 */

header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', FALSE);
header('Pragma: no-cache');
?>
<!doctype html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=3.0">
	<title><?php echo apply_filters( 'woo_vou_check_qrcode_page_title', __( 'Redeem voucher code', 'woovoucher' ) ); ?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo WOO_VOU_URL; ?>includes/css/woo-vou-check-qrcode.css">
</head>
<body><?php
	
	global $woo_vou_public;	
	$public = $woo_vou_public;
	$redeem = false;

	if( !empty( $redeem_response ) && $redeem_response == 'success' ) {

		echo "<div class='woo-vou-voucher-code-msg success'>" . __( 'Thank you for your business, voucher code submitted successfully.', 'woovoucher' ) . "</div>";
		unset( $_GET['woo_vou_code'] );
		unset( $_POST['voucode'] );
		$redeem = true;
	}

	//Check if the user is logged in.  If not, show the login form.
	if ( !is_user_logged_in() ) {

		$args = array(
		        'echo'		=> true,
		        'redirect'	=> add_query_arg( get_site_url(), $_SERVER["QUERY_STRING"] )
		);

		wp_login_form( $args );
	} else {

		if( !$redeem ) {

			foreach ( $voucodes as $voucode ) {

				$voucode = trim( $voucode ); // remove spaces from voucher code

				// assign voucher code to $_POST variable.
				// Needed because $_POST['voucode'] used in function woo_vou_check_voucher_code()
				$_POST['voucode'] = $voucode;

				// Check voucher code and get result
				$voucher_data = $public->woo_vou_check_voucher_code();

				if( !empty( $voucher_data ) ) {

					if( empty( $voucode ) ) {

						echo "<div class='woo-vou-voucher-code-msg error'>" . __( 'Please enter voucher code.', 'woovoucher' ) . "</div>";
					} else if( !empty( $voucher_data['success'] ) ) { ?>
						<form class="woo-vou-check-vou-code-form" method="post" action="">
							<input type="hidden" name="voucode" value="<?php echo $voucode; ?>" />
							<table class="form-table woo-vou-check-code">
								<?php 
									/**
									 * Add do action to add custom code by other plugins
									 */
									do_action( 'woo_vou_check_qrcode_top' ); 
								?>
								<tr>
									<td>
										<?php
											$class = 'success';
											if( isset( $voucher_data['expire'] ) && $voucher_data['expire'] == true )
												$class = 'error';
										?>									
										<div class="woo-vou-voucher-code-msg <?php echo $class; ?>">
											<span><?php echo $voucher_data['success']; ?></span>
										</div>
										<?php echo $voucher_data['product_detail']; ?>
									</td>
								</tr>
								<?php if( isset( $voucher_data['expire'] ) && $voucher_data['expire'] == true ) { } else { ?>
									<tr class="woo-vou-voucher-code-submit-wrap">
										<td>
											<?php 
												echo apply_filters('woo_vou_voucher_code_submit',
													'<input type="submit" id="woo_vou_voucher_code_submit" name="woo_vou_voucher_code_submit" class="button-primary" value="' . __( "Redeem", "woovoucher" ) . '"/>'
												);
											?>
											<div class="woo-vou-loader woo-vou-voucher-code-submit-loader"><img src="<?php echo WOO_VOU_IMG_URL;?>/ajax-loader.gif"/></div>
										</td>
									</tr>									
								<?php } ?>
								<?php 
									/**
									 * Add do action to add custom code by other plugins
									 */
									do_action( 'woo_vou_check_qrcode_bottom' ); 
								?>
							</table>
						</form><?php

					} else if( !empty( $voucher_data['error'] ) ) {
						echo "<div class='woo-vou-voucher-code-msg error' style='clear:both'>" . $voucher_data['error'] . "</div>";
					} else if( !empty( $voucher_data['used'] ) ) {
						echo "<div class='woo-vou-voucher-code-msg error' style='clear:both'>" . $voucher_data['used'] . "</div>";
					}
				}
			} // End of foreach
		} // End of if $redeem
	}?>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
    <script src="<?php echo WOO_VOU_URL; ?>includes/js/woo-vou-check-qrcode.js"></script>
</body>
</html><?php
exit();