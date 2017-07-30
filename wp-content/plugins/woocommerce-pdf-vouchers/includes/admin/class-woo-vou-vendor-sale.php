<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Email Class for Vendor Sale Notification
 * 
 * Handles to the email notification template.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.4
 */
class Woo_Vou_Vendor_Sale extends WC_Email {

	public $model;

	/**
	 * Constructor
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.4
	 */
	public function __construct() {

		global $woo_vou_model;

		$this->model	= $woo_vou_model;

		$this->id          = 'woo_vou_vendor_sale_notification';
		$this->title       = __( 'Vendor Sale', 'woovoucher' );
		$this->description = __( 'Vendor Sale Notification Email Template.', 'woovoucher' );

		$this->heading     = __( 'Vendor Sale Notification', 'woovoucher' );
		$this->subject     = __( 'New Sale!', 'woovoucher' );

		$this->template_html  = 'emails/vendor-sales.php';
		$this->template_plain = 'emails/plain/vendor-sales.php';

		$this->template_base  = WOO_VOU_DIR . '/includes/templates/';

		// Triggers for this email via our do_action
		add_action( 'woo_vou_vendor_sale_email_notification', array( $this, 'trigger' ), 20, 1 );

		parent::__construct();
	}

	/**
	 * Vendor Sale Notification
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.4
	 */
	public function trigger( $vendor_data ) {

		// replace variables in the subject/headings
	    $this->find[] 		= '{site_name}';
	    $this->replace[] 	= $vendor_data['site_name'];
	    $this->find[] 		= '{product_title}';
	    $this->replace[] 	= $vendor_data['product_title'];
	    $this->find[] 		= '{product_price}';
	    $this->replace[] 	= $vendor_data['product_price'];
	    $this->find[] 		= '{voucher_code}';
	    $this->replace[] 	= $vendor_data['voucher_code'];
	    $this->find[] 		= '{order_id}';
	    $this->replace[] 	= $vendor_data['order_id'];

	    //Asign required object for feature use
	    $this->object		= $vendor_data;

		$this->send( $vendor_data['vendor_email'], $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	/**
	 * Gets the email subject
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.4
	 */
	public function get_subject() {

		return apply_filters( 'woocommerce_email_subject_' . $this->id, $this->format_string( $this->subject ), $this->object );
	}

	/**
	 * Gets the email heading
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.4
	 */
	public function get_heading() {

		return apply_filters( 'woocommerce_email_heading_' . $this->id, $this->format_string( $this->heading ), $this->object );
	}

	/**
	 * Gets the email HTML content
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.4
	 */
	public function get_content_html() {
		ob_start();
		wc_get_template(
			$this->template_html,
			array(
				'email_heading' 	=> $this->get_heading(),
				'site_name'			=> $this->object['site_name'],
				'product_title'		=> $this->object['product_title'],
				'voucher_code'		=> $this->object['voucher_code'],
				'product_price'		=> $this->object['product_price'],
				'product_quantity'	=> $this->object['product_quantity'],
				'order_id'			=> $this->object['order_id'],
				'voucher_link'		=> $this->object['voucher_link'],
				'customer_name'		=> $this->object['customer_name'],
				'shipping_address'	=> $this->object['shipping_address'],
				'shipping_postcode'	=> $this->object['shipping_postcode'],
				'shipping_city'		=> $this->object['shipping_city'],
			),
			'',
			$this->template_base
		);
		return ob_get_clean();
	}

	/**
	 * Gets the email plain content
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.4
	 */
	public function get_content_plain() {
		ob_start();
		wc_get_template(
			$this->template_plain,
			array(
				'email_heading' 	=> $this->get_heading(),
				'site_name'			=> $this->object['site_name'],
				'product_title'		=> $this->object['product_title'],
				'voucher_code'		=> $this->object['voucher_code'],
				'product_price'		=> $this->object['product_price'],
				'product_quantity'	=> $this->object['product_quantity'],
				'order_id'			=> $this->object['order_id'],
				'customer_name'		=> $this->object['customer_name'],
				'shipping_address'	=> $this->object['shipping_address'],
				'shipping_postcode'	=> $this->object['shipping_postcode'],
				'shipping_city'		=> $this->object['shipping_city'],
			),
			'',
			$this->template_base
		);
		return ob_get_clean();
	}

	/**
	 * Initialize Settings Form Fields
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.4
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'woovoucher' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'woovoucher' ),
				'default' => 'yes',
			),
			'subject' => array(
				'title'       => __( 'Subject', 'woovoucher' ),
				'type'        => 'text',
				'description' => '<p class="description">'.
									__( 'This is the subject line for the vendor sale notification email. Available template tags for subject fields are :', 'woovoucher' ).
									'<br /><code>{site_name}</code> - '.__( 'displays the site name', 'woovoucher' ).
									'<br /><code>{product_title}</code> - '.__( 'displays the product title', 'woovoucher' ).
									'<br /><code>{product_price}</code> - '.__( 'displays the product price', 'woovoucher' ).
									'<br /><code>{voucher_code}</code> - '.__( 'displays the voucher code', 'woovoucher' ).'</p>',
				'placeholder' => '',
				'default'     => '',
			),
			'heading' => array(
				'title'       => __( 'Email Heading', 'woovoucher' ),
				'type'        => 'text',
				'description' => __( 'This controls the main heading contained within the email notification. Leave blank to use the default heading:', 'woovoucher' ) . '<code> '. $this->heading . '</code>.',
				'placeholder' => '',
				'default'     => '',
			),
			'email_type' => array(
				'title'       => __( 'Email type', 'woovoucher' ),
				'type'        => 'select',
				'description' => __( 'Choose which format of email to send.', 'woovoucher' ),
				'default'     => 'html',
				'class'       => 'email_type',
				'options' => array(
					'plain'     => __( 'Plain text', 'woovoucher' ),
					'html'      => __( 'HTML', 'woovoucher' ),
				),
			),
		);
	}
}