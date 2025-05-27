<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;
use Config\Database;

class Auth extends Controller
{
    public function login()
    {
        $input = $this->request->getJSON(true);
        $email = $input['email'] ?? null;
        $password = $input['password'] ?? null;
        $session = session();

        $model = new UserModel();
        $user = $model->where('email', $email)->first();

        if (!$user) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Email tidak ditemukan']);
        }
        if ($user['is_active'] != 1) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User belum aktif']);
        }
        if (!password_verify($password, $user['password'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Password salah']);
        }


        $db = Database::connect();
        $builder = $db->table('pegawai_penempatan as pp');
        $builder->select('uu.id_unit_usaha, uu.nm_unit_usaha, uk.nm_unit_kerja, ul.id_unit_level, ul.nm_unit_level');
        $builder->join('unit_usaha uu', 'pp.id_unit_usaha = uu.id_unit_usaha', 'left');
        $builder->join('unit_kerja uk', 'pp.id_unit_kerja = uk.id_unit_kerja', 'left');
        $builder->join('unit_level ul', 'pp.id_unit_level = ul.id_unit_level', 'left');  // join tabel unit_level
        $builder->where('pp.id_pegawai', $user['id_pegawai']);
        $penempatan = $builder->get()->getRowArray();


        $session->set([
            'user_id' => $user['user_id'],
            'id_pegawai' => $user['id_pegawai'],
            'nama' => $user['nama'],
            'email' => $user['email'],
            'role_id' => $user['role_id'],
            'unit_usaha_id' => $penempatan['id_unit_usaha'] ?? '',
            'unit_usaha' => $penempatan['nm_unit_usaha'] ?? '',
            'unit_kerja' => $penempatan['nm_unit_kerja'] ?? '',
            'unit_level_id' => $penempatan['id_unit_level'] ?? '',
            'unit_level_name' => $penempatan['nm_unit_level'] ?? '',
            'logged_in' => true,
        ]);


        return $this->response->setJSON(['status' => 'success', 'message' => 'Login berhasil']);
    }


    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
}
