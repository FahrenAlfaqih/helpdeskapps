<?php

namespace App\Controllers;

use App\Models\KategoriModel;
use CodeIgniter\Controller;

class Kategori extends Controller
{
    protected $kategoriModel;
    protected $session;

    public function __construct()
    {
        $this->kategoriModel = new KategoriModel();
        $this->session = session();
    }

    // List semua kategori yang sesuai unit usaha session
    public function index()
    {
        $unitUsaha = $this->session->get('unit_usaha_id');
        

        $data['kategori'] = $this->kategoriModel
            ->where('unit_usaha', $unitUsaha)
            ->findAll();

        return view('kategori/index', $data);
    }

    // Tampilkan form tambah kategori
    public function create()
    {
        return view('kategori/create');
    }

    // Simpan kategori baru
    public function store()
    {
        $unitUsaha = $this->session->get('unit_usaha_id');

        $validation =  \Config\Services::validation();

        $rules = [
            'nama_kategori' => 'required|min_length[3]|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $this->kategoriModel->save([
            'nama_kategori' => $this->request->getPost('nama_kategori'),
            'unit_usaha' => $unitUsaha,
        ]);

        return redirect()->to('master/kategori')->with('success', 'Kategori berhasil ditambahkan.');
    }

    // Form edit kategori
    public function edit($id = null)
    {
        $kategori = $this->kategoriModel->find($id);
        if (!$kategori) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Kategori tidak ditemukan'])->setStatusCode(404);
        }
        if ($kategori['unit_usaha'] !== $this->session->get('unit_usaha_id')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak'])->setStatusCode(403);
        }

        return $this->response->setJSON($kategori);
    }

    // Update kategori
    public function update($id = null)
    {
        $kategori = $this->kategoriModel->find($id);
        if (!$kategori) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Kategori tidak ditemukan'])->setStatusCode(404);
        }
        if ($kategori['unit_usaha'] !== $this->session->get('unit_usaha_id')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak'])->setStatusCode(403);
        }

        $validation = \Config\Services::validation();
        $rules = ['nama_kategori' => 'required|min_length[3]|max_length[100]'];
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors(),
            ])->setStatusCode(422);
        }

        $this->kategoriModel->update($id, [
            'nama_kategori' => $this->request->getPost('nama_kategori'),
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Kategori berhasil diupdate.']);
    }


    // Hapus kategori
    public function delete($id)
    {
        $kategori = $this->kategoriModel->find($id);
        if (!$kategori) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Kategori tidak ditemukan');
        }
        if ($kategori['unit_usaha'] !== $this->session->get('unit_usaha_id')) {
            return redirect()->to('/kategori')->with('error', 'Akses ditolak');
        }

        $this->kategoriModel->delete($id);

        return redirect()->to('master/kategori')->with('success', 'Kategori berhasil dihapus.');
    }
}
