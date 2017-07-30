jQuery(document).ready(function($){

	// Defined Ids of fields which need to lock
    var ids = ['_woo_vou_enable','_woo_vou_enable_recipient_name','_woo_vou_recipient_name_label','_woo_vou_recipient_name_max_length','_woo_vou_recipient_name_is_required','_woo_vou_enable_recipient_email','_woo_vou_recipient_email_label','_woo_vou_recipient_email_is_required','_woo_vou_enable_recipient_message','_woo_vou_recipient_message_label','_woo_vou_recipient_message_max_length','_woo_vou_recipient_message_is_required','_woo_vou_product_start_date','_woo_vou_product_exp_date','_woo_vou_enable_pdf_template_selection','_woo_vou_pdf_template_selection_label','_woo_vou_pdf_template_selection','_woo_vou_pdf_template','_woo_vou_vendor_user','_woo_vou_sec_vendor_users','_woo_vou_using_type','_woo_vou_codes','_woo_vou_import_csv','_woo_vou_purchased_codes','_woo_vou_used_codes','_woo_vou_exp_type_specific_date','_woo_vou_exp_type_based_on_purchase','_woo_vou_start_date','_woo_vou_exp_date','_woo_vou_address_phone','_woo_vou_website','_woo_vou_how_to_use', '_woo_vou_disable_redeem_day_Monday', '_woo_vou_disable_redeem_day_Tuesday', '_woo_vou_disable_redeem_day_Wednesday', '_woo_vou_disable_redeem_day_Thursday', '_woo_vou_disable_redeem_day_Friday', '_woo_vou_disable_redeem_day_Saturday', '_woo_vou_disable_redeem_day_Sunday'];
	var i;
	
	// loop through all ids and disabled it
    for (i = 0; i < ids.length; i++) {
        $('#'+ids[i]).attr('disabled','disabled');
        $('#'+ids[i]).after($('.wcml_lock_img').clone().removeClass('wcml_lock_img').show());
    }

    // Define Classes of fields which need to lock
    var classes = ['_woo_vou_logo_field .woo-vou-meta-upload_image_button', '_woo_vou_locations_field .woo-vou-meta-text', '_woo_vou_map_link_field .woo-vou-meta-text']   
    
    // loop through all classes and disabled it
    for (i = 0; i < classes.length; i++) {
        $('.'+classes[i]).attr('disabled','disabled');
        $('.'+classes[i]).after($('.wcml_lock_img').clone().removeClass('wcml_lock_img').show());
    }
});