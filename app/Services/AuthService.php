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
     * @param string $nis
     * @param string $password
     * @param string|null $deviceId
     * @return array
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
            return ['status' => 403, 'message' => 'Akun Anda telah diblokir. Silakan hubungi Admin.'];
        }

        if (!password_verify($password, (string)$siswa['password'])) {
            return ['status' => 401, 'message' => 'Password yang Anda masukkan salah.'];
        }

        if (!empty($siswa['device_id']) && $siswa['device_id'] !== $deviceId) {
            return ['status' => 403, 'message' => 'Akun ini sudah terikat dengan perangkat HP lain.'];
        }

        $tokenPayload = [
            'id_siswa' => $siswa['id_siswa'],
            'nis'      => $siswa['nis']
        ];

        $accessToken  = $this->jwtAuth->generateAccessToken($tokenPayload);
        $refreshToken = $this->jwtAuth->generateRefreshToken($tokenPayload);

        $this->siswaModel->update($siswa['id_siswa'], [
            'device_id'  => $deviceId,
            'api_token'  => hash('sha256', $refreshToken),
            'last_login' => date('Y-m-d H:i:s')
        ]);

        return [
            'status'        => 200,
            'message'       => 'Login berhasil.',
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'data'          => [
                'id_siswa'    => $siswa['id_siswa'],
                'nis'         => $siswa['nis'],
                'nama_siswa'  => $siswa['nama_siswa'],
                'kelas_id'    => $siswa['kelas_id'],
                'nama_kelas'  => $siswa['nama_kelas'] ?? 'Siswa Aktif',
                'foto_profil' => $siswa['foto_profil']
            ]
        ];
    }

    /**
     * @param string $refreshToken
     * @return array
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        $decoded = $this->jwtAuth->decodeToken($refreshToken);

        if ($decoded['status'] !== 'valid') {
            return ['status' => 401, 'message' => 'Refresh token tidak valid atau telah kedaluwarsa.'];
        }

        $idSiswa = $decoded['data']->id_siswa;
        $nis     = $decoded['data']->nis;

        $siswa = $this->siswaModel->select('is_blocked, api_token')->find($idSiswa);

        if (!$siswa || $siswa['is_blocked'] == 1 || $siswa['api_token'] !== hash('sha256', $refreshToken)) {
            return ['status' => 401, 'message' => 'Sesi tidak valid, token telah dicabut, atau akun diblokir.'];
        }

        $newPayload = [
            'id_siswa' => $idSiswa,
            'nis'      => $nis
        ];

        $newAccessToken  = $this->jwtAuth->generateAccessToken($newPayload);
        $newRefreshToken = $this->jwtAuth->generateRefreshToken($newPayload);

        $this->siswaModel->update($idSiswa, [
            'api_token' => hash('sha256', $newRefreshToken)
        ]);

        return [
            'status'        => 200,
            'access_token'  => $newAccessToken,
            'refresh_token' => $newRefreshToken
        ];
    }

    /**
     * @param int $idSiswa
     * @return array
     */
    public function logoutSiswa(int $idSiswa): array
    {
        $this->siswaModel->update($idSiswa, [
            'api_token' => null,
            'fcm_token' => null
        ]);

        return ['status' => 200, 'message' => 'Logout berhasil. Sesi dan notifikasi telah dinonaktifkan.'];
    }
}
