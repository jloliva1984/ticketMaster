<?php

namespace App\Controllers;

use App\Models\TaskTicketModel;
use App\Models\InvoiceTicketModel;
use CodeIgniter\HTTP\ResponseInterface;

class UnmatchedTickets extends BaseController
{
    private TaskTicketModel    $taskTicketModel;
    private InvoiceTicketModel $invoiceTicketModel;

    public function __construct()
    {
        $this->taskTicketModel    = new TaskTicketModel();
        $this->invoiceTicketModel = new InvoiceTicketModel();
    }

    // ── GET /unmatched-tickets ────────────────────────────────────
    public function index(): string
    {
        return $this->render('unmatched_tickets/index', [
            'pageTitle'   => lang('General.unmatched_tickets'),
            'activeMenu'  => 'unmatched_tickets',
            'breadcrumbs' => [lang('General.unmatched_tickets') => null],
        ]);
    }

    // ── GET /unmatched-tickets/data  (DataTable AJAX) ─────────────
    public function data(): ResponseInterface
    {
        $rows = $this->taskTicketModel->unmatched();

        $data = array_map(function ($r) {
            $statusBadge = $r['task_status'] === 'delivered'
                ? '<span class="badge bg-success">' . lang('General.status_delivered') . '</span>'
                : '<span class="badge bg-warning text-dark">' . lang('General.status_open') . '</span>';

            $matchBtn = '<button class="btn btn-sm btn-outline-primary" '
                . 'onclick="openMatch(' . $r['id'] . ',' . (int)$r['cantera_id'] . ')">'
                . '<i class="bi bi-link-45deg me-1"></i>' . lang('General.match')
                . '</button>';

            return [
                'id'             => $r['id'],
                'no_ticket'      => esc($r['no_ticket']),
                'fecha'          => $r['fecha'] ? date('m/d/Y', strtotime($r['fecha'])) : '—',
                'tipo_trabajo'   => esc($r['tipo_trabajo'] ?? '—'),
                'nombre_cantera' => esc($r['nombre_cantera'] ?? '—'),
                'direccion'      => esc($r['direccion'] ?? '—'),
                'rate'           => '$' . number_format((float)$r['rate'], 2),
                'no_camion'      => esc($r['no_camion'] ?? '—'),
                'nombre_chofer'  => esc($r['nombre_chofer'] ?? '—'),
                'task_status'    => $statusBadge,
                'actions'        => $matchBtn,
            ];
        }, $rows);

        return $this->response->setJSON(['data' => $data]);
    }

    // ── GET /unmatched-tickets/candidates  (modal AJAX) ──────────
    // Returns invoice tickets available for matching for a given quarry.
    public function candidates(): ResponseInterface
    {
        $canterId = (int) $this->request->getGet('cantera_id');
        if ($canterId <= 0) {
            return $this->jsonError('Invalid quarry.', 422);
        }

        $rows = $this->invoiceTicketModel->unmatchedForQuarry($canterId);
        return $this->response->setJSON(['data' => $rows]);
    }

    // ── POST /unmatched-tickets/match/:id ─────────────────────────
    public function match(int $taskTicketId): ResponseInterface
    {
        $invoiceTicketId = (int) $this->request->getPost('invoice_ticket_id');

        if ($invoiceTicketId <= 0) {
            return $this->jsonError(lang('General.required'), 422);
        }

        // Fetch the target invoice ticket
        $invTicket = $this->invoiceTicketModel->find($invoiceTicketId);
        if (! $invTicket) {
            return $this->jsonError('Invoice ticket not found.', 404);
        }

        // Fetch the task ticket to verify ownership
        $taskTicket = $this->taskTicketModel->find($taskTicketId);
        if (! $taskTicket) {
            return $this->jsonError('Task ticket not found.', 404);
        }

        // Quarry must match
        if ((int)$taskTicket['cantera_id'] !== (int)$invTicket['cantera_id']) {
            return $this->jsonError('Quarry mismatch between task and invoice ticket.', 422);
        }

        // Update task ticket number to match the invoice ticket
        $this->taskTicketModel->update($taskTicketId, [
            'no_ticket' => $invTicket['no_ticket'],
        ]);

        return $this->jsonSuccess(lang('General.matched'));
    }
}
