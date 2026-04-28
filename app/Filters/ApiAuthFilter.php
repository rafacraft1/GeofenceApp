<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $header = $request->getHeaderLine('Authorization');
        $token  = str_replace('Bearer ', '', $header);

        if (empty($token)) {
            return Services::response()
                ->setJSON(['status' => 'error', 'message' => 'Token otorisasi tidak ditemukan.'])
                ->setStatusCode(401);
        }

        $db = \Config\Database::connect();

        // --- PERBAIKAN: CEK BERDASARKAN api_token, BUKAN device_id ---
        $siswa = $db->table('siswa')->where('api_token', $token)->get()->getRow();

        if (!$siswa) {
            return Services::response()
                ->setJSON(['status' => 'error', 'message' => 'Token tidak valid atau sesi berakhir.'])
                ->setStatusCode(401);
        }

        if ($siswa->is_blocked == 1) {
            return Services::response()
                ->setJSON(['status' => 'error', 'message' => 'Akun diblokir karena pelanggaran.'])
                ->setStatusCode(403);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Kosongkan saja untuk proses after
    }
}
