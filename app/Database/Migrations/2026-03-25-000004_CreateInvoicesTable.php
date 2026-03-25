<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInvoicesTable extends Migration
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
            'no_factura' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'fecha' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'cantera_id' => [
                'type'     => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null'     => false,
            ],
            'due_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'fecha_recibida' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'pdf_file' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
            ],
            'monto_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '12,2',
                'default'    => '0.00',
                'null'       => false,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['pending', 'received', 'paid'],
                'default'    => 'pending',
                'null'       => false,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey('no_factura');
        $this->forge->addKey('cantera_id');
        $this->forge->addKey('fecha');
        $this->forge->addKey('status');
        $this->forge->addForeignKey('cantera_id', 'quarries', 'id', 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('invoices');
    }

    public function down(): void
    {
        $this->forge->dropTable('invoices', true);
    }
}
