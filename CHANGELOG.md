# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **Responsive image component and collection filters** — new `responsive-image.php` component for adaptive image rendering; progressive AJAX filtering for collection listings with `collectionFilters.js` module supporting client-side filtering, pagination, and history API integration
- **Monorepo standardization pass** — added level-8 PHPStan quality gates, explicit PHPUnit suites, pre-commit hooks, `WebApiClientInterface`, shared service base class, block ViewModels, stale API cache fallback, POST throttling, modular JS source/build tooling, and expanded resolver/form/cache test coverage
- **Map embed block** — new standalone `map_embed` block renderer for displaying embedded maps and iframes with configurable aspect ratio and height; complementary to `contact_info` block which now focuses on contact details only
- **Unified collection grid block** — migrated from `news_grid` and `portfolio_grid` to a single `collection_grid` block renderer; supports flexible configuration (order_by, order_direction, layout_variant) for paginated collection listings across the frontend
- **Expanded form field rendering** — enhanced `FormController` and `form_embed` block view to support new form field types with improved layout and validation message handling; dynamic field type rendering adapts to expanded field type catalog from Domain
- **Forms module** — new public-facing forms feature to display and submit CMS forms with field validation and submission handling (CMS-012)
- **Analytics tracking** — automatic page view tracking to the domain CMS analytics API with user agent and referrer data

### Changed
- **Intelligent image loading optimization** — `responsive-image.php` component now dynamically determines loading strategy based on image sequence (1st/2nd/etc.) and device type, deferring lazy-loading until after the initial above-fold images; mobile devices load 1 image eagerly while desktop loads up to 4; images from picsum.photos now include responsive srcsets and sizes for adaptive image serving.
- **Form embed layout flexibility** — refactored `form_embed` block view to render as either a two-column layout (with optional left-column heading/description/info boxes) or a centered single-column layout when no left-content is present; improves visual presentation for form-only embeddings and reduces wasted whitespace
- **Generic block naming normalization** — consolidated domain-specific block views (faq_accordion, contact_form, location_info, logo_showcase, etc.) into unified generic names (accordion, form_embed, contact_info, asset_showcase, etc.); updated `BlockRenderer` to reflect new block names; improves consistency across admin/web rendering
- **Contact form migration** — replace legacy `ContactController` with new `FormController` powered by CMS forms system

### Fixed
- **Gallery modal caption text color** — ensure gallery caption inherits consistent `text-white/90` opacity in modal overlay; refactored base CSS element styles into `@layer base` to prevent utility class override issues
- **Block renderers** — `alert`, `accordion`, `rich_text`, `tab_item`, and `tabs` now fall back to legacy `body`/`html` payload keys via `block_text_content()` when `content` is empty, matching the domain's normalized rich-text contract
- **`CacheInvalidator` cache pattern** — correct wildcard pattern to match actual cache key structure (`web_api_*_scope_*` instead of `web_api_scope_*`)
- **Accessibility (Lighthouse audit)** — mobile submenu toggle button no longer exposes an unnamed control to assistive tech (`aria-hidden`); footer "Menú"/"Redes Sociales" labels no longer skip heading levels (now `<p>`); hero slider dots now meet the 24×24px minimum touch target while keeping their visual size, via a separate hit-area button and visual span in `hero_slider.php` + `heroCarousel.js`; entry date labels darkened from `text-slate-400` to `text-slate-500` (and the `--color-text-muted` design token) to meet 4.5:1 contrast
- **CMS-configured background colors silently not rendering** — `page_header`'s `bg_color` config field (and any other dynamic `bg-*`/`from-*`/`via-*`/`to-*` class sourced from CMS data) was being purged by Tailwind's build because the v3 `safelist` in `tailwind.config.js` was never actually loaded under this project's Tailwind v4 CSS-first setup; restored via `@source inline(...)` in `app.css`
- **Broken color-utility typos** — `text-slate-450`, `text-slate-605`, and `border-slate-350` (non-existent Tailwind steps that silently compiled to nothing) corrected to `-500`/`-600`/`-300` across `collection_listing.php` and `image.php`

### Changed
- **Static asset caching** — added `Cache-Control`/`Expires` (1 year, immutable) and gzip compression for CSS/JS/images/fonts in the Apache vhost config; assets are already cache-busted via `?v=filemtime()` query strings
- **CMS color safelist scoped to `bg-` only** — the safelist restore above (see Fixed) originally covered `bg-`/`from-`/`via-`/`to-` for all colors, ballooning `compiled.css` from ~69 KiB to ~284 KiB; `page_header`'s `bg_color` is the only dynamic color field in the CMS schema and every `from-`/`via-`/`to-` usage in the codebase is a hardcoded literal already caught by Tailwind's normal scanner, so the safelist now covers `bg-` only, bringing `compiled.css` back down to ~89 KiB

### Removed
- **Legacy contact module** — removed `SiteContactService` and `ContactController` in favor of domain-driven forms system
- **Dead build config** — removed `tailwind.config.js` (superseded by Tailwind v4's CSS-first `@theme` in `app.css`; its safelist was ported forward, see Fixed above) and leftover scaffold artifacts (`env`, `.gitignore_temp`, `pnpm-workspace.yaml`, `.npmrc` — the latter two conflicted with the project's pinned npm package manager and committed `package-lock.json`)
