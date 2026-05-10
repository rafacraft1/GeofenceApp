<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\SiswaModel;

class AuthApi extends ResourceController
{
    protected $format = 'json';
    protected SiswaModel $siswaModel;

    public function __construct()
    {
        $this->siswaModel = new SiswaModel();
    }

    public function login()
    {
        $nis      = $this->request->getPost('nis');
        $password = (string) $this->request->getPost('password');
        $deviceId = $this->request->getPost('device_id');

        if (!$nis || !$password) {
            return $this->failValidationErrors('NIS dan Password wajib diisi.');
        }

        // Menggunakan SiswaModel (Otomatis mengembalikan Array)
        $siswa = $this->siswaModel->where('nis', $nis)->first();

        if (!$siswa) {
            return $this->failNotFound('Akun dengan NIS tersebut tidak ditemukan.');
        }

        if ($siswa['is_blocked'] == 1) {
            return $this->failUnauthorized('Akun Anda telah diblokir. Silakan hubungi Admin.');
        }

        if (password_verify($password, $siswa['password'])) {

            // Logika Pengikatan Perangkat (Device Binding)
            if (!empty($siswa['device_id']) && $siswa['device_id'] !== $deviceId) {
                return $this->failUnauthorized('Akun ini sudah terikat dengan perangkat HP lain.');
            }

            // Generate Token API baru
            $token = bin2hex(random_bytes(32));

            // update() pada model otomatis menangani updated_at
            $this->siswaModel->update($siswa['id_siswa'], [
                'api_token'  => $token,
                'device_id'  => $deviceId,
                'last_login' => date('Y-m-d H:i:s')
            ]);

            return $this->respond([
                'status'  => 200,
                'message' => 'Login berhasil.',
                'token'   => $token,
                'data'    => [
                    'id_siswa'    => $siswa['id_siswa'],
                    'nis'         => $siswa['nis'],
                    'nama_siswa'  => $siswa['nama_siswa'],
                    'kelas_id'    => $siswa['kelas_id'],
                    'foto_profil' => $siswa['foto_profil']
                ]
            ]);
        }

        return $this->failUnauthorized('Password yang Anda masukkan salah.');
    }
}
