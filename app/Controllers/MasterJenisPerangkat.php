<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\JenisPerangkatModel;

class MasterJenisPerangkat extends Controller
{
    protected $jenisPerangkatModel;

    public function __construct()
    {
        helper('session');
        $this->jenisPerangkatModel = new JenisPerangkatModel();
    }

    public function index()
    {
        return view('master/jenis_perangkat/index');
    }

    public function list()
    {
        $data = $this->jenisPerangkatModel->orderBy('nama')->findAll();
        return $this->response->setJSON($data);
    }

    public function create()
    {
        $nama = $this->request->getPost('nama');
        $kategori = $this->request->getPost('kategori');

        if (!$nama) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Nama wajib diisi']);
        }
        if (!$kategori) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Kategori wajib diisi']);
        }

        $this->jenisPerangkatModel->insert(['nama' => $nama, 'kategori' => $kategori]);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Jenis perangkat berhasil ditambahkan']);
    }

    public function update($id)
    {
        $nama = $this->request->getPost('nama');
        $kategori = $this->request->getPost('kategori');

        if (!$nama) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Nama wajib diisi']);
        }
        if (!$kategori) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Kategori wajib diisi']);
        }

        $jenis = $this->jenisPerangkatModel->find($id);
        if (!$jenis) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Jenis perangkat tidak ditemukan']);
        }

        $this->jenisPerangkatModel->update($id, ['nama' => $nama, 'kategori' => $kategori]);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Jenis perangkat berhasil diperbarui']);
    }


    public function delete($id)
    {
        $jenis = $this->jenisPerangkatModel->find($id);
        if (!$jenis) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Jenis perangkat tidak ditemukan']);
        }

        $this->jenisPerangkatModel->delete($id);
        return $this->response->setJSON(['status' => 'success', 'message' => 'Jenis perangkat berhasil dihapus']);
    }

    public function listByKategori($kategori)
    {
        $data = $this->jenisPerangkatModel->getByKategori($kategori);
        return $this->response->setJSON($data);
    }
}
