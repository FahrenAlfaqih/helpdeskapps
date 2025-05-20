<?php

namespace App\Models;

use CodeIgniter\Model;


class JenisPerangkatModel extends Model
{
    protected $table = 'jenis_perangkat';
    protected $primaryKey = 'id';
    protected $allowedFields = ['nama', 'kategori'];
    protected $returnType = 'array';

    public function getAll()
    {
        return $this->orderBy('nama')->findAll();
    }

    public function getByKategori($kategori)
    {
        return $this->where('kategori', $kategori)
            ->orderBy('nama')
            ->findAll();
    }
}
