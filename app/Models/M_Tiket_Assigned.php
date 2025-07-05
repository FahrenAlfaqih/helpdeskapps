<?php

namespace App\Models;

use CodeIgniter\Model;

class M_Tiket_Assigned extends Model
{
    protected $table      = 'ticket_assignees';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'id_tiket',
        'assigned_to',
        'sequence',
        'assigned_at',
        'finished_at',
        'komentar_penyelesaian',
        'komentar_staff',
        'rating_time',
        'rating_service',
    ];
}
