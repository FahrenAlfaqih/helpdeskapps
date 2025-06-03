<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\UserModel;
use App\Models\RoleModel;

class Users extends Controller
{
    protected $userModel;
    protected $roleModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->roleModel = new RoleModel();
        helper('url');
        helper('form');
    }

    public function index()
    {
        $session = session();
        $roleId = $session->get('role_id');

        // Kirim data roles untuk dropdown pembuatan user
        // Sesuaikan role apa saja yang bisa dibuat user login
        $roles = $this->getAllowedRoles($roleId);

        return view('users/index', ['roles' => $roles]);
    }

    // Fungsi untuk membatasi role yang bisa dibuat berdasarkan role user login
    private function getAllowedRoles($roleId)
    {
        // Admin bisa buat semua selain Admin sendiri
        if ($roleId == 1) {
            return $this->roleModel->where('id !=', 1)->findAll();
        }

        // Kepala Unit IT bisa buat Pegawai IT
        if ($roleId == 2) {
            return $this->roleModel->where('name', 'Pegawai IT')->findAll();
        }

        // Kepala Unit GA bisa buat Pegawai GA
        if ($roleId == 3) {
            return $this->roleModel->where('name', 'Pegawai GA')->findAll();
        }

        return [];
    }

    public function listUsers()
    {
        $session = session();
        $roleId = $session->get('role_id');

        if ($roleId == 1) { // Admin
            $users = $this->userModel
                ->select('users.id, users.username, users.full_name, users.email, roles.name as role_name')
                ->join('roles', 'roles.id = users.role_id')
                ->where('users.role_id !=', 1)
                ->findAll();
        } elseif ($roleId == 2) {
            $users = $this->userModel
                ->select('users.id, users.username, users.full_name, roles.name as role_name')
                ->join('roles', 'roles.id = users.role_id')
                ->where('users.role_id', 4)  // Pegawai IT
                ->findAll();
        } elseif ($roleId == 3) {
            $users = $this->userModel
                ->select('users.id, users.username, users.full_name, roles.name as role_name')
                ->join('roles', 'roles.id = users.role_id')
                ->where('users.role_id', 5)  // Pegawai GA
                ->findAll();
        } else {
            $users = [];
        }

        return $this->response->setJSON($users);
    }

    public function create()
    {
        $session = session();
        $roleId = $session->get('role_id');

        $roles = $this->getAllowedRoles($roleId);
        return view('users/create', ['roles' => $roles]);
    }


    public function createUser()
    {
        $session = session();
        $roleId = $session->get('role_id');

        $data = $this->request->getJSON();

        // Validasi sederhana
        if (!$data->username || !$data->password || !$data->full_name || !$data->email  || !$data->role_id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak lengkap']);
        }

        // Cek role yang boleh dibuat user login
        $allowedRoles = array_column($this->getAllowedRoles($roleId), 'id');
        if (!in_array($data->role_id, $allowedRoles)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak berhak membuat user dengan role tersebut']);
        }

        // Cek username unik
        if ($this->userModel->where('username', $data->username)->first()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Username sudah dipakai']);
        }

        // Simpan user baru
        $newUser = [
            'username'  => $data->username,
            'password'  => password_hash($data->password, PASSWORD_DEFAULT),
            'full_name' => $data->full_name,
            'email' => $data->email,
            'role_id'   => $data->role_id,
        ];

        $this->userModel->insert($newUser);

        return $this->response->setJSON(['status' => 'success', 'message' => 'User berhasil dibuat']);
    }

    public function deleteUser()
    {
        $session = session();
        $roleId = $session->get('role_id');

        $data = $this->request->getJSON();

        if (!isset($data->id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'ID user tidak ditemukan']);
        }

        // Admin hanya boleh hapus user selain admin
        if ($roleId != 1) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Anda tidak punya hak hapus user']);
        }

        $this->userModel->delete($data->id);

        return $this->response->setJSON(['status' => 'success', 'message' => 'User berhasil dihapus']);
    }

    public function updateUser()
    {
        $data = $this->request->getJSON();

        if (!$data->id || !$data->full_name || !$data->role_id || !$data->email) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Data tidak lengkap']);
        }

        $user = $this->userModel->find($data->id);
        if (!$user) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User tidak ditemukan']);
        }

        $updateData = [
            'full_name' => $data->full_name,
            'role_id' => $data->role_id,
            'email' => $data->email,
        ];

        $this->userModel->update($data->id, $updateData);

        return $this->response->setJSON(['status' => 'success', 'message' => 'User berhasil diperbarui']);
    }
}
