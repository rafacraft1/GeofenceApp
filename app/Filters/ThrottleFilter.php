<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class ThrottleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $throttler = Services::throttler();

        $header = $request->getHeaderLine('Authorization');
        $token  = str_replace('Bearer ', '', $header);

        $throttleKey = !empty($token) ? md5($token) : $request->getIPAddress();

        if ($throttler->check($throttleKey, 60, MINUTE) === false) {
            return Services::response()
                ->setStatusCode(429)
                ->setJSON([
                    'status'  => 'error',
                    'message' => 'Terlalu banyak request. Silakan tunggu beberapa saat.'
                ]);
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
