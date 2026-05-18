<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class WebAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Disinkronkan dengan key session yang ada di AuthWeb.php ('isLoggedIn')
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/admin/login')->with('error', 'Akses ditolak. Silakan login.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
