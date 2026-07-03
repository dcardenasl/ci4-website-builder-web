# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- **Forms module** — new public-facing forms feature to display and submit CMS forms with field validation and submission handling (CMS-012)
- **Analytics tracking** — automatic page view tracking to the domain CMS analytics API with user agent and referrer data

### Changed
- **Contact form migration** — replace legacy `ContactController` with new `FormController` powered by CMS forms system

### Fixed
- **Block renderers** — `alert`, `faq_accordion`, `rich_text`, `tab_item`, and `tabs` now fall back to legacy `body`/`html` payload keys via `block_text_content()` when `content` is empty, matching the domain's normalized rich-text contract
- **`CacheInvalidator` cache pattern** — correct wildcard pattern to match actual cache key structure (`web_api_*_scope_*` instead of `web_api_scope_*`)

### Removed
- **Legacy contact module** — removed `SiteContactService` and `ContactController` in favor of domain-driven forms system
