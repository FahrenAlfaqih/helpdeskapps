<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $password = password_hash('12345678', PASSWORD_BCRYPT);

        $data = [
            [
                'user_id' => 'user01',
                'id_pegawai' => 'PG_1407',
                'id_vendor' => 0,
                'nama' => 'Admin Sistem',
                'email' => 'admin@example.com',
                'password' => $password,
                'image' => 'default.jpg',
                'is_active' => 1,
                'role_id' => 'ADMIN',
                'id_application' => 'APP1',
                'date_created' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 'user02',
                'id_pegawai' => 'PG_1408',
                'id_vendor' => 0,
                'nama' => 'Staff IT Karimun',
                'email' => 'it.karimun@example.com',
                'password' => $password,
                'image' => 'default.jpg',
                'is_active' => 1,
                'role_id' => 'IT_KARIMUN',
                'id_application' => 'APP1',
                'date_created' => date('Y-m-d H:i:s'),
            ],
            [
                'user_id' => 'user03',
                'id_pegawai' => 'PG_1409',
                'id_vendor' => 0,
                'nama' => 'Requestor RSBT',
                'email' => 'user.rsbt@example.com',
                'password' => $password,
                'image' => 'default.jpg',
                'is_active' => 1,
                'role_id' => 'REQUESTOR',
                'id_application' => 'APP1',
                'date_created' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('user')->insertBatch($data);
    }
}
