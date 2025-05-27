<?php

namespace App\Controllers;

use App\Models\SubKategoriModel;
use App\Models\KategoriModel;
use CodeIgniter\Controller;

class SubKategori extends Controller
{
    protected $subKategoriModel;
    protected $kategoriModel;
    protected $session;

    public function __construct()
    {
        $this->subKategoriModel = new SubKategoriModel();
        $this->kategoriModel = new KategoriModel();
        $this->session = session();
    }

    // Index list subkategori
    public function index()
    {
        $unitUsaha = $this->session->get('unit_usaha_id');

        $builder = $this->subKategoriModel->builder();
        $builder->select('sub_kategori.*, kategori.nama_kategori, kategori.unit_usaha');
        $builder->join('kategori', 'sub_kategori.id_kategori = kategori.id_kategori');
        $builder->where('kategori.unit_usaha', $unitUsaha);

        $data['subkategori'] = $builder->get()->getResultArray();

        return view('subkategori/index', $data);
    }

    public function create()
    {
        $unitUsaha = $this->session->get('unit_usaha_id');

        // Ambil kategori sesuai unit usaha
        $kategoriModel = new \App\Models\KategoriModel();
        $data['kategori'] = $kategoriModel->where('unit_usaha', $unitUsaha)->findAll();

        return view('subkategori/create', $data);
    }

    public function store()
    {
        $unitUsaha = $this->session->get('unit_usaha_id');
        $validation =  \Config\Services::validation();

        $rules = [
            'id_kategori' => 'required',
            'nama_subkategori' => 'required|min_length[3]|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors(),
            ]);
        }

        $this->subKategoriModel->save([
            'id_kategori' => $this->request->getPost('id_kategori'),
            'nama_subkategori' => $this->request->getPost('nama_subkategori'),
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Sub Kategori berhasil ditambahkan.'
        ]);
    }


    // Ambil data subkategori untuk modal edit (ajax)
    public function edit($id)
    {
        $subkategori = $this->subKategoriModel->find($id);
        if (!$subkategori) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'SubKategori tidak ditemukan']);
        }

        // Pastikan subkategori terkait unit usaha session user
        $kategori = $this->kategoriModel->find($subkategori['id_kategori']);
        if (!$kategori || $kategori['unit_usaha'] !== $this->session->get('unit_usaha_id')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $subkategori]);
    }

    // Update subkategori via ajax
    public function update($id)
    {
        $subkategori = $this->subKategoriModel->find($id);
        if (!$subkategori) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'SubKategori tidak ditemukan']);
        }

        $kategori = $this->kategoriModel->find($subkategori['id_kategori']);
        if (!$kategori || $kategori['unit_usaha'] !== $this->session->get('unit_usaha_id')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);
        }

        $validation =  \Config\Services::validation();
        $rules = [
            'nama_subkategori' => 'required|min_length[3]|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors(),
            ]);
        }

        $this->subKategoriModel->update($id, [
            'nama_subkategori' => $this->request->getPost('nama_subkategori'),
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'SubKategori berhasil diupdate.']);
    }

    // Delete subkategori via ajax
    public function delete($id)
    {
        $subkategori = $this->subKategoriModel->find($id);
        if (!$subkategori) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'SubKategori tidak ditemukan']);
        }

        // Ambil kategori terkait
        $kategori = (new \App\Models\KategoriModel())->find($subkategori['id_kategori']);
        if (!$kategori) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Kategori terkait tidak ditemukan']);
        }

        if ($kategori['unit_usaha'] !== $this->session->get('unit_usaha_id')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak']);
        }

        $this->subKategoriModel->delete($id);

        return $this->response->setJSON(['status' => 'success', 'message' => 'SubKategori berhasil dihapus.']);
    }
}
