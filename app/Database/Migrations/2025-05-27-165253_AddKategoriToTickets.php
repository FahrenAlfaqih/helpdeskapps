<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKategoriToTickets extends Migration
{
    public function up()
    {
        $fields = [
            'kategori_id' => [
                'type' => 'INT',
                'null' => true,
            ],
            'subkategori_id' => [
                'type' => 'INT',
                'null' => true,
            ],
        ];
        $this->forge->addColumn('tiket', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tiket', ['kategori_id', 'subkategori_id']);
    }
}
