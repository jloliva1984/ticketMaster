<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTaskTicketsTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'task_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'no_ticket' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'fecha' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'tipo_trabajo' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'cantera_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'direccion' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'rate' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => '0.00',
                'null'       => false,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('task_id');
        $this->forge->addKey('no_ticket');
        $this->forge->addKey('cantera_id');
        $this->forge->addForeignKey('task_id',   'tasks',    'id', 'CASCADE',  'CASCADE');
        $this->forge->addForeignKey('cantera_id', 'quarries', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('task_tickets');
    }

    public function down(): void
    {
        $this->forge->dropTable('task_tickets', true);
    }
}
