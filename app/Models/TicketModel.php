<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $table      = 'tickets';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'title',
        'description',
        'requestor_id',
        'assigned_unit',
        'assigned_head_id',
        'assigned_staff_id',
        'status',
        'kategori',
        'ruangan_id',
        'jenis_perangkat_id',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $returnType = 'array';

    // Helper join string buat reuse
    protected function baseSelect()
    {
        return $this->select('tickets.*,
                          req.full_name as requestor_name,
                          head.full_name as head_name,
                          staff.full_name as staff_name,
                          ruangan.nama as ruangan_name,
                          jenis_perangkat.nama as jenis_perangkat_name,
                          tickets.kategori');  
    }


    public function getTicketsByUnit($unit)
    {
        return $this->baseSelect()
            ->join('users as req', 'req.id = tickets.requestor_id', 'left')
            ->join('users as head', 'head.id = tickets.assigned_head_id', 'left')
            ->join('users as staff', 'staff.id = tickets.assigned_staff_id', 'left')
            ->join('ruangan', 'ruangan.id = tickets.ruangan_id', 'left')
            ->join('jenis_perangkat', 'jenis_perangkat.id = tickets.jenis_perangkat_id', 'left')
            ->where('tickets.assigned_unit', $unit)
            ->orderBy('tickets.created_at', 'DESC')
            ->findAll();
    }

    public function getTicketsByStaff($staffId)
    {
        return $this->baseSelect()
            ->join('users as req', 'req.id = tickets.requestor_id', 'left')
            ->join('users as head', 'head.id = tickets.assigned_head_id', 'left')
            ->join('users as staff', 'staff.id = tickets.assigned_staff_id', 'left')
            ->join('ruangan', 'ruangan.id = tickets.ruangan_id', 'left')
            ->join('jenis_perangkat', 'jenis_perangkat.id = tickets.jenis_perangkat_id', 'left')
            ->where('tickets.assigned_staff_id', $staffId)
            ->orderBy('tickets.created_at', 'DESC')
            ->findAll();
    }

    public function getTicketsByRequestor($requestorId)
    {
        return $this->baseSelect()
            ->join('users as req', 'req.id = tickets.requestor_id', 'left')
            ->join('users as head', 'head.id = tickets.assigned_head_id', 'left')
            ->join('users as staff', 'staff.id = tickets.assigned_staff_id', 'left')
            ->join('ruangan', 'ruangan.id = tickets.ruangan_id', 'left')
            ->join('jenis_perangkat', 'jenis_perangkat.id = tickets.jenis_perangkat_id', 'left')
            ->where('tickets.requestor_id', $requestorId)
            ->orderBy('tickets.created_at', 'DESC')
            ->findAll();
    }

    public function getTicketDetailById($id)
    {
        return $this->select('tickets.*, 
                          req.full_name as requestor_name,
                          staff.full_name as staff_name,
                          ruangan.nama as ruangan_nama,
                          jenis_perangkat.nama as jenis_perangkat_nama,
                          tickets.kategori')
            ->join('users as req', 'req.id = tickets.requestor_id', 'left')
            ->join('users as staff', 'staff.id = tickets.assigned_staff_id', 'left')
            ->join('ruangan', 'ruangan.id = tickets.ruangan_id', 'left')
            ->join('jenis_perangkat', 'jenis_perangkat.id = tickets.jenis_perangkat_id', 'left')
            ->where('tickets.id', $id)
            ->first();
    }
}
