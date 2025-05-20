<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $password = password_hash('adminadmin', PASSWORD_DEFAULT);

        $data = [
            'username'  => 'admin',
            'password'  => $password,
            'full_name' => 'Administrator',
            'role_id'   => 1, 
        ];

        $this->db->table('users')->insert($data);
    }
}
