<?php
/**
 * Changelog content — full version history.
 * Prepend a new block here every time you ship a release.
 *
 * @package DadsFam_SEO
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<div class="dfseo-changelog">

    <!-- ── v1.4.5 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.4.5</span>
            <span class="dfseo-cl-date">31 May 2025</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--fix">Bug Fix</span>
        </div>
        <div class="dfseo-cl-body">
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--fixed">Fixed</span>
                <ul>
                    <li><strong>AI Tools showing &ldquo;[object Object]&rdquo; instead of results</strong> &mdash; three AI tools (Keyword Suggestions, Content Optimisation, and Content Outline) returned structured data objects, but the display code treated each item as plain text. They now render properly with full detail.</li>
                    <li><strong>Keyword Suggestions</strong> now shows each keyword with its type (primary/long-tail/semantic), search intent, and difficulty &mdash; click any keyword to set it as your focus keyword.</li>
                    <li><strong>Content Optimisation</strong> now shows each recommendation with a colour-coded priority badge (high/medium/low), category, description, and a concrete how-to-fix tip.</li>
                    <li><strong>Content Outline</strong> now renders the full hierarchical outline (H1, intro, H2 sections, H3 subsections, key points, target keywords, conclusion, FAQs) with a one-click <strong>&ldquo;Insert outline into editor&rdquo;</strong> button that works in both the Block editor and Classic editor.</li>
                    <li><strong>More reliable AI responses</strong> &mdash; JSON extraction is now robust against any extra text the AI includes around the data, so tools no longer fail with &ldquo;Invalid response&rdquo;.</li>
                </ul>
            </div>
        </div>
    </div>
<!-- ── v1.4.3 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.4.3</span>
            <span class="dfseo-cl-date">2 Jun 2026</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--fix">Bug Fix</span>
        </div>
        <div class="dfseo-cl-body">
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--fixed">Fixed</span>
                <ul>
                    <li><strong>AI API key wiped on every Save Settings</strong> — the API key field was <code>type="password"</code> with the real key in the HTML <code>value</code> attribute. Some browsers intentionally do not submit pre-filled password field values on form save. Every Save Settings wipe the key to empty. Now the field never exposes the real key in HTML (shows a safe masked placeholder like <code>sk-ant-••••••XXXXXX</code>), and an empty submission is skipped so the existing key is always preserved.</li>
                    <li><strong>Claude model string updated</strong> — <code>claude-sonnet-4-20250514</code> → <code>claude-sonnet-4-6</code> (current API model ID).</li>
                </ul>
            </div>
        </div>
<!-- ── v1.4.2 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.4.2</span>
            <span class="dfseo-cl-date">2 Jun 2026</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--fix">Bug Fix</span>
        </div>
        <div class="dfseo-cl-body">
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--fixed">Fixed</span>
                <ul>
                    <li><strong>SEO score column broken on WooCommerce products list</strong> — <code>manage_posts_columns</code> fires for ALL post types including WooCommerce products. With 9 existing columns, the extra SEO column overflowed off-screen and the header was squashed into a vertical "S E O" text. Replaced the generic hook with type-specific hooks (<code>manage_post_posts_columns</code>, <code>manage_page_posts_columns</code>) and added a proper WooCommerce integration via <code>manage_product_posts_columns</code> with a compact 🏆 header and a 60px fixed width so it never overflows again.</li>
                </ul>
            </div>
        </div>
<!-- ── v1.4.1 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.4.1</span>
            <span class="dfseo-cl-date">1 Jun 2026</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--fix">Bug Fix</span>
        </div>
        <div class="dfseo-cl-body">
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--fixed">Fixed</span>
                <ul>
                    <li><strong>Analytics "Traffic by Search Engine" and "Content Decay" stuck on "Loading…"</strong> — Chart.js was never enqueued as a WordPress script dependency. When the timeline had data, <code>new Chart(...)</code> threw <code>ReferenceError: Chart is not defined</code>, halting JS execution before the engine breakdown and content decay sections could render. Chart.js is now properly enqueued from CDN as a dependency. Each dashboard section is also wrapped in its own try-catch so one failure can never block the others.</li>
                </ul>
            </div>
        </div>
<!-- ── v1.4.0 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.4.0</span>
            <span class="dfseo-cl-date">30 May 2025</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--feature">Major Update</span>
        </div>
        <div class="dfseo-cl-body">
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--new">New</span>
                <ul>
                    <li><strong>Analytics dashboard completely rebuilt</strong> — four summary cards (Organic Clicks, Impressions, Top Search Engine, Declining Posts) each with trend arrows showing % change vs the previous period.</li>
                    <li><strong>Date range selector</strong> — 7, 14, 30, 60, 90 day views. Previously the REST endpoint supported date ranges but the UI had no way to use them.</li>
                    <li><strong>Trend comparison</strong> — every stat now shows ↑ or ↓ with a percentage compared to the same-length period before it, so you know immediately if traffic is growing or falling.</li>
                    <li><strong>Traffic by Search Engine</strong> — visual bar chart showing which engines (Google, Bing, DuckDuckGo, Yandex, Ecosia, etc.) are sending you traffic and their relative share.</li>
                    <li><strong>Content Decay detection</strong> — automatically identifies posts that received organic traffic before but have dropped &gt;30% in the current period. Prompts you to update those posts before rankings fall further.</li>
                    <li><strong>Smart empty state</strong> — instead of showing zeros, the chart now shows a clear explanation of why there&rsquo;s no data yet and what to do about it (common for new installs).</li>
                    <li><strong>Search engine source tracking</strong> — the plugin now records which search engine sent each visit so the engine breakdown chart fills in automatically over time.</li>
                </ul>
            </div>
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--fixed">Fixed</span>
                <ul>
                    <li>Stray <code>&lt;/div&gt;</code> immediately after the opening wrap div caused layout inconsistency on the analytics page.</li>
                </ul>
            </div>
        </div>
<!-- ── v1.3.7 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.3.7</span>
            <span class="dfseo-cl-date">30 May 2026</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--fix">Enhancement</span>
        </div>
        <div class="dfseo-cl-body">
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--improved">Improved</span>
                <ul>
                    <li><strong>Google Search Console verification hint rewritten</strong> — the old placeholder <code>google-site-verification=...</code> was confusing. Now shows a real example of the meta tag and explicit instruction: paste only the value inside <code>content="..."</code> (e.g. <code>google488b50570eb5568d</code>), nothing else.</li>
                </ul>
            </div>
        </div>
<!-- ── v1.3.6 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.3.6</span>
            <span class="dfseo-cl-date">30 May 2026</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--fix">Bug Fix</span>
        </div>
        <div class="dfseo-cl-body">
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--fixed">Fixed</span>
                <ul>
                    <li><strong>Instant Indexing tabs reverting to Submit URLs (confirmed fix)</strong> — v1.3.5 shipped with the same broken buttons because the fix script crashed halfway and never wrote to disk. Applied directly via <code>str_replace</code> and verified all three tab buttons carry <code>type="button"</code>.</li>
                </ul>
            </div>
        </div>
<!-- ── v1.3.5 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.3.5</span>
            <span class="dfseo-cl-date">30 May 2025</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--fix">Bug Fix</span>
        </div>
        <div class="dfseo-cl-body">
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--fixed">Fixed</span>
                <ul>
                    <li><strong>Instant Indexing tabs reverted to Submit URLs on every click</strong> — the Configuration and History tab buttons had no <code>type="button"</code> attribute, so inside the settings form they defaulted to <code>type="submit"</code>. Every click submitted the form and reloaded the page, resetting the active tab.</li>
                    <li><strong>Tab state now persists through page reloads</strong> — active tab is saved to the URL hash (<code>#idx-config</code>, <code>#idx-history</code>). After saving settings the correct tab is restored automatically.</li>
                    <li><strong>Google API key simplified</strong> — changed from a JSON service account textarea to a plain text field matching SiteSEO. Paste your <code>AIzaSy…</code> API key directly.</li>
                </ul>
            </div>
        </div>
<!-- ── v1.3.4 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.3.4</span>
            <span class="dfseo-cl-date">30 May 2025</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--feature">Feature Update</span>
        </div>
        <div class="dfseo-cl-body">
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--improved">Improved</span>
                <ul>
                    <li><strong>Instant Indexing completely redesigned</strong> to match the familiar 3-tab layout: <strong>Submit URLs</strong> (manual bulk submission with engine selection and Update/Remove action), <strong>Configuration</strong> (Bing key + Google service account), and <strong>History</strong> (showing Time, URL, Google Response, and Bing Response side by side — exactly like SiteSEO&rsquo;s interface).</li>
                    <li><strong>Bulk manual submission</strong> — paste up to 100 URLs (one per line), choose Google and/or Bing, choose Update or Remove, click Submit. Live results table appears immediately below.</li>
                    <li><strong>History shows both Google and Bing responses per URL</strong> — colour-coded (green = 200/202, muted = N/A, red = error).</li>
                    <li>Log now stores <code>google_response</code> and <code>bing_response</code> separately per entry so history is always accurate.</li>
                </ul>
            </div>
        </div>
<!-- ── v1.3.3 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.3.3</span>
            <span class="dfseo-cl-date">29 May 2025</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--fix">Bug Fix</span>
        </div>
        <div class="dfseo-cl-body">
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--fixed">Fixed</span>
                <ul>
                    <li><strong>Navigation missing on premium-locked pages (Analytics, Bulk Editor)</strong> — the premium check fired before the header was rendered, so the blue nav bar was completely absent and users had no way to navigate back to Dashboard or other pages without using the browser back button. Header now renders first on all pages regardless of premium status, then the lock overlay appears below it.</li>
                </ul>
            </div>
        </div>
<!-- ── v1.3.2 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.3.2</span>
            <span class="dfseo-cl-date">29 May 2025</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--fix">Bug Fix</span>
        </div>
        <div class="dfseo-cl-body">
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--fixed">Fixed</span>
                <ul>
                    <li><strong>All settings tabs saved nothing, but always showed "Settings saved."</strong> — <code>handle_save()</code> checked for a nonce field named <code>dfseo_settings_nonce_dfseo_sitemap</code> but the form generates <code>dfseo_settings_nonce_sitemap</code> (no <code>dfseo_</code> prefix). The nonce check failed silently on every tab — nothing was ever written to the database — but the confirmation message still showed unconditionally. Fixed by deriving the correct nonce key from the group name.</li>
                    <li><strong>Sitemap stylesheet not applying</strong> — the XSL file was referenced via a rewrite rule (<code>/sitemap.xsl</code>) that required a permalink flush after every install. Changed to reference the file directly from the plugin URL (<code>wp-content/plugins/.../assets/sitemap.xsl</code>) which always works immediately with no flush needed.</li>
                    <li><strong>Sitemap stylesheet only applied to the sitemap index</strong> — the <code>&lt;?xml-stylesheet?&gt;</code> processing instruction was only injected into one of the four render methods. Now present in all four (index, post-type, taxonomy, and news sitemaps).</li>
                </ul>
            </div>
        </div>
<!-- ── v1.3.1 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.3.1</span>
            <span class="dfseo-cl-date">29 May 2025</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--feature">Enhancement</span>
        </div>
        <div class="dfseo-cl-body">
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--new">New</span>
                <ul>
                    <li><strong>Styled sitemap browser view</strong> — added an XSLT stylesheet so <code>/sitemap.xml</code> displays as a beautiful branded page with your DadsFam SEO header, a summary table of all sitemap files (or URLs), last-modified dates, and a link to Google Search Console. Search engines still receive the raw XML — the stylesheet is purely for humans viewing the sitemap in a browser.</li>
                </ul>
            </div>
        </div>
<!-- ── v1.3.0 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.3.0</span>
            <span class="dfseo-cl-date">29 May 2025</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--feature">Feature Update</span>
        </div>
        <div class="dfseo-cl-body">
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--new">New — Free</span>
                <ul>
                    <li><strong>Instant Indexing — IndexNow (Settings → Instant Indexing)</strong> — tells Bing, Yandex, DuckDuckGo, Seznam, and every other IndexNow-compatible engine the moment you publish, update, or remove content. No waiting for crawlers. Auto-generated verification key served at <code>/{key}.txt</code> automatically — no file uploads needed. Auto-submit on publish/update/delete (toggle). Manual one-click submit for any URL. Full submission log.</li>
                </ul>
            </div>
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--pro">New — Premium</span>
                <ul>
                    <li><strong>Google Indexing API</strong> — submit URLs directly to Google for priority crawling. Paste your Google Cloud service account JSON key and Google is notified instantly on publish/update/delete. Uses OAuth2 JWT signing with <code>openssl</code> — no external libraries required. Step-by-step setup guide included in the settings panel.</li>
                </ul>
            </div>
        </div>
<!-- ── v1.2.2 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.2.2</span>
            <span class="dfseo-cl-date">29 May 2025</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--fix">Bug Fix</span>
        </div>
        <div class="dfseo-cl-body">
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--fixed">Fixed</span>
                <ul>
                    <li><strong>Redirects page hung on a loading spinner</strong> — two root causes. (1) The loading overlay started visible in HTML with no <code>display:none</code>, so if anything prevented the AJAX call from running, it would stay visible forever. (2) The JS guard checked for <code>#dfseo-redirect-add-btn</code> which was never added to the HTML, so <code>loadRedirects()</code> was never called and the overlay never got hidden. Both fixed: overlay starts hidden, guard now checks for <code>#dfseo-redirects-list</code>, and the Add Redirect button has been added to the view.</li>
                    <li><strong>WordPress core sitemap not being disabled</strong> — WP 5.5+ registers its own sitemap at <code>/wp-sitemap.xml</code> which runs alongside ours, creating duplicate/conflicting sitemaps. Added <code>add_filter('wp_sitemaps_enabled', '__return_false')</code> so WordPress&rsquo;s built-in sitemap is disabled and DadsFam SEO&rsquo;s sitemap at <code>/sitemap.xml</code> is the only one active.</li>
                </ul>
            </div>
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--improved">Improved</span>
                <ul>
                    <li><strong>Analytics: broader search engine detection</strong> — the organic-visit tracker now uses domain-based detection instead of requiring <code>/search</code> in the URL path. Now correctly tracks DuckDuckGo, Ecosia, Brave, Qwant, Startpage, Ask, AOL, Sogou, Naver, Seznam, and Swisscows in addition to Google, Bing, Yahoo, Yandex, and Baidu.</li>
                </ul>
            </div>
        </div>
<!-- ── v1.2.1 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.2.1</span>
            <span class="dfseo-cl-date">28 May 2025</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--fix">Bug Fix</span>
        </div>
        <div class="dfseo-cl-body">
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--fixed">Fixed</span>
                <ul>
                    <li><strong>Redirects page hung on a loading spinner forever.</strong> The loading overlay was never hidden after the data loaded, and there was no error handler if the request failed. Now hides correctly and shows a clear message on success, empty, or error.</li>
                    <li><strong>License page was missing the Activate/Deactivate buttons and the last-check banner.</strong> The <code>$check_info</code> variable was used in the view but never defined, which threw an error on PHP 8 and broke the entire active-licence screen. Now defined correctly — buttons and banner are back.</li>
                    <li><strong>Premium could theoretically switch off during a server outage.</strong> If the licence server was reachable but returned a 500/503 error or a non-JSON response, the old code treated it as "licence invalid". Now any HTTP error or malformed response is treated as a temporary glitch — your active status is always preserved. Premium only ever deactivates on a genuine, clean "not valid" response, a real force-lock, or manual deactivation.</li>
                </ul>
            </div>
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--new">New</span>
                <ul>
                    <li><strong>"Re-check Now" button</strong> on the Licence page — manually re-verify your licence on demand. If the server can't be reached it tells you clearly and keeps premium active.</li>
                    <li><strong>Support card</strong> on the Licence and Changelog pages — <a href="mailto:support@dadsfam.co.za">support@dadsfam.co.za</a> for everyone. Premium customers see a ⭐ Priority badge, but free users are always welcome to reach out.</li>
                </ul>
            </div>
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--improved">Improved</span>
                <ul>
                    <li>Changelog page redesigned with a branded hero header and clearer version cards.</li>
                    <li>WordPress.org compliance: tags trimmed to 5, and a full External Services disclosure added to the readme (licence server, Anthropic API, sitemap ping).</li>
                </ul>
            </div>
        </div>
<!-- ── v1.2.0 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.2.0</span>
            <span class="dfseo-cl-date">28 May 2025</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--feature">Feature Update</span>
        </div>
        <div class="dfseo-cl-body">
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--new">New</span>
                <ul>
                    <li><strong>First-Run Setup Wizard</strong> — a clean 5-step guided setup launches automatically after activation. Covers site info, social profiles, Google Search Console verification, and next steps. Fully skippable. Re-launchable any time from Dashboard → Run Setup Wizard.</li>
                    <li><strong>Getting Started checklist</strong> on the Dashboard — live progress tracker showing which setup steps are complete (homepage description, social profiles, Google Search Console, focus keyword on a post, Premium licence). Each item links directly to where you fix it.</li>
                    <li><strong>Contextual help on every settings field</strong> — every setting now has a 💡 plain-English explanation of what it does, why it matters, and what to enter. No more guessing what "noindex author archives" means.</li>
                    <li>Setup wizard link added to Dashboard Quick Actions for easy re-launch.</li>
                </ul>
            </div>
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--improved">Improved</span>
                <ul>
                    <li>General, Schema, Sitemap, Advanced, AI Tools, and Local SEO settings pages fully rewritten with friendly, plain-language descriptions on every single field.</li>
                    <li>Site Verification section now explains what each verification is for and where to get the code.</li>
                    <li>Sitemap settings now include a direct "Submit to Google →" link.</li>
                    <li>Local SEO country field now explains two-letter ISO codes with examples.</li>
                    <li>Coordinates fields explain how to get your latitude/longitude from Google Maps.</li>
                </ul>
            </div>
        </div>
<!-- ── v1.1.0 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.1.0</span>
            <span class="dfseo-cl-date">28 May 2025</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--feature">Feature Update</span>
        </div>
        <div class="dfseo-cl-body">

            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--new">New — Free</span>
                <ul>
                    <li><strong>RSS Feed SEO</strong> — new <code>DFSEO_RSS</code> class enhances all WordPress RSS feeds: featured image prepended to every item, canonical source attribution appended (deters content scrapers), <code>&lt;atom:link&gt;</code> self-reference in feed header, and <code>X-Robots-Tag: noindex</code> on feed URLs so Google indexes your posts — not your feeds.</li>
                    <li><strong>7 new social profile fields</strong> in Settings → Social: TikTok, Pinterest, Threads, WhatsApp Business, Mastodon, GitHub, Telegram. All automatically included in your Organisation <code>sameAs</code> schema array.</li>
                    <li><strong>Additional Profiles textarea</strong> — add any social/profile URL not listed (one per line). Each valid URL is appended to the <code>sameAs</code> schema. Covers Spotify, SoundCloud, Twitch, Substack, Goodreads, or any future platform without a plugin update.</li>
                </ul>
            </div>

            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--pro">New — Premium</span>
                <ul>
                    <li><strong>SEOPress import</strong> — reads <code>_seopress_titles_title</code>, <code>_seopress_titles_desc</code>, <code>_seopress_analysis_target_kw</code>, <code>_seopress_robots_index</code>, and <code>_seopress_robots_canonical_url</code> meta keys.</li>
                    <li><strong>The SEO Framework import</strong> — reads <code>_genesis_title</code>, <code>_genesis_description</code>, <code>_genesis_noindex</code>, <code>_genesis_nofollow</code>, and <code>_genesis_canonical_uri</code> meta keys (autodescription plugin).</li>
                    <li><strong>Slim SEO import</strong> — reads the <code>slim_seo</code> JSON meta key and extracts title, description, canonical, and noindex values.</li>
                    <li><strong>SiteSEO import (Softaculous)</strong> — reads <code>_siteseo_titles_title</code>, <code>_siteseo_titles_desc</code>, <code>_siteseo_titles_canonical</code>, <code>_siteseo_robots_index</code>, and <code>_siteseo_robots_follow</code> meta keys. Note appended in Import settings if meta keys differ on your install.</li>
                    <li>Import page redesigned with plugin detection badges and per-source progress indicators.</li>
                </ul>
            </div>

        </div>
<!-- ── v1.0.6 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.0.6</span>
            <span class="dfseo-cl-date">28 May 2025</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--feature">Enhancement</span>
        </div>
        <div class="dfseo-cl-body">
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--new">New</span>
                <ul>
                    <li><strong>Last verified / Next check</strong> now shown on the Licence page — displays the exact date &amp; time plus a human-readable &ldquo;X minutes ago&rdquo; / &ldquo;in X minutes&rdquo; so you can always see when the licence was last confirmed and when the next hourly background check is due.</li>
                </ul>
            </div>
        </div>
<!-- ── v1.0.5 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.0.5</span>
            <span class="dfseo-cl-date">28 May 2025</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--fix">Bug Fix</span>
        </div>
        <div class="dfseo-cl-body">
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--fixed">Fixed</span>
                <ul>
                    <li><strong>License page showed wrong key and "Free Version" even when PRO was active.</strong> The shared header partial used $key and $is_active as loop variable names, which overwrote the same-named variables set by license.php. $key ended up as <code>changelog</code> (last nav item) and $is_active ended up as <code>false</code>. All header-internal variables now prefixed $_h_ and unset after use.</li>
                </ul>
            </div>
        </div>
<!-- ── v1.0.4 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.0.4</span>
            <span class="dfseo-cl-date">28 May 2025</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--feature">UI Update</span>
        </div>
        <div class="dfseo-cl-body">
            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--improved">Improved</span>
                <ul>
                    <li><strong>Full admin UI redesign</strong> — replaced the plain WordPress-style page header with a full-width dark-blue branded header bar matching the DadsFam design system used across all DadsFam plugins.</li>
                    <li><strong>Horizontal tab navigation</strong> in the header bar (Dashboard, Settings, Redirects, Analytics, Bulk Editor, License, Changelog). Active tab highlighted with amber underline.</li>
                    <li><strong>PRO / Free indicator badge</strong> — top right of every page. Shows ⭐ PRO (amber) when licensed, or a "Free — Upgrade ↗" chip when not, linking directly to the License page.</li>
                    <li><strong>Version chip</strong> — plugin version always visible top right, monospace, subtle glass style.</li>
                    <li>Locked premium tabs show a 🔒 icon and reduced opacity so it's clear they require a licence.</li>
                </ul>
            </div>
        </div>
<!-- ── v1.0.3 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.0.3</span>
            <span class="dfseo-cl-date">28 May 2025</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--fix">Bug Fix</span>
        </div>
        <div class="dfseo-cl-body">

            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--fixed">Fixed</span>
                <ul>
                    <li>
                        <strong>License recheck was running daily instead of hourly.</strong>
                        The DF License Manager guarantees Force Lock takes effect
                        <em>"within 1 hour"</em> even if the initial ping to the client site fails
                        (e.g. the site was offline or behind a firewall at that moment). The
                        license verify cron was scheduled <code>daily</code>, meaning a
                        suspended key could remain active for up to 24 hours when force lock
                        couldn't reach the site. Moved to its own <code>dfseo_license_cron</code>
                        event on an <code>hourly</code> schedule. Analytics prune and 404 log
                        cleanup remain on the daily cron — they don't need to run frequently.
                    </li>
                </ul>
            </div>

        </div>
<!-- ── v1.0.2 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.0.2</span>
            <span class="dfseo-cl-date">28 May 2025</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--fix">Bug Fix</span>
        </div>
        <div class="dfseo-cl-body">

            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--fixed">Fixed</span>
                <ul>
                    <li>
                        <strong>Licensing — premium features never activated.</strong>
                        <code>is_active()</code> was checking for <code>status === 'active'</code>
                        but the DF License Manager server returns <code>valid: true</code>.
                        All premium gates were permanently locked regardless of having a valid key.
                        Now correctly reads <code>valid === true</code> from the server response.
                    </li>
                    <li>
                        <strong>Lock token not stored.</strong> The verify response includes a
                        <code>lock_token</code> used by the Force Lock feature in DF License Manager.
                        It was being discarded; now stored in <code>dfseo_license_lock_token</code>.
                        Without this, the Force Lock button in DF License Manager had no effect on
                        this plugin.
                    </li>
                    <li>
                        <strong>License info page showed wrong fields.</strong> The admin licence
                        page was trying to display <code>site_url</code> from the server response —
                        a field that doesn't exist. Now correctly shows <code>product</code>,
                        <code>expires</code>, and the masked key.
                    </li>
                    <li>
                        Deactivation now also clears <code>dfseo_license_lock_token</code>.
                        Uninstall routine updated to delete it too.
                    </li>
                </ul>
            </div>

            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--new">New</span>
                <ul>
                    <li>
                        <strong>Force-lock REST endpoint</strong> registered at
                        <code>POST /wp-json/dflm/v1/force-lock</code>. When dadsfam.co.za
                        suspends a licence via the DF License Manager "⚡ Force Lock" button,
                        it now pings this endpoint and premium access is revoked instantly
                        rather than waiting up to 12 hours for the transient to expire.
                        The endpoint verifies the incoming key matches the stored key before
                        clearing the cache.
                    </li>
                </ul>
            </div>

        </div>
<!-- ── v1.0.1 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.0.1</span>
            <span class="dfseo-cl-date">28 May 2025</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--fix">Bug Fix</span>
        </div>
        <div class="dfseo-cl-body">

            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--fixed">Fixed</span>
                <ul>
                    <li>
                        <strong>Critical — Plugin would not activate on PHP 7.4 / 8.0.</strong>
                        The <code>paginate()</code> method in <code>class-dfseo-db.php</code> used
                        <code>...$spread, $extra1, $extra2</code> syntax (positional args after spread)
                        which is only valid from PHP 8.1 onwards. Changed to
                        <code>array_merge( $where_vals, [ $per_page, $offset ] )</code>
                        so the plugin activates cleanly on all supported PHP versions (7.4+).
                    </li>
                </ul>
            </div>

            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--new">New</span>
                <ul>
                    <li>
                        <strong>Changelog admin page</strong> — <em>DadsFam SEO → Changelog</em> now shows
                        the full version history so site admins can see exactly what changed and when.
                    </li>
                    <li>
                        Separate class files introduced for <code>DFSEO_Local_Seo</code>,
                        <code>DFSEO_Bulk_Edit</code>, <code>DFSEO_Import</code>,
                        <code>DFSEO_Woocommerce</code>, and <code>DFSEO_Ajax</code>.
                        Previously all five were bundled inside a single
                        <code>class-dfseo-misc.php</code>; splitting them out makes each class
                        individually autoloadable and easier to maintain.
                    </li>
                </ul>
            </div>

        </div>
<!-- ── v1.0.0 ──────────────────────────────────────────────────────────── -->
    <div class="dfseo-cl-version">
        <div class="dfseo-cl-header">
            <span class="dfseo-cl-num">1.0.0</span>
            <span class="dfseo-cl-date">28 May 2025</span>
            <span class="dfseo-cl-tag dfseo-cl-tag--feature">Initial Release</span>
        </div>
        <div class="dfseo-cl-body">

            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--new">New — Core (Free)</span>
                <ul>
                    <li><strong>23-point SEO analysis engine</strong> — weighted checks (total ≈ 100 pts) covering keyword placement in title, meta description, URL, intro paragraph, H1, subheadings and image alt text; keyword density; content length; heading structure; internal &amp; external links; image alt coverage; readability metrics (Flesch-Kincaid, sentence length, paragraph length, transition words, passive voice).</li>
                    <li><strong>SEO score (0–100)</strong> stored per post as <code>_dfseo_score</code>, recalculated automatically on every save. Colour-coded: green ≥ 80 / amber 50–79 / red &lt; 50.</li>
                    <li><strong>Tabbed meta box</strong> below the post editor — General, Readability, Social, Advanced tabs (+ Schema and AI Tools for Premium). Includes real-time SERP preview (desktop &amp; mobile toggle) with live character counters and progress bars for title (50–60 chars) and meta description (120–160 chars).</li>
                    <li><strong>XML Sitemap</strong> — sitemap index at <code>/sitemap.xml</code> with per-type sub-sitemaps for all public post types and taxonomies; image sitemap included. Auto-pings Google and Bing on publish.</li>
                    <li><strong>JSON-LD structured data</strong> — WebSite + SearchAction (homepage), Organisation, WebPage, Article / BlogPosting, BreadcrumbList. Schema type selectable per post (WebPage, Article, BlogPosting, NewsArticle, FAQPage, ContactPage, AboutPage, etc.).</li>
                    <li><strong>OpenGraph + Twitter Card</strong> meta tags — per-post OG image picker in the meta box, falls back to featured image then to a global default image set in Settings.</li>
                    <li><strong>Canonical URLs</strong> — auto-generated from permalink; per-post override field in Advanced tab.</li>
                    <li><strong>Robots directives</strong> — per-post noindex, nofollow, noarchive, nosnippet checkboxes; global noindex controls for date archives, author archives, tag archives, and paginated pages.</li>
                    <li><strong>Virtual robots.txt</strong> — DadsFam SEO takes over <code>/robots.txt</code> output; includes sitemap URL automatically; custom rules field in Advanced settings.</li>
                    <li><strong>Breadcrumbs</strong> — schema-enhanced HTML breadcrumb trail via <code>[dfseo_breadcrumbs]</code> shortcode. Separator and Home label configurable in settings.</li>
                    <li><strong>Image SEO</strong> — injects focus keyword as alt text for any image in content that is missing one; auto-populates alt text from filename on media upload.</li>
                    <li><strong>Site verification</strong> — meta tag support for Google Search Console, Bing Webmaster Tools, Yandex, Pinterest, and Norton Safe Web.</li>
                    <li><strong>SEO score column</strong> — sortable column added to all public post-type list screens showing the score badge at a glance.</li>
                    <li><strong>Admin dashboard page</strong> — site-wide SEO health overview with score distribution cards (Great / OK / Poor / No keyword), top 10 posts needing attention, quick-action links, HTTPS and robots.txt health indicators.</li>
                    <li><strong>WordPress dashboard widget</strong> — compact SEO health summary on the WP home screen.</li>
                    <li><strong>Tabbed settings</strong> — General, Social, Sitemap, Schema, Advanced, AI Tools, Local SEO, Import tabs. All settings registered via WordPress Settings API.</li>
                    <li><strong>Custom database tables</strong> on activation: <code>dfseo_redirects</code>, <code>dfseo_404_log</code>, <code>dfseo_keyword_rankings</code>, <code>dfseo_analytics</code>.</li>
                    <li>Clean <strong>uninstall</strong> — drops all custom tables, deletes all plugin options and scheduled events when deleted from wp-admin.</li>
                </ul>
            </div>

            <div class="dfseo-cl-group">
                <span class="dfseo-cl-label dfseo-cl-label--pro">New — Premium</span>
                <ul>
                    <li><strong>AI Meta Generator</strong> — one-click SEO title &amp; meta description generation using the Anthropic Claude API (model: claude-sonnet-4). API key stored server-side, never exposed to the browser.</li>
                    <li><strong>AI Keyword Suggestions</strong> — generates 10+ relevant keyword ideas including long-tail and semantic variations for the current post.</li>
                    <li><strong>AI Content Optimisation</strong> — returns specific, actionable recommendations to improve the post's SEO score.</li>
                    <li><strong>AI Title Variants</strong> — generates 5 alternative SEO title ideas optimised for click-through rate.</li>
                    <li><strong>AI Content Outline</strong> — produces a full SEO-optimised content outline with headings and key talking points.</li>
                    <li><strong>AI FAQ Generator</strong> — generates FAQ schema entries from post content; one-click adds them to the FAQ schema tab.</li>
                    <li><strong>Redirect Manager</strong> — full CRUD UI for 301, 302, 307, and 410 redirects with hit-count tracking and regex support. Redirects processed server-side on <code>template_redirect</code>.</li>
                    <li><strong>404 Log</strong> — captures every 404 with URL, referrer, user agent, IP hash, hit count, and last-seen timestamp. Includes "Create Redirect" shortcut directly from the log.</li>
                    <li><strong>Analytics Dashboard</strong> — tracks organic visits (identified by HTTP referrer from Google, Bing, Yahoo, DuckDuckGo, Yandex, Baidu) per post per day. Canvas line chart with 7 / 14 / 30 / 90 day range selector; top-10 posts by clicks. Configurable data retention (30–365 days).</li>
                    <li><strong>Bulk SEO Editor</strong> — filterable table of all published posts with inline-editable focus keyword, SEO title, and meta description. Filter by: no keyword, no meta description, poor score (&lt;50). Saves all changes in a single batch REST call and re-scores every updated post.</li>
                    <li><strong>FAQ Schema</strong> — add unlimited Q&amp;A pairs per post; outputs <code>FAQPage</code> JSON-LD for Google rich results.</li>
                    <li><strong>Local SEO</strong> — full <code>LocalBusiness</code> JSON-LD schema with address, phone, email, geo-coordinates, and 12 business type options.</li>
                    <li><strong>Data Import</strong> — one-click batch import of focus keywords, SEO titles, meta descriptions, and noindex flags from <strong>Yoast SEO</strong>, <strong>Rank Math</strong>, and <strong>All in One SEO</strong>. Runs in 50-post batches with live progress indicator.</li>
                    <li><strong>WooCommerce integration</strong> — removes WooCommerce's own structured data output to prevent duplication; DadsFam SEO schema takes over for product pages.</li>
                    <li><strong>Google News Sitemap</strong> — generates <code>/sitemap-news.xml</code> for Google News submission.</li>
                    <li><strong>DF License Manager integration</strong> — licence key activation/deactivation via <code>dadsfam.co.za/wp-json/dfem-licenses/v1/verify</code>. 12-hour transient cache; daily background re-verify via WP-Cron. All premium features gated behind <code>dfseo_is_premium()</code>.</li>
                </ul>
            </div>

        </div>

</div>
