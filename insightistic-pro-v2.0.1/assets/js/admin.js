/* global insightisticPro, Chart */
'use strict';

( function ( $ ) {

	/* ------------------------------------------------------------------ */
	/* State                                                               */
	/* ------------------------------------------------------------------ */
	var timelineChart = null;
	var sourcesChart  = null;
	var currentData   = null;

	/* ================================================================== */
	/* UTILITY HELPERS                                                     */
	/* ================================================================== */

	function fmt( n ) {
		return Number( n ).toLocaleString();
	}

	function fmtMoney( n ) {
		return '$' + Number( n ).toLocaleString( undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 } );
	}

	function escHtml( str ) {
		return String( str )
			.replace( /&/g, '&amp;' )
			.replace( /</g, '&lt;' )
			.replace( />/g, '&gt;' )
			.replace( /"/g, '&quot;' );
	}

	function truncate( str, len ) {
		return str.length > len ? str.slice( 0, len ) + '…' : str;
	}

	function changeClass( val ) {
		if ( val > 0 ) return 'isp-change-up';
		if ( val < 0 ) return 'isp-change-down';
		return 'isp-change-flat';
	}

	function changeArrow( val ) {
		if ( val > 0 ) return '▲ +' + val + '%';
		if ( val < 0 ) return '▼ ' + val + '%';
		return '—';
	}

	function changeHtml( val ) {
		if ( isNaN( val ) || 0 === val ) {
			return '<span class="isp-change-flat">—</span>';
		}
		var sign  = val > 0 ? '+' : '';
		var cls   = val > 0 ? 'isp-change-up' : 'isp-change-down';
		var arrow = val > 0 ? '▲' : '▼';
		return '<span class="' + cls + '">' + arrow + ' ' + sign + val + '% ' + insightisticPro.i18n.vsLastPeriod + '</span>';
	}

	function spinnerBtn( $btn, text ) {
		$btn.find( '.isp-btn-text' ).text( text );
		$btn.find( '.isp-btn-icon' ).html( '<span class="isp-inline-spinner"></span>' );
		$btn.prop( 'disabled', true );
	}

	function resetBtn( $btn, icon, text ) {
		$btn.find( '.isp-btn-text' ).text( text );
		$btn.find( '.isp-btn-icon' ).text( icon );
		$btn.prop( 'disabled', false );
	}

	function channelIcon( channel ) {
		var c = ( channel || '' ).toLowerCase();
		if ( c.indexOf( 'organic' ) !== -1 ) return '🔍';
		if ( c.indexOf( 'direct' ) !== -1 )  return '🎯';
		if ( c.indexOf( 'social' ) !== -1 )   return '📲';
		if ( c.indexOf( 'email' ) !== -1 )    return '✉️';
		if ( c.indexOf( 'paid' ) !== -1 || c.indexOf( 'cpc' ) !== -1 ) return '💰';
		if ( c.indexOf( 'referral' ) !== -1 ) return '🔗';
		if ( c.indexOf( 'affiliate' ) !== -1 )return '🤝';
		return '🌐';
	}

	/* ================================================================== */
	/* DASHBOARD TABS                                                      */
	/* ================================================================== */

	function initDashTabs() {
		$( '.isp-dash-tab' ).on( 'click', function () {
			var tab = $( this ).data( 'tab' );
			$( '.isp-dash-tab' ).removeClass( 'isp-dash-tab-active' );
			$( this ).addClass( 'isp-dash-tab-active' );
			$( '.isp-tab-content' ).hide();
			$( '#isp-dash-' + tab ).show();
		} );
	}

	/* ================================================================== */
	/* GA4: OVERVIEW TAB                                                   */
	/* ================================================================== */

	function loadData( auto ) {
		var $btn  = $( '#isp-load-data' );
		var days  = $( '#isp-date-range' ).val() || '28';

		spinnerBtn( $btn, insightisticPro.i18n.loading );

		if ( ! auto ) {
			destroyCharts();
			$( '#isp-overview-cards, #isp-detail-cards, #isp-charts, #isp-content-cards, #isp-table-header, #isp-ai-insights' ).hide();
		}

		$( '#isp-data-container' ).html( loadingHtml( insightisticPro.i18n.loading ) );

		$.ajax( {
			url    : insightisticPro.ajaxUrl,
			method : 'POST',
			data   : { action: 'insightistic_get_data', days: days, nonce: insightisticPro.nonce },
			success: function ( res ) {
				if ( res.success ) {
					currentData = res.data;
					renderDashboard( res.data );
				} else {
					showError( res.data || insightisticPro.i18n.error );
				}
			},
			error: function () { showError( insightisticPro.i18n.error ); },
			complete: function () { resetBtn( $btn, '↺', insightisticPro.i18n.refreshData ); }
		} );
	}

	function renderDashboard( data ) {
		if ( data.overview ) {
			renderOverviewCards( data.overview );
			$( '#isp-overview-cards' ).show();
		}
		if ( data.countries || data.pages ) {
			renderCountries( data.countries || [] );
			renderPages( data.pages || [] );
			$( '#isp-detail-cards' ).show();
		}
		if ( data.channels || data.top_posts ) {
			renderChannels( data.channels || [] );
			renderTopPosts( data.top_posts || [] );
			$( '#isp-content-cards' ).show();
		}
		if ( data.chartData ) {
			renderCharts( data.chartData );
			$( '#isp-charts' ).show();
		}
		$( '#isp-data-container' ).html( data.html || '<p>' + insightisticPro.i18n.noData + '</p>' );
		$( '#isp-table-header' ).show();
		if ( insightisticPro.aiEnabled ) {
			$( '#isp-ai-analyze' ).show();
		}
	}

	function showError( msg ) {
		$( '#isp-data-container' ).html(
			'<div class="isp-initial-state"><div class="isp-notice isp-notice-error">⚠ ' + escHtml( msg ) + '</div></div>'
		);
	}

	function loadingHtml( msg ) {
		return '<div class="isp-initial-state"><div class="isp-spinner-wrap"><div class="isp-spinner"></div><p>' + escHtml( msg ) + '</p></div></div>';
	}

	/* ---- Overview Cards ---- */
	function renderOverviewCards( ov ) {
		// Sessions
		$( '#isp-val-sessions' ).text( fmt( ov.sessions.value ) );
		$( '#isp-chg-sessions' ).html( changeHtml( ov.sessions.change ) );
		// Users
		$( '#isp-val-users' ).text( fmt( ov.unique_users.value ) );
		$( '#isp-chg-users' ).html( changeHtml( ov.unique_users.change ) );
		// Pageviews
		if ( ov.pageviews ) {
			$( '#isp-val-pageviews' ).text( fmt( ov.pageviews.value ) );
			$( '#isp-chg-pageviews' ).html( changeHtml( ov.pageviews.change ) );
		} else {
			$( '#isp-card-pageviews' ).hide();
		}
		// Avg duration
		if ( ov.avg_duration ) {
			$( '#isp-val-duration' ).text( ov.avg_duration.value );
			$( '#isp-chg-duration' ).html( changeHtml( ov.avg_duration.change ) );
		} else {
			$( '#isp-card-duration' ).hide();
		}
		// Bounce rate
		if ( ov.bounce_rate ) {
			$( '#isp-val-bounce' ).text( ov.bounce_rate.value + '%' );
			// Bounce change: lower is better so invert arrow
			var bChg = ov.bounce_rate.change;
			var bHtml = bChg === 0 ? '<span class="isp-change-flat">—</span>' :
				( bChg > 0
					? '<span class="isp-change-down">▲ +' + bChg + '% ' + insightisticPro.i18n.vsLastPeriod + '</span>'
					: '<span class="isp-change-up">▼ ' + bChg + '% ' + insightisticPro.i18n.vsLastPeriod + '</span>' );
			$( '#isp-chg-bounce' ).html( bHtml );
		} else {
			$( '#isp-card-bounce' ).hide();
		}
		// New vs Return
		if ( ov.new_vs_return ) {
			var nvr = ov.new_vs_return;
			$( '#isp-val-newreturn' ).text( nvr.new_pct + '% New' );
			$( '#isp-newreturn-bar' ).html(
				'<div class="isp-newreturn-bar">' +
				'<div class="isp-newreturn-new" style="width:' + nvr.new_pct + '%" title="New: ' + nvr.new_pct + '%"></div>' +
				'<div class="isp-newreturn-return" style="width:' + nvr.return_pct + '%" title="Returning: ' + nvr.return_pct + '%"></div>' +
				'</div>'
			);
		} else {
			$( '#isp-card-newreturn' ).hide();
		}
		// Revenue
		if ( ov.revenue && ov.revenue.value > 0 ) {
			$( '#isp-val-revenue' ).text( fmtMoney( ov.revenue.value ) );
			$( '#isp-chg-revenue' ).html( changeHtml( ov.revenue.change ) );
		} else {
			$( '#isp-card-revenue' ).hide();
		}
		// Transactions
		if ( ov.transactions && ov.transactions.value > 0 ) {
			$( '#isp-val-tx' ).text( fmt( ov.transactions.value ) );
			$( '#isp-chg-tx' ).html( changeHtml( ov.transactions.change ) );
		} else {
			$( '#isp-card-tx' ).hide();
		}
	}

	/* ---- Countries ---- */
	function renderCountries( countries ) {
		var $list = $( '#isp-countries-list' ).empty();
		if ( ! countries.length ) {
			$list.html( '<li class="isp-rank-loading">' + insightisticPro.i18n.noData + '</li>' );
			return;
		}
		$.each( countries, function ( i, c ) {
			$list.append(
				'<li>' +
				'<span class="isp-rank-num">' + ( i + 1 ) + '</span>' +
				'<span class="isp-rank-name" title="' + escHtml( c.country ) + '">' + escHtml( c.country ) + '</span>' +
				'<div class="isp-rank-bar-wrap"><div class="isp-rank-bar"><div class="isp-rank-bar-fill" style="width:' + c.share + '%"></div></div></div>' +
				'<span class="isp-rank-share">' + c.share + '%</span>' +
				'<span class="isp-rank-change ' + changeClass( c.change ) + '">' + changeArrow( c.change ) + '</span>' +
				'</li>'
			);
		} );
	}

	/* ---- Top Pages ---- */
	function renderPages( pages ) {
		var $list = $( '#isp-pages-list' ).empty();
		if ( ! pages.length ) {
			$list.html( '<li class="isp-rank-loading">' + insightisticPro.i18n.noData + '</li>' );
			return;
		}
		$.each( pages, function ( i, p ) {
			var label = p.title && p.title !== '(not set)' ? p.title : p.path;
			$list.append(
				'<li>' +
				'<span class="isp-rank-num">' + ( i + 1 ) + '</span>' +
				'<span class="isp-rank-name" title="' + escHtml( label ) + '">' + escHtml( truncate( label, 34 ) ) + '</span>' +
				'<div class="isp-rank-bar-wrap"><div class="isp-rank-bar"><div class="isp-rank-bar-fill" style="width:' + p.share + '%"></div></div></div>' +
				'<span class="isp-rank-share">' + p.share + '%</span>' +
				'<span class="isp-rank-change ' + changeClass( p.change ) + '">' + changeArrow( p.change ) + '</span>' +
				'</li>'
			);
		} );
	}

	/* ---- Traffic Channels ---- */
	function renderChannels( channels ) {
		var $wrap = $( '#isp-channels-table' ).empty();
		if ( ! channels.length ) {
			$wrap.html( '<p style="padding:16px;color:#9ca3af;font-size:13px;">' + insightisticPro.i18n.noData + '</p>' );
			return;
		}
		var html = '';
		$.each( channels, function ( i, ch ) {
			html += '<div class="isp-channel-row">' +
				'<span class="isp-channel-icon">' + channelIcon( ch.channel ) + '</span>' +
				'<span class="isp-channel-name">' + escHtml( ch.channel ) + '</span>' +
				'<div class="isp-channel-bar-wrap"><div class="isp-channel-bar"><div class="isp-channel-bar-fill" style="width:' + ch.share + '%"></div></div></div>' +
				'<span class="isp-channel-pct">' + ch.share + '%</span>' +
				'<span class="isp-channel-sessions">' + fmt( ch.sessions ) + '</span>' +
				'<span class="isp-channel-change ' + changeClass( ch.change ) + '">' + changeArrow( ch.change ) + '</span>' +
				'</div>';
		} );
		$wrap.html( html );
	}

	/* ---- Top Posts ---- */
	function renderTopPosts( posts ) {
		var $list = $( '#isp-posts-list' ).empty();
		if ( ! posts.length ) {
			$list.html( '<li class="isp-rank-loading">' + insightisticPro.i18n.noData + '</li>' );
			return;
		}
		$.each( posts, function ( i, p ) {
			var label = p.title && p.title !== '(not set)' ? p.title : p.path;
			$list.append(
				'<li>' +
				'<span class="isp-rank-num">' + ( i + 1 ) + '</span>' +
				'<span class="isp-rank-name" title="' + escHtml( label ) + '">' + escHtml( truncate( label, 36 ) ) + '</span>' +
				'<span class="isp-rank-share">' + fmt( p.views ) + ' views</span>' +
				'</li>'
			);
		} );
	}

	/* ---- Charts ---- */
	function renderCharts( data ) {
		var tlCtx = document.getElementById( 'isp-chart-timeline' );
		if ( tlCtx ) {
			if ( timelineChart ) { timelineChart.destroy(); }
			var hasRevenue = data.revenue && data.revenue.some( function ( v ) { return v > 0; } );
			var datasets   = [ {
				label: insightisticPro.i18n.sessions, data: data.sessions,
				borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.08)',
				borderWidth: 2, fill: true, tension: 0.4,
				pointRadius: data.labels.length > 60 ? 0 : 3, pointHoverRadius: 5, yAxisID: 'y'
			} ];
			if ( hasRevenue ) {
				datasets.push( {
					label: insightisticPro.i18n.revenue, data: data.revenue,
					borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)',
					borderWidth: 2, fill: true, tension: 0.4,
					pointRadius: data.labels.length > 60 ? 0 : 3, pointHoverRadius: 5, yAxisID: 'y1'
				} );
			}
			var scales = {
				x: { grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 11 }, maxTicksLimit: 12 } },
				y: { position: 'left', grid: { color: 'rgba(0,0,0,0.04)' }, ticks: { font: { size: 11 }, callback: function ( v ) { return fmt( v ); } } }
			};
			if ( hasRevenue ) {
				scales.y1 = { position: 'right', grid: { drawOnChartArea: false }, ticks: { font: { size: 11 }, callback: function ( v ) { return '$' + fmt( v ); } } };
			}
			timelineChart = new Chart( tlCtx, {
				type: 'line', data: { labels: data.labels, datasets: datasets },
				options: { responsive: true, maintainAspectRatio: false, interaction: { mode: 'index', intersect: false },
					plugins: { legend: { display: hasRevenue, position: 'top', labels: { font: { size: 12 }, usePointStyle: true } }, tooltip: { mode: 'index', intersect: false } },
					scales: scales }
			} );
		}
		renderSourcesDonut();
	}

	function renderSourcesDonut() {
		if ( ! currentData || ! currentData.structured_data ) return;
		var sdCtx = document.getElementById( 'isp-chart-sources' );
		if ( ! sdCtx ) return;
		if ( sourcesChart ) { sourcesChart.destroy(); }
		var channels = currentData.structured_data.channels || [];
		if ( ! channels.length ) return;
		var sorted = channels.slice().sort( function ( a, b ) { return b.visitors - a.visitors; } );
		var top    = sorted.slice( 0, 8 );
		var other  = sorted.slice( 8 ).reduce( function ( acc, c ) { return acc + c.visitors; }, 0 );
		var labels = top.map( function ( c ) { return c.source + ' / ' + c.medium; } );
		var values = top.map( function ( c ) { return c.visitors; } );
		if ( other > 0 ) { labels.push( 'Other' ); values.push( other ); }
		var palette = [ '#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#f97316','#84cc16','#6b7280' ];
		sourcesChart = new Chart( sdCtx, {
			type: 'doughnut',
			data: { labels: labels, datasets: [ { data: values, backgroundColor: palette, borderWidth: 2, borderColor: '#fff', hoverBorderWidth: 3 } ] },
			options: { responsive: true, maintainAspectRatio: false, cutout: '62%',
				plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, usePointStyle: true, padding: 10, boxWidth: 8 } },
					tooltip: { callbacks: { label: function ( ctx ) { var total = ctx.dataset.data.reduce( function ( a, b ) { return a + b; }, 0 ); var pct = total > 0 ? ( ( ctx.parsed / total ) * 100 ).toFixed( 1 ) : 0; return ' ' + fmt( ctx.parsed ) + ' (' + pct + '%)'; } } } } }
		} );
	}

	function destroyCharts() {
		if ( timelineChart ) { timelineChart.destroy(); timelineChart = null; }
		if ( sourcesChart )  { sourcesChart.destroy();  sourcesChart  = null; }
	}

	/* ================================================================== */
	/* AI ANALYSIS                                                         */
	/* ================================================================== */

	function runAI() {
		if ( ! currentData ) return;
		var $btn   = $( '#isp-ai-analyze' );
		var $panel = $( '#isp-ai-insights' );
		var orig   = $btn.html();
		$btn.prop( 'disabled', true ).html( '<span class="isp-inline-spinner"></span> ' + insightisticPro.i18n.analyzing );
		$panel.html( '<div class="isp-ai-panel"><div class="isp-ai-header"><div class="isp-ai-title"><span class="isp-ai-icon">✨</span>' + escHtml( insightisticPro.i18n.analyzing ) + '</div></div></div>' ).show();
		$.ajax( {
			url: insightisticPro.ajaxUrl, method: 'POST',
			data: { action: 'insightistic_ai_analyze', data: JSON.stringify( currentData.structured_data ), days: $( '#isp-date-range' ).val() || '28', nonce: insightisticPro.nonce },
			success: function ( res ) {
				if ( res.success ) { $panel.html( res.data.html ); }
				else { $panel.html( '<div class="isp-notice isp-notice-error" style="margin:16px;">⚠ ' + escHtml( res.data || insightisticPro.i18n.error ) + '</div>' ); }
			},
			error: function () { $panel.html( '<div class="isp-notice isp-notice-error" style="margin:16px;">⚠ ' + escHtml( insightisticPro.i18n.error ) + '</div>' ); },
			complete: function () { $btn.prop( 'disabled', false ).html( orig ); }
		} );
	}

	/* ================================================================== */
	/* SEARCH CONSOLE TAB                                                  */
	/* ================================================================== */

	function loadGSC() {
		var $btn  = $( '#isp-load-gsc' );
		var days  = $( '#isp-gsc-date-range' ).val() || '28';

		spinnerBtn( $btn, insightisticPro.i18n.loading );
		$( '#isp-gsc-cards,#isp-gsc-tables,#isp-gsc-devices' ).hide();
		$( '#isp-gsc-loading' ).html( loadingHtml( insightisticPro.i18n.loading ) );

		$.ajax( {
			url: insightisticPro.ajaxUrl, method: 'POST',
			data: { action: 'insightistic_get_gsc_data', days: days, nonce: insightisticPro.nonce },
			success: function ( res ) {
				if ( res.success ) {
					renderGSC( res.data );
				} else {
					$( '#isp-gsc-loading' ).html( '<div class="isp-initial-state"><div class="isp-notice isp-notice-error">⚠ ' + escHtml( res.data || insightisticPro.i18n.error ) + '</div></div>' );
				}
			},
			error: function () { $( '#isp-gsc-loading' ).html( '<div class="isp-initial-state"><div class="isp-notice isp-notice-error">⚠ ' + escHtml( insightisticPro.i18n.error ) + '</div></div>' ); },
			complete: function () { resetBtn( $btn, '↺', insightisticPro.i18n.loadData ); }
		} );
	}

	function renderGSC( data ) {
		$( '#isp-gsc-loading' ).hide();

		// Stat cards.
		if ( data.overview ) {
			var ov = data.overview;
			$( '#isp-gsc-clicks' ).text( fmt( ov.clicks.value ) );
			$( '#isp-gsc-clicks-chg' ).html( changeHtml( ov.clicks.change ) );
			$( '#isp-gsc-impr' ).text( fmt( ov.impressions.value ) );
			$( '#isp-gsc-impr-chg' ).html( changeHtml( ov.impressions.change ) );
			$( '#isp-gsc-ctr' ).text( ov.ctr.value + '%' );
			$( '#isp-gsc-ctr-chg' ).html( changeHtml( ov.ctr.change ) );
			$( '#isp-gsc-pos' ).text( '#' + ov.position.value );
			// Position change: improvement is down (lower number).
			var posChg = ov.position.change;
			var posHtml = posChg === 0 ? '<span class="isp-change-flat">—</span>' :
				( posChg > 0
					? '<span class="isp-change-up">▲ +' + posChg + '% ' + insightisticPro.i18n.vsLastPeriod + '</span>'
					: '<span class="isp-change-down">▼ ' + posChg + '% ' + insightisticPro.i18n.vsLastPeriod + '</span>' );
			$( '#isp-gsc-pos-chg' ).html( posHtml );
			$( '#isp-gsc-cards' ).show();
		}

		// Queries table.
		if ( data.top_queries && data.top_queries.length ) {
			$( '#isp-gsc-queries' ).html( buildGSCTable( data.top_queries, 'query' ) );
		}
		// Pages table.
		if ( data.top_pages && data.top_pages.length ) {
			$( '#isp-gsc-pages' ).html( buildGSCTable( data.top_pages, 'page' ) );
		}
		$( '#isp-gsc-tables' ).show();

		// Device bars.
		if ( data.devices && data.devices.length ) {
			renderDeviceBars( data.devices );
			$( '#isp-gsc-devices' ).show();
		}
	}

	function buildGSCTable( rows, type ) {
		var colLabel = type === 'query' ? 'Query' : 'Page';
		var html = '<table class="isp-gsc-table"><thead><tr>' +
			'<th>' + colLabel + '</th>' +
			'<th>Clicks</th><th>Impr.</th><th>CTR</th><th>Position</th>' +
			'</tr></thead><tbody>';
		$.each( rows, function ( i, row ) {
			var label   = type === 'query' ? row.query : row.page.replace( /^https?:\/\/[^\/]+/, '' );
			var posCls  = row.position <= 3 ? 'isp-pos-top3' : ( row.position <= 10 ? 'isp-pos-top10' : 'isp-pos-low' );
			html += '<tr>' +
				'<td title="' + escHtml( type === 'query' ? row.query : row.page ) + '">' + escHtml( truncate( label, 50 ) ) + '</td>' +
				'<td>' + fmt( row.clicks ) + '</td>' +
				'<td>' + fmt( row.impressions ) + '</td>' +
				'<td>' + row.ctr + '%</td>' +
				'<td><span class="isp-gsc-pos-badge ' + posCls + '">#' + row.position + '</span></td>' +
				'</tr>';
		} );
		html += '</tbody></table>';
		return html;
	}

	function renderDeviceBars( devices ) {
		var icons = { 'MOBILE': '📱', 'DESKTOP': '🖥️', 'TABLET': '📟' };
		var fillCls = { 'MOBILE': 'isp-device-fill-mobile', 'DESKTOP': 'isp-device-fill-desktop', 'TABLET': 'isp-device-fill-tablet' };
		var html = '';
		$.each( devices, function ( i, d ) {
			var key = ( d.device || '' ).toUpperCase();
			html += '<div class="isp-device-bar-row">' +
				'<span class="isp-device-icon">' + ( icons[ key ] || '🌐' ) + '</span>' +
				'<span class="isp-device-name">' + escHtml( d.device.charAt(0).toUpperCase() + d.device.slice(1).toLowerCase() ) + '</span>' +
				'<div class="isp-device-track"><div class="isp-device-fill ' + ( fillCls[ key ] || '' ) + '" style="width:' + d.share + '%"></div></div>' +
				'<span class="isp-device-pct">' + d.share + '%</span>' +
				'<span class="isp-device-clicks">' + fmt( d.clicks ) + ' clicks</span>' +
				'</div>';
		} );
		$( '#isp-gsc-device-bars' ).html( html );
	}

	/* ================================================================== */
	/* PAGESPEED TAB                                                       */
	/* ================================================================== */

	function runPageSpeed() {
		var $btn  = $( '#isp-run-pagespeed' );
		var url   = $( '#isp-psi-url' ).val() || insightisticPro.defaultUrl;

		spinnerBtn( $btn, insightisticPro.i18n.testing );
		$( '#isp-psi-results' ).hide();
		$( '#isp-psi-loading' ).html( loadingHtml( insightisticPro.i18n.testing ) ).show();

		$.ajax( {
			url: insightisticPro.ajaxUrl, method: 'POST',
			data: { action: 'insightistic_get_pagespeed', page_url: url, nonce: insightisticPro.nonce },
			success: function ( res ) {
				if ( res.success ) {
					renderPageSpeed( res.data );
				} else {
					$( '#isp-psi-loading' ).html( '<div class="isp-initial-state"><div class="isp-notice isp-notice-error">⚠ ' + escHtml( res.data || insightisticPro.i18n.error ) + '</div></div>' );
				}
			},
			error: function () { $( '#isp-psi-loading' ).html( '<div class="isp-initial-state"><div class="isp-notice isp-notice-error">⚠ ' + escHtml( insightisticPro.i18n.error ) + '</div></div>' ); },
			complete: function () { resetBtn( $btn, '⚡', insightisticPro.i18n.runTest ); }
		} );
	}

	function renderPageSpeed( data ) {
		$( '#isp-psi-loading' ).hide();

		// Render both rings.
		renderScoreRing( 'mobile',  data.mobile  );
		renderScoreRing( 'desktop', data.desktop );

		// CWV grids.
		if ( data.mobile )  { $( '#isp-cwv-mobile' ).html( buildCWVGrid( data.mobile.cwv ) ); }
		if ( data.desktop ) { $( '#isp-cwv-desktop' ).html( buildCWVGrid( data.desktop.cwv ) ); }

		$( '#isp-psi-results' ).show();
	}

	function renderScoreRing( device, data ) {
		if ( ! data ) return;

		var score     = data.score || 0;
		var cls       = score >= 90 ? 'good' : ( score >= 50 ? 'moderate' : 'poor' );
		var label     = score >= 90 ? 'Good' : ( score >= 50 ? 'Needs Improvement' : 'Poor' );
		var ringColor = score >= 90 ? '#10b981' : ( score >= 50 ? '#f59e0b' : '#ef4444' );

		// Score text
		$( '#isp-' + device + '-score' )
			.text( score )
			.closest( '.isp-ring-container' )
			.attr( 'class', 'isp-ring-container isp-score-' + cls );

		// Animate the ring stroke.
		var circumference = 2 * Math.PI * 54; // 339.3
		var offset        = circumference - ( score / 100 ) * circumference;
		var $ring         = $( '#isp-' + device + '-ring' );
		$ring.attr( 'stroke', ringColor );
		$ring.css( 'stroke-dashoffset', circumference );
		setTimeout( function () {
			$ring.css( { 'stroke-dashoffset': offset, 'stroke': ringColor } );
		}, 80 );

		// Label below ring.
		$( '#isp-' + device + '-label' )
			.text( label )
			.attr( 'class', 'isp-speed-score-label isp-lbl-' + cls );
	}

	var cwvLabels = { lcp: 'LCP', inp: 'INP / TBT', cls: 'CLS', fcp: 'FCP', tbt: 'TBT', si: 'Speed Index' };

	function buildCWVGrid( cwv ) {
		if ( ! cwv ) return '<p style="color:#9ca3af;padding:16px;">' + escHtml( insightisticPro.i18n.noData ) + '</p>';
		var keys = [ 'lcp', 'inp', 'cls', 'fcp', 'tbt', 'si' ];
		var html = '';
		$.each( keys, function ( i, key ) {
			if ( ! cwv[ key ] ) return;
			var m      = cwv[ key ];
			var status = m.status || 'unknown';
			var label  = cwvLabels[ key ] || key.toUpperCase();
			html += '<div class="isp-cwv-item isp-cwv-item-' + escHtml( status ) + '">' +
				'<div class="isp-cwv-metric-name">' + escHtml( label ) + '</div>' +
				'<div class="isp-cwv-metric-val">' + escHtml( m.display ) + '</div>' +
				'<div class="isp-cwv-metric-label">' + escHtml( m.label || '' ) + '</div>' +
				'<span class="isp-cwv-badge isp-cwv-badge-' + escHtml( status ) + '">' + escHtml( status.charAt(0).toUpperCase() + status.slice(1) ) + '</span>' +
				'</div>';
		} );
		return html;
	}

	/* ================================================================== */
	/* SETTINGS: TABS                                                      */
	/* ================================================================== */

	function initSettingsTabs() {
		$( '.isp-tab-btn' ).on( 'click', function () {
			var tab = $( this ).data( 'tab' );
			$( '.isp-tab-btn' ).removeClass( 'isp-tab-active' );
			$( this ).addClass( 'isp-tab-active' );
			$( '.isp-tab-panel' ).hide();
			$( '#isp-tab-' + tab ).show();
		} );

		// Anchor-based tab activation (e.g., Settings#gsc).
		var hash = window.location.hash.replace( '#', '' );
		if ( hash && $( '#isp-tab-' + hash ).length ) {
			$( '.isp-tab-btn[data-tab="' + hash + '"]' ).trigger( 'click' );
		}
	}

	/* ================================================================== */
	/* SETTINGS: AI PROVIDER TOGGLE                                        */
	/* ================================================================== */

	function initProviderToggle() {
		$( 'input[name="ai_provider"]' ).on( 'change', function () {
			var provider = $( this ).val();
			$( '.isp-provider-card' ).removeClass( 'isp-provider-selected' );
			$( this ).closest( '.isp-provider-card' ).addClass( 'isp-provider-selected' );
			$( '.isp-provider-settings' ).hide();
			if ( provider !== 'none' ) {
				$( '#isp-settings-' + provider ).show();
			}
		} );
	}

	/* ================================================================== */
	/* SETTINGS: JSON IMPORTER                                             */
	/* ================================================================== */

	function initJsonImporter() {
		$( '#isp-toggle-json' ).on( 'click', function () {
			$( '#isp-json-wrap' ).toggle();
		} );
		$( '#isp-extract-json' ).on( 'click', function () {
			var raw     = $( '#isp-json-input' ).val();
			var $notice = $( '#isp-json-notice' );
			try {
				var json = JSON.parse( raw );
				if ( json.client_email ) { $( '#api_email' ).val( json.client_email ); }
				if ( json.private_key ) {
					$( '#api_private_key' ).show().val( json.private_key );
					$( '.isp-key-stored' ).hide();
				}
				$( '#isp-json-wrap' ).hide();
				$( '#isp-json-input' ).val( '' );
				// Show inline success notice instead of alert().
				$notice
					.removeClass( 'isp-notice-error' )
					.addClass( 'isp-notice-success' )
					.text( insightisticPro.i18n.jsonOk )
					.show();
			} catch ( e ) {
				// Show inline error notice instead of alert().
				$notice
					.removeClass( 'isp-notice-success' )
					.addClass( 'isp-notice-error' )
					.text( insightisticPro.i18n.jsonErr )
					.show();
			}
		} );
		$( '#isp-change-key' ).on( 'click', function () {
			$( '.isp-key-stored' ).hide();
			$( '#api_private_key' ).show().focus();
		} );
		$( '#isp-change-psi-key' ).on( 'click', function () {
			$( this ).closest( '.isp-key-stored' ).hide();
			$( '#pagespeed_api_key' ).show().focus();
		} );
		$( '#isp-change-secret' ).on( 'click', function () {
			$( this ).closest( '.isp-key-stored' ).hide();
			$( '#measurement_secret' ).show().focus();
		} );
	}

	/* ================================================================== */
	/* SETTINGS: TEST CONNECTIONS                                          */
	/* ================================================================== */

	function initTestConnection() {
		// GA4.
		$( '#isp-test-connection' ).on( 'click', function () {
			var $btn = $( this ), $result = $( '#isp-test-result' ), orig = $btn.html();
			$btn.prop( 'disabled', true ).html( '<span class="isp-inline-spinner"></span> Testing…' );
			$result.html( '' );
			$.ajax( {
				url: insightisticPro.ajaxUrl, method: 'POST',
				data: { action: 'insightistic_test_connection', nonce: insightisticPro.nonce },
				success: function ( res ) {
					$result.html( res.success
						? '<div class="isp-notice isp-notice-success">✓ ' + escHtml( res.data ) + '</div>'
						: '<div class="isp-notice isp-notice-error">✗ ' + escHtml( res.data ) + '</div>' );
				},
				error: function () { $result.html( '<div class="isp-notice isp-notice-error">✗ ' + insightisticPro.i18n.error + '</div>' ); },
				complete: function () { $btn.prop( 'disabled', false ).html( orig ); }
			} );
		} );

		// GSC.
		$( '#isp-test-gsc' ).on( 'click', function () {
			var $btn = $( this ), $result = $( '#isp-test-gsc-result' ), orig = $btn.html();
			$btn.prop( 'disabled', true ).html( '<span class="isp-inline-spinner"></span> Testing…' );
			$result.html( '' );
			$.ajax( {
				url: insightisticPro.ajaxUrl, method: 'POST',
				data: { action: 'insightistic_test_gsc', nonce: insightisticPro.nonce },
				success: function ( res ) {
					$result.html( res.success
						? '<div class="isp-notice isp-notice-success">✓ ' + escHtml( res.data ) + '</div>'
						: '<div class="isp-notice isp-notice-error">✗ ' + escHtml( res.data ) + '</div>' );
				},
				error: function () { $result.html( '<div class="isp-notice isp-notice-error">✗ ' + insightisticPro.i18n.error + '</div>' ); },
				complete: function () { $btn.prop( 'disabled', false ).html( orig ); }
			} );
		} );
	}

	/* ================================================================== */
	/* CWV Device Tab Toggle                                               */
	/* ================================================================== */

	function initCwvTabs() {
		$( document ).on( 'click', '.isp-cwv-tab', function () {
			var tab = $( this ).data( 'cwv-tab' );
			$( '.isp-cwv-tab' ).removeClass( 'isp-cwv-tab-active' );
			$( this ).addClass( 'isp-cwv-tab-active' );
			$( '#isp-cwv-mobile, #isp-cwv-desktop' ).hide();
			$( '#isp-cwv-' + tab ).show();
		} );
	}

	/* ================================================================== */
	/* ADDONS: FILTER TABS                                                 */
	/* ================================================================== */

	/**
	 * Addon filter — previously an inline <script> in templates/addons.php.
	 * Moved here so no inline scripts are present in templates (WP dir requirement).
	 */
	function initAddonsFilter() {
		var $btns  = $( '.isp-addon-filter-btn' );
		var $cards = $( '.isp-addon-card' );

		$btns.on( 'click', function () {
			var filter = $( this ).data( 'filter' );
			$btns.removeClass( 'isp-addon-filter-active' );
			$( this ).addClass( 'isp-addon-filter-active' );
			$cards.each( function () {
				var show = filter === 'all'
					|| $( this ).data( 'type' )   === filter
					|| $( this ).data( 'status' ) === filter;
				$( this ).toggle( show );
			} );
		} );
	}

	/* ================================================================== */
	/* INIT                                                                */
	/* ================================================================== */

	$( function () {

		// Dashboard page.
		if ( $( '#isp-load-data' ).length ) {
			initDashTabs();
			initCwvTabs();

			// Auto-load GA4 data immediately.
			loadData( true );

			$( '#isp-load-data' ).on( 'click', function () { loadData( false ); } );
			$( '#isp-ai-analyze' ).on( 'click', runAI );
			$( '#isp-load-gsc' ).on( 'click', loadGSC );
			$( '#isp-run-pagespeed' ).on( 'click', runPageSpeed );

			// Allow Enter key on URL input to trigger PageSpeed test.
			$( '#isp-psi-url' ).on( 'keypress', function ( e ) {
				if ( e.which === 13 ) { runPageSpeed(); }
			} );
		}

		// Settings page.
		if ( $( '.isp-settings-wrap' ).length ) {
			initSettingsTabs();
			initProviderToggle();
			initJsonImporter();
			initTestConnection();
		}

		// Addons page.
		if ( $( '.isp-addons-wrap' ).length ) {
			initAddonsFilter();
		}

	} );

	// Resize charts.
	var resizeTimer;
	$( window ).on( 'resize', function () {
		clearTimeout( resizeTimer );
		resizeTimer = setTimeout( function () {
			if ( timelineChart ) { timelineChart.resize(); }
			if ( sourcesChart )  { sourcesChart.resize(); }
		}, 200 );
	} );

} )( jQuery );
