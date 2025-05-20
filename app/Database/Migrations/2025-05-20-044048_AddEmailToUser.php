<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEmailToUser extends Migration
{
    public function up()
    {
        $fields = [
            'email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
                'after' => 'full_name' 
            ]
        ];

        $this->forge->addColumn('users', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('users', 'email');
    }
}
