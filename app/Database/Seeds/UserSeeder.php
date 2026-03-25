<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

/**
 * UserSeeder
 *
 * Creates default admin + demo user accounts.
 *
 * Run with:
 *   php spark db:seed UserSeeder
 *
 * CHANGE PASSWORDS BEFORE PRODUCTION DEPLOY.
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $users = [
            [
                'name'       => 'Administrador',
                'email'      => 'admin@ticketmaster.lt',
                'password'   => password_hash('Admin@1234', PASSWORD_BCRYPT),
                'role'       => 'admin',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name'       => 'Usuario Demo',
                'email'      => 'user@ticketmaster.lt',
                'password'   => password_hash('User@1234', PASSWORD_BCRYPT),
                'role'       => 'user',
                'is_active'  => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        // Only insert if table is empty (idempotent)
        $existing = $this->db->table('users')->countAllResults();
        if ($existing === 0) {
            $this->db->table('users')->insertBatch($users);
            echo "  UserSeeder: 2 users created.\n";
        } else {
            echo "  UserSeeder: skipped (users table already has data).\n";
        }
    }
}
