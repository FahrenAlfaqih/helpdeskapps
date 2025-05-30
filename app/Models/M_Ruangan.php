<?php

namespace App\Models;

use CodeIgniter\Model;

class M_Ruangan extends Model
{
    protected $table = 'ruangan';
    protected $primaryKey = 'id_ruangan';
    protected $allowedFields = ['id_ruangan', 'nm_ruangan', 'created_at', 'updated_at'];
    protected $useTimestamps = true;

    public function generateIdRuangan()
    {
        $last = $this->select('id_ruangan')
                     ->like('id_ruangan', 'RGN_')
                     ->orderBy('id_ruangan', 'DESC')
                     ->first();

        if (!$last) {
            return 'RGN_001';
        }

        $num = (int) substr($last['id_ruangan'], 4);
        $num++;
        return 'RGN_' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
}
