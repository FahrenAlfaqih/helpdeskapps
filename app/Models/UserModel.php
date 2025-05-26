<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'user';
    protected $primaryKey = 'user_id';
    protected $allowedFields = ['user_id', 'id_pegawai', 'nama', 'email', 'password', 'image', 'is_active', 'role_id', 'id_application'];
    protected $useTimestamps = true;
    protected $createdField  = 'date_created';
}
