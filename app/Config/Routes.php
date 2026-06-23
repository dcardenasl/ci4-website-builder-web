<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Health check endpoint (no locale prefix, excluded from localized routing)
$routes->get('health', 'HealthController::index', ['as' => 'health']);
$routes->get('sitemap.xml', 'SitemapController::index', ['as' => 'sitemap']);

// Internal cache invalidation — no locale prefix, secured by X-Invalidate-Key header
$routes->post('cache/invalidate', 'CacheController::invalidate', ['as' => 'cache_invalidate']);

// Contact form submission — works with or without locale prefix
$routes->post('contacto/enviar', 'ContactController::store', ['as' => 'contact_store']);

// Restrict routes with {locale} to Config\App::$supportedLocales
$routes->useSupportedLocalesOnly(true);

// Contact form submission (localized)
$routes->post('{locale}/contacto/enviar', 'ContactController::store', ['as' => 'contact_store_localized']);

// Localized routes
$routes->get('{locale}', 'PageController::home', ['as' => 'home_localized']);
$routes->get('{locale}/sitemap.xml', 'SitemapController::index', ['as' => 'sitemap_localized']);
$routes->get('{locale}/(:any)', 'PageController::resolve/$1');

// Fallback non-localized routes (redirected/resolved dynamically)
$routes->get('/', 'PageController::home', ['as' => 'home']);
$routes->get('(:any)', 'PageController::resolve/$1');
