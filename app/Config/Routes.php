<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

$routes->get('/', static fn () => redirect()->to(site_url('tatkal')));
$routes->get('tatkal', 'TatkalController::dashboard');
$routes->get('tatkal/pnr', 'TatkalController::pnr');
$routes->get('tatkal/rejected', 'TatkalController::rejected');
$routes->get('tatkal/reset', 'TatkalController::reset');
$routes->post('tatkal/cancel', 'TatkalController::cancel');
$routes->get('tatkal/live', 'TatkalController::liveMetrics');
