<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

global $woo_vou_model, $post;

$model = $woo_vou_model;

$prefix = WOO_VOU_META_PREFIX;
		
$bg_style = woo_vou_meta_value( array( 'id' => $prefix . 'pdf_bg_style', 'type' => 'radio' ) );
$pdf_view = woo_vou_meta_value( array( 'id' => $prefix . 'pdf_view', 'type' => 'select' ) );
$bg_pattern_css = $bg_image_css = $pdf_size_css = 'woo-vou-meta-display-none';

if( $bg_style == 'color' ) { //Check background style is color
	
} else if( $bg_style == 'image' ) { //Check background style is image
	$bg_image_css = '';
} else { //Check background style is pattern
	$bg_pattern_css = '';
}

//if( $pdf_view == 'port' ) {
	$pdf_size_css	= '';
//}

//Get pdf sizes
$pdf_sizes	= woo_vou_get_pdf_sizes_select();

wp_nonce_field( WOO_VOU_PLUGIN_BASENAME, 'at_woo_vou_meta_box_nonce' );

	woo_vou_content_begin();
	
		// voucher background image option
		woo_vou_add_radio( array( 'id' => $prefix . 'pdf_bg_style', 'name'=> __( 'Background Style:', 'woovoucher' ), 'default' => 'pattern', 'options' => array( 'pattern' => __( 'Background Pattern', 'woovoucher' ), 'image' => __( 'Background Image', 'woovoucher' ), 'color' => __( 'Background Color', 'woovoucher' ) ), 'desc' => __( 'Choose the background style for the PDF.', 'woovoucher' ) ) );
	
		// voucher background pattern
		woo_vou_add_bg_pattern( array( 'id' => $prefix . 'pdf_bg_pattern', 'wrap_class' => 'woo-vou-meta-bg-pattern-wrap ' . $bg_pattern_css, 'name'=> __( 'Background Pattern:', 'woovoucher' ), 'default' => 'pattern1', 'options' => array( 'pattern1', 'pattern2', 'pattern3', 'pattern4', 'pattern5' ), 'desc' => __( 'Select background pattern for the PDF.', 'woovoucher' ) ) );
	
		// voucher background image
		woo_vou_add_image( array( 'id' => $prefix . 'pdf_bg_img', 'wrap_class' => 'woo-vou-meta-bg-image-wrap ' . $bg_image_css, 'name'=> __( 'Background Image:', 'woovoucher' ), 'desc' => __( 'Upload the background image for the PDF.<br><b>Note:</b> Image height/width should be the same size as per the PDF size you select.', 'woovoucher' ) ) );
	
		// voucher background color
		woo_vou_add_color( array( 'id' => $prefix . 'pdf_bg_color', 'name'=> __( 'Background Color:', 'woovoucher' ), 'desc' => __( 'Select background color for the PDF.', 'woovoucher' ) ) );
	
		// voucher lanscap or portrait view
		woo_vou_add_select( array( 'id' => $prefix . 'pdf_view', 'style' => 'min-width:200px;float: left;', 'class' => 'regular-text wc-enhanced-select', 'name'=> __( 'View:', 'woovoucher' ), 'options' => array( 'land' => __( 'Landscape', 'woovoucher' ), 'port' => __( 'Portrait', 'woovoucher' ) ), 'desc' => __( 'Select voucher pdf view in landscape or portrait.', 'woovoucher' ) ) );
		
		// voucher pdf size
		woo_vou_add_select( array( 'id' => $prefix . 'pdf_size', 'wrap_class' => 'woo-vou-meta-pdf-size-wrap ' . $pdf_size_css, 'default' => 'A4', 'style' => 'min-width:200px;float: left;', 'class' => 'regular-text wc-enhanced-select', 'name'=> __( 'Pdf Size:', 'woovoucher' ), 'options' => $pdf_sizes, 'desc' => __( 'Select voucher pdf size.', 'woovoucher' ) ) );
		
		// voucher margin top
		woo_vou_add_number( array( 'id' => $prefix . 'pdf_margin_top', 'class' => 'small-text', 'name'=> __( 'Margin Top:', 'woovoucher' ), 'desc' => __( 'Enter the margin top for the PDF, please set margin in pixel.', 'woovoucher' ), 'field_desc' => ' px' ) );
		
		// voucher margin top
		woo_vou_add_number( array( 'id' => $prefix . 'pdf_margin_bottom', 'class' => 'small-text', 'name'=> __( 'Margin Bottom:', 'woovoucher' ), 'desc' => __( 'Enter the margin bottom for the PDF, please set margin in pixel.', 'woovoucher' ), 'field_desc' => ' px' ) );
	
		// voucher margin left
		woo_vou_add_number( array( 'id' => $prefix . 'pdf_margin_left', 'class' => 'small-text', 'name'=> __( 'Margin Left:', 'woovoucher' ), 'desc' => __( 'Enter the margin left for the PDF, please set margin in pixel.', 'woovoucher' ), 'field_desc' => ' px' ) );
	
		// voucher margin right
		woo_vou_add_number( array( 'id' => $prefix . 'pdf_margin_right', 'class' => 'small-text', 'name'=> __( 'Margin Right:', 'woovoucher' ), 'desc' => __( 'Enter the margin right for the PDF, please set margin in pixel.', 'woovoucher' ), 'field_desc' => ' px' ) );
	
	woo_vou_content_end();	

?>