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

    protected $allowedFields    = [
        'nama_lengkap',
        'username',
        'password_hash',
        'role_id'
    ];

    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = 'updated_at';

    public function getUserWithRole(string $username = '')
    {
        $builder = $this->select('users.*, roles.nama_role')
            ->join('roles', 'roles.id_role = users.role_id')
            ->orderBy('users.role_id', 'ASC');

        if ($username !== '') {
            return $builder->where('username', $username)->first();
        }

        return $builder->findAll();
    }

    public function getWaliKelasInfo(int $userId)
    {
        return $this->db->table('kelas')
            ->select('id_kelas, nama_kelas')
            ->where('wali_kelas_id', $userId)
            ->get()
            ->getRowArray();
    }
}
