<?php
namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Kategori extends BaseController
{
    use ResponseTrait;

    public function penanggungJawab($id)
    {
        $db = \Config\Database::connect();
        $kategori = $db->table('kategori')
            ->select('penanggung_jawab')
            ->where('id_kategori', $id)
            ->get()->getRow();

        if (!$kategori) {
            return $this->failNotFound('Kategori tidak ditemukan');
        }

        $penanggungJawab = json_decode($kategori->penanggung_jawab ?? '[]');
        return $this->respond(['penanggung_jawab' => $penanggungJawab]);
    }
}
