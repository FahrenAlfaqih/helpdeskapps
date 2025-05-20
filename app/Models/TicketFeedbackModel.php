<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketFeedbackModel extends Model
{
    protected $table = 'ticket_feedback';  
    protected $primaryKey = 'id';
    protected $allowedFields = ['ticket_id', 'requestor_id', 'rating', 'suggestion', 'created_at'];
    public $timestamps = false; // kamu simpan manual created_at

    // Kalau mau, bisa tambahin fungsi helper untuk ambil feedback berdasarkan ticket_id
    public function getByTicketId($ticketId)
    {
        return $this->where('ticket_id', $ticketId)->first();
    }
}
