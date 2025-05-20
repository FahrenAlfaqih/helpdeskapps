<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeadlineToTickets extends Migration
{
    public function up()
    {
        $fields = [
            'deadline' => [
                'type' => 'DATE',
                'null' => true,
                'after' => 'status'
            ],
            'resolution_comment' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'status'
            ],
        ];

        $this->forge->addColumn('tickets', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('tickets', ['deadline', 'resolution_comment']);
    }
}
