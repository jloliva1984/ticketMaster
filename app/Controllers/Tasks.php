<?php

namespace App\Controllers;

use App\Models\TaskModel;
use App\Models\TaskTicketModel;
use App\Models\TruckModel;
use App\Models\QuarryModel;
use CodeIgniter\HTTP\ResponseInterface;

class Tasks extends BaseController
{
    private TaskModel       $model;
    private TaskTicketModel $ticketModel;
    private TruckModel      $truckModel;
    private QuarryModel     $quarryModel;

    public function __construct()
    {
        $this->model       = new TaskModel();
        $this->ticketModel = new TaskTicketModel();
        $this->truckModel  = new TruckModel();
        $this->quarryModel = new QuarryModel();
    }

    // ── GET /tasks ───────────────────────────────────────────────
    public function index(): string
    {
        return $this->render('tasks/index', [
            'pageTitle'   => lang('General.tasks'),
            'activeMenu'  => 'tasks',
            'breadcrumbs' => [lang('General.tasks') => null],
        ]);
    }

    // ── GET /tasks/data  (DataTable AJAX) ────────────────────────
    public function data(): ResponseInterface
    {
        $records = $this->model->forDataTable();
        $rows    = [];

        foreach ($records as $r) {
            $isOpen = $r['status'] === 'open';

            $statusBadge = $isOpen
                ? '<span class="badge bg-warning-subtle text-warning fw-semibold">Open</span>'
                : '<span class="badge bg-success-subtle text-success fw-semibold">Delivered</span>';

            $deliverBtn = $isOpen
                ? sprintf(
                    '<button class="btn btn-sm btn-success" onclick="markDelivered(%d)" title="Mark as Delivered">
                        <i class="bi bi-check-circle-fill me-1"></i>Deliver
                    </button>',
                    $r['id']
                )
                : sprintf(
                    '<small class="text-muted">%s</small>',
                    $r['delivered_at'] ? date('m/d/Y', strtotime($r['delivered_at'])) : '—'
                );

            $rows[] = [
                'id'            => $r['id'],
                'no_camion'     => esc($r['no_camion']),
                'nombre_chofer' => esc($r['nombre_chofer']),
                'periodo'       => $r['fecha_inicio']
                    ? date('m/d/Y', strtotime($r['fecha_inicio'])) . ' – ' . ($r['fecha_fin'] ? date('m/d/Y', strtotime($r['fecha_fin'])) : '…')
                    : '—',
                'monto_total'   => '$' . number_format($r['monto_total'], 2),
                'status'        => $statusBadge,
                'delivered'     => $deliverBtn,
                'created_at'    => date('m/d/Y', strtotime($r['created_at'])),
                'actions'       => $this->buildActions($r),
            ];
        }

        return $this->jsonResponse(['data' => $rows]);
    }

    // ── GET /tasks/create ────────────────────────────────────────
    public function create(): string
    {
        return $this->render('tasks/form', [
            'pageTitle'   => 'New Task',
            'activeMenu'  => 'tasks',
            'breadcrumbs' => [lang('General.tasks') => base_url('tasks'), 'New' => null],
            'task'        => null,
            'tickets'     => [],
            'trucks'      => $this->truckModel->forSelect(),
            'quarries'    => $this->quarryModel->forSelect(),
        ]);
    }

    // ── POST /tasks ──────────────────────────────────────────────
    public function store(): ResponseInterface
    {
        $errors = $this->validateTask();
        if ($errors !== true) {
            return $this->jsonError(lang('General.error_generic'), 422, $errors);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $taskId = $this->model->insert($this->buildTaskData(), true);
        $this->saveTickets($taskId, $this->request->getPost('tickets') ?? []);

        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->jsonError('Failed to save task.', 500);
        }

        return $this->jsonSuccess(lang('General.saved'), ['id' => $taskId]);
    }

    // ── GET /tasks/{id}/edit ─────────────────────────────────────
    public function edit(int $id): string
    {
        $task = $this->model->withTruck($id);
        if (! $task) {
            return redirect()->to(base_url('tasks'))->with('error', 'Task not found.');
        }

        return $this->render('tasks/form', [
            'pageTitle'   => 'Edit Task #' . $id,
            'activeMenu'  => 'tasks',
            'breadcrumbs' => [lang('General.tasks') => base_url('tasks'), 'Edit' => null],
            'task'        => $task,
            'tickets'     => $this->ticketModel->forTask($id),
            'trucks'      => $this->truckModel->forSelect(),
            'quarries'    => $this->quarryModel->forSelect(),
        ]);
    }

