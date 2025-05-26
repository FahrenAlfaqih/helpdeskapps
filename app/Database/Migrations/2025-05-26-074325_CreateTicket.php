<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTicket extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_tiket' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_pegawai_requestor' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'unit_level_requestor' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'unit_bisnis_requestor' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'unit_usaha_requestor' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'unit_organisasi_requestor' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'unit_kerja_requestor' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'unit_kerja_sub_requestor' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'unit_lokasi_requestor' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'judul' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => false,
            ],
            'deskripsi' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'gambar' => [
                'type' => 'VARCHAR',
                'constraint' => '255',
                'null' => true,
            ],
            'id_unit_tujuan' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
            ],
            'prioritas' => [
                'type' => 'ENUM',
                'constraint' => ['High', 'Medium', 'Low'],
                'default' => 'Medium',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['Open', 'In Progress', 'Done', 'Closed'],
                'default' => 'Open',
            ],
            'assigned_to' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => true,
            ],
            'komentar_penyelesaian' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'confirm_by_requestor' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
            ],
            'rating_time' => [
                'type' => 'INT',
                'null' => true,
            ],
            'rating_service' => [
                'type' => 'INT',
                'null' => true,
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

        $this->forge->addKey('id_tiket', true);
        $this->forge->createTable('tiket');
    }

    public function down()
    {
        $this->forge->dropTable('tiket');
    }
}
