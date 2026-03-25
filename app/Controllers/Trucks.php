<?php

namespace App\Controllers;

use App\Models\TruckModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Trucks (Camiones) Controller
 */
class Trucks extends BaseController
{
    private TruckModel $model;

    public function __construct()
    {
        $this->model = new TruckModel();
    }

    // ── GET /trucks ─────────────────────────────────────────────
    public function index(): string
    {
        return $this->render('trucks/index', [
            'pageTitle'   => lang('General.trucks'),
            'activeMenu'  => 'trucks',
            'breadcrumbs' => [lang('General.trucks') => null],
        ]);
    }

    // ── GET /trucks/data  (DataTable AJAX) ──────────────────────
    public function data(): ResponseInterface
    {
        $records = $this->model->forDataTable();
        $rows    = [];

        foreach ($records as $r) {
            $statusBadge = $r['is_active']
                ? '<span class="badge bg-success-subtle text-success">Active</span>'
                : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>';

            $rows[] = [
                'id'            => $r['id'],
                'no_camion'     => esc($r['no_camion']),
                'nombre_chofer' => esc($r['nombre_chofer']),
                'is_active'     => $statusBadge,
                'created_at'    => date('m/d/Y', strtotime($r['created_at'])),
                'actions'       => $this->buildActions($r),
            ];
        }

        return $this->jsonResponse(['data' => $rows]);
    }

    // ── GET /trucks/{id}/edit  (modal data AJAX) ────────────────
    public function edit(int $id): ResponseInterface
    {
        $record = $this->model->find($id);

        if (! $record) {
            return $this->jsonError('Truck not found.', 404);
        }

        return $this->jsonResponse($record);
    }

    // ── GET /trucks/chofer/{no_camion}  (auto-fill driver AJAX) ─
    public function chofer(string $noCamion): ResponseInterface
    {
        $truck = $this->model->findByNumber($noCamion);

        if (! $truck) {
            return $this->jsonError('Truck not found.', 404);
        }

        return $this->jsonResponse([
            'nombre_chofer' => $truck['nombre_chofer'],
        ]);
    }

    // ── POST /trucks ─────────────────────────────────────────────
    public function store(): ResponseInterface
    {
        $rules = [
            'no_camion'     => 'required|min_length[1]|max_length[50]|is_unique[trucks.no_camion]',
            'nombre_chofer' => 'required|min_length[2]|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return $this->jsonError(lang('General.error_generic'), 422, $this->validator->getErrors());
        }

        $this->model->insert([
            'no_camion'     => strtoupper(trim($this->request->getPost('no_camion'))),
            'nombre_chofer' => $this->request->getPost('nombre_chofer'),
            'is_active'     => (int) $this->request->getPost('is_active', FILTER_SANITIZE_NUMBER_INT) ?: 1,
        ]);

        return $this->jsonSuccess(lang('General.saved'));
    }

    // ── POST /trucks/{id} ────────────────────────────────────────
    public function update(int $id): ResponseInterface
    {
        $record = $this->model->find($id);
        if (! $record) {
            return $this->jsonError('Truck not found.', 404);
        }

        $rules = [
            'no_camion'     => "required|min_length[1]|max_length[50]|is_unique[trucks.no_camion,id,{$id}]",
            'nombre_chofer' => 'required|min_length[2]|max_length[100]',
        ];

        if (! $this->validate($rules)) {
            return $this->jsonError(lang('General.error_generic'), 422, $this->validator->getErrors());
        }

        $this->model->update($id, [
            'no_camion'     => strtoupper(trim($this->request->getPost('no_camion'))),
            'nombre_chofer' => $this->request->getPost('nombre_chofer'),
            'is_active'     => (int) $this->request->getPost('is_active', FILTER_SANITIZE_NUMBER_INT),
        ]);

        return $this->jsonSuccess(lang('General.saved'));
    }

    // ── DELETE /trucks/{id} ──────────────────────────────────────
    public function destroy(int $id): ResponseInterface
    {
        $record = $this->model->find($id);
        if (! $record) {
            return $this->jsonError('Truck not found.', 404);
        }

        $this->model->delete($id);

        return $this->jsonSuccess(lang('General.deleted'));
    }

    // ── Private ──────────────────────────────────────────────────
    private function buildActions(array $r): string
    {
        return sprintf(
            '<div class="d-flex gap-1">
                <button class="btn btn-sm btn-outline-primary" onclick="openEdit(%d)" title="Edit">
                    <i class="bi bi-pencil-fill"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteTruck(%d,\'%s\')" title="Delete">
                    <i class="bi bi-trash-fill"></i>
                </button>
            </div>',
            $r['id'], $r['id'], esc($r['no_camion'], 'js')
        );
    }
}
