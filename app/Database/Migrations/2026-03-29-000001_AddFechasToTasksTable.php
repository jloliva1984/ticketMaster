<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddFechasToTasksTable extends Migration
{
    public function up(): void
    {
        $fields = [
            'fecha_inicio' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'nombre_chofer',
            ],
            'fecha_fin' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'fecha_inicio',
            ],
        ];
        $this->forge->addColumn('tasks', $fields);
        $this->forge->dropColumn('tasks', 'periodo');
    }

    public function down(): void
    {
        $this->forge->dropColumn('tasks', 'fecha_inicio');
        $this->forge->dropColumn('tasks', 'fecha_fin');
        $this->forge->addColumn('tasks', [
            'periodo' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'after'      => 'nombre_chofer',
            ],
        ]);
    }
}
