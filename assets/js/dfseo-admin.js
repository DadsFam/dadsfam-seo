/**
 * DadsFam SEO — Main Admin JavaScript
 *
 * @package DadsFam_SEO
 */
/* global dfseoAdmin, wp, jQuery */

(function( $ ) {
	'use strict';

	const dfSEO = {

		// ── Init ────────────────────────────────────────────────────────────
		init() {
			this.bindTabs();
			this.bindOGImagePicker();
			this.bindDismissNotices();
			this.bindImportButtons();
			this.bindRedirectsPage();
			this.bindAnalyticsPage();
			this.bindBulkEditPage();
			this.bindSubTabs();
			this.bindFaqSchemaManager();
		},

		// ── Tabs (settings + metabox) ────────────────────────────────────────
		bindTabs() {
			$( document ).on( 'click', '.dfseo-tab', function(e) {
				e.preventDefault();
				const $this   = $( this );
				const tabId   = $this.data( 'tab' );
				const $container = $this.closest( '.dfseo-tabs' );

				$container.find( '.dfseo-tab' ).removeClass( 'active' ).attr( 'aria-selected', 'false' );
				$container.find( '.dfseo-tab-content' ).hide();
				$this.addClass( 'active' ).attr( 'aria-selected', 'true' );
				$container.find( '#dfseo-tab-' + tabId ).show();
			});
		},

		// ── OG image media picker ────────────────────────────────────────────
		bindOGImagePicker() {
			let mediaFrame;

			$( document ).on( 'click', '#dfseo-og-image-btn', function(e) {
				e.preventDefault();
				if ( mediaFrame ) { mediaFrame.open(); return; }
				mediaFrame = wp.media({
					title:    dfseoAdmin.i18n.selectImage || 'Select Image',
					button:   { text: 'Use this image' },
					multiple: false,
					library:  { type: 'image' },
				});
				mediaFrame.on( 'select', function() {
					const attachment = mediaFrame.state().get( 'selection' ).first().toJSON();
					$( '#dfseo_og_image_id' ).val( attachment.id );
					const thumb = attachment.sizes && attachment.sizes.thumbnail
						? attachment.sizes.thumbnail.url
						: attachment.url;
					const $wrap = $( '#dfseo-og-image-wrap' );
					$wrap.html( '<img src="' + thumb + '" class="dfseo-og-thumb" id="dfseo-og-thumb">' );
					$( '#dfseo-og-card-img' ).css( 'background-image', 'url(' + ( attachment.sizes?.large?.url || attachment.url ) + ')' );
					if ( ! $( '#dfseo-og-image-remove' ).length ) {
						$( '#dfseo-og-image-btn' ).after( '<button type="button" class="dfseo-btn dfseo-btn-ghost" id="dfseo-og-image-remove">Remove</button>' );
					}
				});
				mediaFrame.open();
			});

			$( document ).on( 'click', '#dfseo-og-image-remove', function(e) {
				e.preventDefault();
				$( '#dfseo_og_image_id' ).val( '' );
				$( '#dfseo-og-image-wrap' ).html( '<div class="dfseo-og-placeholder" id="dfseo-og-thumb"><span class="dashicons dashicons-format-image"></span><span>No image set</span></div>' );
				$( '#dfseo-og-card-img' ).css( 'background-image', '' );
				$( this ).remove();
			});
		},

		// ── Dismiss notices ──────────────────────────────────────────────────
		bindDismissNotices() {
			$( document ).on( 'click', '.dfseo-notice .notice-dismiss, .dfseo-notice[data-notice]', function() {
				const notice = $( this ).closest( '[data-notice]' ).data( 'notice' );
				if ( ! notice ) return;
				$.post( dfseoAdmin.ajaxUrl, {
					action: 'dfseo_dismiss_notice',
					notice,
					nonce:  dfseoAdmin.nonce,
				});
			});
		},

		// ── Import ───────────────────────────────────────────────────────────
		bindImportButtons() {
			$( document ).on( 'click', '.dfseo-import-start', function() {
				const $btn    = $( this );
				const source  = $btn.data( 'source' );
				const $prog   = $( '#dfseo-import-' + source );
				const $bar    = $( '#dfseo-import-' + source + '-bar' );
				const $status = $( '#dfseo-import-' + source + '-status' );

				$btn.prop( 'disabled', true ).text( 'Importing…' );
				$prog.show();

				let offset = 0;
				const batchSize = 50;
				let total = 0;

				function runBatch() {
					$.ajax({
						url:    dfseoAdmin.restUrl + 'import/' + source,
						method: 'POST',
						beforeSend( xhr ) { xhr.setRequestHeader( 'X-WP-Nonce', dfseoAdmin.restNonce ); },
						data: JSON.stringify({ offset }),
						contentType: 'application/json',
					}).done( function( res ) {
						total += res.imported || 0;
						offset += batchSize;
						$status.text( total + ' posts imported…' );
						if ( res.has_more ) {
							$bar.css( 'width', '50%' );
							setTimeout( runBatch, 200 );
						} else {
							$bar.css( 'width', '100%' );
							$status.text( '✓ Done! ' + total + ' posts imported.' );
							$btn.prop( 'disabled', false ).text( 'Import Again' );
						}
					}).fail( function() {
						$status.text( '✗ Error. Please try again.' );
						$btn.prop( 'disabled', false );
					});
				}
				runBatch();
			});
		},

		// ── Sub-tabs ─────────────────────────────────────────────────────────
		bindSubTabs() {
			$( document ).on( 'click', '.dfseo-sub-tab', function() {
				const tab = $( this ).data( 'subtab' );
				$( '.dfseo-sub-tab' ).removeClass( 'active' );
				$( this ).addClass( 'active' );
				$( '#dfseo-redirects-list' ).toggle( tab === 'redirects' );
				$( '#dfseo-404-list' ).toggle( tab === '404' );
				if ( tab === '404' ) dfSEO.load404Log();
			});
		},

		// ── Redirects page ────────────────────────────────────────────────────
		bindRedirectsPage() {
			if ( ! $( '#dfseo-redirects-list' ).length ) return;

			dfSEO.loadRedirects( 1 );

			$( '#dfseo-redirect-add-btn' ).on( 'click', function() {
				$( '#dfseo-redirect-form' ).slideDown();
				$( '#rd-id, #rd-source, #rd-target, #rd-note' ).val( '' );
				$( '#rd-type' ).val( '301' );
				$( '#dfseo-redirect-form-title' ).text( 'Add Redirect' );
			});

			$( '#dfseo-redirect-cancel' ).on( 'click', function() { $( '#dfseo-redirect-form' ).slideUp(); } );

			$( '#dfseo-redirect-save' ).on( 'click', function() {
				const id     = $( '#rd-id' ).val();
				const method = id ? 'PUT' : 'POST';
				const url    = dfseoAdmin.restUrl + 'redirects' + ( id ? '/' + id : '' );
				$.ajax({
					url, method,
					beforeSend( xhr ) { xhr.setRequestHeader( 'X-WP-Nonce', dfseoAdmin.restNonce ); },
					data: JSON.stringify({
						source_url:    $( '#rd-source' ).val(),
						target_url:    $( '#rd-target' ).val(),
						redirect_type: parseInt( $( '#rd-type' ).val() ),
						note:          $( '#rd-note' ).val(),
					}),
					contentType: 'application/json',
				}).done( function() {
					$( '#dfseo-redirect-form' ).slideUp();
					dfSEO.loadRedirects( 1 );
				});
			});

			$( document ).on( 'click', '.dfseo-rd-edit', function() {
				const $row = $( this ).closest( 'tr' );
				$( '#rd-id' ).val( $row.data( 'id' ) );
				$( '#rd-source' ).val( $row.data( 'source' ) );
				$( '#rd-target' ).val( $row.data( 'target' ) );
				$( '#rd-type' ).val( $row.data( 'type' ) );
				$( '#rd-note' ).val( $row.data( 'note' ) );
				$( '#dfseo-redirect-form-title' ).text( 'Edit Redirect' );
				$( '#dfseo-redirect-form' ).slideDown();
				$( 'html, body' ).animate({ scrollTop: $( '#dfseo-redirect-form' ).offset().top - 80 }, 200 );
			});

			$( document ).on( 'click', '.dfseo-rd-delete', function() {
				if ( ! confirm( dfseoAdmin.i18n.confirmDelete ) ) return;
				const id = $( this ).data( 'id' );
				$.ajax({
					url:    dfseoAdmin.restUrl + 'redirects/' + id,
					method: 'DELETE',
					beforeSend( xhr ) { xhr.setRequestHeader( 'X-WP-Nonce', dfseoAdmin.restNonce ); },
				}).done( function() { dfSEO.loadRedirects( 1 ); });
			});

			$( document ).on( 'change', '.dfseo-rd-toggle', function() {
				const id      = $( this ).data( 'id' );
				const enabled = $( this ).prop( 'checked' ) ? 1 : 0;
				$.ajax({
					url:    dfseoAdmin.restUrl + 'redirects/' + id,
					method: 'PUT',
					beforeSend( xhr ) { xhr.setRequestHeader( 'X-WP-Nonce', dfseoAdmin.restNonce ); },
					data: JSON.stringify({ enabled }),
					contentType: 'application/json',
				});
			});
		},

		loadRedirects( page ) {
			$( '.dfseo-loading-overlay' ).show();
			$.ajax({
				url:    dfseoAdmin.restUrl + 'redirects?page=' + page,
				method: 'GET',
				beforeSend( xhr ) { xhr.setRequestHeader( 'X-WP-Nonce', dfseoAdmin.restNonce ); },
			}).done( function( res ) {
				const rows = ( res.items || [] ).map( r =>
					`<tr data-id="${r.id}" data-source="${ dfSEO.esc(r.source_url) }" data-target="${ dfSEO.esc(r.target_url) }" data-type="${r.redirect_type}" data-note="${ dfSEO.esc(r.note) }">
						<td><code>${ dfSEO.esc(r.source_url) }</code></td>
						<td><a href="${ dfSEO.esc(r.target_url) }" target="_blank">${ dfSEO.esc(r.target_url) }</a></td>
						<td><span class="dfseo-score-badge ${r.redirect_type === 301 ? 'great' : 'ok'}">${r.redirect_type}</span></td>
						<td>${r.hit_count}</td>
						<td><label><input type="checkbox" class="dfseo-rd-toggle" data-id="${r.id}" ${r.enabled ? 'checked':''}>On</label></td>
						<td>
							<button type="button" class="dfseo-btn dfseo-btn-sm dfseo-btn-ghost dfseo-rd-edit">Edit</button>
							<button type="button" class="dfseo-btn dfseo-btn-sm dfseo-btn-ghost dfseo-rd-delete" data-id="${r.id}">Delete</button>
						</td>
					</tr>`
				).join('');
				$( '#dfseo-redirects-body' ).html( rows || '<tr><td colspan="6" style="text-align:center;color:#6b7280;padding:20px">No redirects yet. Click "Add Redirect" to create one.</td></tr>' );
			}).fail( function() {
				$( '#dfseo-redirects-body' ).html( '<tr><td colspan="6" style="text-align:center;color:#dc2626;padding:20px">Could not load redirects. Please refresh the page.</td></tr>' );
			}).always( function() {
				$( '.dfseo-loading-overlay' ).hide();
			});
		},

		load404Log() {
			$.ajax({
				url:    dfseoAdmin.restUrl + '404-log',
				method: 'GET',
				beforeSend( xhr ) { xhr.setRequestHeader( 'X-WP-Nonce', dfseoAdmin.restNonce ); },
			}).done( function( res ) {
				const rows = ( res.items || [] ).map( r =>
					`<tr>
						<td><code>${ dfSEO.esc(r.url) }</code></td>
						<td>${r.hit_count}</td>
						<td>${r.last_seen}</td>
						<td><a href="${ dfseoAdmin.ajaxUrl.replace('admin-ajax.php','admin.php') }?page=dfseo-redirects#" class="dfseo-btn dfseo-btn-sm dfseo-btn-secondary dfseo-404-create-redirect" data-url="${ dfSEO.esc(r.url) }">Create Redirect</a></td>
					</tr>`
				).join('');
				$( '#dfseo-404-body' ).html( rows || '<tr><td colspan="4" style="text-align:center;color:#6b7280;padding:20px">No 404 errors logged.</td></tr>' );
			});
		},

		// ── Analytics page ────────────────────────────────────────────────────
		bindAnalyticsPage() {
			if ( ! $( '#dfseo-analytics-chart' ).length ) return;
			let currentDays = 30;
			dfSEO.loadAnalytics( currentDays );

			$( document ).on( 'click', '.dfseo-range-btn', function() {
				$( '.dfseo-range-btn' ).removeClass( 'active' );
				$( this ).addClass( 'active' );
				currentDays = parseInt( $( this ).data( 'days' ) );
				dfSEO.loadAnalytics( currentDays );
			});
		},

		loadAnalytics( days ) {
			$.ajax({
				url:    dfseoAdmin.restUrl + 'analytics?days=' + days,
				method: 'GET',
				beforeSend( xhr ) { xhr.setRequestHeader( 'X-WP-Nonce', dfseoAdmin.restNonce ); },
			}).done( function( res ) {
				const timeline = res.timeline || [];
				const topPosts = res.top_posts || [];

				const totalClicks = timeline.reduce( (s, r) => s + parseInt(r.clicks||0), 0 );
				const totalImps   = timeline.reduce( (s, r) => s + parseInt(r.impressions||0), 0 );
				$( '#dfseo-total-clicks' ).text( totalClicks.toLocaleString() );
				$( '#dfseo-total-impressions' ).text( totalImps.toLocaleString() );

				// Draw chart with canvas
				dfSEO.drawLineChart( timeline );

				const postRows = topPosts.map( (p, i) =>
					`<tr><td>${i+1}. <a href="${ dfSEO.esc(p.url||'') }" target="_blank">${ dfSEO.esc(p.title||p.post_id) }</a></td><td>${p.clicks}</td></tr>`
				).join('');
				$( '#dfseo-top-posts-body' ).html( postRows || '<tr><td colspan="2" class="dfseo-muted" style="text-align:center">No data yet.</td></tr>' );
			});
		},

		drawLineChart( data ) {
			const canvas = document.getElementById( 'dfseo-analytics-chart' );
			if ( ! canvas ) return;
			const ctx = canvas.getContext( '2d' );
			if ( ! ctx ) return;

			// Simple canvas line chart (no external library needed)
			const w = canvas.parentElement.offsetWidth - 32;
			canvas.width  = w;
			canvas.height = 200;
			const H = 200, pad = { top: 20, right: 20, bottom: 40, left: 50 };
			const chartW = w - pad.left - pad.right;
			const chartH = H - pad.top - pad.bottom;
			const maxVal = Math.max( 1, ...data.map( d => parseInt(d.clicks||0) ) );

			ctx.clearRect( 0, 0, w, H );
			ctx.fillStyle = '#f9fafb';
			ctx.fillRect( 0, 0, w, H );

			if ( ! data.length ) {
				ctx.fillStyle = '#9ca3af'; ctx.font = '13px sans-serif'; ctx.textAlign = 'center';
				ctx.fillText( 'No organic traffic data yet.', w/2, H/2 ); return;
			}

			// Grid lines
			ctx.strokeStyle = '#e5e7eb'; ctx.lineWidth = 1;
			for ( let i = 0; i <= 4; i++ ) {
				const y = pad.top + chartH - (i / 4) * chartH;
				ctx.beginPath(); ctx.moveTo( pad.left, y ); ctx.lineTo( pad.left + chartW, y ); ctx.stroke();
				ctx.fillStyle = '#9ca3af'; ctx.font = '10px sans-serif'; ctx.textAlign = 'right';
				ctx.fillText( Math.round( (i / 4) * maxVal ), pad.left - 6, y + 3 );
			}

			// Line
			const pts = data.map( (d, i) => ({
				x: pad.left + (i / Math.max(1, data.length-1)) * chartW,
				y: pad.top + chartH - (parseInt(d.clicks||0) / maxVal) * chartH,
			}));

			// Fill
			ctx.beginPath();
			ctx.moveTo( pts[0].x, pad.top + chartH );
			pts.forEach( p => ctx.lineTo( p.x, p.y ) );
			ctx.lineTo( pts[pts.length-1].x, pad.top + chartH );
			ctx.closePath();
			ctx.fillStyle = 'rgba(245,158,11,.15)';
			ctx.fill();

			// Stroke
			ctx.beginPath(); ctx.strokeStyle = '#f59e0b'; ctx.lineWidth = 2;
			pts.forEach( (p, i) => i === 0 ? ctx.moveTo(p.x, p.y) : ctx.lineTo(p.x, p.y) );
			ctx.stroke();

			// Dots
			pts.forEach( p => {
				ctx.beginPath();
				ctx.arc( p.x, p.y, 3, 0, Math.PI * 2 );
				ctx.fillStyle = '#f59e0b'; ctx.fill();
			});

			// Labels
			const step = Math.max( 1, Math.floor( data.length / 6 ) );
			data.forEach( (d, i) => {
				if ( i % step !== 0 && i !== data.length - 1 ) return;
				ctx.fillStyle = '#9ca3af'; ctx.font = '10px sans-serif'; ctx.textAlign = 'center';
				ctx.fillText( d.date_recorded || '', pts[i].x, H - 8 );
			});
		},

		// ── Bulk edit ─────────────────────────────────────────────────────────
		bindBulkEditPage() {
			if ( ! $( '#dfseo-bulk-load' ).length ) return;
			let currentPage = 1;
			const changes = {};

			$( '#dfseo-bulk-load' ).on( 'click', function() {
				currentPage = 1;
				dfSEO.loadBulkPosts( currentPage, changes );
			});

			$( document ).on( 'input change', '.dfseo-bulk-field', function() {
				const $row  = $( this ).closest( 'tr' );
				const id    = $row.data( 'id' );
				const field = $( this ).data( 'field' );
				if ( ! changes[id] ) changes[id] = { id };
				changes[id][field] = $( this ).val();
				$row.addClass( 'dfseo-changed' );
			});

			$( '#dfseo-bulk-save-all' ).on( 'click', function() {
				const updates = Object.values( changes );
				if ( ! updates.length ) { alert( 'No changes to save.' ); return; }
				const $btn = $( this ).prop( 'disabled', true ).text( 'Saving…' );
				$.ajax({
					url:    dfseoAdmin.restUrl + 'bulk-update',
					method: 'POST',
					beforeSend( xhr ) { xhr.setRequestHeader( 'X-WP-Nonce', dfseoAdmin.restNonce ); },
					data: JSON.stringify({ updates }),
					contentType: 'application/json',
				}).done( function() {
					$btn.prop( 'disabled', false ).text( 'Save All Changes' );
					for ( const id in changes ) delete changes[id];
					$( 'tr.dfseo-changed' ).removeClass( 'dfseo-changed' );
					alert( dfseoAdmin.i18n.saved );
				}).fail( function() { $btn.prop( 'disabled', false ).text( 'Save All Changes' ); alert( dfseoAdmin.i18n.errorSaving ); });
			});

			$( document ).on( 'click', '.dfseo-bulk-page-btn', function() {
				currentPage = parseInt( $( this ).data( 'page' ) );
				dfSEO.loadBulkPosts( currentPage, changes );
			});
		},

		loadBulkPosts( page, changes ) {
			const $body = $( '#dfseo-bulk-tbody' );
			const $load = $( '#dfseo-bulk-loading' );
			const pt     = $( '#dfseo-bulk-pt' ).val();
			const filter = $( '#dfseo-bulk-filter' ).val();
			const search = $( '#dfseo-bulk-search' ).val();

			$body.html( '' );
			$load.show();

			$.ajax({
				url: dfseoAdmin.restUrl + 'bulk-posts?post_type=' + encodeURIComponent(pt) + '&filter=' + encodeURIComponent(filter) + '&search=' + encodeURIComponent(search) + '&page=' + page,
				method: 'GET',
				beforeSend( xhr ) { xhr.setRequestHeader( 'X-WP-Nonce', dfseoAdmin.restNonce ); },
			}).done( function( res ) {
				$load.hide();
				if ( ! res.posts || ! res.posts.length ) {
					$body.html( '<tr><td colspan="5" style="text-align:center;padding:20px;color:#6b7280">No posts found.</td></tr>' );
					return;
				}
				const rows = res.posts.map( p => {
					const s = p.seo_score;
					const cls = s >= 80 ? 'great' : s >= 50 ? 'ok' : s > 0 ? 'poor' : 'na';
					return `<tr data-id="${p.id}">
						<td><a href="${ dfSEO.esc(p.url) }" target="_blank">${ dfSEO.esc(p.title) }</a></td>
						<td><input type="text" class="dfseo-input dfseo-bulk-field" data-field="focus_keyword" value="${ dfSEO.esc(p.focus_keyword||'') }" placeholder="Focus keyword…"></td>
						<td><input type="text" class="dfseo-input dfseo-bulk-field" data-field="seo_title" value="${ dfSEO.esc(p.seo_title||'') }" placeholder="SEO title…"></td>
						<td><textarea class="dfseo-input dfseo-bulk-field" rows="2" data-field="meta_desc" placeholder="Meta description…">${ dfSEO.esc(p.meta_desc||'') }</textarea></td>
						<td><span class="dfseo-score-badge ${cls}">${s || '—'}</span></td>
					</tr>`;
				}).join('');
				$body.html( rows );

				// Pagination
				const $pag = $( '#dfseo-bulk-pagination' ).html( '' );
				for ( let i = 1; i <= res.pages; i++ ) {
					$pag.append( `<button type="button" class="dfseo-bulk-page-btn ${i === page ? 'active' : ''}" data-page="${i}">${i}</button>` );
				}
			}).fail( function() { $load.hide(); $body.html( '<tr><td colspan="5" style="color:red;padding:20px">Error loading posts.</td></tr>' ); });
		},

		// ── FAQ Schema Manager ────────────────────────────────────────────────
		bindFaqSchemaManager() {
			if ( ! $( '#dfseo-faq-add' ).length ) return;

			$( '#dfseo-faq-add' ).on( 'click', function() {
				const idx = $( '.dfseo-faq-item' ).length;
				const $item = $(`<div class="dfseo-faq-item" data-index="${idx}">
					<input type="text" class="dfseo-input dfseo-faq-q" placeholder="Question…">
					<textarea class="dfseo-textarea dfseo-faq-a" rows="2" placeholder="Answer…"></textarea>
					<button type="button" class="dfseo-btn dfseo-btn-ghost dfseo-faq-remove">Remove</button>
				</div>`);
				$( '#dfseo-faq-list' ).append( $item );
				dfSEO.syncFaqField();
			});

			$( document ).on( 'click', '.dfseo-faq-remove', function() {
				$( this ).closest( '.dfseo-faq-item' ).remove();
				dfSEO.syncFaqField();
			});

			$( document ).on( 'input', '.dfseo-faq-q, .dfseo-faq-a', function() {
				dfSEO.syncFaqField();
			});
		},

		syncFaqField() {
			const faqs = [];
			$( '.dfseo-faq-item' ).each( function() {
				const q = $( this ).find( '.dfseo-faq-q' ).val();
				const a = $( this ).find( '.dfseo-faq-a' ).val();
				if ( q || a ) faqs.push({ q, a });
			});
			$( '#dfseo_faq_items' ).val( JSON.stringify( faqs ) );
		},

		// ── Utilities ────────────────────────────────────────────────────────
		esc( str ) {
			return String( str || '' ).replace( /&/g,'&amp;' ).replace( /</g,'&lt;' ).replace( />/g,'&gt;' ).replace( /"/g,'&quot;' ).replace( /'/g,'&#039;' );
		},

		restPost( endpoint, data ) {
			return $.ajax({
				url:    dfseoAdmin.restUrl + endpoint,
				method: 'POST',
				beforeSend( xhr ) { xhr.setRequestHeader( 'X-WP-Nonce', dfseoAdmin.restNonce ); },
				data:         JSON.stringify( data ),
				contentType: 'application/json',
			});
		},
	};

	$( document ).ready( function() { dfSEO.init(); } );

	// Expose for other scripts
	window.dfSEO = dfSEO;

})( jQuery );
