<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use App\Models\SiswaModel;
use App\Libraries\JWTAuth;

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

        $jwtAuth = new JWTAuth();
        $decoded = $jwtAuth->decodeToken($token);

        if (!$decoded) {
            return Services::response()
                ->setJSON(['status' => 401, 'message' => 'Token tidak valid atau sesi telah berakhir.'])
                ->setStatusCode(401);
        }

        $siswaModel = new SiswaModel();
        $siswa      = $siswaModel->select('id_siswa, nis, nama_siswa, kelas_id, is_blocked')->find($decoded->id_siswa);

        if (!$siswa) {
            return Services::response()
                ->setJSON(['status' => 401, 'message' => 'Sesi tidak valid.'])
                ->setStatusCode(401);
        }

        if ($siswa['is_blocked'] == 1) {
            return Services::response()
                ->setJSON(['status' => 403, 'message' => 'Akun Anda diblokir karena terindikasi pelanggaran.'])
                ->setStatusCode(403);
        }

        $request->siswaAuth = $siswa;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
