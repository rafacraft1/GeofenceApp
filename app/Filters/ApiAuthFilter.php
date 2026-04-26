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
            return Services::response()->setStatusCode(401)->setJSON([
                'status'  => 'error',
                'message' => 'Token otentikasi tidak ditemukan.'
            ]);
        }

        // Dekode Token (Simulasi validasi ke DB via device_id / token untuk tahap MVP)
        $db = \Config\Database::connect();
        $siswa = $db->table('siswa')->where('device_id', $token)->get()->getRow();

        if (!$siswa) {
            return Services::response()->setStatusCode(401)->setJSON([
                'status'  => 'error',
                'message' => 'Token tidak valid atau perangkat tidak dikenali.'
            ]);
        }

        // Cek Status Pemblokiran (3 Strikes Rule)
        if ($siswa->is_blocked == 1) {
            return Services::response()->setStatusCode(403)->setJSON([
                'status'  => 'error',
                'message' => 'Akun diblokir karena terdeteksi manipulasi (Fake GPS). Hubungi Admin.'
            ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
