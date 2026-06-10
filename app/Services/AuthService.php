<?php

namespace App\Services;

use App\Models\SiswaModel;
use App\Libraries\JWTAuth;

class AuthService
{
    protected SiswaModel $siswaModel;
    protected JWTAuth $jwtAuth;

    public function __construct()
    {
        $this->siswaModel = new SiswaModel();
        $this->jwtAuth    = new JWTAuth();
    }

    /**
     * Memproses logika otentikasi siswa
     */
    public function attemptLogin(string $nis, string $password, ?string $deviceId): array
    {
        $siswa = $this->siswaModel->select('siswa.id_siswa, siswa.nis, siswa.password, siswa.is_blocked, siswa.device_id, siswa.nama_siswa, siswa.kelas_id, siswa.foto_profil, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->where('nis', $nis)
            ->first();

        if (!$siswa) {
            return ['status' => 404, 'message' => 'Akun dengan NIS tersebut tidak ditemukan.'];
        }

        if ($siswa['is_blocked'] == 1) {
            return ['status' => 401, 'message' => 'Akun Anda telah diblokir. Silakan hubungi Admin.'];
        }

        if (!password_verify($password, $siswa['password'])) {
            return ['status' => 401, 'message' => 'Password yang Anda masukkan salah.'];
        }

        // Logika Pengikatan Perangkat (Device Binding)
        if (!empty($siswa['device_id']) && $siswa['device_id'] !== $deviceId) {
            return ['status' => 401, 'message' => 'Akun ini sudah terikat dengan perangkat HP lain.'];
        }

        // Generate JWT Token (Data yang di-encode cukup data publik/ID yang tidak sensitif)
        $tokenPayload = [
            'id_siswa' => $siswa['id_siswa'],
            'nis'      => $siswa['nis']
        ];
        $jwtToken = $this->jwtAuth->generateToken($tokenPayload);

        // Update Token dan Last Login di Database
        $this->siswaModel->update($siswa['id_siswa'], [
            'api_token'  => $jwtToken, // Kita tetap simpan JWT di sini untuk kebutuhan session web/admin jika diperlukan
            'device_id'  => $deviceId,
            'last_login' => date('Y-m-d H:i:s')
        ]);

        return [
            'status'  => 200,
            'message' => 'Login berhasil.',
            'token'   => $jwtToken,
            'data'    => [
                'id_siswa'    => $siswa['id_siswa'],
                'nis'         => $siswa['nis'],
                'nama_siswa'  => $siswa['nama_siswa'],
                'kelas_id'    => $siswa['kelas_id'],
                'nama_kelas'  => $siswa['nama_kelas'] ?? 'Siswa Aktif', // KUNCI PENYELESAIAN MASALAH
                'foto_profil' => $siswa['foto_profil']
            ]
        ];
    }
}
