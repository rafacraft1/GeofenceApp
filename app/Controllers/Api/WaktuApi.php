<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;

class WaktuApi extends ResourceController
{
    protected $format = 'json';

    public function index()
    {
        $timezone   = env('app.appTimezone', 'Asia/Jakarta');
        $waktuNow   = Time::now($timezone);

        return $this->respond([
            'status'       => 200,
            'waktu_server' => $waktuNow->toDateTimeString(),
            'timestamp'    => $waktuNow->getTimestamp()
        ]);
    }
}
