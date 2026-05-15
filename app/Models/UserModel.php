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
     * Mengambil data user beserta nama role-nya menggunakan JOIN.
     * Jika $username diisi, kembalikan 1 baris data spesifik.
     * Jika $username kosong (''), kembalikan seluruh data user.
     */
    public function getUserWithRole(string $username = '')
    {
        $builder = $this->select('users.*, roles.nama_role')
            ->join('roles', 'roles.id_role = users.role_id')
            ->orderBy('users.role_id', 'ASC');

        if ($username !== '') {
            // Mode pencarian spesifik untuk Login/Auth
            return $builder->where('username', $username)->first();
        }

        // Mode pengambilan massal untuk halaman Manajemen User
        return $builder->findAll();
    }
}
