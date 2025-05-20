<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRolesAndUsers extends Migration
{
    public function up()
    {
        // Tabel roles
        $this->forge->addField([
            'id'   => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'name' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'unique'     => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('roles');

        // Insert roles default
        $this->db->table('roles')->insertBatch([
            ['name' => 'Admin'],
            ['name' => 'Kepala Unit IT'],
            ['name' => 'Kepala Unit GA'],
            ['name' => 'Pegawai IT'],
            ['name' => 'Pegawai GA'],
            ['name' => 'Requestor'],
        ]);

        // Tabel users
        $this->forge->addField([
            'id'        => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'username'  => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'unique'     => true,
            ],
            'password'  => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
            ],
            'full_name' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'role_id'   => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
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
        $this->forge->addForeignKey('role_id', 'roles', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('users');
    }

    public function down()
    {
        $this->forge->dropTable('users');
        $this->forge->dropTable('roles');
    }
}
