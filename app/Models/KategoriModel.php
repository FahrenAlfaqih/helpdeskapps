<?php

namespace App\Models;

use CodeIgniter\Model;

class KategoriModel extends Model
{
    protected $table = 'kategori';
    protected $primaryKey = 'id_kategori';

    protected $allowedFields = ['nama_kategori', 'unit_usaha', 'created_at', 'updated_at'];

    protected $useTimestamps = true;  // otomatis handle created_at dan updated_at

    // Relasi: satu kategori punya banyak sub kategori
    public function getSubKategori()
    {
        return $this->hasMany(SubKategoriModel::class, 'id_kategori', 'id_kategori');
    }
}
