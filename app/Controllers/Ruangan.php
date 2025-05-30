<?php

namespace App\Controllers;

use App\Models\M_Ruangan;
use CodeIgniter\Controller;

class Ruangan extends Controller
{
    protected $ruanganModel;
    protected $session;

    public function __construct()
    {
        $this->ruanganModel = new M_Ruangan();
        $this->session = session();
    }

    public function index()
    {
        $data['ruangan'] = $this->ruanganModel->findAll();
        return view('ruangan/index', $data);
    }

    public function create()
    {
        return view('ruangan/create');
    }

    public function store()
    {
        $validation =  \Config\Services::validation();

        $rules = [
            'nm_ruangan' => 'required|min_length[3]|max_length[100]',
        ];

        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors()
            ])->setStatusCode(422);
        }
        $data = [
            'id_ruangan' => $this->ruanganModel->generateIdRuangan(),
            'nm_ruangan' => $this->request->getPost('nm_ruangan'),
        ];

        $this->ruanganModel->insert($data);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Ruangan berhasil dibuat'
        ])->setStatusCode(200);
    }

    public function edit($id = null)
    {
        $ruangan = $this->ruanganModel->find($id);
        if (!$ruangan) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Ruangan tidak ditemukan'])->setStatusCode(404);
        }

        return $this->response->setJSON($ruangan);
    }

    public function update($id = null)
    {
        $ruangan = $this->ruanganModel->find($id);
        if (!$ruangan) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Ruangan tidak ditemukan'])->setStatusCode(404);
        }

        $validation = \Config\Services::validation();
        $rules = ['nm_ruangan' => 'required|min_length[3]|max_length[100]'];
        if (!$this->validate($rules)) {
            return $this->response->setJSON([
                'status' => 'error',
                'errors' => $validation->getErrors(),
            ])->setStatusCode(422);
        }

        $this->ruanganModel->update($id, [
            'nm_ruangan' => $this->request->getPost('nm_ruangan'),
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Ruangan berhasil diupdate.']);
    }

    public function delete($id)
    {
        $ruangan = $this->ruanganModel->find($id);
        if (!$ruangan) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Ruangan tidak ditemukan');
        }

        $this->ruanganModel->delete($id);

        return redirect()->to('master/ruangan')->with('success', 'Ruangan berhasil dihapus.');
    }
}
