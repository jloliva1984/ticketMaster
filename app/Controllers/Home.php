<?php

namespace App\Controllers;

/**
 * Home / Dashboard Controller
 * Protected by AuthFilter (see Routes.php)
 */
class Home extends BaseController
{
    public function index(): string
    {
        // Stats will be populated from models in Phase 4+
        return $this->render('home/dashboard', [
            'pageTitle'  => lang('General.dashboard'),
            'activeMenu' => 'dashboard',
            'stats' => [
                'invoices'  => 0,
                'tasks'     => 0,
                'quarries'  => 0,
                'unmatched' => 0,
            ],
            'recentTrucks' => [],
        ]);
    }
}
