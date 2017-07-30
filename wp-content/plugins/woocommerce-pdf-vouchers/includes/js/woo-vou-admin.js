jQuery( document ).ready( function( $ ) {

	//Media Uploader
	$( document ).on( 'click', '.woo-vou-upload-button', function() {
	
		var imgfield,showfield;
		imgfield = jQuery(this).prev('input').attr('id');
		showfield = jQuery(this).parents('td').find('.woo-vou-img-view');
    	
		if(typeof wp == "undefined" || WooVouAdminSettings.new_media_ui != '1' ){// check for media uploader
				
			tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
	    	
			window.original_send_to_editor = window.send_to_editor;
			window.send_to_editor = function(html) {
				
				if(imgfield)  {
					
					var mediaurl = $('img',html).attr('src');
					$('#'+imgfield).val(mediaurl);
					showfield.html('<img src="'+mediaurl+'" />');
					tb_remove();
					imgfield = '';
					
				} else {
					
					window.original_send_to_editor(html);
					
				}
			};
	    	return false;
			  
		} else {
			
			var file_frame;
			//window.formfield = '';
			
			//new media uploader
			var button = jQuery(this);
	
			//window.formfield = jQuery(this).closest('.file-input-advanced');
		
			// If the media frame already exists, reopen it.
			if ( file_frame ) {
				//file_frame.uploader.uploader.param( 'post_id', set_to_post_id );
				file_frame.open();
			  return;
			}
	
			// Create the media frame.
			file_frame = wp.media.frames.file_frame = wp.media({
				frame: 'post',
				state: 'insert',
				//title: button.data( 'uploader_title' ),
				/*button: {
					text: button.data( 'uploader_button_text' ),
				},*/
				multiple: false  // Set to true to allow multiple files to be selected
			});
	
			file_frame.on( 'menu:render:default', function(view) {
		        // Store our views in an object.
		        var views = {};
	
		        // Unset default menu items
		        view.unset('library-separator');
		        view.unset('gallery');
		        view.unset('featured-image');
		        view.unset('embed');
	
		        // Initialize the views in our view object.
		        view.set(views);
		    });
	
			// When an image is selected, run a callback.
			file_frame.on( 'insert', function() {
	
				// Get selected size from media uploader
				var selected_size = $('.attachment-display-settings .size').val();
				
				var selection = file_frame.state().get('selection');
				selection.each( function( attachment, index ) {
					attachment = attachment.toJSON();
					
					// Selected attachment url from media uploader
					var attachment_url = attachment.sizes[selected_size].url;
					var attachment_id = attachment.id;
					
					if(index == 0){
						// place first attachment in field
						$('#'+imgfield).val(attachment_url);
						showfield.html('<img src="'+attachment_url+'" />');
						
					} else{
						$('#'+imgfield).val(attachment_url);
						showfield.html('<img src="'+attachment_url+'" />');
					}
				});
			});
	
			// Finally, open the modal
			file_frame.open();			
		}
		
	});
	
	// function to display/hide pdf fonts
	function woo_vou_display_pdf_fonts() {
		// check if pdf font addon is active
		if( WooVouAdminSettings.is_pdf_fonts_plugin_active ) {
			if( $('#vou_char_support').is(':checked') ) {
				$('#vou_char_support').parents('tr').next().show();				
			} else {
				$('#vou_char_support').parents('tr').next().hide();
			}
		}
	}
	
	// call to function to hide/show pdf fonts
	woo_vou_display_pdf_fonts();	
	
	// on click display/hide pdf fonts
	$("#vou_char_support").on( 'click', function() {		
		woo_vou_display_pdf_fonts();		
	});
	
	// function to toggle remove voucher dowload link option
	function woo_vou_toggle_remove_voucher_download_link_option() {
		
		if( $("input[name='multiple_pdf']").is(':checked') ) {
			$("#revoke_voucher_download_link_access").parents('tr').fadeIn();
		} else {
			$("#revoke_voucher_download_link_access").parents('tr').fadeOut();
		}
	}
	
	// Setting page onload show/hide remove voucher download link option
	woo_vou_toggle_remove_voucher_download_link_option();
	
	// Setting page toggle remove voucher download link on click multiple voucher checkbox
	$(document).on('click', "input[name='multiple_pdf']", function() {
		woo_vou_toggle_remove_voucher_download_link_option();
	});
		
});