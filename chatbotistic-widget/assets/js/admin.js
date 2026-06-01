/**
 * Chatbotistic Widget — admin JS.
 *
 * Handles two things on the Widgets page:
 *   • Cloning new rule rows from <template> blocks.
 *   • Removing rule rows.
 *
 * And one global guard on the License page: confirm before deactivating.
 */
( function () {
	'use strict';

	const i18n = ( window.cbwAdmin && window.cbwAdmin.i18n ) || {};

	// ── Add row from <template>. ──────────────────────────────────────────────
	document.addEventListener( 'click', function ( e ) {
		const addBtn = e.target.closest( '.js-cbw-add-row' );
		if ( ! addBtn ) return;
		e.preventDefault();

		const targetId = addBtn.dataset.target;
		const table    = document.getElementById( targetId );
		if ( ! table ) return;

		const rowType = table.dataset.rowType; // 'post' | 'url'
		const tpl     = document.getElementById( 'cbw-tpl-row-' + rowType );
		if ( ! tpl ) return;

		const tbody = table.querySelector( 'tbody' );
		const empty = tbody.querySelector( '.cbw-empty-row' );
		if ( empty ) empty.remove();

		// Clone everything inside the <template>'s first <tr>.
		const fragment = tpl.content.cloneNode( true );
		tbody.appendChild( fragment );
	} );

	// ── Remove row. ───────────────────────────────────────────────────────────
	document.addEventListener( 'click', function ( e ) {
		const rmBtn = e.target.closest( '.js-cbw-remove-row' );
		if ( ! rmBtn ) return;
		e.preventDefault();
		if ( i18n.confirm_remove && ! window.confirm( i18n.confirm_remove ) ) return;
		const row = rmBtn.closest( 'tr.cbw-rule-row' );
		if ( row ) row.remove();
	} );

	// ── Confirm before deactivating license. ─────────────────────────────────
	document.addEventListener( 'submit', function ( e ) {
		const form = e.target;
		if ( ! form.querySelector || ! form.querySelector( '.js-cbw-confirm-deactivate' ) ) return;
		if ( i18n.confirm_deactivate && ! window.confirm( i18n.confirm_deactivate ) ) {
			e.preventDefault();
		}
	} );
} )();
