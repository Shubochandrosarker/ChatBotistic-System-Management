/**
 * Insightistic Pro – Frontend Engagement Tracking
 * Fires custom GA4 events via Measurement Protocol.
 * < 2KB minified. Loaded only when enabled in Settings.
 *
 * @package Insightistic_Pro
 */
( function () {
	'use strict';

	if ( typeof ispTracking === 'undefined' ) return;

	var cfg    = ispTracking;
	var origin = window.location.hostname;

	/* ------------------------------------------------------------------ */
	/* Measurement Protocol helper                                         */
	/* ------------------------------------------------------------------ */
	function sendEvent( name, params ) {
		var endpoint = 'https://www.google-analytics.com/mp/collect'
			+ '?measurement_id=' + encodeURIComponent( cfg.measurementId )
			+ '&api_secret=' + encodeURIComponent( cfg.apiSecret );

		var payload = {
			client_id : getClientId(),
			events    : [ { name: name, params: params || {} } ]
		};

		// Use sendBeacon if available, fallback to fetch.
		var body = JSON.stringify( payload );
		if ( navigator.sendBeacon ) {
			navigator.sendBeacon( endpoint, new Blob( [ body ], { type: 'application/json' } ) );
		} else if ( window.fetch ) {
			fetch( endpoint, { method: 'POST', body: body } );
		}
	}

	function getClientId() {
		var id = readCookie( '_ga' );
		if ( id ) {
			var parts = id.split( '.' );
			if ( parts.length >= 4 ) return parts[2] + '.' + parts[3];
		}
		// Generate a random client ID and cache it.
		id = readCookie( 'isp_cid' );
		if ( ! id ) {
			id = Math.floor( Math.random() * 1e9 ) + '.' + Math.floor( Date.now() / 1000 );
			document.cookie = 'isp_cid=' + id + ';max-age=63072000;path=/';
		}
		return id;
	}

	function readCookie( name ) {
		var match = document.cookie.match( new RegExp( '(^| )' + name + '=([^;]+)' ) );
		return match ? match[2] : '';
	}

	/* ------------------------------------------------------------------ */
	/* Outbound Link Tracking                                              */
	/* ------------------------------------------------------------------ */
	if ( cfg.trackOutbound ) {
		document.addEventListener( 'click', function ( e ) {
			var link = e.target.closest( 'a' );
			if ( ! link ) return;
			var href = link.href || '';
			if ( href && link.hostname && link.hostname !== origin && ! href.startsWith( 'javascript' ) ) {
				sendEvent( 'outbound_link_click', {
					link_url   : href,
					link_domain: link.hostname,
					link_text  : ( link.textContent || '' ).trim().slice( 0, 100 )
				} );
			}
		} );
	}

	/* ------------------------------------------------------------------ */
	/* File Download Tracking                                              */
	/* ------------------------------------------------------------------ */
	if ( cfg.trackDownloads ) {
		var downloadExts = /\.(pdf|zip|docx?|xlsx?|pptx?|mp3|mp4|csv|rar|7z|tar|gz|dmg|exe|pkg)(\?|#|$)/i;
		document.addEventListener( 'click', function ( e ) {
			var link = e.target.closest( 'a' );
			if ( ! link ) return;
			var href = link.href || '';
			if ( downloadExts.test( href ) ) {
				var ext  = href.match( downloadExts );
				sendEvent( 'file_download', {
					file_url  : href,
					file_name : href.split( '/' ).pop().split( '?' )[0],
					file_ext  : ext ? ext[1].toLowerCase() : ''
				} );
			}
		} );
	}

	/* ------------------------------------------------------------------ */
	/* Scroll Depth Tracking                                               */
	/* ------------------------------------------------------------------ */
	if ( cfg.trackScroll ) {
		var scrollFired   = {};
		var scrollMilestones = [ 25, 50, 75, 100 ];

		function onScroll() {
			var scrollTop   = window.pageYOffset || document.documentElement.scrollTop;
			var docHeight   = document.documentElement.scrollHeight - document.documentElement.clientHeight;
			var pct         = docHeight > 0 ? Math.round( ( scrollTop / docHeight ) * 100 ) : 0;

			for ( var i = 0; i < scrollMilestones.length; i++ ) {
				var milestone = scrollMilestones[ i ];
				if ( pct >= milestone && ! scrollFired[ milestone ] ) {
					scrollFired[ milestone ] = true;
					sendEvent( 'scroll_depth', {
						percent_scrolled: milestone,
						page_location   : window.location.href
					} );
				}
			}
		}

		var scrollTimer;
		window.addEventListener( 'scroll', function () {
			clearTimeout( scrollTimer );
			scrollTimer = setTimeout( onScroll, 200 );
		}, { passive: true } );
	}

	/* ------------------------------------------------------------------ */
	/* Element Click Tracking (.isp-track)                                */
	/* ------------------------------------------------------------------ */
	if ( cfg.trackEvents && cfg.eventSelectors && cfg.eventSelectors.length ) {
		document.addEventListener( 'click', function ( e ) {
			for ( var i = 0; i < cfg.eventSelectors.length; i++ ) {
				var el = e.target.closest( cfg.eventSelectors[ i ] );
				if ( el ) {
					sendEvent( 'element_click', {
						element_id   : el.id || '',
						element_class: el.className || '',
						element_text : ( el.textContent || '' ).trim().slice( 0, 100 ),
						page_location: window.location.href
					} );
					break;
				}
			}
		} );
	}

} )();
