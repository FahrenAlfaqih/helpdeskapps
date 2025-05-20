<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRuangandanJenisPerangkat extends Migration
{
    public function up()
    {
        // Tabel ruangan
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'nama' => ['type' => 'VARCHAR', 'constraint' => 100, 'unique' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('ruangan');

        // Tabel jenis_perangkat
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'auto_increment' => true],
            'nama' => ['type' => 'VARCHAR', 'constraint' => 100, 'unique' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('jenis_perangkat');

        // Update tabel tickets
        $fields = [
            'ruangan_id' => ['type' => 'INT', 'null' => true],
            'jenis_perangkat_id' => ['type' => 'INT', 'null' => true],
            'kategori' => ['type' => 'VARCHAR', 'constraint' => 50, 'null' => true],
        ];
        $this->forge->addColumn('tickets', $fields);

        // Add foreign keys (jika DB support)
        $this->db->query('ALTER TABLE tickets ADD CONSTRAINT fk_tickets_ruangan FOREIGN KEY (ruangan_id) REFERENCES ruangan(id)');
        $this->db->query('ALTER TABLE tickets ADD CONSTRAINT fk_tickets_jenis_perangkat FOREIGN KEY (jenis_perangkat_id) REFERENCES jenis_perangkat(id)');
    }

    public function down()
    {
        $this->forge->dropForeignKey('tickets', 'fk_tickets_ruangan');
        $this->forge->dropForeignKey('tickets', 'fk_tickets_jenis_perangkat');

        $this->forge->dropColumn('tickets', ['ruangan_id', 'jenis_perangkat_id', 'kategori']);

        $this->forge->dropTable('ruangan');
        $this->forge->dropTable('jenis_perangkat');
    }
}
