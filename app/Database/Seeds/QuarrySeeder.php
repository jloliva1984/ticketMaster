<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class QuarrySeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $quarries = [
            ['nombre_cantera' => 'Cantera Norte', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['nombre_cantera' => 'Cantera Sur',   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['nombre_cantera' => 'Cantera Este',  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['nombre_cantera' => 'Piedra Azul',   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['nombre_cantera' => 'Río Grande',    'is_active' => 0, 'created_at' => $now, 'updated_at' => $now],
        ];

        if ($this->db->table('quarries')->countAllResults() === 0) {
            $this->db->table('quarries')->insertBatch($quarries);
            echo "  QuarrySeeder: 5 quarries created.\n";
        } else {
            echo "  QuarrySeeder: skipped.\n";
        }
    }
}
