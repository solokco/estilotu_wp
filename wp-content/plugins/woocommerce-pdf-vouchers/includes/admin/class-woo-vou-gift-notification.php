<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Email Class for Gift Notification
 * 
 * Handles to the email notification template.
 * 
 * @package WooCommerce - PDF Vouchers
 * @since 2.3.4
 */
class Woo_Vou_Gift_Notification extends WC_Email {

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

		$this->id          = 'woo_vou_gift_notification';
		$this->title       = __( 'Gift Notification', 'woovoucher' );
		$this->description = __( 'Gift Notification Email Template.', 'woovoucher' );

		$this->heading     = __( 'Gift Notification', 'woovoucher' );
		$this->subject     = __( 'You have received a voucher from', 'woovoucher' ) . ' {first_name} {last_name}';

		$this->template_html  = 'emails/gift-notification.php';
		$this->template_plain = 'emails/plain/gift-notification.php';

		$this->template_base  = WOO_VOU_DIR . '/includes/templates/';

		// Triggers for this email via our do_action
		add_action( 'woo_vou_gift_email_notification', array( $this, 'trigger' ), 20, 1 );

		parent::__construct();
	}

	/**
	 * Gift Notification
	 * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.4
	 */
	public function trigger( $gift_data ) {

		// Declare flag variables to indicate whether the value is already set in $this->find
		$first_name_key = $last_name_key = $recipient_name_key = false;
		
		// replace variables in the subject/headings
		foreach( $this->find as $key => $value ) {
			
			if( $value == '{first_name}' ) {
				$this->replace[$key] 	= $gift_data['first_name']; // If value is set, than replace the value in $this->replace by getting key in $this->find
				$first_name_key 		= true; // Set appropriate flag to true
			}
			if( $value == '{last_name}' ) {
				$this->replace[$key] 	= $gift_data['last_name']; // If value is set, than replace the value in $this->replace by getting key in $this->find
				$last_name_key 			= true; // Set appropriate flag to true
			}
			if( $value == '{recipient_name}' ) {
				$this->replace[$key] 	= $gift_data['recipient_name']; // If value is set, than replace the value in $this->replace by getting key in $this->find
				$recipient_name_key 	= true; // Set appropriate flag to true
			}
		}
		
		// If flag is not set then create new value in $this->find and $this->replace array
		if( $first_name_key == false ) {
		    $this->find[] 		= '{first_name}';
		    $this->replace[] 	= $gift_data['first_name'];
		}
		if( $last_name_key == false ) {
		    $this->find[] 		= '{last_name}';
		    $this->replace[] 	= $gift_data['last_name'];
		}
		if( $recipient_name_key == false ) {
		    $this->find[] 		= '{recipient_name}';
		    $this->replace[] 	= $gift_data['recipient_name'];
		}

	    //Asign required object for feature use
	    $this->object		= $gift_data;

	    if( isset( $gift_data['attachments'] ) && !empty( $gift_data['attachments'] ) ) {//check if attachment not empty
	    	add_filter( 'woocommerce_email_attachments', array( $this, 'get_email_attachments' ), 10, 3 );
	    }

	    if( isset( $gift_data['woo_vou_extra_emails'] ) && !empty( $gift_data['woo_vou_extra_emails'] ) ) {//check if extra emails not empty
	    	add_filter( 'woocommerce_email_headers', array( $this, 'add_bcc_to_wc_admin_gift_notify' ), 10, 3 );
	    }

		if ( ! $this->is_enabled() ) {
			return;
		}

		$this->send( $gift_data['recipient_email'], $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
	}

	/**
     * Get attachments.
     * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.4
     */
    public function get_email_attachments( $data, $id, $object ) {

		return $this->object['attachments'];
	}

	/**
     * Add Extra Emails.
     * 
	 * @package WooCommerce - PDF Vouchers
	 * @since 2.3.4
     */
    public function add_bcc_to_wc_admin_gift_notify( $headers = '', $id = '', $wc_email = array() ) {

    	if ( $id == 'woo_vou_gift_notification' && !empty( $this->object['woo_vou_extra_emails'] ) ) {
			$headers	.= 'Bcc: ' . $this->object['woo_vou_extra_emails'] . "\r\n";
	    }

	    return $headers;
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
				'first_name'		=> $this->object['first_name'],
				'last_name'			=> $this->object['last_name'],
				'recipient_name'	=> $this->object['recipient_name'],
				'voucher_link'		=> $this->object['voucher_link'],
				'recipient_message'	=> $this->object['recipient_message']
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
				'voucher_link'		=> $this->object['voucher_link'],
				'recipient_message'	=> $this->object['recipient_message']
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
									__( 'This is the subject line for the gift notification email. Available template tags for subject fields are :', 'woovoucher' ).
									'<br /><code>{first_name}</code> - '.__( 'displays the first name of customer', 'woovoucher' ).
									'<br /><code>{last_name}</code> - '.__( 'displays the last name of customer', 'woovoucher' ).
									'<br /><code>{recipient_name}</code> - '.__( 'displays the recipient name', 'woovoucher' ).'</p>',
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