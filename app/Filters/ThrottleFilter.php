<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class ThrottleFilter implements FilterInterface
{
    /**
     * @param RequestInterface $request
     * @param array|null $arguments
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $throttler = Services::throttler();

        $header = (string) $request->getHeaderLine('Authorization');
        $token  = str_replace('Bearer ', '', $header);
        $uri    = (string) $request->getUri()->getPath();

        if (strpos($uri, 'auth/login') !== false) {
            $throttleKey = 'login_' . $request->getIPAddress();
            $limit       = 20;
        } elseif (!empty($token)) {
            $throttleKey = 'api_user_' . md5($token);
            $limit       = 120;
        } else {
            $throttleKey = 'api_guest_' . $request->getIPAddress();
            $limit       = 60;
        }

        if ($throttler->check($throttleKey, $limit, MINUTE) === false) {
            return Services::response()
                ->setStatusCode(429)
                ->setJSON([
                    'status'     => 429,
                    'error_code' => 'TOO_MANY_REQUESTS',
                    'message'    => 'Terlalu banyak permintaan. Silakan tunggu sebentar.'
                ]);
        }
    }

    /**
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array|null $arguments
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null) {}
}
