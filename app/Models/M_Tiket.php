<?php

namespace App\Models;

use CodeIgniter\Model;

class M_Tiket extends Model
{
    protected $table = 'tiket';
    protected $primaryKey = 'id_tiket';

    protected $allowedFields = [
        'id_tiket',
        'id_pegawai_requestor',
        'unit_level_requestor',
        'unit_bisnis_requestor',
        'unit_usaha_requestor',
        'unit_organisasi_requestor',
        'unit_kerja_requestor',
        'unit_kerja_sub_requestor',
        'unit_lokasi_requestor',
        'judul',
        'deskripsi',
        'gambar',
        'id_unit_tujuan',
        'kategori_id',
        'subkategori_id',
        'id_ruangan',
        'prioritas',
        'status',
        'assigned_to',
        'komentar_penyelesaian',
        'komentar_staff',
        'confirm_by_requestor',
        'rating_time',
        'rating_service',
        'created_at',
        'updated_at',
    ];


    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $validationRules = [
        'judul' => 'required|max_length[255]',
        'deskripsi' => 'required',
        'prioritas' => 'permit_empty|in_list[High,Medium,Low]',
        'status' => 'in_list[Open,In Progress,Done,Closed]',
    ];

    public function generateIdTiket()
    {
        $lastRecord = $this->select('id_tiket')
            ->like('id_tiket', 'TK_')
            ->orderBy('id_tiket', 'DESC')
            ->limit(1)
            ->first();

        if (!$lastRecord) {
            return 'TK_001';
        }

        $lastId = $lastRecord['id_tiket'];
        $num = (int) substr($lastId, 3);
        $num++;
        return 'TK_' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
}
