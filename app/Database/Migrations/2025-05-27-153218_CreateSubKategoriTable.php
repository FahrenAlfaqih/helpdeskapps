<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSubKategoriTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id_subkategori' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'id_kategori' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => false,
                'comment' => 'Foreign key ke tabel kategori',
            ],
            'nama_subkategori' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
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
        $this->forge->addKey('id_subkategori', true);
        $this->forge->addForeignKey('id_kategori', 'kategori', 'id_kategori', 'CASCADE', 'CASCADE');
        $this->forge->createTable('sub_kategori');
    }

    public function down()
    {
        $this->forge->dropTable('sub_kategori');
    }
}
