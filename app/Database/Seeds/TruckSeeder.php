<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TruckSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $trucks = [
            ['no_camion' => 'T-001', 'nombre_chofer' => 'Carlos Méndez',   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['no_camion' => 'T-002', 'nombre_chofer' => 'Luis Fernández',  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['no_camion' => 'T-003', 'nombre_chofer' => 'Pedro González',  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['no_camion' => 'T-004', 'nombre_chofer' => 'Miguel Ramírez',  'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['no_camion' => 'T-005', 'nombre_chofer' => 'Roberto Castro',  'is_active' => 0, 'created_at' => $now, 'updated_at' => $now],
        ];

        if ($this->db->table('trucks')->countAllResults() === 0) {
            $this->db->table('trucks')->insertBatch($trucks);
            echo "  TruckSeeder: 5 trucks created.\n";
        } else {
            echo "  TruckSeeder: skipped.\n";
        }
    }
}
