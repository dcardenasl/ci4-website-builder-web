<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Homepage
$routes->get('/', 'PageController::home', ['as' => 'home']);

// Sitemap
$routes->get('sitemap.xml', 'SitemapController::index', ['as' => 'sitemap']);

// Dynamic page/collection/entry resolver - must be last
$routes->get('(:any)', 'PageController::resolve/$1');
