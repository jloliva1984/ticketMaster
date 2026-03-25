<?php

namespace App\Controllers;

use App\Models\QuarryModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Quarries (Canteras) Controller
 */
class Quarries extends BaseController
{
    private QuarryModel $model;

    public function __construct()
    {
        $this->model = new QuarryModel();
    }

    // ── GET /quarries ───────────────────────────────────────────
    public function index(): string
    {
        return $this->render('quarries/index', [
            'pageTitle'   => lang('General.quarries'),
            'activeMenu'  => 'quarries',
            'breadcrumbs' => [lang('General.quarries') => null],
        ]);
    }

    // ── GET /quarries/data  (DataTable AJAX) ────────────────────
    public function data(): ResponseInterface
    {
        $records = $this->model->forDataTable();
        $rows    = [];

        foreach ($records as $r) {
            $statusBadge = $r['is_active']
                ? '<span class="badge bg-success-subtle text-success">Active</span>'
                : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>';

            $rows[] = [
                'id'             => $r['id'],
                'nombre_cantera' => esc($r['nombre_cantera']),
                'is_active'      => $statusBadge,
                'created_at'     => date('m/d/Y', strtotime($r['created_at'])),
                'actions'        => $this->buildActions($r),
            ];
        }

        return $this->jsonResponse(['data' => $rows]);
    }

    // ── GET /quarries/{id}/edit  (modal data AJAX) ──────────────
    public function edit(int $id): ResponseInterface
    {
        $record = $this->model->find($id);

        if (! $record) {
            return $this->jsonError('Quarry not found.', 404);
        }

        return $this->jsonResponse($record);
    }

    // ── POST /quarries ──────────────────────────────────────────
    public function store(): ResponseInterface
    {
        $rules = [
            'nombre_cantera' => 'required|min_length[2]|max_length[150]|is_unique[quarries.nombre_cantera]',
        ];

        if (! $this->validate($rules)) {
            return $this->jsonError(lang('General.error_generic'), 422, $this->validator->getErrors());
        }

        $this->model->insert([
            'nombre_cantera' => $this->request->getPost('nombre_cantera'),
            'is_active'      => (int) $this->request->getPost('is_active', FILTER_SANITIZE_NUMBER_INT) ?: 1,
        ]);

        return $this->jsonSuccess(lang('General.saved'));
    }

    // ── POST /quarries/{id} ─────────────────────────────────────
    public function update(int $id): ResponseInterface
    {
        $record = $this->model->find($id);
        if (! $record) {
            return $this->jsonError('Quarry not found.', 404);
        }

        $rules = [
            'nombre_cantera' => "required|min_length[2]|max_length[150]|is_unique[quarries.nombre_cantera,id,{$id}]",
        ];

        if (! $this->validate($rules)) {
            return $this->jsonError(lang('General.error_generic'), 422, $this->validator->getErrors());
        }

        $this->model->update($id, [
            'nombre_cantera' => $this->request->getPost('nombre_cantera'),
            'is_active'      => (int) $this->request->getPost('is_active', FILTER_SANITIZE_NUMBER_INT),
        ]);

        return $this->jsonSuccess(lang('General.saved'));
    }

    // ── DELETE /quarries/{id} ───────────────────────────────────
    public function destroy(int $id): ResponseInterface
    {
        $record = $this->model->find($id);
        if (! $record) {
            return $this->jsonError('Quarry not found.', 404);
        }

        $this->model->delete($id);

        return $this->jsonSuccess(lang('General.deleted'));
    }

    // ── Private ─────────────────────────────────────────────────
    private function buildActions(array $r): string
    {
        return sprintf(
            '<div class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-primary" onclick="openEdit(%d)" title="Edit">
                    <i class="bi bi-pencil-fill"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteQuarry(%d,\'%s\')" title="Delete">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </div>',
            $r['id'], $r['id'], esc($r['nombre_cantera'], 'js')
        );
    }
}
