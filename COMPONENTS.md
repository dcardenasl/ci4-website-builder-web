# Components Checklist

## Core Infrastructure
- [x] WebApiClient (app/Libraries/WebApiClient.php)
- [x] BasePublicWebController (app/Controllers/BasePublicWebController.php)
- [x] Services factory (app/Config/Services.php)
- [x] App configuration (app/Config/App.php)

## Service Layer
- [x] SiteSettingsService (app/Services/SiteSettingsService.php)
- [x] SiteMenuService (app/Services/SiteMenuService.php)
- [x] SitePageService (app/Services/SitePageService.php)
- [x] SiteCollectionService (app/Services/SiteCollectionService.php)
- [x] SiteEntryService (app/Services/SiteEntryService.php)
- [x] SiteRedirectService (app/Services/SiteRedirectService.php)

## Block Rendering System
- [x] BlockRenderer library (app/Libraries/BlockRenderer.php)
- [x] rich_text block (app/Views/blocks/rich_text.php)
- [x] image block (app/Views/blocks/image.php)
- [x] hero_banner block (app/Views/blocks/hero_banner.php)
- [x] cta block (app/Views/blocks/cta.php)
- [x] container block (app/Views/blocks/container.php)
- [x] unknown block fallback (app/Views/blocks/unknown.php)

## Layout & Views
- [x] Master layout (app/Views/layouts/public.php)
- [x] Head partial (app/Views/layouts/partials/head.php)
- [x] Header partial (app/Views/layouts/partials/header.php)
- [x] Footer partial (app/Views/layouts/partials/footer.php)
- [x] Flash messages partial (app/Views/layouts/partials/flash_messages.php)
- [x] Generic page view (app/Views/page.php)
- [x] Collection index view (app/Views/collection/index.php)
- [x] Collection detail view (app/Views/collection/show.php)
- [x] Error 404 view (app/Views/errors/404.php)

## Controllers
- [x] PageController (app/Controllers/PageController.php)
  - 5-step page resolution algorithm
  - home() method for homepage
  - resolve() method for dynamic routing
  - SEO data extraction and rendering
- [x] SitemapController (app/Controllers/SitemapController.php)
  - XML sitemap generation
  - Includes pages and collection entries
  - Caching for performance

## Routing & Configuration
- [x] Routes configuration (app/Config/Routes.php)
  - GET / → homepage
  - GET /sitemap.xml → XML sitemap
  - GET (:any) → dynamic catch-all resolver
- [x] Environment configuration (.env)
- [x] NPM configuration (package.json)
- [x] Tailwind CSS setup (tailwind.config.js, postcss.config.js)

## Quality & Documentation
- [x] CLAUDE.md - Project documentation
- [x] IMPLEMENTATION.md - Implementation details
- [x] COMPONENTS.md - This file
- [x] Configuration files (.php-cs-fixer.dist.php, phpstan.neon, phpunit.xml.dist)
