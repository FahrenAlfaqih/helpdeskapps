<?php

namespace App\Controllers;

use App\Models\RoleModel;
use CodeIgniter\Controller;
use App\Models\UserModel;

class Auth extends Controller
{
    public function login()
    {
        $session = session();
        $userModel = new UserModel();
        $roleModel = new RoleModel(); 

        $data = $this->request->getJSON();

        if (!$data || !isset($data->username) || !isset($data->password)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Username dan password wajib diisi']);
        }

        $user = $userModel->getUserByUsername($data->username);

        if ($user && password_verify($data->password, $user['password'])) {
            // Cari nama role berdasarkan role_id
            $roleName = 'Role tidak diketahui';
            if ($user['role_id']) {
                $role = $roleModel->find($user['role_id']);
                $roleName = $role['name'] ?? $roleName;
            }

            // Set session lengkap dengan role_name
            $session->set([
                'user_id'   => $user['id'],
                'username'  => $user['username'],
                'full_name' => $user['full_name'],
                'role_id'   => $user['role_id'],
                'role_name' => $roleName,
                'logged_in' => true
            ]);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Login berhasil']);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Username atau password salah']);
        }
    }


    public function logout()
    {
        session()->destroy();
        return redirect('/');
    }
}
