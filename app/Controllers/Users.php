<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Users Controller — Admin only (enforced by AdminFilter in routes)
 */
class Users extends BaseController
{
    private UserModel $model;

    public function __construct()
    {
        $this->model = new UserModel();
    }

    // ── GET /users ──────────────────────────────────────────────
    public function index(): string
    {
        return $this->render('users/index', [
            'pageTitle'   => lang('General.users'),
            'activeMenu'  => 'users',
            'breadcrumbs' => [lang('General.users') => null],
        ]);
    }

    // ── GET /users/data  (DataTable AJAX) ───────────────────────
    public function data(): ResponseInterface
    {
        $records = $this->model->forDataTable();
        $rows    = [];

        foreach ($records as $r) {
            $statusBadge = $r['is_active']
                ? '<span class="badge bg-success-subtle text-success">Active</span>'
                : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>';

            $roleBadge = $r['role'] === 'admin'
                ? '<span class="badge bg-primary-subtle text-primary">Admin</span>'
                : '<span class="badge bg-info-subtle text-info">User</span>';

            $rows[] = [
                'id'         => $r['id'],
                'name'       => esc($r['name']),
                'email'      => esc($r['email']),
                'role'       => $roleBadge,
                'is_active'  => $statusBadge,
                'last_login' => $r['last_login'] ? date('m/d/Y H:i', strtotime($r['last_login'])) : '—',
                'created_at' => date('m/d/Y', strtotime($r['created_at'])),
                'actions'    => $this->buildActions($r),
            ];
        }

        return $this->jsonResponse(['data' => $rows]);
    }

    // ── GET /users/{id}/edit  (modal data AJAX) ─────────────────
    public function edit(int $id): ResponseInterface
    {
        $user = $this->model->select('id, name, email, role, is_active')->find($id);

        if (! $user) {
            return $this->jsonError('User not found.', 404);
        }

        return $this->jsonResponse($user);
    }

    // ── POST /users  (create) ───────────────────────────────────
    public function store(): ResponseInterface
    {
        $rules = [
            'name'     => 'required|min_length[2]|max_length[100]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[8]',
            'role'     => 'required|in_list[admin,user]',
        ];

        if (! $this->validate($rules)) {
            return $this->jsonError(
                lang('General.error_generic'),
                422,
                $this->validator->getErrors()
            );
        }

        $data = [
            'name'      => $this->request->getPost('name'),
            'email'     => $this->request->getPost('email'),
            'password'  => $this->request->getPost('password'),
            'role'      => $this->request->getPost('role'),
            'is_active' => (int) $this->request->getPost('is_active'),
        ];

        $this->model->insert($data);

        return $this->jsonSuccess(lang('General.saved'));
    }

    // ── POST /users/{id}  (update) ──────────────────────────────
    public function update(int $id): ResponseInterface
    {
        $user = $this->model->find($id);
        if (! $user) {
            return $this->jsonError('User not found.', 404);
        }

        $rules = [
            'name'  => 'required|min_length[2]|max_length[100]',
            'email' => "required|valid_email|is_unique[users.email,id,{$id}]",
            'role'  => 'required|in_list[admin,user]',
        ];

        // Validate password only if provided
        $newPassword = $this->request->getPost('password');
        if (! empty($newPassword)) {
            $rules['password'] = 'min_length[8]';
        }

        if (! $this->validate($rules)) {
            return $this->jsonError(
                lang('General.error_generic'),
                422,
                $this->validator->getErrors()
            );
        }

        $data = [
            'name'      => $this->request->getPost('name'),
            'email'     => $this->request->getPost('email'),
            'role'      => $this->request->getPost('role'),
            'is_active' => (int) $this->request->getPost('is_active'),
        ];

        if (! empty($newPassword)) {
            $data['password'] = $newPassword; // model hashes it via callback
        }

        $this->model->update($id, $data);

        return $this->jsonSuccess(lang('General.saved'));
    }

    // ── DELETE /users/{id} ──────────────────────────────────────
    public function destroy(int $id): ResponseInterface
    {
        // Prevent deleting your own account
        if ($id === (int) session()->get('user_id')) {
            return $this->jsonError('You cannot delete your own account.', 403);
        }

        $user = $this->model->find($id);
        if (! $user) {
            return $this->jsonError('User not found.', 404);
        }

        $this->model->delete($id);

        return $this->jsonSuccess(lang('General.deleted'));
    }

    // ── Private ─────────────────────────────────────────────────
    private function buildActions(array $user): string
    {
        $editBtn = sprintf(
            '<button class="btn btn-sm btn-outline-primary" onclick="openEdit(%d)" title="Edit">
                <i class="bi bi-pencil-fill"></i>
            </button>',
            $user['id']
        );

        // Can't delete self
        $currentId = (int) session()->get('user_id');
        $deleteBtn = $user['id'] !== $currentId
            ? sprintf(
                '<button class="btn btn-sm btn-outline-danger" onclick="deleteUser(%d,\'%s\')" title="Delete">
                    <i class="bi bi-trash-fill"></i>
                </button>',
                $user['id'],
                esc($user['name'], 'js')
            )
            : '<button class="btn btn-sm btn-outline-secondary" disabled title="Cannot delete yourself"><i class="bi bi-trash-fill"></i></button>';

        return '<div class="d-flex gap-1">' . $editBtn . $deleteBtn . '</div>';
    }
}
