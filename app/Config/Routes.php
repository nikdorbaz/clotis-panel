<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');

$routes->group('stock', static function ($routes) {
  $routes->add('(:num)', 'Stock::index/$1');
});
