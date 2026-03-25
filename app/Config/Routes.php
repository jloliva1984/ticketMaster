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
$routes->setAutoRoute(false);

/*
|--------------------------------------------------------------------------
| Public Routes — no auth required
|--------------------------------------------------------------------------
*/

// Health check
$routes->get('/ping', static function () {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'app' => 'TicketMaster.LT']);
    exit;
});

// Language switcher
$routes->get('lang/(:segment)', 'Language::switch/$1');

// Authentication
$routes->group('auth', ['namespace' => 'App\Controllers\Auth'], static function ($routes) {
    $routes->get('login',   'AuthController::login');
    $routes->post('login',  'AuthController::loginProcess');
    $routes->get('logout',  'AuthController::logout');
});

/*
|--------------------------------------------------------------------------
| Protected Routes — require auth filter
|--------------------------------------------------------------------------
*/
$routes->group('', ['filter' => 'auth'], static function ($routes) {

    // Dashboard
    $routes->get('/', 'Home::index');

    // Phase 4 — Catalog CRUDs
    // $routes->resource('quarries', ['controller' => 'Quarries']);
    // $routes->resource('trucks',   ['controller' => 'Trucks']);

    // Phase 4 — Users (admin only)
    // $routes->group('users', ['filter' => 'admin', 'controller' => 'Users'], static function ($routes) {
    //     $routes->get('/',             'Users::index');
    //     $routes->get('create',        'Users::create');
    //     $routes->post('/',            'Users::store');
    //     $routes->get('(:num)/edit',   'Users::edit/$1');
    //     $routes->post('(:num)',       'Users::update/$1');
    //     $routes->delete('(:num)',     'Users::destroy/$1');
    //     $routes->get('data',          'Users::data');      // DataTable AJAX
    // });

    // Phase 5 — Invoices
    // $routes->resource('invoices', ['controller' => 'Invoices']);
    // $routes->get('invoices/data', 'Invoices::data');
    // $routes->get('invoices/(:num)/export/(:alpha)', 'Invoices::export/$1/$2');

    // Phase 6 — Tasks
    // $routes->resource('tasks', ['controller' => 'Tasks']);
    // $routes->get('tasks/data', 'Tasks::data');
    // $routes->post('tasks/(:num)/deliver', 'Tasks::markDelivered/$1');

    // Phase 7 — Reports
    // $routes->get('reports',              'Reports::index');
    // $routes->get('reports/export/(:alpha)', 'Reports::export/$1');

    // Phase 8 — Unmatched tickets
    // $routes->get('unmatched-tickets',                   'UnmatchedTickets::index');
    // $routes->get('unmatched-tickets/data',              'UnmatchedTickets::data');
    // $routes->post('unmatched-tickets/match/(:num)',     'UnmatchedTickets::match/$1');
});
