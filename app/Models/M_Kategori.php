<?php

namespace App\Models;

use CodeIgniter\Model;

class M_Kategori extends Model
{
    protected $table = 'kategori';
    protected $primaryKey = 'id_kategori';

    protected $allowedFields = ['id_kategori', 'nama_kategori', 'unit_usaha', 'created_at', 'updated_at'];

    protected $useTimestamps = true;
    public function getSubKategori()
    {
        return $this->hasMany(M_SubKategori::class, 'id_kategori', 'id_kategori');
    }

    public function generateIdKategori()
    {
        $lastRecord = $this->select('id_kategori')
            ->like('id_kategori', 'KT_')
            ->orderBy('id_kategori', 'DESC')
            ->limit(1)
            ->first();

        if (!$lastRecord) {
            return 'KT_001';
        }

        $lastId = $lastRecord['id_kategori'];
        $num = (int) substr($lastId, 3);
        $num++;
        $newId = 'KT_' . str_pad($num, 3, '0', STR_PAD_LEFT);

        return $newId;
    }
}
