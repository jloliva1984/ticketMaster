<?php

namespace App\Controllers;

use App\Models\InvoiceModel;
use App\Models\TaskModel;
use App\Models\QuarryModel;
use App\Models\TaskTicketModel;

/**
 * Home / Dashboard Controller
 * Protected by AuthFilter (see Routes.php)
 */
class Home extends BaseController
{
    public function index(): string
    {
        $invoiceModel    = new InvoiceModel();
        $taskModel       = new TaskModel();
        $quarryModel     = new QuarryModel();
        $taskTicketModel = new TaskTicketModel();

        $stats = [
            'invoices'  => $invoiceModel->where('deleted_at IS NULL')->countAllResults(),
            'tasks'     => $taskModel->where('deleted_at IS NULL')->countAllResults(),
            'quarries'  => $quarryModel->where('deleted_at IS NULL')->where('is_active', 1)->countAllResults(),
            'unmatched' => $taskTicketModel->unmatchedCount(),
        ];

        // Recent invoices — last 5
        $recentInvoices = $invoiceModel->forDataTable();
        $recentInvoices = array_slice($recentInvoices, 0, 5);

        // Trucks summary — top 5 by amount this month
        $from = date('Y-m-01');
        $to   = date('Y-m-d');
        $recentTrucks = $taskModel->reportByTruck($from, $to);
        $recentTrucks = array_slice($recentTrucks, 0, 5);

        return $this->render('home/dashboard', [
            'pageTitle'     => lang('General.dashboard'),
            'activeMenu'    => 'dashboard',
            'stats'         => $stats,
            'recentInvoices' => $recentInvoices,
            'recentTrucks'  => $recentTrucks,
        ]);
    }
}