    // ── POST /tasks/{id} ─────────────────────────────────────────
    public function update(int $id): ResponseInterface
    {
        $task = $this->model->find($id);
        if (! $task) {
            return $this->jsonError('Task not found.', 404);
        }

        $errors = $this->validateTask();
        if ($errors !== true) {
            return $this->jsonError(lang('General.error_generic'), 422, $errors);
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $data = $this->buildTaskData();
        // Preserve delivered_at if already set
        if ($task['status'] === 'delivered') {
            unset($data['status'], $data['delivered_at']);
        }

        $this->model->update($id, $data);
        $this->ticketModel->deleteForTask($id);
        $this->saveTickets($id, $this->request->getPost('tickets') ?? []);

        $db->transComplete();

        if (! $db->transStatus()) {
            return $this->jsonError('Failed to update task.', 500);
        }

        return $this->jsonSuccess(lang('General.saved'));
    }

    // ── POST /tasks/{id}/deliver  (Mark as delivered) ────────────
    public function markDelivered(int $id): ResponseInterface
    {
        $task = $this->model->find($id);
        if (! $task) {
            return $this->jsonError('Task not found.', 404);
        }

        if ($task['status'] === 'delivered') {
            return $this->jsonError('Task is already marked as delivered.', 409);
        }

        $this->model->markDelivered($id);

        return $this->jsonSuccess('Task marked as delivered.');
    }

    // ── DELETE /tasks/{id} ───────────────────────────────────────
    public function destroy(int $id): ResponseInterface
    {
        $task = $this->model->find($id);
        if (! $task) {
            return $this->jsonError('Task not found.', 404);
        }

        $this->model->delete($id);

        return $this->jsonSuccess(lang('General.deleted'));
    }

    // ── GET /tasks/{id}/show ─────────────────────────────────────
    public function show(int $id): string
    {
        $task = $this->model->withTruck($id);
        if (! $task) {
            return redirect()->to(base_url('tasks'))->with('error', 'Task not found.');
        }

        return $this->render('tasks/show', [
            'pageTitle'   => 'Task #' . $id . ' — ' . $task['no_camion'],
            'activeMenu'  => 'tasks',
            'breadcrumbs' => [lang('General.tasks') => base_url('tasks'), '#' . $id => null],
            'task'        => $task,
            'tickets'     => $this->ticketModel->forTask($id),
        ]);
    }

    // ── Private helpers ───────────────────────────────────────────

    private function validateTask(): array|bool
    {
        $rules = [
            'truck_id'      => 'required|integer',
            'nombre_chofer' => 'required|min_length[2]|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return $this->validator->getErrors();
        }

        return true;
    }

    private function buildTaskData(): array
    {
        return [
            'truck_id'      => (int) $this->request->getPost('truck_id'),
            'nombre_chofer' => trim($this->request->getPost('nombre_chofer')),
            'fecha_inicio'  => $this->parseDate($this->request->getPost('fecha_inicio')) ?: null,
            'fecha_fin'     => $this->parseDate($this->request->getPost('fecha_fin')) ?: null,
            'monto_total'   => $this->calcTotal($this->request->getPost('tickets') ?? []),
            'status'        => $this->request->getPost('status') ?? 'open',
            'notes'         => $this->request->getPost('notes'),
        ];
    }

    private function saveTickets(int $taskId, array $tickets): void
    {
        if (empty($tickets)) {
            return;
        }

        $now  = date('Y-m-d H:i:s');
        $rows = [];

        foreach ($tickets as $t) {
            if (empty($t['no_ticket'])) {
                continue;
            }
            $rows[] = [
                'task_id'      => $taskId,
                'no_ticket'    => trim($t['no_ticket']),
                'fecha'        => $this->parseDate($t['fecha'] ?? '') ?: date('Y-m-d'),
                'tipo_trabajo' => $t['tipo_trabajo'] ?? null,
                'cantera_id'   => ! empty($t['cantera_id']) ? (int) $t['cantera_id'] : null,
                'direccion'    => $t['direccion'] ?? null,
                'rate'         => (float) ($t['rate'] ?? 0),
                'created_at'   => $now,
                'updated_at'   => $now,
            ];
        }

        if (! empty($rows)) {
            $this->ticketModel->insertBatch($rows);
        }
    }

    private function calcTotal(array $tickets): float
    {
        return array_sum(array_map(fn($t) => (float) ($t['rate'] ?? 0), $tickets));
    }

    private function parseDate(?string $date): ?string
    {
        if (empty($date)) {
            return null;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }
        $d = \DateTime::createFromFormat('m/d/Y', $date);
        return $d ? $d->format('Y-m-d') : null;
    }

    private function buildActions(array $r): string
    {
        $showUrl = base_url('tasks/' . $r['id'] . '/show');
        $editUrl = base_url('tasks/' . $r['id'] . '/edit');

        return sprintf(
            '<div class="d-flex gap-1">
                <a href="%s" class="btn btn-sm btn-outline-info" title="View"><i class="bi bi-eye-fill"></i></a>
                <a href="%s" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil-fill"></i></a>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteTask(%d)" title="Delete">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </div>',
            $showUrl, $editUrl, $r['id']
        );
    }
}
