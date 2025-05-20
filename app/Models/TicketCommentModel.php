<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketCommentModel extends Model
{
    protected $table = 'comment';  // nama tabel
    protected $primaryKey = 'id';
    protected $allowedFields = ['ticket_id', 'user_id', 'comment', 'created_at'];
    public $timestamps = false;

    public function getByTicketId($ticketId)
    {
        return $this->where('ticket_id', $ticketId)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }
}
