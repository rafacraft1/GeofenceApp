<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use App\Models\SiswaModel;

class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $header = $request->getHeaderLine('Authorization');
        $token  = str_replace('Bearer ', '', $header);

        if (empty($token)) {
            return Services::response()
                ->setJSON(['status' => 401, 'message' => 'Token otorisasi tidak ditemukan.'])
                ->setStatusCode(401);
        }

        // Menggunakan SiswaModel agar standar interaksi DB tetap terjaga
        $siswaModel = new SiswaModel();

        // Asumsi SiswaModel me-return 'array' (standar CI4)
        $siswa = $siswaModel->where('api_token', $token)->first();

        if (!$siswa) {
            return Services::response()
                ->setJSON(['status' => 401, 'message' => 'Token tidak valid atau sesi telah berakhir.'])
                ->setStatusCode(401);
        }

        // Cek pemblokiran
        if (isset($siswa['is_blocked']) && $siswa['is_blocked'] == 1) {
            return Services::response()
                ->setJSON(['status' => 403, 'message' => 'Akun Anda diblokir karena terindikasi pelanggaran.'])
                ->setStatusCode(403);
        }

        // INJEKSI DATA: Menyimpan data siswa ke dalam Request agar dapat dipakai di semua Controller API
        // Ini menghilangkan kebutuhan fungsi getSiswaAuth() di tiap Controller.
        $request->siswaAuth = $siswa;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada tindakan setelah request untuk API Auth
    }
}
