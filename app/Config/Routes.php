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
$routes->get('/ping', static function () {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'ok', 'app' => 'TicketMaster.LT']);
    exit;
});

$routes->get('lang/(:segment)', 'Language::switch/$1');

// Authentication
$routes->group('auth', ['namespace' => 'App\Controllers\Auth'], static function ($routes) {
    $routes->get('login',   'AuthController::login');
    $routes->post('login',  'AuthController::loginProcess');
    $routes->get('logout',  'AuthController::logout');
});

/*
|--------------------------------------------------------------------------
| Protected Routes — require auth
|--------------------------------------------------------------------------
*/
$routes->group('', ['filter' => 'auth'], static function ($routes) {

    // Dashboard
    $routes->get('/', 'Home::index');

    // ── Catalog ─────────────────────────────────────────────────
    // Quarries
    $routes->get('quarries',              'Quarries::index');
    $routes->get('quarries/data',         'Quarries::data');
    $routes->get('quarries/(:num)/edit',  'Quarries::edit/$1');
    $routes->post('quarries',             'Quarries::store');
    $routes->post('quarries/(:num)',      'Quarries::update/$1');
    $routes->delete('quarries/(:num)',    'Quarries::destroy/$1');

    // Trucks
    $routes->get('trucks',                         'Trucks::index');
    $routes->get('trucks/data',                    'Trucks::data');
    $routes->get('trucks/(:num)/edit',             'Trucks::edit/$1');
    $routes->get('trucks/chofer/(:segment)',        'Trucks::chofer/$1');
    $routes->post('trucks',                        'Trucks::store');
    $routes->post('trucks/(:num)',                 'Trucks::update/$1');
    $routes->delete('trucks/(:num)',               'Trucks::destroy/$1');

    // ── Users (admin only) ───────────────────────────────────────
    $routes->group('users', ['filter' => 'admin'], static function ($routes) {
        $routes->get('/',           'Users::index');
        $routes->get('data',        'Users::data');
        $routes->get('(:num)/edit', 'Users::edit/$1');
        $routes->post('/',          'Users::store');
        $routes->post('(:num)',     'Users::update/$1');
        $routes->delete('(:num)',   'Users::destroy/$1');
    });

    // ── Invoices (Phase 5) ───────────────────────────────────────
    $routes->get('invoices',                         'Invoices::index');
    $routes->get('invoices/data',                    'Invoices::data');
    $routes->get('invoices/export/excel',            'Invoices::exportExcel');
    $routes->get('invoices/create',                  'Invoices::create');
    $routes->post('invoices',                        'Invoices::store');
    $routes->get('invoices/(:num)/show',             'Invoices::show/$1');
    $routes->get('invoices/(:num)/edit',             'Invoices::edit/$1');
    $routes->post('invoices/(:num)',                 'Invoices::update/$1');
    $routes->delete('invoices/(:num)',               'Invoices::destroy/$1');
    $routes->get('invoices/(:num)/export/pdf',       'Invoices::exportPdf/$1');
    $routes->get('invoices/(:num)/pdf',              'Invoices::servePdf/$1');

    // ── Tasks (Phase 6) ──────────────────────────────────────────
    $routes->get('tasks',                  'Tasks::index');
    $routes->get('tasks/data',             'Tasks::data');
    $routes->get('tasks/create',           'Tasks::create');
    $routes->post('tasks',                 'Tasks::store');
    $routes->get('tasks/(:num)/show',      'Tasks::show/$1');
    $routes->get('tasks/(:num)/edit',      'Tasks::edit/$1');
    $routes->post('tasks/(:num)',          'Tasks::update/$1');
    $routes->delete('tasks/(:num)',        'Tasks::destroy/$1');
    $routes->post('tasks/(:num)/deliver',  'Tasks::markDelivered/$1');

    // ── Reports (Phase 7) ────────────────────────────────────────
    // $routes->get('reports',                      'Reports::index');
    // $routes->get('reports/export/(:alpha)',       'Reports::export/$1');

    // ── Unmatched Tickets (Phase 8) ──────────────────────────────
    // $routes->get('unmatched-tickets',                    'UnmatchedTickets::index');
    // $routes->get('unmatched-tickets/data',               'UnmatchedTickets::data');
    // $routes->post('unmatched-tickets/match/(:num)',      'UnmatchedTickets::match/$1');
});
