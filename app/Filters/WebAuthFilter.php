<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class WebAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (!$session->get('isLoggedIn')) {
            return redirect()->to('/admin/login')->with('error', 'Akses ditolak di halaman login.');
        }

        $lastActivity = $session->get('last_activity');
        $timeout      = 600;

        if ($lastActivity && (time() - $lastActivity > $timeout)) {
            $session->destroy();
            return redirect()->to('/admin/login')->with('warning', 'Sesi login habis silahkan login kembali untuk melanjutkan.');
        }

        $session->set('last_activity', time());
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
