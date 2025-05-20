<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddKategoriToJenisPerangkat extends Migration
{
    public function up()
    {
        $fields = [
            'kategori' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
                'default'    => 'Lainnya',  // bisa disesuaikan
                'after'      => 'nama'      // posisi kolom setelah 'nama'
            ],
        ];
        $this->forge->addColumn('jenis_perangkat', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('jenis_perangkat', 'kategori');
    }
}
