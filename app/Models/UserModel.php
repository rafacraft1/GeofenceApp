<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id_user';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;

    // Membatasi kolom yang diizinkan untuk keamanan
    protected $allowedFields    = [
        'nama_lengkap',
        'username',
        'password_hash',
        'role_id'
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    /**
     * Mengambil data user beserta nama role-nya menggunakan JOIN
     */
    public function getUserWithRole(string $username)
    {
        return $this->select('users.*, roles.nama_role')
            ->join('roles', 'roles.id_role = users.role_id')
            ->where('username', $username)
            ->first();
    }
}
