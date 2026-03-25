<?php

use CodeIgniter\Router\RouteCollection;

/**
 * Routes Configuration
 *
 * @var RouteCollection $routes
 */

$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();

// All routes must be explicit — no auto-routing
$routes->setAutoRoute(false);

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

// Health check
$routes->get('/ping', static function () {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'app' => 'TicketMaster.LT', 'version' => CI_VERSION]);
    exit;
});

// Language switcher
$routes->get('lang/(:segment)', 'Language::switch/$1');

/*
|--------------------------------------------------------------------------
| Phase 3 — Auth (uncomment when implemented)
|--------------------------------------------------------------------------
*/
// $routes->group('auth', ['namespace' => 'App\Controllers\Auth'], static function ($routes) {
//     $routes->get('login',   'AuthController::login');
//     $routes->post('login',  'AuthController::loginProcess');
//     $routes->get('logout',  'AuthController::logout');
// });

/*
|--------------------------------------------------------------------------
| Protected Routes (require auth filter — Phase 3+)
|--------------------------------------------------------------------------
*/
// $routes->group('', ['filter' => 'auth'], static function ($routes) {

    // Dashboard
    $routes->get('/', 'Home::index');

    // Phase 4 — Catalog CRUDs
    // $routes->resource('quarries', ['controller' => 'Quarries']);
    // $routes->resource('trucks',   ['controller' => 'Trucks']);
    // $routes->resource('users',    ['controller' => 'Users', 'filter' => 'admin']);

    // Phase 5 — Invoices
    // $routes->resource('invoices', ['controller' => 'Invoices']);

    // Phase 6 — Tasks
    // $routes->resource('tasks', ['controller' => 'Tasks']);
    // $routes->post('tasks/(:num)/deliver', 'Tasks::markDelivered/$1');

    // Phase 7 — Reports
    // $routes->get('reports',         'Reports::index');
    // $routes->get('reports/export',  'Reports::export');

    // Phase 8 — Unmatched tickets
    // $routes->get('unmatched-tickets',            'UnmatchedTickets::index');
    // $routes->post('unmatched-tickets/match/(:num)', 'UnmatchedTickets::match/$1');

// });
