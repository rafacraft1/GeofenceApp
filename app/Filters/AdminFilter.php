<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Asumsi Role ID untuk Admin di database tabel `roles` adalah 1
        if ((int) session()->get('role_id') !== 1) {
            // Jika bukan Admin (misal Guru), kembalikan ke dashboard dengan pesan error
            return redirect()->to('/admin/dashboard')->with('error', 'Akses ditolak! Halaman tersebut khusus untuk Admin Utama.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
