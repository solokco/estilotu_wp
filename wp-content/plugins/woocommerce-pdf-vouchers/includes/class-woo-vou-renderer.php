<?php 

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Renderer Class
 *
 * To handles some small HTML content for front end and backend
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 1.0.0
 */
class WOO_Vou_Renderer {
	
	public $mainmodel, $model;
	
	public function __construct() {
		
		global $woo_vou_model;
		$this->model = $woo_vou_model;
	}

	/**
	 * Add Popup For Purchased Codes 
	 * 
	 * Handels to show purchased voucher codes popup
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_purchased_codes_popup( $postid ) {
		
		ob_start();
		include_once( WOO_VOU_ADMIN . '/forms/woo-vou-purchased-codes-popup.php' ); // Including purchased voucher code file
		$html = ob_get_clean();
		
		return $html;
	}

	/**
	 * Add Popup For Used Codes
	 * 
	 * Handels to show used voucher codes popup
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.1.0
	 */
	public function woo_vou_used_codes_popup( $postid ) {
		
		ob_start();
		include_once( WOO_VOU_ADMIN . '/forms/woo-vou-used-codes-popup.php' ); // Including used voucher code file
		$html = ob_get_clean();
		
		return $html;
	}

	/**
	 * Function For ajax edit of all controls
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_page_builder() {
		
		global $wp_version;
							
		$controltype	= $_POST['type'];
		$bgcolor		= isset( $_POST['bgcolor'] ) ? $_POST['bgcolor'] : '';
		$fontcolor		= isset( $_POST['fontcolor'] ) ? $_POST['fontcolor'] : '';
		$fontsize		= isset( $_POST['fontsize'] ) ? $_POST['fontsize'] : '';
		$textalign		= isset( $_POST['textalign'] ) ? $_POST['textalign'] : '';
		$codetextalign	= isset( $_POST['codetextalign'] ) ? $_POST['codetextalign'] : '';
		$codeborder		= isset( $_POST['codeborder'] ) ? $_POST['codeborder'] : '';
		$codecolumn		= isset( $_POST['codecolumn'] ) ? $_POST['codecolumn'] : '';
		$vouchercodes	= isset( $_POST['vouchercodes'] ) ? $_POST['vouchercodes'] : '';
		
		$qrcodewidth	= isset( $_POST['qrcodewidth'] ) ? $_POST['qrcodewidth'] : '';
		$qrcodeheight	= isset( $_POST['qrcodeheight'] ) ? $_POST['qrcodeheight'] : '';
		$qrcodecolor	= isset( $_POST['qrcodecolor'] ) ? $_POST['qrcodecolor'] : '';
		$qrcodetype		= isset( $_POST['qrcodetype'] ) ? $_POST['qrcodetype'] : '';
		$qrcodeborder	= isset( $_POST['qrcodeborder'] ) ? $_POST['qrcodeborder'] : '';
		$qrcoderesponse	= isset( $_POST['qrcoderesponse'] ) ? $_POST['qrcoderesponse'] : '';
		
		$barcodewidth	= isset( $_POST['barcodewidth'] ) ? $_POST['barcodewidth'] : '';
		$barcodeheight	= isset( $_POST['barcodeheight'] ) ? $_POST['barcodeheight'] : '';
		$barcodecolor	= isset( $_POST['barcodecolor'] ) ? $_POST['barcodecolor'] : '';
		$barcodetype	= isset( $_POST['barcodetype'] ) ? $_POST['barcodetype'] : '';
		$barcodeborder	= isset( $_POST['barcodeborder'] ) ? $_POST['barcodeborder'] : '';
		
		if( empty($qrcodecolor) ){
			$qrcodecolor = '#000000';
		}
		
		if( empty($barcodecolor) ){
			$barcodecolor = '#000000';
		}

		$align_data = array(
								'left' 		=> __( 'Left', 'woovoucher' ),
								'center'	=> __( 'Center', 'woovoucher' ),
								'right' 	=> __( 'Right', 'woovoucher' ),
							);
							
		$qrcodes_type_data = array(
								'vertical'	 => __( 'Vertical', 'woovoucher' ),
								'horizontal' => __( 'Horizontal', 'woovoucher' ),
							);
						
		$qrcode_scan_response_data = array(
										'url'  => __( 'Redeem URL', 'woovoucher' ),
										'code' => __( 'Voucher Code', 'woovoucher' ),
									);
							
		$barcodes_type_data = array(
								'vertical'	 => __( 'Vertical', 'woovoucher' ),
								'horizontal' => __( 'Horizontal', 'woovoucher' ),
							);

		$border_data = array( '1', '2', '3' );

		$column_data = array(
								'1' 	=> __( '1 Column', 'woovoucher' ),
								'2'		=> __( '2 Column', 'woovoucher' ),
								'3' 	=> __( '3 Column', 'woovoucher' ),
							);

		if( $controltype == 'textblock' ) {

			$editorid = $_POST['editorid'];
			ob_start();
			echo '	<table class="form-table">
						<tbody>';
			echo '<tr>
								<th scope="row">
									' . __( 'Title', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';			
									$settings = array( 
															'textarea_name' => $editorid,
															'media_buttons'=> false,
															'quicktags'=> true,
															'teeny' => false,
															'editor_class' => 'content pbrtextareahtml'
														);
									wp_editor( '', $editorid, $settings );	
			echo '					<span class="description">' . sprintf( __( 'Enter a voucher code title.', 'woovoucher' ), '<code>{codes}</code>' ) . '</span>
								</td>
							</tr>';
							
			echo '			<tr>
								<th scope="row">
									' . __( 'Title Background Color', 'woovoucher' ) . '
								</th>
								<td>';
							
								if( $wp_version >= 3.5 ) {
									
									echo '<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_color_box" data-default-color="" />';
									
								} else {
									echo '<div style="position:relative;">
												<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_edit_bg_color" />
												<input type="button" class="woo_vou_color_box button-secondary" value="'.__('Select Color','woovoucher').'">
												<div class="colorpicker" style="z-index:100; position:absolute; display:none;"></div>
											</div>';
								}
			echo '					<br /><span class="description">' . __( 'Select a background color for the text box.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'Title Font Size', 'woovoucher' ) . '
								</th>
								<td>
									<input type="text" value="' . $fontsize . '" id="woo_vou_edit_font_size" name="woo_vou_edit_font_size" class="woo_vou_font_size_box small-text" maxlength="2" />
									' . __( 'pt', 'woovoucher' ) . '<br /><span class="description">' . __( 'Enter a font size for the text box.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
										
			echo '			<tr>
								<th scope="row">
									' . __( 'Title Alignment', 'woovoucher' ) . '
								</th>
								<td>
									<select id="woo_vou_edit_text_align" name="woo_vou_edit_text_align" class="woo_vou_text_align_box">';
									foreach ( $align_data as $align_key => $align_value ) {
										echo '<option value="' . $align_key . '" ' . selected( $textalign, $align_key, false ) . '>' . $align_value . '</option>';
									}
			echo '					</select>
									<br /><span class="description">' . __( 'Select text align for the voucher code title.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '<tr>
								<th scope="row">
									' . __( 'Voucher Code', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';			
									$settings = array( 
															'textarea_name' => $editorid . 'codes',
															'media_buttons'=> false,
															'quicktags'=> true,
															'teeny' => false,
															'editor_class' => 'content pbrtextareahtml'
														);
									wp_editor( '', $editorid . 'codes', $settings );	
			echo '					<span class="description">' . __( 'Enter your voucher codes content. The available tags are:' , 'woovoucher').' <br /> <code>{codes}</code> - '.__( 'displays the voucher code(s)', 'woovoucher' ) . '</span>
								</td>
							</tr>';
							
			echo '			<tr>
								<th scope="row">
									' . __( 'Voucher Code Border', 'woovoucher' ) . '
								</th>
								<td>
									<select id="woo_vou_edit_code_border" name="woo_vou_edit_code_border" class="woo_vou_code_border_box">
										<option value="">' . __( 'Select', 'woovoucher' ) . '</option>';
									foreach ( $border_data as $border ) {
										echo '<option value="' . $border . '" ' . selected( $codeborder, $border, false ) . '>' . $border . '</option>';
									}
			echo '					</select>
									<br /><span class="description">' . __( 'Select border for the voucher code.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
								
			echo '			<tr>
								<th scope="row">
									' . __( 'Voucher Code Alignment', 'woovoucher' ) . '
								</th>
								<td>
									<select id="woo_vou_edit_code_text_align" name="woo_vou_edit_code_text_align" class="woo_vou_code_text_align_box">';
									foreach ( $align_data as $align_key => $align_value ) {
										echo '<option value="' . $align_key . '" ' . selected( $codetextalign, $align_key, false ) . '>' . $align_value . '</option>';
									}
			echo '					</select>
									<br /><span class="description">' . __( 'Select text align for the voucher code.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '		</tbody>
					</table>';
			
			$html = ob_get_contents();
			ob_end_clean();
			
		} else if($controltype == 'message') {
			
			$editorid = $_POST['editorid'];
			ob_start();
			echo '	<table class="form-table">
						<tbody>';
							
			echo '			<tr>
								<th scope="row">
									' . __( 'Background Color', 'woovoucher' ) . '
								</th>
								<td>';
							
								if( $wp_version >= 3.5 ) {
									
									echo '<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_color_box" data-default-color="" />';
									
								} else {
									echo '<div style="position:relative;">
												<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_edit_bg_color" />
												<input type="button" class="woo_vou_color_box button-secondary" value="'.__('Select Color','woovoucher').'">
												<div class="colorpicker" style="z-index:100; position:absolute; display:none;"></div>
											</div>';
								}
			echo '					<br /><span class="description">' . __( 'Select a background color for the text box.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
										
			echo '			<tr>
								<th scope="row">
									' . __( 'Content', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';			
									$settings = array( 
															'textarea_name' => $editorid,
															'media_buttons'=> false,
															'quicktags'=> true,
															'teeny' => false,
															'editor_class' => 'content pbrtextareahtml'
														);
									wp_editor( '', $editorid, $settings );	
			echo '					<span class="description">' . __( 'Enter your content. The available tags are:' , 'woovoucher' ). ' <br /><code>{redeem}</code> -'. __( 'displays the voucher redeem instruction', 'woovoucher' ) . '</span>
								</td>
							</tr>
						</tbody>
					</table>';
				
			$html = ob_get_contents();
			ob_end_clean();
			
		} else if( $controltype == 'expireblock' ) {
			
			$editorid = $_POST['editorid'];
			ob_start();
			echo '	<table class="form-table">
						<tbody>';
							
			echo '			<tr>
								<th scope="row">
									' . __( 'Background Color', 'woovoucher' ) . '
								</th>
								<td>';
									
									if( $wp_version >= 3.5 ) {
										
										echo '<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_color_box" data-default-color="" />';
										
									} else {
										echo '<div style="position:relative;">
													<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_edit_bg_color" />
													<input type="button" class="woo_vou_color_box button-secondary" value="'.__('Select Color','woovoucher').'">
													<div class="colorpicker" style="z-index:100; position:absolute; display:none;"></div>
												</div>';
									}
			echo '					<br /><span class="description">' . __( 'Select a background color for the text box.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'Content', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';
				
									$settings = array('textarea_name' => $editorid, 'media_buttons'=> false,'quicktags'=> true, 'teeny' => false , 'editor_class' => 'content pbrtextareahtml');
									wp_editor('',$editorid,$settings);
			
			echo '					<span class="description">' . __( 'Enter your content. The available tags are:' , 'woovoucher').' <br /><code>{expiredate}</code> - '.__( 'displays the voucher expire date', 'woovoucher' ) . ' <br /><code>{expiredatetime}</code> - '.__( 'displays the voucher expire date & time', 'woovoucher' ) . '</span>
								</td>
							</tr>
						</tbody>
					</table>';
			
			$html = ob_get_contents();
			ob_end_clean();
			
		} else if( $controltype == 'venaddrblock' ) {
			
			$editorid = $_POST['editorid'];
			ob_start();
			echo '	<table class="form-table">
						<tbody>';
							
			echo '			<tr>
								<th scope="row">
									' . __( 'Background Color', 'woovoucher' ) . '
								</th>
								<td>';
							
								if( $wp_version >= 3.5 ) {
									
									echo '<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_color_box" data-default-color="" />';
									
								} else {
									echo '<div style="position:relative;">
												<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_edit_bg_color" />
												<input type="button" class="woo_vou_color_box button-secondary" value="'.__('Select Color','woovoucher').'">
												<div class="colorpicker" style="z-index:100; position:absolute; display:none;"></div>
											</div>';
								}
			echo '					<br /><span class="description">' . __( 'Select a background color for the text box.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
										
			echo '			<tr>
								<th scope="row">
									' . __( 'Content', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';
				
									$settings = array('textarea_name' => $editorid, 'media_buttons'=> false,'quicktags'=> true, 'teeny' => false , 'editor_class' => 'content pbrtextareahtml');
									wp_editor('',$editorid,$settings);
			
			echo '					<span class="description">' . __( 'Enter your content. The available tags are:' , 'woovoucher').' <br /> <code>{vendoraddress}</code> - '. __( 'displays the vendor\' address', 'woovoucher' ) . '</span>
								</td>
							</tr>
						</tbody>
					</table>';
			
			$html = ob_get_contents();
			ob_end_clean();
			
		} else if( $controltype == 'siteurlblock' ) {
			
			$editorid = $_POST['editorid'];
			ob_start();
			echo '	<table class="form-table">
						<tbody>';
							
			echo '			<tr>
								<th scope="row">
									' . __( 'Background Color', 'woovoucher' ) . '
								</th>
								<td>';
							
								if( $wp_version >= 3.5 ) {
									
									echo '<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_color_box" data-default-color="" />';
									
								} else {
									echo '<div style="position:relative;">
												<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_edit_bg_color" />
												<input type="button" class="woo_vou_color_box button-secondary" value="'.__('Select Color','woovoucher').'">
												<div class="colorpicker" style="z-index:100; position:absolute; display:none;"></div>
											</div>';
								}
			echo '					<br /><span class="description">' . __( 'Select a background color for the text box.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
										
			echo '			<tr>
								<th scope="row">
									' . __( 'Content', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';
				
									$settings = array('textarea_name' => $editorid, 'media_buttons'=> false,'quicktags'=> true, 'teeny' => false , 'editor_class' => 'content pbrtextareahtml');
									wp_editor('',$editorid,$settings);
			
			echo '					<span class="description">' . __( 'Enter your content. The available tags are:', 'woovoucher').' <br /><code>{siteurl}</code> - '.__( 'displays the website url', 'woovoucher' ). '</span>
								</td>
							</tr>
						</tbody>
					</table>';
			
			$html = ob_get_contents();
			ob_end_clean();
			
		} else if( $controltype == 'locblock' ) {
			
			$editorid = $_POST['editorid'];
			ob_start();
			echo '	<table class="form-table">
						<tbody>';
							
			echo '			<tr>
								<th scope="row">
									' . __( 'Background Color', 'woovoucher' ) . '
								</th>
								<td>';
							
								if( $wp_version >= 3.5 ) {
									
									echo '<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_color_box" data-default-color="" />';
									
								} else {
									echo '<div style="position:relative;">
												<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_edit_bg_color" />
												<input type="button" class="woo_vou_color_box button-secondary" value="'.__('Select Color','woovoucher').'">
												<div class="colorpicker" style="z-index:100; position:absolute; display:none;"></div>
											</div>';
								}
			echo '					<br /><span class="description">' . __( 'Select a background color for the text box.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
										
			echo '			<tr>
								<th scope="row">
									' . __( 'Content', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';
				
									$settings = array('textarea_name' => $editorid, 'media_buttons'=> false,'quicktags'=> true, 'teeny' => false , 'editor_class' => 'content pbrtextareahtml');
									wp_editor('',$editorid,$settings);
			
			echo '					<span class="description">' . __( 'Enter your content. The available tags are:' , 'woovoucher').' <br /><code>{location}</code> - '.__( 'displays the voucher location', 'woovoucher' ) . '</span>
								</td>
							</tr>
						</tbody>
					</table>';
			
			$html = ob_get_contents();
			ob_end_clean();
			
		} else if( $controltype == 'customblock' ) {
			
			$editorid = $_POST['editorid'];
			ob_start();
			echo '	<table class="form-table">
						<tbody>';
							
			echo '			<tr>
								<th scope="row">
									' . __( 'Background Color', 'woovoucher' ) . '
								</th>
								<td>';
							
								if( $wp_version >= 3.5 ) {
									
									echo '<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_color_box" data-default-color="" />';
									
								} else {
									echo '<div style="position:relative;">
												<input type="text" value="' . $bgcolor . '" id="woo_vou_edit_bg_color" name="woo_vou_edit_bg_color" class="woo_vou_edit_bg_color" />
												<input type="button" class="woo_vou_color_box button-secondary" value="'.__('Select Color','woovoucher').'">
												<div class="colorpicker" style="z-index:100; position:absolute; display:none;"></div>
											</div>';
								}
			echo '					<br /><span class="description">' . __( 'Select a background color for the text box.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
										
			echo '			<tr>
								<th scope="row">
									' . __( 'Content', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';
				
									$settings = array('textarea_name' => $editorid, 'media_buttons'=> true,'quicktags'=> true, 'teeny' => false , 'editor_class' => 'content pbrtextareahtml');
									wp_editor('',$editorid,$settings);
			
			echo '					<span class="description">' . sprintf( __( 'Enter your custom content. You can find %ssupported shortcodes%s list' , 'woovoucher'), '<strong>', '</strong>' );
			echo '					<a href="http://wpweb.co.in/documents/woocommerce-pdf-vouchers/shortcodes-support/" target="_blank">' . __( 'here', 'woovoucher' ) . '</a>';
			echo ' 					</span>
								</td>
							</tr>
						</tbody>
					</table>';
			
			$html = ob_get_contents();
			ob_end_clean();
			
		} else if( $controltype == 'qrcodeblock' ) {
			
			$editorid = $_POST['editorid'];
			ob_start();
			echo '	<table class="form-table">
						<tbody>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'Content', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';
				
									$settings = array('textarea_name' => $editorid, 'media_buttons'=> true,'quicktags'=> true, 'teeny' => false , 'editor_class' => 'content pbrtextareahtml');
									wp_editor('',$editorid,$settings);
			
			echo '					<span class="description">' . __( 'Enter your custom content. The available tags are:' , 'woovoucher')
										.'<br /><code>{qrcode}</code> - '.__( 'displays single QR Code for multiple voucher code(s).', 'woovoucher' )
									. '</span>
								</td>
							</tr>';
			echo '			<tr>
								<th scope="row">
									' . __( 'QR Code Width', 'woovoucher' ) . '
								</th>
								<td>
									<input type="text" value="' . $qrcodewidth . '" id="woo_vou_edit_qrcode_width" name="woo_vou_edit_qrcode_width" class="woo_vou_edit_qrcode_width small-text" maxlength="3" />&nbsp;<span>mm</span>';
			echo '					<br /><span class="description">' . __( 'Please enter qrcode width. Leave it blank to auto set width as per selected pdf size.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'QR Code Height', 'woovoucher' ) . '
								</th>
								<td>
									<input type="text" value="' . $qrcodeheight . '" id="woo_vou_edit_qrcode_height" name="woo_vou_edit_qrcode_height" class="woo_vou_edit_qrcode_height small-text" maxlength="3" />&nbsp;<span>mm</span>';
			echo '					<br /><span class="description">' . __( 'Please enter qrcode height. Leave it blank to auto set height as per selected pdf size.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'QR Code Color', 'woovoucher' ) . '
								</th>
								<td>';
							
								if( $wp_version >= 3.5 ) {
									
									echo '<input type="text" value="' . $qrcodecolor . '" id="woo_vou_edit_qrcode_color" name="woo_vou_edit_qrcode_color" class="woo_vou_color_box" data-default-color="#000000" />';
									
								} else {
									echo '<div style="position:relative;">
												<input type="text" value="' . $qrcodecolor . '" id="woo_vou_edit_qrcode_color" name="woo_vou_edit_qrcode_color" class="woo_vou_edit_qrcode_color" />
												<input type="button" class="woo_vou_color_box button-secondary" value="'.__('Select Color','woovoucher').'">
												<div class="colorpicker" style="z-index:100; position:absolute; display:none;"></div>
											</div>';
								}
			echo '					<br /><span class="description">' . __( 'Please select qrcode color.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'Enable Border', 'woovoucher' ) . '
								</th>
								<td>
									<input type="checkbox" value="1" id="woo_vou_edit_qrcode_border" name="woo_vou_edit_qrcode_border" class="woo_vou_edit_qrcode_border" '.checked(!empty($qrcodeborder), true, false).' />
								</td>
							</tr>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'Return Value', 'woovoucher' ) . '
								</th>
								<td>
									<select id="woo_vou_edit_qrcode_response" name="woo_vou_edit_qrcode_response" class="woo_vou_edit_qrcode_response">';
									foreach ( $qrcode_scan_response_data as $key => $value ) {
										echo '<option value="' . $key . '" ' . selected( $qrcoderesponse, $key, false ) . '>' . $value . '</option>';
									}
			echo '					</select>
									<br /><span class="description">' . __( 'Please select Return Value.<br><b>Redeem URL:</b> When you scan the QR code, it will return mobile friendly page URL where you get option to redeem the voucher code.<br><b>Voucher Code :</b> When you scan the QR code it will return actual Voucher Code.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '		</tbody>
					</table>';
			
			$html = ob_get_contents();
			ob_end_clean();
			
		} else if( $controltype == 'qrcodesblock' ) {
			
			$editorid = $_POST['editorid'];
			ob_start();
			echo '	<table class="form-table">
						<tbody>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'Content', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';
				
									$settings = array('textarea_name' => $editorid, 'media_buttons'=> true,'quicktags'=> true, 'teeny' => false , 'editor_class' => 'content pbrtextareahtml');
									wp_editor('',$editorid,$settings);
			
			echo '					<span class="description">' . __( 'Enter your custom content. The available tags are:' , 'woovoucher')
										.'<br /><codes>{qrcodes}</codes> - '.__( 'displays separate QR Codes for multiple voucher code(s).', 'woovoucher' )
									. '</span>
								</td>
							</tr>';
			echo '			<tr>
								<th scope="row">
									' . __( 'QR Code Width', 'woovoucher' ) . '
								</th>
								<td>
									<input type="text" value="' . $qrcodewidth . '" id="woo_vou_edit_qrcode_width" name="woo_vou_edit_qrcode_width" class="woo_vou_edit_qrcode_width small-text" maxlength="3" />&nbsp;<span>mm</span>';
			echo '					<br /><span class="description">' . __( 'Please enter qrcodes width. Leave it blank to auto set width as per selected pdf size.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'QR Code Height', 'woovoucher' ) . '
								</th>
								<td>
									<input type="text" value="' . $qrcodeheight . '" id="woo_vou_edit_qrcode_height" name="woo_vou_edit_qrcode_height" class="woo_vou_edit_qrcode_height small-text" maxlength="3" />&nbsp;<span>mm</span>';
			echo '					<br /><span class="description">' . __( 'Please enter qrcodes height. Leave it blank to auto set height as per selected pdf size.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'QR Code Color', 'woovoucher' ) . '
								</th>
								<td>';
							
								if( $wp_version >= 3.5 ) {
									
									echo '<input type="text" value="' . $qrcodecolor . '" id="woo_vou_edit_qrcode_color" name="woo_vou_edit_qrcode_color" class="woo_vou_color_box" data-default-color="#000000" />';
									
								} else {
									echo '<div style="position:relative;">
												<input type="text" value="' . $qrcodecolor . '" id="woo_vou_edit_qrcode_color" name="woo_vou_edit_qrcode_color" class="woo_vou_edit_qrcode_color" />
												<input type="button" class="woo_vou_color_box button-secondary" value="'.__('Select Color','woovoucher').'">
												<div class="colorpicker" style="z-index:100; position:absolute; display:none;"></div>
											</div>';
								}
			echo '					<br /><span class="description">' . __( 'Please select qrcodes color.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'Display Type', 'woovoucher' ) . '
								</th>
								<td>
									<select id="woo_vou_edit_qrcode_type" name="woo_vou_edit_qrcode_type" class="woo_vou_edit_qrcode_type">';
									foreach ( $qrcodes_type_data as $key => $value ) {
										echo '<option value="' . $key . '" ' . selected( $qrcodetype, $key, false ) . '>' . $value . '</option>';
									}
			echo '					</select>
									<br /><span class="description">' . __( 'Please select qrcodes display type.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'Enable Border', 'woovoucher' ) . '
								</th>
								<td>
									<input type="checkbox" value="1" id="woo_vou_edit_qrcode_border" name="woo_vou_edit_qrcode_border" class="woo_vou_edit_qrcode_border" '.checked(!empty($qrcodeborder), true, false).' />
								</td>
							</tr>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'Return Value', 'woovoucher' ) . '
								</th>
								<td>
									<select id="woo_vou_edit_qrcode_response" name="woo_vou_edit_qrcode_response" class="woo_vou_edit_qrcode_response">';
									foreach ( $qrcode_scan_response_data as $key => $value ) {
										echo '<option value="' . $key . '" ' . selected( $qrcoderesponse, $key, false ) . '>' . $value . '</option>';
									}
			echo '					</select>
									<br /><span class="description">' . __( 'Please select Return Value.<br><b>Redeem URL:</b> When you scan the QR code, it will return mobile friendly page URL where you get option to redeem the voucher code.<br><b>Voucher Code :</b> When you scan the QR code it will return actual Voucher Code.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '		</tbody>
					</table>';
			
			$html = ob_get_contents();
			ob_end_clean();
			
		} else if( $controltype == 'barcodeblock' ) {
			
			$editorid = $_POST['editorid'];
			ob_start();
			echo '	<table class="form-table">
						<tbody>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'Content', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';
				
									$settings = array('textarea_name' => $editorid, 'media_buttons'=> true,'quicktags'=> true, 'teeny' => false , 'editor_class' => 'content pbrtextareahtml');
									wp_editor('',$editorid,$settings);
			
			echo '					<span class="description">' . __( 'Enter your custom content. The available tags are:' , 'woovoucher')
										.'<br /><code>{barcode}</code> - '.__( 'displays single Barcode for multiple voucher code(s).', 'woovoucher' )
									. '</span>
								</td>
							</tr>';
			echo '			<tr>
								<th scope="row">
									' . __( 'Barcode Width', 'woovoucher' ) . '
								</th>
								<td>
									<input type="text" value="' . $barcodewidth . '" id="woo_vou_edit_barcode_width" name="woo_vou_edit_barcode_width" class="woo_vou_edit_barcode_width small-text" maxlength="3" />&nbsp;<span>mm</span>';
			echo '					<br /><span class="description">' . __( 'Please enter barcode width. Leave it blank to auto set width as per selected pdf size.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'Barcode Height', 'woovoucher' ) . '
								</th>
								<td>
									<input type="text" value="' . $barcodeheight . '" id="woo_vou_edit_barcode_height" name="woo_vou_edit_barcode_height" class="woo_vou_edit_barcode_height small-text" maxlength="3" />&nbsp;<span>mm</span>';
			echo '					<br /><span class="description">' . __( 'Please enter barcode height. Leave it blank to auto set height as per selected pdf size.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'Barcode Color', 'woovoucher' ) . '
								</th>
								<td>';
							
								if( $wp_version >= 3.5 ) {
									
									echo '<input type="text" value="' . $barcodecolor . '" id="woo_vou_edit_barcode_color" name="woo_vou_edit_barcode_color" class="woo_vou_color_box" data-default-color="#000000" />';
									
								} else {
									echo '<div style="position:relative;">
												<input type="text" value="' . $barcodecolor . '" id="woo_vou_edit_barcode_color" name="woo_vou_edit_barcode_color" class="woo_vou_edit_barcode_color" />
												<input type="button" class="woo_vou_color_box button-secondary" value="'.__('Select Color','woovoucher').'">
												<div class="colorpicker" style="z-index:100; position:absolute; display:none;"></div>
											</div>';
								}
			echo '					<br /><span class="description">' . __( 'Please select barcode color.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'Enable Border', 'woovoucher' ) . '
								</th>
								<td>
									<input type="checkbox" value="1" id="woo_vou_edit_barcode_border" name="woo_vou_edit_barcode_border" class="woo_vou_edit_barcode_border" '.checked(!empty($barcodeborder), true, false).' />
								</td>
							</tr>';
			
			echo '		</tbody>
					</table>';
			
			$html = ob_get_contents();
			ob_end_clean();
		} else if( $controltype == 'barcodesblock' ) {
			
			$editorid = $_POST['editorid'];
			ob_start();
			echo '	<table class="form-table">
						<tbody>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'Content', 'woovoucher' ) . '
								</th>
								<td class="woo_vou_ajax_editor">';
				
									$settings = array('textarea_name' => $editorid, 'media_buttons'=> true,'quicktags'=> true, 'teeny' => false , 'editor_class' => 'content pbrtextareahtml');
									wp_editor('',$editorid,$settings);
			
			echo '					<span class="description">' . __( 'Enter your custom content. The available tags are:' , 'woovoucher')
										.'<br /><code>{barcodes}</code> - '.__( 'displays separate Barcodes for multiple voucher code(s).', 'woovoucher' )
									. '</span>
								</td>
							</tr>';
			echo '			<tr>
								<th scope="row">
									' . __( 'Barcode Width', 'woovoucher' ) . '
								</th>
								<td>
									<input type="text" value="' . $barcodewidth . '" id="woo_vou_edit_barcode_width" name="woo_vou_edit_barcode_width" class="woo_vou_edit_barcode_width small-text" maxlength="3" />&nbsp;<span>mm</span>';
			echo '					<br /><span class="description">' . __( 'Please enter barcode width. Leave it blank to auto set width as per selected pdf size.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'Barcode Height', 'woovoucher' ) . '
								</th>
								<td>
									<input type="text" value="' . $barcodeheight . '" id="woo_vou_edit_barcode_height" name="woo_vou_edit_barcode_height" class="woo_vou_edit_barcode_height small-text" maxlength="3" />&nbsp;<span>mm</span>';
			echo '					<br /><span class="description">' . __( 'Please enter barcode height. Leave it blank to auto set height as per selected pdf size.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'Barcode Color', 'woovoucher' ) . '
								</th>
								<td>';
							
								if( $wp_version >= 3.5 ) {
									
									echo '<input type="text" value="' . $barcodecolor . '" id="woo_vou_edit_barcode_color" name="woo_vou_edit_barcode_color" class="woo_vou_color_box" data-default-color="#000000" />';
									
								} else {
									echo '<div style="position:relative;">
												<input type="text" value="' . $barcodecolor . '" id="woo_vou_edit_barcode_color" name="woo_vou_edit_barcode_color" class="woo_vou_edit_barcode_color" />
												<input type="button" class="woo_vou_color_box button-secondary" value="'.__('Select Color','woovoucher').'">
												<div class="colorpicker" style="z-index:100; position:absolute; display:none;"></div>
											</div>';
								}
			echo '					<br /><span class="description">' . __( 'Please select barcode color.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'Display Type', 'woovoucher' ) . '
								</th>
								<td>
									<select id="woo_vou_edit_barcodes_type" name="woo_vou_edit_barcodes_type" class="woo_vou_edit_barcodes_type">';
									foreach ( $barcodes_type_data as $key => $value ) {
										echo '<option value="' . $key . '" ' . selected( $barcodetype, $key, false ) . '>' . $value . '</option>';
									}
			echo '					</select>
									<br /><span class="description">' . __( 'Please select barcodes display type.', 'woovoucher' ) . '</span>
								</td>
							</tr>';
			
			echo '			<tr>
								<th scope="row">
									' . __( 'Enable Border', 'woovoucher' ) . '
								</th>
								<td>
									<input type="checkbox" value="1" id="woo_vou_edit_barcode_border" name="woo_vou_edit_barcode_border" class="woo_vou_edit_barcode_border" '.checked(!empty($barcodeborder), true, false).' />
								</td>
							</tr>';
			
			echo '		</tbody>
					</table>';
			
			$html = ob_get_contents();
			ob_end_clean();
		}
		
		echo $html;
		exit;
	}
	
	/**
	 * Add Custom File Name settings
	 * 
	 * Handle to add custom file name settings
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woo_vou_render_filename_callback( $field ) {
		
		global $woocommerce;
		
		if ( isset( $field['title'] ) && isset( $field['id'] ) ) :

			$filetype	= isset( $field['options'] ) ? $field['options'] : '';
			$file_val	= get_option( $field['id']);
			$file_val	= !empty($file_val) ? $file_val : '';
			?>
				<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['title'] ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<fieldset>
							<input name="<?php echo esc_attr( $field['id']  ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" type="text" value="<?php echo esc_attr( $file_val ); ?>" style="min-width: 300px;"/><?php echo $filetype;?>
						</fieldset>
						<span class="description"><?php echo $field['desc'];?></span>
					</td>
				</tr>
			<?php

		endif;
	}
	
	/**
	 * Display Textarea/Editor HTML
	 * 
	 * Handle to add custom file name settings
	 *
	 * @package WooCommerce - PDF Vouchers
	 * @since 1.0.0
	 */
	public function woocommerce_admin_field_vou_textarea( $field ) {
		
		global $woocommerce;

		if ( isset( $field['title'] ) && isset( $field['id'] ) ) :

			$file_val	= get_option( $field['id']);
			$file_val	= !empty($file_val) ? $file_val : '';
			$editor		= ( isset( $field['editor'] ) && $field['editor'] == true ) ? true : false;
			
			$editor_cofig = array(
									'media_buttons'	=> true,
									'textarea_rows'	=> 5,
									'editor_class'	=> 'woo-vou-wpeditor'
								);
				
			?>
			
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['title'] ); ?></label>
				</th>
				<td class="forminp forminp-text">
					<fieldset><?php 
						if( $editor ) {
							
							wp_editor( $file_val, esc_attr( $field['id'] ), $editor_cofig );
							
						} else { ?>
							
							<textarea name="<?php echo esc_attr( $field['id']  ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" style="width: 99%;height:200px;"/><?php echo esc_attr( $file_val ); ?></textarea>
						<?php } ?>
					</fieldset>
					<span class="description"><?php echo $field['desc'];?></span>
				</td>
			</tr><?php
		
