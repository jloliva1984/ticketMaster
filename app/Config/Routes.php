<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Routes Configuration
 *
 * @var RouteCollection $routes
 */

/*
|--------------------------------------------------------------------------
| Default Routing Options
|--------------------------------------------------------------------------
*/
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

// Disable auto-routing for security — all routes must be explicit
$routes->setAutoRoute(false);

/*
|--------------------------------------------------------------------------
| Route Definitions
|--------------------------------------------------------------------------
*/

// Home
$routes->get('/', 'Home::index');

// Health-check endpoint (useful for Hostinger monitoring)
$routes->get('/ping', static function () {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'app' => 'TicketMaster.LT']);
    exit;
});

/*
|--------------------------------------------------------------------------
| Phase 3+ — Auth & feature routes (added in later phases)
|--------------------------------------------------------------------------
*/
// $routes->group('auth', ['namespace' => 'App\Controllers\Auth'], static function ($routes) {
//     $routes->get('login',    'AuthController::login');
//     $routes->post('login',   'AuthController::loginProcess');
//     $routes->get('logout',   'AuthController::logout');
// });
