/**
 * DadsFam SEO — Post Editor Analysis & SERP Preview JS
 * Handles: live SERP preview, char counters, real-time analysis, AI tool calls
 *
 * @package DadsFam_SEO
 */
/* global dfseoAdmin, dfSEO, jQuery, tinymce */

(function( $ ) {
	'use strict';

	const dfAnalysis = {

		debounceTimer: null,
		lastKeyword:   '',
		serpMode:      'desktop',

		TITLE_IDEAL_MIN:  50,
		TITLE_IDEAL_MAX:  60,
		DESC_IDEAL_MIN:   120,
		DESC_IDEAL_MAX:   160,

		// ── Labels for each check key ──────────────────────────────────────
		labels: {
			keyword_in_title:          'Keyword in SEO title',
			keyword_in_meta_desc:      'Keyword in meta description',
			keyword_in_url:            'Keyword in URL/slug',
			keyword_in_intro:          'Keyword in first paragraph',
			keyword_density:           'Keyword density (0.5–2.5%)',
			keyword_in_h1:             'Keyword in H1 heading',
			keyword_in_headings:       'Keyword in subheadings',
			keyword_in_alt:            'Keyword in image alt text',
			title_length:              'SEO title length (50–60 chars)',
			meta_desc_length:          'Meta description length (120–160 chars)',
			has_h1:                    'Has an H1 heading',
			has_subheadings:           'Uses H2/H3 subheadings',
			content_length:            'Content length (600+ words)',
			has_images:                'Contains images',
			all_images_have_alt:       'All images have alt text',
			has_internal_links:        'Has internal links',
			has_external_links:        'Has outbound links',
			no_keyword_stuffing:       'No keyword stuffing',
			readability_sentence_len:  'Average sentence length',
			readability_paragraph:     'Paragraph length',
			readability_transition:    'Transition words used',
			readability_passive:       'Passive voice not overused',
			readability_fleschkincaid: 'Flesch reading ease',
			geo_question_heading:      '🤖 Question-style heading (AI-friendly)',
			geo_has_list_or_table:     '🤖 Has list or table (AI-citable)',
			geo_direct_answer:         '🤖 Direct answer in opening',
		},

		readabilityKeys: new Set([
			'readability_sentence_len',
			'readability_paragraph',
			'readability_transition',
			'readability_passive',
			'readability_fleschkincaid',
		]),

		// ── Init ─────────────────────────────────────────────────────────
		init() {
			this.bindSerpPreview();
			this.bindAnalysisTriggers();
			this.bindAnalyseButton();
			this.bindAiTools();
			this.bindLinkSuggestions();
			this.initCharCounters();

			// Auto-run analysis if focus keyword already set
			const kw = $( '#dfseo_focus_keyword' ).val();
			if ( kw ) {
				setTimeout( () => this.runAnalysis(), 800 );
			}
		},

		// ── SERP Preview ─────────────────────────────────────────────────
		bindSerpPreview() {
			// Desktop/Mobile toggle
			$( document ).on( 'click', '.dfseo-serp-mode', ( e ) => {
				const mode = $( e.currentTarget ).data( 'mode' );
				this.serpMode = mode;
				$( '.dfseo-serp-mode' ).removeClass( 'active' );
				$( e.currentTarget ).addClass( 'active' );
				$( '#dfseo-serp-desktop' ).toggle( mode === 'desktop' );
				$( '#dfseo-serp-mobile' ).toggle( mode === 'mobile' );
			});

			// Update SERP preview on input
			$( document ).on( 'input', '#dfseo_title, #dfseo_meta_desc', () => {
				this.updateSerpPreview();
			});
		},

		updateSerpPreview() {
			const titleInput = $( '#dfseo_title' ).val().trim();
			const descInput  = $( '#dfseo_meta_desc' ).val().trim();
			const siteName   = dfseoAdmin.siteName || '';
			const separator  = dfseoAdmin.separator || '–';

			const displayTitle = titleInput
				|| ( $( '#title' ).val() ? $( '#title' ).val() + ' ' + separator + ' ' + siteName : siteName );
			const displayDesc  = descInput || $( '#dfseo-serp-desc-d' ).text();

			// Desktop
			$( '#dfseo-serp-title-d' ).text( this.truncate( displayTitle, 60 ) );
			$( '#dfseo-serp-desc-d' ).text( this.truncate( displayDesc, 160 ) );

			// Mobile
			$( '#dfseo-serp-title-m' ).text( this.truncate( displayTitle, 60 ) );
			$( '#dfseo-serp-desc-m' ).text( this.truncate( displayDesc, 120 ) );

			// OG card
			$( '#dfseo-og-card-title' ).text( this.truncate( displayTitle, 55 ) );
			$( '#dfseo-og-card-desc' ).text( this.truncate( displayDesc, 80 ) );
		},

		// ── Char counters ─────────────────────────────────────────────────
		initCharCounters() {
			this.updateTitleCounter();
			this.updateDescCounter();

			$( document ).on( 'input', '#dfseo_title', () => {
				this.updateTitleCounter();
				this.debounceAnalysis();
			});

			$( document ).on( 'input', '#dfseo_meta_desc', () => {
				this.updateDescCounter();
				this.debounceAnalysis();
			});

			$( document ).on( 'input', '#dfseo_focus_keyword', () => {
				this.debounceAnalysis();
			});
		},

		updateTitleCounter() {
			const len = mb_strlen( $( '#dfseo_title' ).val() );
			const $count = $( '#dfseo-title-count' );
			const $bar   = $( '#dfseo-title-progress' );
			const $ctr   = $( '#dfseo-title-counter' );

			$count.text( len );
			const pct = Math.min( 100, Math.round( len / 60 * 100 ) );
			$bar.css( 'width', pct + '%' );

			$ctr.removeClass( 'good warn over' );
			$bar.css( 'background', '#d1d5db' );
			if ( len >= this.TITLE_IDEAL_MIN && len <= this.TITLE_IDEAL_MAX ) {
				$ctr.addClass( 'good' );
				$bar.css( 'background', '#16a34a' );
			} else if ( len > this.TITLE_IDEAL_MAX ) {
				$ctr.addClass( 'over' );
				$bar.css( 'background', '#dc2626' );
			} else if ( len > 30 ) {
				$ctr.addClass( 'warn' );
				$bar.css( 'background', '#d97706' );
			}
		},

		updateDescCounter() {
			const len = mb_strlen( $( '#dfseo_meta_desc' ).val() );
			const $count = $( '#dfseo-desc-count' );
			const $bar   = $( '#dfseo-desc-progress' );
			const $ctr   = $( '#dfseo-desc-counter' );

			$count.text( len );
			const pct = Math.min( 100, Math.round( len / 160 * 100 ) );
			$bar.css( 'width', pct + '%' );

			$ctr.removeClass( 'good warn over' );
			$bar.css( 'background', '#d1d5db' );
			if ( len >= this.DESC_IDEAL_MIN && len <= this.DESC_IDEAL_MAX ) {
				$ctr.addClass( 'good' );
				$bar.css( 'background', '#16a34a' );
			} else if ( len > this.DESC_IDEAL_MAX ) {
				$ctr.addClass( 'over' );
				$bar.css( 'background', '#dc2626' );
			} else if ( len > 70 ) {
				$ctr.addClass( 'warn' );
				$bar.css( 'background', '#d97706' );
			}
		},

		// ── Analysis triggers ──────────────────────────────────────────────
		bindAnalysisTriggers() {
			// Debounced on keyword/title/desc changes
			$( document ).on( 'input', '#dfseo_focus_keyword', () => {
				this.debounceAnalysis( 1000 );
			});
		},

		debounceAnalysis( delay = 1500 ) {
			clearTimeout( this.debounceTimer );
			this.debounceTimer = setTimeout( () => this.runAnalysis(), delay );
		},

		bindAnalyseButton() {
			$( document ).on( 'click', '#dfseo-run-analysis', () => {
				this.runAnalysis();
			});
		},

		// ── Internal link suggestions ──────────────────────────────────────
		bindLinkSuggestions() {
			$( document ).on( 'click', '#dfseo-find-links', () => {
				const $box = $( '#dfseo-link-suggestions' );
				const keyword = $( '#dfseo_focus_keyword' ).val().trim();
				const title = $( '#dfseo_title' ).val().trim()
					|| ( $( '#title' ).val() ? $( '#title' ).val().trim() : '' );

				if ( ! keyword && ! title ) {
					$box.html( '<p class="dfseo-muted" style="font-size:13px">' +
						'Add a focus keyword (or post title) first, then search for link opportunities.</p>' );
					return;
				}

				$box.html( '<p class="dfseo-muted" style="font-size:13px"><span class="dfseo-spinner"></span> Searching your content…</p>' );

				$.ajax({
					url: dfseoAdmin.restUrl + 'link-suggestions',
					method: 'GET',
					data: { post_id: dfseoAdmin.postId, keyword: keyword, title: title },
					beforeSend( xhr ) { xhr.setRequestHeader( 'X-WP-Nonce', dfseoAdmin.restNonce ); }
				}).done( ( data ) => {
					const items = ( data && data.suggestions ) || [];
					if ( ! items.length ) {
						$box.html( '<p class="dfseo-muted" style="font-size:13px">No related published content found yet. As you publish more posts on similar topics, suggestions will appear here.</p>' );
						return;
					}
					const rows = items.map( ( s ) => {
						const linked = s.already_linked
							? '<span style="font-size:11px;color:#16a34a;font-weight:700;white-space:nowrap">✓ already linked</span>'
							: '<button type="button" class="dfseo-btn dfseo-btn-ghost dfseo-btn-sm dfseo-copy-link" data-url="' + s.url + '" style="white-space:nowrap">📋 Copy link</button>';
						const matched = ( s.matched || [] ).slice( 0, 3 ).join( ', ' );
						return '<div style="display:flex;align-items:center;gap:10px;padding:8px 10px;background:#f9fafb;border-radius:8px;margin-bottom:6px">'
							+ '<div style="flex:1;min-width:0">'
							+ '<a href="' + s.url + '" target="_blank" style="font-weight:600;font-size:13px;text-decoration:none;display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">' + $( '<span>' ).text( s.title ).html() + '</a>'
							+ ( matched ? '<span style="font-size:11px;color:#9ca3af">matches: ' + $( '<span>' ).text( matched ).html() + '</span>' : '' )
							+ '</div>' + linked + '</div>';
					});
					$box.html(
						'<p class="dfseo-hint" style="margin:0 0 8px">' + items.length + ' related page(s) found. Copy a link and paste it into your content where relevant:</p>'
						+ rows.join( '' )
					);
				}).fail( () => {
					$box.html( '<p class="dfseo-muted" style="font-size:13px">Could not load suggestions. Please try again.</p>' );
				});
			});

			// Copy link to clipboard
			$( document ).on( 'click', '.dfseo-copy-link', function() {
				const url = $( this ).data( 'url' );
				const $btn = $( this );
				navigator.clipboard.writeText( url ).then( () => {
					const orig = $btn.html();
					$btn.html( '✓ Copied!' );
					setTimeout( () => $btn.html( orig ), 1500 );
				});
			});
		},

		// ── Run analysis ───────────────────────────────────────────────────
		runAnalysis() {
			const keyword = $( '#dfseo_focus_keyword' ).val().trim();
			if ( ! keyword ) {
				$( '#dfseo-analysis-results' ).html(
					'<p class="dfseo-muted" style="padding:8px 0">' + dfseoAdmin.i18n.noKeyword + '</p>'
				);
				return;
			}

			const $loading = $( '#dfseo-analysis-loading' );
			const $results = $( '#dfseo-analysis-results' );
			$loading.show();
			$results.html( '' );

			// Get TinyMCE content if active
			let content = '';
			if ( typeof tinymce !== 'undefined' && tinymce.activeEditor && ! tinymce.activeEditor.isHidden() ) {
				content = tinymce.activeEditor.getContent({ format: 'text' });
			} else {
				content = $( '#content' ).val() || '';
			}

			$.post(
				dfseoAdmin.ajaxUrl,
				{
					action:        'dfseo_run_analysis',
					nonce:         dfseoAdmin.nonce,
					post_id:       dfseoAdmin.postId,
					focus_keyword: keyword,
					title:         $( '#dfseo_title' ).val(),
					meta_desc:     $( '#dfseo_meta_desc' ).val(),
					content,
				},
				( response ) => {
					$loading.hide();
					if ( response.success && response.data ) {
						this.renderResults( response.data );
					} else {
						$results.html( '<p style="color:#dc2626">Analysis failed. Please try again.</p>' );
					}
				}
			).fail( () => {
				$loading.hide();
				$results.html( '<p style="color:#dc2626">Server error. Please try again.</p>' );
			});
		},

		// ── Render results ─────────────────────────────────────────────────
		renderResults( data ) {
			const score   = data.score   || 0;
			const checks  = data.checks  || {};
			const readability = data.readability || {};

			// Update score circle
			this.updateScoreCircle( score );

			// Separate checks into SEO and readability
			const seoChecks  = {};
			const readChecks = {};
			for ( const [ key, val ] of Object.entries( checks ) ) {
				if ( this.readabilityKeys.has( key ) ) readChecks[key] = val;
				else seoChecks[key] = val;
			}

			// Render SEO tab results
			$( '#dfseo-analysis-results' ).html( this.buildChecksHtml( seoChecks ) );

			// Render readability tab
			$( '#dfseo-readability-summary' ).html( this.buildReadabilityHtml( readChecks, readability ) );
		},

		buildChecksHtml( checks ) {
			const good = [], warn = [], bad = [];
			for ( const [ key, val ] of Object.entries( checks ) ) {
				const item = { key, ...val };
				if ( val.status === 'good' )     good.push( item );
				else if ( val.status === 'ok' )  warn.push( item );
				else                              bad.push( item );
			}

			let html = '';
			if ( bad.length ) {
				html += '<div class="dfseo-analysis-section">';
				html += '<div class="dfseo-analysis-section-title">⚠️ Needs improvement (' + bad.length + ')</div>';
				html += bad.map( c => this.checkItemHtml( c ) ).join('');
				html += '</div>';
			}
			if ( warn.length ) {
				html += '<div class="dfseo-analysis-section">';
				html += '<div class="dfseo-analysis-section-title">💡 Could be better (' + warn.length + ')</div>';
				html += warn.map( c => this.checkItemHtml( c ) ).join('');
				html += '</div>';
			}
			if ( good.length ) {
				html += '<div class="dfseo-analysis-section">';
				html += '<div class="dfseo-analysis-section-title">✅ Good (' + good.length + ')</div>';
				html += good.map( c => this.checkItemHtml( c ) ).join('');
				html += '</div>';
			}
			if ( ! html ) html = '<p class="dfseo-muted">No checks returned.</p>';
			return html;
		},

		checkItemHtml( check ) {
			const icons   = { good: '✅', ok: '💡', bad: '❌', na: '➖' };
			const label   = this.labels[ check.key ] || check.key;
			const message = check.message || '';
			return `<div class="dfseo-check-item ${check.status}">
				<span class="dfseo-check-icon">${icons[check.status] || '➖'}</span>
				<div>
					<strong>${this.esc(label)}</strong>
					${message ? '<br><span>' + this.esc(message) + '</span>' : ''}
				</div>
			</div>`;
		},

		buildReadabilityHtml( checks, readability ) {
			const fk   = readability.flesch_kincaid || 0;
			const grade = fk >= 60 ? 'great' : fk >= 40 ? 'ok' : 'poor';
			const label = fk >= 60 ? 'Easy to read' : fk >= 40 ? 'Moderate' : 'Difficult';

			let html = `<div class="dfseo-readability-meter">
				<div class="dfseo-readability-score-num ${grade}">${Math.round(fk)}</div>
				<div>
					<strong>${this.esc(label)}</strong>
					<div class="dfseo-hint">Flesch Reading Ease score (higher = easier)</div>
				</div>
			</div>`;

			if ( Object.keys( checks ).length ) {
				html += this.buildChecksHtml( checks );
			}

			const stats = readability.stats || {};
			if ( Object.keys( stats ).length ) {
				html += `<div class="dfseo-analysis-section">
					<div class="dfseo-analysis-section-title">Content stats</div>
					<div class="dfseo-check-item na">
						<span class="dfseo-check-icon">📊</span>
						<div>
							Words: <strong>${stats.word_count || 0}</strong> &nbsp;
							Sentences: <strong>${stats.sentence_count || 0}</strong> &nbsp;
							Paragraphs: <strong>${stats.paragraph_count || 0}</strong>
						</div>
					</div>
				</div>`;
			}
			return html;
		},

		// ── Score circle update ────────────────────────────────────────────
		updateScoreCircle( score ) {
			const grade  = score >= 80 ? 'great' : score >= 50 ? 'ok' : score > 0 ? 'poor' : 'na';
			const labels = { great: dfseoAdmin.i18n.good, ok: dfseoAdmin.i18n.ok, poor: dfseoAdmin.i18n.poor, na: 'Not analysed' };

			$( '#dfseo-score-num' ).text( score );
			$( '#dfseo-score-circle' ).attr( 'class', 'dfseo-score-circle ' + grade );
			$( '#dfseo-score-label' ).text( labels[ grade ] ).attr( 'class', 'dfseo-score-label ' + grade );
			$( '.dfseo-score-fill' ).attr( 'stroke-dasharray', score + ', 100' );
		},

		// ── AI Tools ──────────────────────────────────────────────────────
		bindAiTools() {
			$( document ).on( 'click', '.dfseo-ai-action', ( e ) => {
				if ( ! dfseoAdmin.isPremium ) {
					alert( dfseoAdmin.i18n.premiumRequired );
					return;
				}
				const $btn    = $( e.currentTarget );
				const action  = $btn.data( 'action' );
				const $btnTxt = $btn.find( '.dfseo-btn-text' );
				const $btnLdr = $btn.find( '.dfseo-btn-loading' );

				$btnTxt.hide();
				$btnLdr.show();
				$btn.prop( 'disabled', true );

				this.callAiApi( action ).done( ( res ) => {
					$btnTxt.show(); $btnLdr.hide(); $btn.prop( 'disabled', false );
					if ( res ) this.renderAiResult( action, res );
				}).fail( () => {
					$btnTxt.show(); $btnLdr.hide(); $btn.prop( 'disabled', false );
					alert( 'AI request failed. Check your API key in DadsFam SEO → Settings → AI Tools.' );
				});
			});

			// Close AI result panel
			$( document ).on( 'click', '#dfseo-ai-result-close', () => {
				$( '#dfseo-ai-result' ).hide();
			});
		},

		callAiApi( action ) {
			let content = '';
			if ( typeof tinymce !== 'undefined' && tinymce.activeEditor && ! tinymce.activeEditor.isHidden() ) {
				content = tinymce.activeEditor.getContent({ format: 'text' });
			} else {
				content = $( '#content' ).val() || '';
			}

			return $.ajax({
				url:    dfseoAdmin.restUrl + 'ai/' + action,
				method: 'POST',
				beforeSend( xhr ) { xhr.setRequestHeader( 'X-WP-Nonce', dfseoAdmin.restNonce ); },
				data: JSON.stringify({
					post_id:       dfseoAdmin.postId,
					focus_keyword: $( '#dfseo_focus_keyword' ).val(),
					title:         $( '#title' ).val() || '',
					content:       content.substring( 0, 8000 ), // Limit for API
				}),
				contentType: 'application/json',
			});
		},

		renderAiResult( action, data ) {
			const $panel   = $( '#dfseo-ai-result' );
			const $title   = $( '#dfseo-ai-result-title' );
			const $content = $( '#dfseo-ai-result-content' );

			const titleMap = {
				'generate-meta':           '🎯 Generated Meta Tags',
				'suggest-keywords':        '🔍 Keyword Suggestions',
				'optimize-content':        '📈 Content Optimisation Tips',
				'generate-title-variants': '✏️ Title Variants',
				'content-outline':         '📋 Content Outline',
				'generate-faq':            '❓ FAQ Suggestions',
			};
			$title.text( titleMap[action] || 'AI Result' );

			if ( action === 'generate-meta' ) {
				const title = data.title || '';
				const desc  = data.description || '';
				$content.html(`
					<div class="dfseo-ai-meta-result">
						<label class="dfseo-label">SEO Title</label>
						<div style="display:flex;gap:8px;margin-bottom:12px">
							<input type="text" class="dfseo-input" id="dfseo-ai-title-val" value="${this.esc(title)}">
							<button type="button" class="dfseo-btn dfseo-btn-primary dfseo-btn-sm" id="dfseo-ai-use-title">Use this</button>
						</div>
						<label class="dfseo-label">Meta Description</label>
						<div style="display:flex;gap:8px">
							<textarea class="dfseo-textarea" rows="2" id="dfseo-ai-desc-val">${this.esc(desc)}</textarea>
							<button type="button" class="dfseo-btn dfseo-btn-primary dfseo-btn-sm" id="dfseo-ai-use-desc">Use this</button>
						</div>
					</div>`);
				$( '#dfseo-ai-use-title' ).on( 'click', () => {
					$( '#dfseo_title' ).val( $( '#dfseo-ai-title-val' ).val() ).trigger( 'input' );
				});
				$( '#dfseo-ai-use-desc' ).on( 'click', () => {
					$( '#dfseo_meta_desc' ).val( $( '#dfseo-ai-desc-val' ).val() ).trigger( 'input' );
				});
			} else if ( action === 'generate-title-variants' ) {
				const variants = data.variants || [];
				$content.html( '<ul>' + variants.map( (v, i) =>
					`<li style="margin-bottom:8px">
						<div style="display:flex;align-items:center;gap:8px">
							<span style="flex:1">${this.esc(v)}</span>
							<button type="button" class="dfseo-btn dfseo-btn-sm dfseo-btn-secondary dfseo-use-variant" data-val="${this.esc(v)}">Use</button>
						</div>
					</li>`
				).join('') + '</ul>' );
				$( document ).on( 'click', '.dfseo-use-variant', function() {
					$( '#dfseo_title' ).val( $( this ).data('val') ).trigger( 'input' );
				});
			} else if ( action === 'suggest-keywords' ) {
				const kws = data.keywords || [];
				const diffColor = { easy:'#16a34a', medium:'#d97706', hard:'#dc2626' };
				$content.html(
					'<div style="display:flex;flex-direction:column;gap:6px">' +
					kws.map( kw => {
						// Each kw may be an object {keyword,type,intent,difficulty} or a plain string
						const word = ( typeof kw === 'string' ) ? kw : ( kw.keyword || '' );
						const type = ( kw && kw.type ) ? kw.type : '';
						const intent = ( kw && kw.intent ) ? kw.intent : '';
						const diff = ( kw && kw.difficulty ) ? kw.difficulty : '';
						const dc = diffColor[diff] || '#6b7280';
						return `<div style="display:flex;align-items:center;gap:8px;padding:6px 10px;background:#f9fafb;border-radius:8px">
							<span class="dfseo-use-kw" style="flex:1;cursor:pointer;font-weight:600;font-size:13px" title="Click to use as focus keyword">${this.esc(word)}</span>
							${type ? `<span style="font-size:10px;text-transform:uppercase;color:#6b7280;background:#fff;padding:2px 6px;border-radius:4px">${this.esc(type)}</span>` : ''}
							${intent ? `<span style="font-size:10px;color:#6b7280">${this.esc(intent)}</span>` : ''}
							${diff ? `<span style="font-size:10px;font-weight:700;color:${dc}">${this.esc(diff)}</span>` : ''}
						</div>`;
					}).join('') +
					'</div><p class="dfseo-hint" style="margin-top:8px">Click any keyword to set it as your focus keyword.</p>' );
				$( document ).on( 'click', '.dfseo-use-kw', function() {
					$( '#dfseo_focus_keyword' ).val( $( this ).text() ).trigger( 'input' );
					dfAnalysis.debounceAnalysis( 500 );
				});
			} else if ( action === 'optimize-content' ) {
				const tips = data.recommendations || data.tips || [];
				const prioColor = { high:'#dc2626', medium:'#d97706', low:'#16a34a' };
				$content.html( tips.map( t => {
					// Each tip may be an object or a plain string
					if ( typeof t === 'string' ) {
						return `<div style="padding:8px 12px;border-left:3px solid #1a4fa0;background:#f9fafb;margin-bottom:8px;font-size:13px">${this.esc(t)}</div>`;
					}
					const prio = t.priority || '';
					const pc = prioColor[prio] || '#6b7280';
					const cat = t.category || '';
					const title = t.title || '';
					const desc = t.description || '';
					const fix = t.how_to_fix || t.howToFix || '';
					return `<div style="padding:10px 14px;border-left:3px solid ${pc};background:#f9fafb;margin-bottom:10px;border-radius:0 6px 6px 0">
						<div style="display:flex;align-items:center;gap:8px;margin-bottom:4px">
							${prio ? `<span style="font-size:10px;font-weight:700;text-transform:uppercase;color:#fff;background:${pc};padding:2px 8px;border-radius:99px">${this.esc(prio)}</span>` : ''}
							${cat ? `<span style="font-size:10px;text-transform:uppercase;color:#6b7280">${this.esc(cat)}</span>` : ''}
						</div>
						<strong style="font-size:13px;display:block;margin-bottom:3px">${this.esc(title)}</strong>
						${desc ? `<p style="font-size:12px;color:#4b5563;margin:0 0 4px">${this.esc(desc)}</p>` : ''}
						${fix ? `<p style="font-size:12px;color:#1a4fa0;margin:0">💡 ${this.esc(fix)}</p>` : ''}
					</div>`;
				}).join('') );
			} else if ( action === 'content-outline' ) {
				// Structured outline: {h1, intro, sections:[{h2, subsections:[{h3,key_points,keywords}]}], conclusion, faqs}
				const sections = data.sections || data.outline || [];
				let html = '<div class="dfseo-outline-result" style="font-size:13px">';
				if ( data.h1 ) html += `<div style="font-size:15px;font-weight:800;margin-bottom:8px">${this.esc(data.h1)}</div>`;
				if ( data.intro ) html += `<p style="color:#4b5563;margin:0 0 12px;font-style:italic">${this.esc(data.intro)}</p>`;

				if ( Array.isArray(sections) && sections.length ) {
					sections.forEach( s => {
						if ( typeof s === 'string' ) { html += `<div style="margin-bottom:6px">• ${this.esc(s)}</div>`; return; }
						html += `<div style="margin:10px 0 4px;font-weight:700;color:#1a4fa0">H2 — ${this.esc(s.h2 || s.title || '')}</div>`;
						if ( s.estimated_words ) html += `<span style="font-size:11px;color:#9ca3af">~${parseInt(s.estimated_words)} words</span>`;
						const subs = s.subsections || [];
						subs.forEach( sub => {
							html += `<div style="margin:6px 0 2px 16px;font-weight:600">H3 — ${this.esc(sub.h3 || sub.title || '')}</div>`;
							const pts = sub.key_points || sub.points || [];
							if ( pts.length ) html += '<ul style="margin:2px 0 4px 32px;color:#4b5563">' + pts.map(p=>`<li>${this.esc(p)}</li>`).join('') + '</ul>';
							const kws = sub.keywords || [];
							if ( kws.length ) html += `<div style="margin:0 0 4px 32px;font-size:11px;color:#16a34a">🔑 ${kws.map(k=>this.esc(k)).join(', ')}</div>`;
						});
					});
				}
				if ( data.conclusion ) html += `<div style="margin:10px 0 4px;font-weight:700;color:#1a4fa0">Conclusion</div><p style="color:#4b5563;margin:0 0 8px">${this.esc(data.conclusion)}</p>`;
				if ( data.faqs && data.faqs.length ) {
					html += '<div style="margin:10px 0 4px;font-weight:700;color:#1a4fa0">FAQs</div><ul style="margin:0 0 8px 16px;color:#4b5563">';
					html += data.faqs.map( f => `<li>${this.esc(f.q || f.question || '')}</li>` ).join('');
					html += '</ul>';
				}
				html += '<button type="button" class="dfseo-btn dfseo-btn-primary dfseo-btn-sm" id="dfseo-insert-outline" style="margin-top:10px">📝 Insert outline into editor</button>';
				html += '</div>';
				$content.html( html );

				// Build a clean markdown/HTML version for insertion
				$( '#dfseo-insert-outline' ).on( 'click', () => {
					let md = '';
					if ( data.h1 ) md += `<h1>${data.h1}</h1>\n`;
					if ( data.intro ) md += `<p>${data.intro}</p>\n`;
					(sections||[]).forEach( s => {
						if ( typeof s === 'string' ) { md += `<h2>${s}</h2>\n`; return; }
						md += `<h2>${s.h2 || s.title || ''}</h2>\n`;
						( s.subsections || [] ).forEach( sub => {
							md += `<h3>${sub.h3 || sub.title || ''}</h3>\n`;
							const pts = sub.key_points || sub.points || [];
							if ( pts.length ) md += '<ul>' + pts.map(p=>`<li>${p}</li>`).join('') + '</ul>\n';
						});
					});
					if ( data.conclusion ) md += `<h2>Conclusion</h2>\n<p>${data.conclusion}</p>\n`;
					this.insertIntoEditor( md );
				});
			} else if ( action === 'generate-faq' ) {
				const faqs = data.faqs || [];
				faqs.forEach( faq => {
					const idx = $( '.dfseo-faq-item' ).length;
					const $item = $(`<div class="dfseo-faq-item" data-index="${idx}">
						<input type="text" class="dfseo-input dfseo-faq-q" value="${this.esc(faq.question||faq.q||'')}">
						<textarea class="dfseo-textarea dfseo-faq-a" rows="2">${this.esc(faq.answer||faq.a||'')}</textarea>
						<button type="button" class="dfseo-btn dfseo-btn-ghost dfseo-faq-remove">Remove</button>
					</div>`);
					$( '#dfseo-faq-list' ).append( $item );
				});
				if ( typeof dfSEO !== 'undefined' ) dfSEO.syncFaqField();
				$content.html( '<p class="dfseo-ok">✓ ' + faqs.length + ' FAQ items added above.</p>' );
			} else {
				$content.html( '<pre style="white-space:pre-wrap;font-size:12px">' + this.esc( JSON.stringify( data, null, 2 ) ) + '</pre>' );
			}

			$panel.slideDown();
		},

		// ── Utilities ─────────────────────────────────────────────────────
		truncate( str, max ) {
			if ( ! str ) return '';
			if ( str.length <= max ) return str;
			return str.substring( 0, max - 1 ) + '…';
		},

		// Insert HTML content into the active editor (Gutenberg or Classic)
		insertIntoEditor( html ) {
			try {
				// Gutenberg block editor
				if ( window.wp && wp.data && wp.data.select( 'core/block-editor' ) && wp.blocks ) {
					const blocks = wp.blocks.rawHandler ? wp.blocks.rawHandler({ HTML: html }) : [];
					if ( blocks.length ) {
						wp.data.dispatch( 'core/block-editor' ).insertBlocks( blocks );
						alert( 'Outline inserted into the editor. ✅' );
						return;
					}
				}
				// Classic editor (TinyMCE)
				if ( window.tinymce && tinymce.activeEditor && ! tinymce.activeEditor.isHidden() ) {
					tinymce.activeEditor.execCommand( 'mceInsertContent', false, html );
					alert( 'Outline inserted into the editor. ✅' );
					return;
				}
				// Classic editor (plain textarea)
				const $ta = jQuery( '#content' );
				if ( $ta.length ) {
					$ta.val( $ta.val() + '\n' + html );
					alert( 'Outline added to the content box. ✅' );
					return;
				}
				// Fallback — copy to clipboard
				navigator.clipboard.writeText( html.replace( /<[^>]+>/g, '' ) );
				alert( 'Could not detect the editor, so the outline was copied to your clipboard instead. 📋' );
			} catch ( e ) {
				console.error( 'DFSEO insert outline:', e );
				alert( 'Could not insert automatically. The outline is shown above to copy manually.' );
			}
		},

		esc( str ) {
			return String( str || '' )
				.replace( /&/g, '&amp;' )
				.replace( /</g, '&lt;' )
				.replace( />/g, '&gt;' )
				.replace( /"/g, '&quot;' )
				.replace( /'/g, '&#039;' );
		},
	};

	// Simple JS "mb_strlen" (counts characters, not bytes)
	function mb_strlen( str ) { return ( str || '' ).length; }

	$( document ).ready( function() { dfAnalysis.init(); } );

	window.dfAnalysis = dfAnalysis;

})( jQuery );
