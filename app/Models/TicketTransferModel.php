<?php namespace App\Models;

use CodeIgniter\Model;

class TicketTransferModel extends Model
{
    protected $table = 'ticket_transfers';
    protected $primaryKey = 'id';
    protected $allowedFields = ['ticket_id', 'from_staff_id', 'to_staff_id', 'reason', 'status', 'created_at', 'updated_at'];
    protected $useTimestamps = true;
    protected $returnType = 'array';

    public function getPendingTransfersForStaff($staffId)
    {
        return $this->where('to_staff_id', $staffId)
                    ->where('status', 'pending')
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
