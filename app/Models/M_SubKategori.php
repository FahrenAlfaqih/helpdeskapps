<?php

namespace App\Models;

use CodeIgniter\Model;

class M_SubKategori extends Model
{
    protected $table = 'sub_kategori';
    protected $primaryKey = 'id_subkategori';

    protected $allowedFields = ['id_subkategori', 'id_kategori', 'nama_subkategori', 'created_at', 'updated_at'];

    protected $useTimestamps = true;

    // Relasi: sub kategori milik kategori
    public function getKategori()
    {
        return $this->belongsTo(M_Kategori::class, 'id_kategori', 'id_kategori');
    }

    public function generateIdSubKategori()
    {
        $lastRecord = $this->select('id_subkategori')
            ->like('id_subkategori', 'SKT_')
            ->orderBy('id_subkategori', 'DESC')
            ->limit(1)
            ->first();

        if (!$lastRecord) {
            return 'SKT_001';
        }

        $lastId = $lastRecord['id_subkategori'];
        $num = (int) substr($lastId, 4);
        $num++;
        $newId = 'SKT_' . str_pad($num, 3, '0', STR_PAD_LEFT);

        return $newId;
    }
}
