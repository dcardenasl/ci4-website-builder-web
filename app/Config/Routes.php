<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Health check endpoint (no locale prefix, excluded from localized routing)
$routes->get('health', 'HealthController::index', ['as' => 'health']);

// Restrict routes with {locale} to Config\App::$supportedLocales
$routes->useSupportedLocalesOnly(true);

// Localized routes
$routes->get('{locale}', 'PageController::home', ['as' => 'home_localized']);
$routes->get('{locale}/sitemap.xml', 'SitemapController::index', ['as' => 'sitemap_localized']);
$routes->get('{locale}/(:any)', 'PageController::resolve/$1');

// Fallback non-localized routes (redirected/resolved dynamically)
$routes->get('/', 'PageController::home', ['as' => 'home']);
$routes->get('sitemap.xml', 'SitemapController::index', ['as' => 'sitemap']);
$routes->get('(:any)', 'PageController::resolve/$1');
