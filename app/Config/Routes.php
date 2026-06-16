<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', 'PageController::home', ['as' => 'home']);
$routes->get('sitemap.xml', 'SitemapController::index', ['as' => 'sitemap']);
$routes->get('(:any)', 'PageController::resolve/$1');
