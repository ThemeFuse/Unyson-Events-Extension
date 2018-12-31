( function ( $ ) {

	var insertParam2 = function ( key, value, uri ) {

		key = encodeURI( key );

		var kvp = key + "=" + encodeURI( value ),
			r = new RegExp( "(&|\\?)" + key + "=[^\&]*" );

		uri = uri.replace( r, "$1" + kvp );

		if ( ! uri.match( r ) ) {
			uri += ( uri.length > 0 ? '&' : '?' ) + kvp;
		}

		return uri
	};

	var initButton = function () {
		var $button = $( this ),
			uri = $button.data( 'uri' ),
			gmtOffset = new Date().getTimezoneOffset() * 60,
			options = "toolbar=yes,menubar=yes,location=yes,status=yes,scrollbars=yes,resizable=yes,width=800,height=600,left=0,top=0";

		uri = insertParam2( 'offset', gmtOffset, uri );

		window.open( uri, "calendar", options );
	};

	$( document ).ready( function () {
		$( '.details-event-button button' ).on( 'click', initButton );
	} );

} )( jQuery );