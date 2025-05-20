<?php namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\RuanganModel;

class MasterRuangan extends Controller
{
    protected $ruanganModel;

    public function __construct()
    {
        helper('session');
        $this->ruanganModel = new RuanganModel();
    }

    public function index()
    {
        return view('master/ruangan/index');
    }

    public function list()
    {
        $data = $this->ruanganModel->orderBy('nama')->findAll();
        return $this->response->setJSON($data);
    }

    public function create()
    {
        $nama = $this->request->getPost('nama');
        if(!$nama){
            return $this->response->setJSON(['status'=>'error','message'=>'Nama wajib diisi']);
        }

        $this->ruanganModel->insert(['nama' => $nama]);
        return $this->response->setJSON(['status'=>'success','message'=>'Ruangan berhasil ditambahkan']);
    }

    public function update($id)
    {
        $nama = $this->request->getPost('nama');
        if(!$nama){
            return $this->response->setJSON(['status'=>'error','message'=>'Nama wajib diisi']);
        }

        $ruangan = $this->ruanganModel->find($id);
        if(!$ruangan){
            return $this->response->setJSON(['status'=>'error','message'=>'Ruangan tidak ditemukan']);
        }

        $this->ruanganModel->update($id, ['nama' => $nama]);
        return $this->response->setJSON(['status'=>'success','message'=>'Ruangan berhasil diperbarui']);
    }

    public function delete($id)
    {
        $ruangan = $this->ruanganModel->find($id);
        if(!$ruangan){
            return $this->response->setJSON(['status'=>'error','message'=>'Ruangan tidak ditemukan']);
        }

        $this->ruanganModel->delete($id);
        return $this->response->setJSON(['status'=>'success','message'=>'Ruangan berhasil dihapus']);
    }
}
