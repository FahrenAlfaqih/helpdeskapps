<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSelesaidanTidakSelesaitoTicket extends Migration
{
    public function up()
    {
        $this->forge->modifyColumn('tickets', [
            'status' => [
                'name' => 'status',
                'type' => 'ENUM',
                'constraint' => ['Open', 'Menunggu', 'In Progress', 'Sedang di Proses', 'Done', 'Transfer', 'Selesai', 'Belum Selesai'],
                'default' => 'Menunggu',
                'null' => false,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->modifyColumn('tickets', [
            'status' => [
                'name' => 'status',
                'type' => 'ENUM',
                'constraint' => ['Open', 'Menunggu', 'In Progress', 'Sedang di Proses', 'Done', 'Transfer'],
                'default' => 'Menunggu',
                'null' => false,
            ],
        ]);
    }
}
