<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// ========================================================================
// 1. JALUR WEB ADMIN & GURU
// ========================================================================
$routes->get('/', function () {
    return redirect()->to('/admin/login');
});

// Rute Publik (Autentikasi)
$routes->get('admin/login', 'Web\AuthWeb::index');
$routes->post('admin/login_action', 'Web\AuthWeb::login');
$routes->get('admin/logout', 'Web\AuthWeb::logout');

// Group Web (Wajib Login - Berlaku untuk Admin & Guru)
$routes->group('admin', ['filter' => 'webAuth', 'namespace' => 'App\Controllers\Web'], function ($routes) {

    // --- MENU UMUM (BISA DIAKSES ADMIN & WALI KELAS) ---
    $routes->get('dashboard', 'Dashboard::index');

    // Manajemen Siswa
    $routes->get('siswa', 'Siswa::index');
    $routes->post('siswa/store', 'Siswa::store');
    $routes->post('siswa/update/(:num)', 'Siswa::update/$1');
    $routes->post('siswa/delete/(:num)', 'Siswa::delete/$1');
    $routes->post('siswa/resetDevice/(:num)', 'Siswa::resetDevice/$1');
    $routes->post('siswa/unblock/(:num)', 'Siswa::unblock/$1');
    $routes->get('siswa/downloadTemplate', 'Siswa::downloadTemplate');
    $routes->post('siswa/import', 'Siswa::import');
    $routes->get('siswa/export', 'Siswa::export');
    $routes->get('siswa/detail/(:num)', 'Siswa::detail/$1');

    // Absensi & Tracking
    $routes->get('absensi', 'Absensi::index');
    $routes->post('absensi/inputManual', 'Absensi::inputManual');
    $routes->get('tracking', 'Tracking::index');
    $routes->get('tracking/siswa/(:num)', 'Tracking::index/$1');
    $routes->get('tracking/getLocation/(:num)', 'Tracking::getLocation/$1');

    // Fitur Operasional & Laporan
    $routes->get('izin', 'Izin::index');
    $routes->post('izin/approve/(:num)', 'Izin::approve/$1');
    $routes->post('izin/reject/(:num)', 'Izin::reject/$1');
    $routes->get('log-fraud', 'LogFraud::index');
    $routes->get('laporan', 'Laporan::index');
    $routes->get('laporan/export', 'Laporan::export');

    // --- MENU KRUSIAL (KHUSUS ADMIN UTAMA) ---
    // Dilindungi lapis kedua oleh filter 'adminOnly'
    $routes->group('', ['filter' => 'adminOnly'], function ($routes) {

        // Manajemen Pengguna (Baru)
        $routes->get('user', 'User::index');
        $routes->post('user/store', 'User::store');
        $routes->post('user/delete/(:num)', 'User::delete/$1');
        // Rute Reset Password ke Default (guru1234)
        $routes->post('user/reset/(:num)', 'User::reset/$1');

        // Manajemen Kelas
        $routes->get('kelas', 'Kelas::index');
        $routes->post('kelas/store', 'Kelas::store');
        $routes->post('kelas/update/(:num)', 'Kelas::update/$1');
        $routes->post('kelas/delete/(:num)', 'Kelas::delete/$1');

        // Pengaturan, Jadwal, Libur, Pengumuman
        $routes->get('pengaturan', 'Pengaturan::index');
        $routes->post('pengaturan/save', 'Pengaturan::save');

        $routes->get('jadwal', 'Jadwal::index');
        $routes->post('jadwal/update', 'Jadwal::update');

        $routes->get('libur', 'Libur::index');
        $routes->post('libur/store', 'Libur::store');
        $routes->post('libur/delete/(:num)', 'Libur::delete/$1');

        $routes->get('pengumuman', 'Pengumuman::index');
        $routes->post('pengumuman/store', 'Pengumuman::store');
        $routes->post('pengumuman/delete/(:num)', 'Pengumuman::delete/$1');
    });

    // API Ping Web (Biarkan diluar karena hanya untuk endpoint internal)
    $routes->post('tracking/pingSiswa/(:num)', '\App\Controllers\Api\TrackingApi::pingSiswa/$1');
});

// ========================================================================
// 2. JALUR API ANDROID
// ========================================================================
$routes->group('api/v1', ['filter' => 'throttle', 'namespace' => 'App\Controllers\Api'], function ($routes) {

    // Auth & Info Publik
    $routes->post('auth/login', 'AuthApi::login');
    $routes->get('pengumuman', 'PengumumanApi::index');
    $routes->get('waktu_server', 'WaktuApi::index');

    // Group Khusus Transaksi Data (Wajib API Token)
    $routes->group('', ['filter' => 'apiAuth'], function ($routes) {
        $routes->post('absen/masuk', 'AbsensiApi::masuk');
        $routes->post('absen/pulang', 'AbsensiApi::pulang');
        $routes->get('absen/riwayat', 'AbsensiApi::riwayat');
        $routes->post('tracking/update', 'TrackingApi::updateLokasi');
        $routes->post('profile/upload-foto', 'ProfileApi::uploadFoto');
        $routes->post('izin/ajukan', 'IzinApi::ajukan');
        $routes->post('fcm/updateToken', 'FcmApi::updateToken');
    });
});
