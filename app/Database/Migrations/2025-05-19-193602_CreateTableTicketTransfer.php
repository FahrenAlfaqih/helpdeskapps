<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableTicketTransfer extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'              => ['type' => 'INT', 'auto_increment' => true],
            'ticket_id'       => ['type' => 'INT', 'null' => false],
            'from_staff_id'   => ['type' => 'INT', 'null' => false],
            'to_staff_id'     => ['type' => 'INT', 'null' => false],
            'reason'          => ['type' => 'TEXT', 'null' => true],
            'status'          => ['type' => 'ENUM', 'constraint' => ['pending', 'accepted', 'rejected'], 'default' => 'pending'],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'default' => null,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('ticket_transfers');
    }

    public function down()
    {
        $this->forge->dropTable('ticket_transfers');
    }
}
