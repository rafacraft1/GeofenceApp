<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');

// --- Jalur Web Admin ---
$routes->get('/', function () {
    return redirect()->to('/admin/login');
});
$routes->get('admin/login', 'Web\AuthWeb::index');
$routes->post('admin/login_action', 'Web\AuthWeb::login');
$routes->get('admin/logout', 'Web\AuthWeb::logout');

$routes->group('admin', ['filter' => 'webAuth', 'namespace' => 'App\Controllers\Web'], function ($routes) {
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('pengaturan', 'Pengaturan::index');
    $routes->post('pengaturan/save', 'Pengaturan::save');

    $routes->get('siswa', 'Siswa::index');
    $routes->post('siswa/store', 'Siswa::store');
    $routes->post('siswa/update/(:num)', 'Siswa::update/$1');
    $routes->post('siswa/reset_device/(:num)', 'Siswa::reset_device/$1');
    $routes->post('siswa/unblock/(:num)', 'Siswa::unblock/$1');
    $routes->get('siswa/download_template', 'Siswa::download_template');
    $routes->post('siswa/import', 'Siswa::import');
    $routes->get('siswa/export', 'Siswa::export');

    $routes->get('absensi', 'Absensi::index');
    $routes->post('absensi/input_manual', 'Absensi::input_manual');
});

// --- Jalur API Android ---
$routes->group('api/v1', ['filter' => 'throttle'], function ($routes) {
    // Auth (Tidak perlu filter token)
    $routes->post('auth/login', 'Api\AuthApi::login');

    // Absensi & Tracking (Wajib filter token 'apiAuth')
    $routes->group('', ['filter' => 'apiAuth'], function ($routes) {
        $routes->post('absen/masuk', 'Api\AbsensiApi::masuk');
        $routes->post('absen/pulang', 'Api\AbsensiApi::pulang');
        $routes->get('absen/riwayat', 'Api\AbsensiApi::riwayat');
        $routes->post('tracking/update', 'Api\TrackingApi::update_lokasi');
    });
});
