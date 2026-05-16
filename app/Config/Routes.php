<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

$routes->get('/', function () {
    return redirect()->to('/admin/login');
});

$routes->get('admin/login', 'Web\AuthWeb::index');
$routes->post('admin/login_action', 'Web\AuthWeb::login');
$routes->get('admin/logout', 'Web\AuthWeb::logout');

// Group Web dengan Filter Dinamis
$routes->group('admin', ['filter' => ['webAuth', 'dynamicAccess'], 'namespace' => 'App\Controllers\Web'], function ($routes) {

    $routes->get('dashboard', 'Dashboard::index');

    // Modul Siswa, Absensi, Tracking, Izin, Laporan, Log Fraud
    $routes->get('siswa', 'Siswa::index');
    $routes->post('siswa/store', 'Siswa::store');
    $routes->post('siswa/update/(:num)', 'Siswa::update/$1');
    $routes->post('siswa/delete/(:num)', 'Siswa::delete/$1');
    $routes->get('siswa/detail/(:num)', 'Siswa::detail/$1');
    $routes->post('siswa/resetDevice/(:num)', 'Siswa::resetDevice/$1');
    $routes->post('siswa/unblock/(:num)', 'Siswa::unblock/$1');

    $routes->get('absensi', 'Absensi::index');
    $routes->get('siswa/export', 'Siswa::export');
    $routes->post('siswa/import', 'Siswa::import');
    $routes->get('siswa/downloadTemplate', 'Siswa::downloadTemplate');
    $routes->post('absensi/inputManual', 'Absensi::inputManual');

    $routes->get('tracking', 'Tracking::index');
    $routes->get('tracking/siswa/(:num)', 'Tracking::index/$1');
    $routes->get('tracking/getLocation/(:segment)', 'Tracking::getLocation/$1');
    $routes->post('tracking/pingSiswa/(:segment)', 'Tracking::pingSiswa/$1');

    $routes->get('izin', 'Izin::index');
    $routes->post('izin/approve/(:num)', 'Izin::approve/$1');
    $routes->post('izin/reject/(:num)', 'Izin::reject/$1');

    $routes->get('log-fraud', 'LogFraud::index');
    $routes->get('laporan', 'Laporan::index');
    $routes->get('laporan/export', 'Laporan::export');

    // Modul Manajemen User & Hak Akses
    $routes->get('user', 'User::index');
    $routes->post('user/store', 'User::store');
    $routes->post('user/delete/(:num)', 'User::delete/$1');
    $routes->post('user/reset/(:num)', 'User::reset/$1');
    $routes->get('user/hak-akses', 'User::hakAkses');
    $routes->post('user/hak-akses/save', 'User::saveHakAkses');

    // Modul Kelas, Pengaturan, Jadwal, Libur, Pengumuman
    $routes->get('kelas', 'Kelas::index');
    $routes->post('kelas/store', 'Kelas::store');
    $routes->post('kelas/update/(:num)', 'Kelas::update/$1');
    $routes->post('kelas/delete/(:num)', 'Kelas::delete/$1');

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

    $routes->get('mutasi', 'Mutasi::index');
    $routes->post('mutasi/proses', 'Mutasi::proses');
    $routes->get('mutasi/checkTujuan/(:any)', 'Mutasi::checkTujuan/$1');
});

// ========================================================================
// 2. JALUR API ANDROID (V1)
// ========================================================================
$routes->group('api/v1', ['filter' => 'throttle', 'namespace' => 'App\Controllers\Api'], function ($routes) {

    // Auth & Info Publik
    $routes->post('auth/login', 'AuthApi::login');
    $routes->get('pengumuman', 'PengumumanApi::index');
    $routes->get('waktu_server', 'WaktuApi::index');

    // ✅ Rute diletakkan DI LUAR grup token agar background service Flutter bisa mengaksesnya
    $routes->post('tracking/store', 'TrackingApi::storeLocation');

    // Group Khusus Transaksi Data (Wajib API Token)
    $routes->group('', ['filter' => 'apiAuth'], function ($routes) {
        $routes->post('absen/masuk', 'AbsensiApi::masuk');
        $routes->post('absen/pulang', 'AbsensiApi::pulang');
        $routes->get('absen/riwayat', 'AbsensiApi::riwayat');

        $routes->post('tracking/updateLokasi', 'TrackingApi::updateLokasi');

        $routes->post('profile/upload-foto', 'ProfileApi::uploadFoto');
        $routes->post('izin/ajukan', 'IzinApi::ajukan');
        $routes->get('izin/riwayat', 'IzinApi::riwayat');
        $routes->post('fcm/updateToken', 'FcmApi::updateToken');
    });
});

// ========================================================================
// 3. JALUR API TRACKING ON-THE-FLY (HYBRID FIFO)
// ========================================================================
$routes->group('api/tracking', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    // Dipanggil Web Admin (AJAX / Fetch) - Tanpa wajib token API
    $routes->post('trigger/(:num)', 'TrackingApi::triggerTracking/$1');
    $routes->get('poll/(:num)', 'TrackingApi::pollLocation/$1');
});
