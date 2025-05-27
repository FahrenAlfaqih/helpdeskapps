<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $table = 'tiket';
    protected $primaryKey = 'id_tiket';

    protected $allowedFields = [
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
        'prioritas',
        'status',
        'assigned_to',
        'komentar_penyelesaian',
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
        'prioritas' => 'in_list[High,Medium,Low]',
        'status' => 'in_list[Open,In Progress,Done,Closed]',
    ];
}
