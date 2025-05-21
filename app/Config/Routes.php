<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->post('auth', 'Home::auth');
$routes->post('logout', 'Home::logout');

$routes->group('stock', static function ($routes) {
  $routes->add('/', 'Stock::index');
  $routes->add('history/(:num)', 'Stock::history/$1');
});

$routes->group('manager', static function ($routes) {
  $routes->add('/', 'Manager::index');
});
