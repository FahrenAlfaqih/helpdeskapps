<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTickets extends Migration
{
public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'title' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'requestor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'assigned_unit' => [
                'type'       => 'ENUM',
                'constraint' => ['IT', 'GA'],
            ],
            'assigned_head_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'assigned_staff_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Open', 'Menunggu', 'In Progress', 'Sedang di Proses', 'Done'],
                'default'    => 'Open',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('requestor_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('assigned_head_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('assigned_staff_id', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('tickets');
    }

    public function down()
    {
        $this->forge->dropTable('tickets');
    }
}
