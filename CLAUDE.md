# CLAUDE.md - ci4-website-builder-web

Generic CI4 4.7 website powered by `ci4-website-builder-domain` API.

## Quick Start

```bash
cd /Users/davidcardenas/Developer/PHP/ci4-website-starter/ci4-website-builder-web

# Development server (port 8186)
php spark serve --port 8186

# Tailwind CSS dev watch
npm run dev:css

# Build production CSS
npm run build:css
```

## Architecture

- **API-driven**: All content comes from `ci4-website-builder-domain` (port 8190).
- **Dynamic routing**: Resolves pages, collections, entries, redirects at runtime.
- **Block-based**: Pages and entries render dynamic blocks by `block_key`.
- **SEO-ready**: Full metadata, schema.org markup, sitemaps.
- **Caching**: Configurable TTL per service (settings 3600s, menus 600s, entries 180s, etc.).

## Key Files

| File | Purpose |
|------|---------|
| `app/Controllers/PageController.php` | Dynamic page/collection/entry resolver |
| `app/Controllers/SitemapController.php` | XML sitemap generation |
| `app/Services/SiteXxxService.php` | API adapters (Settings, Menu, Page, Collection, Entry, Redirect) |
| `app/Libraries/WebApiClient.php` | HTTP client with caching (from teatromuseo) |
| `app/Libraries/BlockRenderer.php` | Recursive block rendering by `block_key` |
| `app/Views/layouts/public.php` | Master layout with pre-loaded menus & settings |
| `app/Views/blocks/*.php` | Block type templates (rich_text, image, hero_banner, cta, container) |

## Configuration

`.env` variables:
- `CI_ENVIRONMENT` = `development`
- `app.baseURL` = `http://localhost:8186/`
- `app.defaultLocale` = `es` (default language)
- `WEB_API_BASE_URL` = `http://localhost:8190` (domain API URL)
- `WEB_API_KEY` = `web_api_test_key` (API key registered in domain)
- `cache.handler` = `file`

**`app.defaultLocale` must match a language registered as active in the Domain CMS
(`cms_languages`), and a `home` page must exist in that language.** This value is read by CI4's
own routing/locale negotiation before any request reaches a controller, so it can't be resolved
dynamically from the Domain's language list — it's a static config value, not derived from CMS
data. If it points at a language that doesn't exist (or whose `home` page was deleted), `/`
resolves cleanly to a 404 rather than crashing, but the site has no working homepage until either
the config or the CMS content is fixed. `ci4-website-builder-admin` (`app.defaultLocale`) and
`ci4-website-builder-domain` (`app.defaultLocale`) each control a different, independent thing —
admin's own UI language and the API's internal `lang()` fallback, respectively — they are not
required to match this value or each other.

## Page Resolution Algorithm

PageController::resolve() implements a 5-step fallback:

1. Try CMS page by slug → `SitePageService::getBySlug(lang, slug)`
2. Try collection prefix resolved from the public API payload → `collection_url_path_info($collection, $path)`
3. Try collection/entry combo → `SiteEntryService::getBySlug(lang, collectionKey, slug)`
4. Try redirect → `SiteRedirectService::resolve(path)`
5. Return 404

## Service Layers

All services in `app/Services/`:
- **SiteSettingsService**: Fetch public settings (cache 3600s)
- **SiteMenuService**: Fetch menu trees (cache 600s)
- **SitePageService**: Fetch pages by slug, list all pages (cache 300-600s)
- **SiteCollectionService**: Fetch collections from the domain API (cache 600s)
- **SiteEntryService**: List entries, fetch by slug (cache 180-300s)
- **SiteRedirectService**: Resolve redirects (cache 3600s)

All return normalized arrays or `null` on error. No exceptions — services degrade gracefully.

## Block Rendering

`BlockRenderer::render(blocks, lang)` recursively renders blocks:
- For each block, checks if view `blocks/{block_key}.php` exists
- Falls back to `blocks/unknown.php` if not found
- Passes `$block`, `$config`, `$data`, `$renderedChildren` to each view
- Built-in types: `rich_text`, `image`, `hero_banner`, `cta`, `container`

Add new block types by creating `app/Views/blocks/{block_key}.php`.

## Prerequisite: Domain Endpoints

Two endpoints required in `ci4-website-builder-domain`:
- `GET /api/v1/public/settings` — Returns all public settings (is_public=1)
- `GET /api/v1/public/{lang}/pages` — Returns published pages for sitemap

See `ci4-website-builder-domain/CLAUDE.md` for setup.

## Testing

Start both servers:
```bash
# Terminal 1: Domain API (port 8190)
cd ci4-website-builder-domain && php spark serve --port 8190

# Terminal 2: Website (port 8186)
cd ci4-website-builder-web && php spark serve --port 8186

# Terminal 3: Tailwind CSS watch
cd ci4-website-builder-web && npm run dev:css
```

Visit:
- Homepage: `http://localhost:8186/`
- Sitemap: `http://localhost:8186/sitemap.xml`
- Page (example): `http://localhost:8186/about` (if page with slug "about" exists in domain)
- Collection (example): `http://localhost:8186/news` (if the collection slug for the active language is `/news`)
- Entry (example): `http://localhost:8186/news/first-post` (if entry exists in domain)

## Troubleshooting

- **401 on every request**: Check `WEB_API_KEY` matches domain app configuration
- **Collections not rendering**: Verify the translated collection slug exists in the domain response
- **CSS not compiled**: Run `npm run build:css` or start `npm run dev:css` watcher
- **Cache issues**: Clear with `php spark cache:clear`

## Future Enhancements

- Pagination UI in collection/index.php
- Form handling for contact/custom pages
- Image file resolution (currently placeholder)
- Multi-language navigation switching
- PWA/offline support
