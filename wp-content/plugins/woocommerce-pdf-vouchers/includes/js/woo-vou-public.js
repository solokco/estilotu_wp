jQuery( document ).ready( function( $ ) { 
	
	// on variation select dropdown, show woo-vou-fields wrapper
	$( ".single_variation_wrap" ).on( "show_variation", function ( b, c ) {
		jQuery(".woo-vou-fields-wrapper-variation").hide();
		jQuery("#woo-vou-fields-wrapper-"+c.variation_id ).show();
	});
	
	// on clear selection, hide woo-vou-fields wrapper
	$( ".single_variation_wrap" ).on( "hide_variation", function ( event ) {
		jQuery(".woo-vou-fields-wrapper-variation").hide();
	});
	
	// Add border and save template id in hidden
	$( document ).on( 'click', '.woo-vou-preview-template-img', function(){
		
		$(this).parents('td').find('.woo-vou-preview-template-img-id').val('');
		
		if( !$(this).hasClass('woo-vou-preview-template-img-border') ) {
			$(this).parents('td').find('*').removeClass('woo-vou-preview-template-img-border');
			$(this).addClass('woo-vou-preview-template-img-border');
			$(this).parents('td').find('.woo-vou-preview-template-img-id').val( $(this).attr('data-id') );
		} else {
			$(this).removeClass('woo-vou-preview-template-img-border');
		}
	});
	
	// Keep saved selected template id
	$( '.woo-vou-preview-template-img-id' ).each( function() {
		var selected_template = $(this).val();
		$(this).parents('td').find('img[data-id="'+selected_template+'"]').addClass('woo-vou-preview-template-img-border');
	});
	
	jQuery('.woo-vou-meta-datetime').each( function() {

		var jQuerythis  = jQuery(this),
	    format = jQuerythis.attr('rel'),
	    id = jQuerythis.attr('id');
	      	  	
	  	if( id == '_woo_vou_start_date' ) {
		  		var expire_date = jQuery('#_woo_vou_exp_date');  	  	
	  		jQuerythis.datetimepicker({
				ampm: true,
				dateFormat : format,
				onSelect: function (selectedDateTime){
					expire_date.datetimepicker('option', 'minDate', jQuerythis.datetimepicker('getDate') );
				}
			});
	  	} else if( id == '_woo_vou_exp_date' ) {
  			var start_date = jQuery('#_woo_vou_start_date');
  	  		jQuerythis.datetimepicker({
				ampm: true,
				dateFormat : format,
				onSelect: function (selectedDateTime){
					start_date.datetimepicker('option', 'maxDate', jQuerythis.datetimepicker('getDate') );
				}
			});
	  	} else {  	        	
	      	jQuerythis.datetimepicker({ampm: true,dateFormat : format });//,timeFormat:'hh:mm:ss',showSecond:true
  	  	}
	});
	if( $('.woo_vou_multi_select').length ) {
    	
    	// apply select2 on simple select dropdown
    	$('.woo_vou_multi_select').select2();	
    }
    
    // Code for toggling column of Used Codes list table on click of button having toggle-row class
    $( document ).on( "click", ".toggle-row", function() {
		
    	// Find closest tr and check is-expanded class
		if( jQuery( this ).closest( 'tr' ).hasClass( 'is-expanded' ) ) { // If th has class is-expanded
			
			jQuery( this ).closest( 'tr' ).removeClass( 'is-expanded' ); // If it has then remove class
			jQuery( this ).closest( 'tr' ).find('td').each( function() { // Find td in that tr
				if( ! jQuery( this ).hasClass( 'column-primary' ) ) { // For td not having column-primary class, hide them else show
					jQuery( this ).hide();	
				}
			});
		} else { // If tr doesn't have class is-expanded
			
			jQuery( this ).closest( 'tr' ).addClass( 'is-expanded' ); // Add is-expanded class to tr
			jQuery( this ).closest( 'tr' ).find('td').each( function() { // Show all td in that tr
				jQuery( this ).show();	
			});
		}				
	});

   if( $("input[name^='_woo_vou_recipient_giftdate']").length ) {
		
		// add datepicker to recipient giftdate
		$("input[name^='_woo_vou_recipient_giftdate']").datepicker({
			dateFormat: "dd-M-yy",
			minDate: 0
		});	
	} 
});

//function for follow post ajax pagination
function woo_vou_used_codes_ajax_pagination( pid ) {
	
	var woo_vou_start_date = jQuery('#woo_vou_hid_start_date').val();
	var woo_vou_end_date = jQuery('#woo_vou_hid_end_date').val();
	var woo_vou_post_id = jQuery('#woo_vou_product_filter').val();
	
	var data = {
					action: 'woo_vou_used_codes_next_page',					
					paging: pid,
					woo_vou_start_date: woo_vou_start_date,
					woo_vou_end_date: woo_vou_end_date,
					woo_vou_post_id: woo_vou_post_id
				};
		
	jQuery('.woo-vou-usedcodes-loader').show();
	jQuery('.woo-vou-used-codes-paging').hide();
	
	jQuery.post( WooVouPublic.ajaxurl, data, function(response) {
		var newresponse = jQuery(response).filter('.woo-vou-used-codes-html').html();
		jQuery('.woo-vou-usedcodes-loader').hide();
		jQuery('.woo-vou-used-codes-html').html(newresponse);
	});	
	return false;
}