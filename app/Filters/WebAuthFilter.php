<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class WebAuthFilter implements FilterInterface
{
    /**
     * @param RequestInterface $request
     * @param array|null $arguments
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/admin/login')->with('error', 'Akses ditolak. Silakan login terlebih dahulu.');
        }

        $lastActivity = $session->get('last_activity');

        // Timeout diatur menjadi 60 Menit (3600 detik) untuk kenyamanan Admin
        $timeout      = 3600;

        if ($lastActivity && (time() - $lastActivity > $timeout)) {
            $session->destroy();
            return redirect()->to('/admin/login')->with('warning', 'Sesi Anda telah habis karena tidak ada aktivitas selama 1 Jam. Silakan login kembali untuk melanjutkan.');
        }

        $session->set('last_activity', time());
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array|null $arguments
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
