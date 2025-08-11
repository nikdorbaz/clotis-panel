<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'Home::index');
$routes->post('auth', 'Home::auth');
$routes->post('logout', 'Home::logout');

$routes->group('stock', static function ($routes) {
  $routes->add('/', 'Stock::ordini');
  $routes->add('ordini', 'Stock::ordini');
  $routes->add('spedizione', 'Stock::spedizione');

  $routes->get('table-data', 'Stock::tableData');

  $routes->add('ordini/campaign/(:num)', 'Stock::ordiniByCampaign/$1');
  $routes->add('spedizione/campaign/(:num)', 'Stock::spedizioneByCampaign/$1');
});

$routes->group('manager', static function ($routes) {
  $routes->add('payments', 'Manager::payments');
  $routes->add('difference', 'Manager::difference');
  // $routes->add('history/(:segment)', 'Manager::history/$1');

  $routes->add('payments/campaign/(:num)', 'Manager::paymentsByCampaign/$1');
  $routes->add('difference/campaign/(:num)', 'Manager::differenceByCampaign/$1');

  $routes->add('monthly', 'Manager::monthly');
});
