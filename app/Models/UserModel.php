<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'id';

    protected $allowedFields = ['username', 'password', 'full_name', 'role_id', 'email'];

    protected $returnType = 'array';

    public function getUserByUsername($username)
    {
        return $this->where('username', $username)->first();
    }
}
