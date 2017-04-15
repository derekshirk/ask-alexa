jQuery( document ).ready( function( $ ) {

	// Update placeholder top label
	$( '.column-filter_label' ).each( function() {
		var top_label = $( this );
		var input = top_label.closest( '.column-form' ).find( '.column-label .input input' );

		var org_value = input.val();
		var org_placeholder = top_label.find( 'input' ).attr( 'placeholder' );

		input.bind( 'keyup change', function() {
			var value = jQuery( this ).val();
			top_label.find( 'input' ).attr( 'placeholder', org_placeholder.replace( org_value, value ) );
		} );
	} );
} );