<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class AuthApi extends ResourceController
{
    protected $format = 'json';

    public function login()
    {
        $nis = $this->request->getPost('nis');
        $device_id = $this->request->getPost('device_id');

        if (!$nis || !$device_id) return $this->failValidationErrors('NIS dan Device ID wajib diisi.');

        $db = \Config\Database::connect();
        $siswa = $db->table('siswa')->where('nis', $nis)->get()->getRow();

        if (!$siswa) return $this->failNotFound('Siswa tidak ditemukan.');
        if ($siswa->is_blocked == 1) return $this->failForbidden('Akun terblokir.');

        // Device Binding Logic
        if (empty($siswa->device_id)) {
            $db->table('siswa')->where('id', $siswa->id)->update(['device_id' => $device_id]);
        } elseif ($siswa->device_id !== $device_id) {
            return $this->failUnauthorized('Perangkat tidak dikenali. Silakan hubungi Admin untuk reset perangkat.');
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => ['siswa_id' => $siswa->id, 'nama' => $siswa->nama_lengkap, 'token' => $device_id]
        ]);
    }
}
