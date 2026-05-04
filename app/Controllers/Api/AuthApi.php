<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class AuthApi extends ResourceController
{
    protected $format = 'json';

    public function login()
    {
        $nis       = $this->request->getPost('nis');
        $device_id = $this->request->getPost('device_id');
        $fcmToken  = $this->request->getPost('fcm_token');

        if (!$nis || !$device_id) {
            return $this->failValidationErrors('NIS dan Device ID wajib diisi.');
        }

        $db = \Config\Database::connect();

        // Join dengan tabel kelas untuk mendapatkan nama kelas (dibutuhkan Firebase)
        $siswa = $db->table('siswa')
            ->select('siswa.*, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->where('nis', $nis)
            ->get()
            ->getRowArray();

        if (!$siswa) return $this->failNotFound('Siswa tidak ditemukan.');
        if ($siswa['is_blocked'] == 1) return $this->failForbidden('Akun terblokir. Hubungi Admin.');

        // 1. Logika Penguncian Perangkat (Device Binding)
        if (empty($siswa['device_id'])) {
            $db->table('siswa')->where('id_siswa', $siswa['id_siswa'])->update(['device_id' => $device_id]);
        } elseif ($siswa['device_id'] !== $device_id) {
            return $this->failUnauthorized('Perangkat tidak dikenali. Gunakan HP yang terdaftar.');
        }

        // 2. Buat Token Baru & Simpan FCM Token
        $api_token = bin2hex(random_bytes(32));
        $db->table('siswa')->where('id_siswa', $siswa['id_siswa'])->update([
            'api_token'  => $api_token,
            'fcm_token'  => $fcmToken,
            'last_login' => date('Y-m-d H:i:s')
        ]);

        return $this->respond([
            'status'  => 'success',
            'message' => 'Login berhasil',
            'data'    => [
                'siswa_id'     => $siswa['id_siswa'],
                'nama_lengkap' => $siswa['nama_siswa'],
                'kelas'        => $siswa['nama_kelas'] ?? '-',
                'foto'         => !empty($siswa['foto_profil']) ? base_url('uploads/siswa/' . $siswa['foto_profil']) : '',
                'token'        => $api_token
            ]
        ]);
    }
}
