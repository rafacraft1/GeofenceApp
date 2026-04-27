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

        if (!$nis || !$device_id) {
            return $this->failValidationErrors('NIS dan Device ID wajib diisi.');
        }

        $db = \Config\Database::connect();
        $siswa = $db->table('siswa')->where('nis', $nis)->get()->getRow();

        if (!$siswa) return $this->failNotFound('Siswa tidak ditemukan.');
        if ($siswa->is_blocked == 1) return $this->failForbidden('Akun terblokir. Hubungi Admin.');

        // 1. Logika Penguncian Perangkat (Device Binding)
        if (empty($siswa->device_id)) {
            $db->table('siswa')->where('id', $siswa->id)->update(['device_id' => $device_id]);
        } elseif ($siswa->device_id !== $device_id) {
            return $this->failUnauthorized('Perangkat tidak dikenali. Gunakan HP yang terdaftar.');
        }

        // 2. INTEGRASI POIN 2: Buat Token Baru (Bukan device_id)
        $api_token = bin2hex(random_bytes(32));
        $db->table('siswa')->where('id', $siswa->id)->update([
            'api_token'  => $api_token,
            'last_login' => date('Y-m-d H:i:s')
        ]);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Login berhasil',
            'data'    => [
                'siswa_id'     => $siswa->id,
                'nama_lengkap' => $siswa->nama_lengkap,
                'token'        => $api_token // Kirim token acak ini ke Android
            ]
        ]);
    }
}
