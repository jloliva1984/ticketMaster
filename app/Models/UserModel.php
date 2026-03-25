<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * UserModel
 *
 * Handles CRUD for the `users` table.
 * Passwords are hashed automatically via model callbacks.
 */
class UserModel extends Model
{
    protected $table         = 'users';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = true;
    protected $useTimestamps  = true;

    protected $allowedFields = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
        'last_login',
    ];

    // ── Callbacks ────────────────────────────────────────────────
    protected $beforeInsert = ['hashPasswordCallback'];
    protected $beforeUpdate = ['hashPasswordCallback'];

    /**
     * Hash password before insert/update — only if 'password' is present
     * and not already a bcrypt hash.
     */
    protected function hashPasswordCallback(array $data): array
    {
        if (! isset($data['data']['password'])) {
            return $data;
        }

        $password = $data['data']['password'];

        // Skip if already hashed (starts with $2y$ = bcrypt)
        if (! str_starts_with($password, '$2y$')) {
            $data['data']['password'] = password_hash($password, PASSWORD_BCRYPT);
        }

        return $data;
    }

    // ── Validation rules ─────────────────────────────────────────
    protected $validationRules = [
        'name'  => 'required|min_length[2]|max_length[100]',
        'email' => 'required|valid_email|max_length[150]|is_unique[users.email,id,{id}]',
        'role'  => 'required|in_list[admin,user]',
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'This email address is already registered.',
        ],
    ];

    protected $skipValidation = false;

    // ── Custom finders ───────────────────────────────────────────

    /**
     * Find an active user by email (for login).
     */
    public function findActiveByEmail(string $email): ?array
    {
        return $this
            ->where('email', $email)
            ->where('is_active', 1)
            ->first();
    }

    /**
     * Verify a plain-text password against a stored hash.
     */
    public function verifyPassword(string $plain, string $hash): bool
    {
        return password_verify($plain, $hash);
    }

    /**
     * Update last_login timestamp — bypasses validation.
     */
    public function touchLastLogin(int $userId): void
    {
        $this->skipValidation(true)
             ->update($userId, ['last_login' => date('Y-m-d H:i:s')]);
        $this->skipValidation(false);
    }

    /**
     * Return all users for DataTable (excludes password).
     */
    public function forDataTable(): array
    {
        return $this->select('id, name, email, role, is_active, last_login, created_at')
                    ->findAll();
    }
}
