<?php

namespace App\Models;

use CodeIgniter\Model;

class SubKategoriModel extends Model
{
    protected $table = 'sub_kategori';
    protected $primaryKey = 'id_subkategori';

    protected $allowedFields = ['id_kategori', 'nama_subkategori', 'created_at', 'updated_at'];

    protected $useTimestamps = true;

    // Relasi: sub kategori milik kategori
    public function getKategori()
    {
        return $this->belongsTo(KategoriModel::class, 'id_kategori', 'id_kategori');
    }
}
