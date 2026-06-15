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

$routes->group('admin', ['filter' => 'webAuth', 'namespace' => 'App\Controllers\Web'], function ($routes) {
    $routes->get('profile', 'User::profile');
    $routes->post('profile/update', 'User::updateProfile');
});

$routes->group('admin', ['filter' => ['webAuth', 'dynamicAccess'], 'namespace' => 'App\Controllers\Web'], function ($routes) {

    $routes->get('/', function () {
        return redirect()->to('/admin/dashboard');
    });

    $routes->get('dashboard', 'Dashboard::index');

    $routes->group('siswa', function ($routes) {
        $routes->get('/', 'Siswa::index');
        $routes->post('store', 'Siswa::store');
        $routes->post('update/(:num)', 'Siswa::update/$1');
        $routes->post('deleteBulk', 'Siswa::deleteBulk');
        $routes->post('delete/(:num)', 'Siswa::delete/$1');
        $routes->get('detail/(:num)', 'Siswa::detail/$1');

        $routes->post('resetDevice/(:num)', 'Siswa::resetDevice/$1');
        $routes->post('unblock/(:num)', 'Siswa::unblock/$1');
        $routes->post('block/(:segment)', 'Siswa::block/$1');

        $routes->get('export', 'Siswa::export');
        $routes->post('import', 'Siswa::import');
        $routes->get('downloadTemplate', 'Siswa::downloadTemplate');
    });

    $routes->group('absensi', function ($routes) {
        $routes->get('/', 'Absensi::index');
        $routes->post('inputManual', 'Absensi::inputManual');
    });

    $routes->group('zona', function ($routes) {
        $routes->get('/', 'Zona::index');
        $routes->post('store', 'Zona::store');
        $routes->post('update/(:num)', 'Zona::update/$1');
        $routes->post('delete/(:num)', 'Zona::delete/$1');
        $routes->post('setDefault/(:num)', 'Zona::setDefault/$1');
        $routes->post('assignAnggota/(:num)', 'Zona::assignAnggota/$1');
        $routes->post('updateJadwal/(:num)', 'Zona::updateJadwal/$1');
    });

    $routes->get('tracking', 'Tracking::index');
    $routes->get('tracking/siswa/(:num)', 'Tracking::index/$1');
    $routes->get('tracking/getLocation/(:segment)', 'Tracking::getLocation/$1');
    $routes->post('tracking/pingSiswa/(:segment)', 'Tracking::pingSiswa/$1');

    $routes->get('izin', 'Izin::index');
    $routes->post('izin/approve/(:num)', 'Izin::approve/$1');
    $routes->post('izin/reject/(:num)', 'Izin::reject/$1');

    $routes->get('log-fraud', 'LogFraud::index');
    $routes->get('audit-log', 'AuditLog::index');
    $routes->get('laporan', 'Laporan::index');
    $routes->get('laporan/export', 'Laporan::export');

    $routes->get('user', 'User::index');
    $routes->post('user/store', 'User::store');
    $routes->post('user/delete/(:num)', 'User::delete/$1');
    $routes->post('user/reset/(:num)', 'User::reset/$1');
    $routes->get('user/hak-akses', 'User::hakAkses');
    $routes->post('user/hak-akses/save', 'User::saveHakAkses');

    $routes->get('kelas', 'Kelas::index');
    $routes->post('kelas/store', 'Kelas::store');
    $routes->post('kelas/update/(:num)', 'Kelas::update/$1');
    $routes->post('kelas/delete/(:num)', 'Kelas::delete/$1');

    $routes->get('pengaturan', 'Pengaturan::index');
    $routes->post('pengaturan/save', 'Pengaturan::save');

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

$routes->group('api/v1', ['filter' => 'throttle', 'namespace' => 'App\Controllers\Api'], function ($routes) {
    $routes->post('auth/login', 'AuthApi::login');
    $routes->post('auth/refresh', 'AuthApi::refresh');
    $routes->get('pengumuman', 'PengumumanApi::index');
    $routes->get('waktu_server', 'WaktuApi::index');

    $routes->group('', ['filter' => 'apiAuth'], function ($routes) {
        $routes->post('tracking/store', 'TrackingApi::storeLocation');
        $routes->post('absen/masuk', 'AbsensiApi::masuk');
        $routes->post('absen/pulang', 'AbsensiApi::pulang');
        $routes->get('absen/riwayat', 'AbsensiApi::riwayat');
        $routes->post('tracking/updateLokasi', 'TrackingApi::updateLocation');
        $routes->post('profile/upload-foto', 'ProfileApi::uploadFoto');
        $routes->post('izin/ajukan', 'IzinApi::ajukan');
        $routes->get('izin/riwayat', 'IzinApi::riwayat');
        $routes->post('fcm/updateToken', 'FcmApi::updateToken');
        $routes->post('auth/logout', 'AuthApi::logout');
    });
});

$routes->group('api/tracking', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    $routes->post('trigger/(:num)', 'TrackingApi::triggerTracking/$1');
    $routes->get('poll/(:num)', 'TrackingApi::pollLocation/$1');
});

$routes->set404Override(function () {
    $request = \Config\Services::request();
    $uri     = (string) $request->getUri()->getPath();

    if (strpos($uri, 'api/') === 0) {
        return \Config\Services::response()
            ->setStatusCode(404)
            ->setJSON([
                'status'     => 404,
                'error_code' => 'ENDPOINT_NOT_FOUND',
                'message'    => 'Endpoint API tidak ditemukan atau method salah.'
            ]);
    }

    if (session()->get('isLoggedIn')) {
        return view('errors/custom_404', ['title' => 'Halaman Tidak Ditemukan']);
    }

    return redirect()->to('/admin/login');
});
