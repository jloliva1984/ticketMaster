<?php

namespace App\Controllers;

/**
 * Home / Dashboard Controller
 */
class Home extends BaseController
{
    public function index(): string
    {
        // TODO Phase 3: redirect to login if not authenticated

        $data = [
            'pageTitle'  => lang('General.dashboard'),
            'activeMenu' => 'dashboard',

            // Stats — populated from models in Phase 4+
            'stats' => [
                'invoices'  => 0,
                'tasks'     => 0,
                'quarries'  => 0,
                'unmatched' => 0,
            ],

            'recentTrucks' => [],

            // Phase 3 will set this from session
            'currentUser' => [
                'name' => 'Admin',
                'role' => 'admin',
            ],
        ];

        return view('home/dashboard', $data);
    }
}
