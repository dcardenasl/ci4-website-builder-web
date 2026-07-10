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
- **Generic block naming normalization** — consolidated domain-specific block views (faq_accordion, contact_form, location_info, logo_showcase, etc.) into unified generic names (accordion, form_embed, contact_info, asset_showcase, etc.); updated `BlockRenderer` to reflect new block names; improves consistency across admin/web rendering
- **Contact form migration** — replace legacy `ContactController` with new `FormController` powered by CMS forms system

### Fixed
- **Gallery modal caption text color** — ensure gallery caption inherits consistent `text-white/90` opacity in modal overlay; refactored base CSS element styles into `@layer base` to prevent utility class override issues
- **Block renderers** — `alert`, `accordion`, `rich_text`, `tab_item`, and `tabs` now fall back to legacy `body`/`html` payload keys via `block_text_content()` when `content` is empty, matching the domain's normalized rich-text contract
- **`CacheInvalidator` cache pattern** — correct wildcard pattern to match actual cache key structure (`web_api_*_scope_*` instead of `web_api_scope_*`)

### Removed
- **Legacy contact module** — removed `SiteContactService` and `ContactController` in favor of domain-driven forms system
