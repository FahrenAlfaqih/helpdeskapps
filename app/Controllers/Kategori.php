<?php

namespace App\Controllers;

use App\Models\M_Kategori;

use CodeIgniter\Controller;

class Kategori extends Controller
{
    protected $kategoriModel;
    protected $session;

    public function __construct()
    {
        $this->kategoriModel = new M_Kategori();
        $this->session = session();
    }

    public function index()
    {
        $unitUsaha = $this->session->get('unit_usaha_id');
        $kategori = $this->kategoriModel->findAll();

        $db = \Config\Database::connect();
        $unitKerjaSubList = $db->table('unit_kerja_sub')
            ->select('id_unit_kerja_sub, nm_unit_kerja_sub')
            ->get()->getResultArray();

        // Buat mapping ID → Nama
        $mapUnitKerjaSub = [];
        foreach ($unitKerjaSubList as $uks) {
            $mapUnitKerjaSub[$uks['id_unit_kerja_sub']] = $uks['nm_unit_kerja_sub'];
        }

        $data['kategori'] = $kategori;
        $data['mapUnitKerjaSub'] = $mapUnitKerjaSub;

        return view('kategori/index', $data);
    }


    public function create()
    {
        return view('kategori/create');
    }

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
        $penanggungJawab = $this->request->getPost('penanggung_jawab');
        $data = [
            'id_kategori' => $this->kategoriModel->generateIdKategori(),
            'nama_kategori' => $this->request->getPost('nama_kategori'),
            'unit_usaha' => $unitUsaha,
            'penanggung_jawab' => json_encode($penanggungJawab),
        ];

        $this->kategoriModel->insert($data);

        // $this->kategoriModel->save([
        //     'nama_kategori' => $this->request->getPost('nama_kategori'),
        //     'unit_usaha' => $unitUsaha,
        // ]);
        return redirect()->to('master/kategori')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit($id = null)
    {
        $kategori = $this->kategoriModel->find($id);
        if (!$kategori) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Kategori tidak ditemukan'])->setStatusCode(404);
        }
        if ($kategori['unit_usaha'] !== $this->session->get('unit_usaha_id')) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Akses ditolak'])->setStatusCode(403);
        }

        return $this->response->setJSON([
            'id_kategori' => $kategori['id_kategori'],
            'nama_kategori' => $kategori['nama_kategori'],
            'penanggung_jawab' => $kategori['penanggung_jawab'],
        ]);
    }


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
            'penanggung_jawab' => json_encode($this->request->getPost('penanggung_jawab') ?? []),
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Kategori berhasil diupdate.']);
    }



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
