=== DadsFam SEO ===
Contributors: dadsfam
Tags: seo, sitemap, schema, meta tags, open graph, geo, ai, llms.txt
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.8.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Enterprise-grade SEO + GEO plugin for WordPress. On-page analysis, AI optimisation, XML sitemaps, structured data, internal link suggestions, redirects, real Search Console keywords, and AI-answer-engine optimisation.

== Description ==

**DadsFam SEO** is a fully-featured, enterprise-grade SEO plugin built to compete with Yoast SEO, Rank Math, and All in One SEO — with a powerful free tier, AI-powered premium features, and next-generation **GEO (Generative Engine Optimization)** to get your content cited by AI answer engines like ChatGPT, Google AI Overviews, Perplexity, Gemini and Claude.

= Free Features =

* **On-Page SEO Analysis** — 26-point weighted scoring engine covering keyword placement, density, content length, heading structure, internal/external links, image alt text, readability (Flesch-Kincaid, sentence length, passive voice, transition words) and more
* **SERP Preview** — Real-time desktop and mobile search result preview with character counters
* **Internal Link Suggestions** — Finds related published content to link to, ranked by relevance to your focus keyword and title, and shows what you've already linked
* **XML Sitemaps** — Cached index sitemap with per-type sub-sitemaps, image sitemap, and search engine notification via IndexNow on publish
* **Schema / Structured Data** — JSON-LD for Article, WebPage, WebSite + SearchAction, Organisation, BreadcrumbList, and Speakable
* **OpenGraph & Twitter Cards** — Full social sharing meta tags with image support
* **Canonical URLs** — Auto-generated or custom per-post canonical
* **Robots Directives** — noindex, nofollow, noarchive, nosnippet per post
* **Robots.txt Management** — Live virtual robots.txt with custom rules support
* **Breadcrumbs** — Schema-enhanced breadcrumbs via `[dfseo_breadcrumbs]` shortcode
* **Image SEO** — Auto-inject focus keyword into alt text on upload
* **Site Verification** — Google, Bing, Yandex, Pinterest
* **Instant Indexing** — IndexNow submission (Bing, Yandex, DuckDuckGo, Seznam, Naver) with manual bulk submission and history
* **SEO Score Column** — Score column on posts, pages, and WooCommerce products
* **Dashboard Widget** — Quick SEO health overview on WP admin home

= GEO — Generative Engine Optimization (Free) =

Get discovered and cited by AI answer engines, the next frontier of search:

* **llms.txt** — Auto-generated AI content map served at `/llms.txt` (the emerging llmstxt.org standard)
* **AI Crawler Controls** — Allow or block 13 major AI bots (OpenAI GPTBot, ClaudeBot, Google-Extended, PerplexityBot, Applebot, Meta AI, CCBot, Bytespider and more) from robots.txt
* **Speakable Schema** — Marks content for voice assistants and AI read-aloud
* **GEO Content Checks** — Analysis flags question-style headings, lists/tables, and concise direct-answer openings that AI engines prefer to quote

= Premium Features =

Unlock with a DadsFam SEO Premium licence:

* **AI Meta Generator** — One-click SEO title and meta description generation using Claude AI
* **AI Keyword Research** — Relevant keyword suggestions with type, intent and difficulty
* **AI Content Optimisation** — Actionable content improvement recommendations
* **AI Title Variants** — Generate click-worthy title alternatives
* **AI Content Outline** — Full SEO-optimised content outline, insertable into the editor
* **Real Keyword Data** — Actual search queries, clicks, impressions, CTR and ranking position straight from the Google Search Console API
* **Redirect Manager** — 301, 302, 307, 410 redirects with hit tracking and regex support
* **Auto-Redirect on URL Change** — Automatically creates a 301 when a published post's slug changes, with redirect-chain prevention
* **Analytics Dashboard** — Organic traffic trends, engine breakdown, and content decay detection over 7–90 day windows
* **Bulk SEO Editor** — Edit focus keyword, title, and description for hundreds of posts at once
* **FAQ Schema** — FAQ rich results with AI-assisted generation
* **Local SEO** — Full LocalBusiness schema with address, phone, geo-coordinates
* **Data Import** — One-click import from Yoast SEO, Rank Math, and All in One SEO
* **WooCommerce** — Product schema and integration
* **News Sitemap** — Google News-compatible sitemap
* **Google Indexing API** — Direct URL submission to Google

= AI Powered by Claude =