		endif;
	}
	
	/**
	 * Upload Callback
	 *
	 * Renders upload fields.
	 *
	 * @since 1.0.0
	 * @package WooCommerce - PDF Vouchers
	 */
	function woo_vou_render_upload_callback( $field ) {
		global $woocommerce;

		if ( isset( $field['title'] ) && isset( $field['id'] ) ) {

			$filetype	= isset( $field['options'] ) ? $field['options'] : '';
			$file_val	= get_option( $field['id'] );
			$file_val	= !empty($file_val) ? $file_val : '';
			
			?>
			<tr valign="top">
					<th scope="row" class="titledesc">
						<label for="<?php echo esc_attr( $field['id'] ); ?>"><?php echo wp_kses_post( $field['title'] ); ?></label>
					</th>
					<td class="forminp forminp-text">
						<fieldset>
							<input name="<?php echo esc_attr( $field['id']  ); ?>" id="<?php echo esc_attr( $field['id'] ); ?>" type="text" value="<?php echo esc_attr( $file_val ); ?>" style="min-width: 300px;"/><?php echo $filetype;?>
							<input type="button" class="woo-vou-upload-button button-secondary" value="<?php _e( 'Upload File', 'woovoucher' );?>"/>
						</fieldset>
						<span class="description"><?php echo $field['desc'];?></span>
					</td>
				</tr>
			<?php
		}
	}
}