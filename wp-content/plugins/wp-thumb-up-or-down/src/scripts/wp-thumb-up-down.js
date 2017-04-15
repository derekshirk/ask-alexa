jQuery( document ).on( 'click', '.love-button', function() {
	var post_id = jQuery(this).data('id');
	var btn 	= jQuery(this);
	jQuery.ajax({
		url : postlove.ajax_url,
		type : 'post',
		data : {
			action : 'post_love_add_love',
			post_id : post_id
		},
		success : function( response ) {
			// jQuery('#love-count').html( response );
			btn.addClass('voted') ;
			jQuery('.hate-button').removeClass('voted') ;
			// btn.removeClass('love-button') ;
		}
	});

	return false;
}) ;

jQuery( document ).on( 'click', '.hate-button', function() {
	var post_id = jQuery(this).data('id');
	var btn 	= jQuery(this);
	jQuery.ajax({
		url : postlove.ajax_url,
		type : 'post',
		data : {
			action : 'post_hate_add_hate',
			post_id : post_id
		},
		success : function( response ) {
			// jQuery('#hate-count').html( response );
			btn.addClass('voted') ;
			jQuery('.love-button').removeClass('voted') ;
			// btn.removeClass('hate-button') ;
		}
	});

	return false;
}) ;