DadsFam SEO Premium integrates with the [Anthropic Claude API](https://www.anthropic.com/) to deliver best-in-class AI SEO features. Your API key is stored securely server-side and never exposed to the browser.

== Installation ==

1. Upload the `dadsfam-seo` folder to `/wp-content/plugins/`
2. Activate the plugin in **Plugins → Installed Plugins**
3. Navigate to **DadsFam SEO → Settings** to configure
4. Edit any post or page to see the SEO meta box below the editor

== Frequently Asked Questions ==

= What is GEO (Generative Engine Optimization)? =

GEO is the practice of optimising your content to be found, understood, and cited by AI answer engines such as ChatGPT, Google AI Overviews, Perplexity, Gemini and Claude — as opposed to traditional SEO which targets the classic search results. As more people get answers from AI, GEO is becoming essential. DadsFam SEO includes an llms.txt generator, AI crawler controls, Speakable schema, and GEO-focused content checks.

= Do I need a Google service account for keyword data? =

To see real keyword data from Google Search Console (queries, clicks, impressions, position), yes — you add a Google service account (the same one used for the Google Indexing API) and grant it access in Search Console. The plugin walks you through it. IndexNow instant indexing for Bing and others works with no setup at all.

= Will this conflict with another SEO plugin? =

Run only one SEO plugin at a time. DadsFam SEO can import your existing data from Yoast, Rank Math, and All in One SEO so you can switch cleanly.

== Changelog ==

= 1.8.5 =
* Fixed: the Analytics chart could grow endlessly and stretch the page on mobile. The chart now holds a fixed proportion so its height can never run away.

= 1.8.4 =
* Fixed (properly this time): the Analytics traffic chart no longer hangs, flashes or re-renders awkwardly. It is now built once and simply updates its data on refresh, so it appears instantly and stays stable.

= 1.8.3 =
* Fixed: the big stat numbers on the Analytics page were invisible in dark mode — now bright white and clearly visible
* Improved: the traffic chart now animates quickly on first load (0.55s) and updates instantly on auto-refresh, so it no longer feels laggy

= 1.8.2 =
* Fixed: unreadable text in dark mode — card/box headers, analytics values, range buttons and engine bars now have proper contrast
* Fixed: the health gauge rendered as a square in some browsers — rebuilt as a clean, crisp circle with a glowing pulse
* Fixed: the Analytics traffic chart no longer hangs or half-renders on load; it now sizes instantly in a fixed container

= 1.8.1 =
* Improved: dark mode and the animated aurora + cursor spotlight now apply across the ENTIRE plugin — Settings, Redirects, Analytics, Bulk Editor, License, Changelog and the setup wizard — not just the dashboard. Every table, card, form, tab and callout is styled for dark.

= 1.8.0 =
* New: full dark-mode overhaul with a light/dark/auto theme toggle (sun/moon in the header) that follows your system setting by default and remembers your choice
* New: dashboard hero with an animated circular SEO Health gauge (0-100), glowing ring, aurora backdrop and cursor spotlight — modeled on the DadsFam Login Security dashboard
* Improved: glassmorphism cards, tables, inputs and tabs all restyle beautifully in dark mode

= 1.7.4 =
* New: the Analytics page is now LIVE — it auto-refreshes every 45s with smoothly rolling numbers, a pulsing LIVE indicator, a glowing gradient-filled chart that animates as it draws, fluidly filling engine bars with a shimmer, and an animated aurora backdrop. All motion respects reduced-motion.

= 1.7.3 =
* New: 3D dynamic card tilt — dashboard and analytics cards now lean toward your cursor with a holographic glare (disabled on touch and reduced-motion)
* Fix: the Welcome notice now stays dismissed permanently and auto-disappears once the plugin is configured
* Fix: post titles containing apostrophes/quotes no longer show raw codes like &#8217; in the analytics tables

= 1.7.2 =
* New: dramatically upgraded interface — animated living-gradient header with light sheen, glassmorphism stat cards that count up and lift on hover, glowing pill tabs, gradient headings and numbers, sliding meta-box tab indicator, and sheen-sweep buttons. All motion respects reduced-motion.

= 1.7.1 =
* New: premium animated GUI — gradient sliding tab indicators, card hover lift, button micro-interactions, animated header accent (with reduced-motion support)
* Improved: GEO Site Summary field now has an "Auto-fill from my site" button and a plain-English example so it is obvious what to write

= 1.7.0 =
* New: Generative Engine Optimization (GEO) — new AI / GEO settings tab
* New: llms.txt auto-generated AI content map at /llms.txt
* New: AI crawler controls for 13 major bots in robots.txt
* New: Speakable schema for voice and AI read-aloud
* New: 3 GEO content checks (question headings, lists/tables, direct answer)
* Fix: Instant Indexing module was initialised twice
* Fix: outdated AI model default for new installs
* Fix: URL rules now refresh automatically after an update so /llms.txt works immediately

= 1.6.0 =
* New: Internal Link Suggestions (free) — a Links tab in the SEO box
* Performance: server-side sitemap caching with automatic invalidation on content changes
* Maintenance: uninstall cleans up new cache entries

= 1.5.1 =
* New: Auto-Redirect on URL change (Premium) with redirect-chain prevention
* Fix: on-by-default toggles could not be switched off
* Fix: modernised search-engine notification via IndexNow (Google retired sitemap ping in 2023)

= 1.5.0 =
* New: real keyword data from the Google Search Console API (queries, clicks, impressions, CTR, position)

= 1.4.x =
* Analytics dashboard rebuilt with trend arrows, engine breakdown, content decay detection
* Chart.js enqueue fix; WooCommerce SEO column layout fix; AI key preserve-on-save fix
* AI Tools rendering fixes (keywords, content optimisation, content outline)
* Dashboard branded banner, SEO tip of the day, robots.txt health check fix

= 1.3.x =
* Instant Indexing (IndexNow + Google) with 3-tab UI, bulk submit, and history
* Styled XSL sitemap browser view; settings save fixes

= 1.2.0 - 1.0.0 =
* Initial release and early iterations: on-page analysis, sitemaps, schema, OpenGraph, redirects, breadcrumbs, image SEO, import, and the freemium licensing system

== Upgrade Notice ==

= 1.7.0 =
Adds Generative Engine Optimization (GEO): llms.txt, AI crawler controls, Speakable schema, and GEO content checks — plus bug fixes. Get your content cited by ChatGPT, Google AI Overviews, Perplexity and more.
