/* Licenseistic admin JS. */
( function ( $ ) {
	'use strict';

	$( function () {
		$( '.wpistic-lsi-copy' ).on( 'click', function ( e ) {
			e.preventDefault();
			var target = $( this ).data( 'target' );
			var $el    = $( target );
			if ( ! $el.length ) {
				return;
			}
			navigator.clipboard && navigator.clipboard.writeText( $el.text().trim() );
			$( this ).text( 'Copied!' );
		} );
	} );
} )( jQuery );
