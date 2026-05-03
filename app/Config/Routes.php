<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
// $routes->get('/', 'Home::index');

// ========================================================================
// 1. JALUR WEB ADMIN
// ========================================================================
$routes->get('/', function () {
    return redirect()->to('/admin/login');
});
$routes->get('admin/login', 'Web\AuthWeb::index');
$routes->post('admin/login_action', 'Web\AuthWeb::login');
$routes->get('admin/logout', 'Web\AuthWeb::logout');

// Group khusus Admin (Wajib Login)
$routes->group('admin', ['filter' => 'webAuth', 'namespace' => 'App\Controllers\Web'], function ($routes) {
    // Dashboard & Pengaturan
    $routes->get('dashboard', 'Dashboard::index');
    $routes->get('pengaturan', 'Pengaturan::index');
    $routes->post('pengaturan/save', 'Pengaturan::save');

    // Manajemen Siswa
    $routes->get('siswa', 'Siswa::index');
    $routes->post('siswa/store', 'Siswa::store');
    $routes->post('siswa/update/(:num)', 'Siswa::update/$1');
    $routes->post('siswa/delete/(:num)', 'Siswa::delete/$1');
    $routes->post('siswa/reset_device/(:num)', 'Siswa::reset_device/$1');
    $routes->post('siswa/unblock/(:num)', 'Siswa::unblock/$1');
    $routes->get('siswa/download_template', 'Siswa::download_template');
    $routes->post('siswa/import', 'Siswa::import');
    $routes->get('siswa/export', 'Siswa::export');

    // Absensi & Tracking
    $routes->get('absensi', 'Absensi::index');
    $routes->post('absensi/input_manual', 'Absensi::input_manual');
    $routes->get('tracking/siswa/(:num)', 'Tracking::index/$1');
    $routes->get('tracking/get_location/(:num)', 'Tracking::get_location/$1');

    // === TAMBAHAN BARU: Route Ping FCM ke Android ===
    $routes->post('tracking/ping_siswa/(:num)', '\App\Controllers\Api\TrackingApi::ping_siswa/$1');

    // Broadcast Pengumuman
    $routes->get('pengumuman', 'Pengumuman::index');
    $routes->post('pengumuman/store', 'Pengumuman::store');
    $routes->post('pengumuman/delete/(:num)', 'Pengumuman::delete/$1');
});

// ========================================================================
// 2. JALUR API ANDROID
// ========================================================================
$routes->group('api/v1', ['filter' => 'throttle', 'namespace' => 'App\Controllers\Api'], function ($routes) {

    // Auth (Tidak perlu filter token apiAuth)
    $routes->post('auth/login', 'AuthApi::login');

    // Pengumuman (Bisa ditarik saat pertama kali buka APK tanpa harus login dulu)
    $routes->get('pengumuman', 'PengumumanApi::index');
    $routes->get('waktu_server', 'WaktuApi::index');

    // Group Khusus Transaksi Data (Wajib menyertakan API Token)
    $routes->group('', ['filter' => 'apiAuth'], function ($routes) {
        $routes->post('absen/masuk', 'AbsensiApi::masuk');
        $routes->post('absen/pulang', 'AbsensiApi::pulang');
        $routes->get('absen/riwayat', 'AbsensiApi::riwayat');
        $routes->post('tracking/update', 'TrackingApi::update_lokasi');
        $routes->post('profile/upload-foto', 'ProfileApi::uploadFoto');
    });
});
