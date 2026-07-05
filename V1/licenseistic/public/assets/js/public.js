/* Licenseistic public JS. */
( function ( $ ) {
	'use strict';

	$( function () {
		$( document ).on( 'click', '.wpistic-lsi-deactivate', function () {
			var $btn          = $( this );
			var activationId  = $btn.data( 'activation' );
			var nonce         = $btn.data( 'nonce' );

			if ( ! activationId ) {
				return;
			}
			if ( ! window.confirm( 'Deactivate this site?' ) ) {
				return;
			}

			$btn.prop( 'disabled', true ).text( 'Deactivating…' );

			$.post( ( window.ajaxurl || '/wp-admin/admin-ajax.php' ), {
				action: 'wpistic_lsi_deactivate_site',
				activation_id: activationId,
				nonce: nonce
			}, function ( response ) {
				if ( response && response.success ) {
					$btn.closest( 'tr' ).fadeOut();
				} else {
					$btn.prop( 'disabled', false ).text( 'Deactivate' );
					window.alert( ( response && response.data && response.data.message ) || 'Error.' );
				}
			} ).fail( function () {
				$btn.prop( 'disabled', false ).text( 'Deactivate' );
				window.alert( 'Request failed.' );
			} );
		} );
	} );
} )( jQuery );
