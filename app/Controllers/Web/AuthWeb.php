<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PengaturanModel;

class AuthWeb extends BaseController
{
    public function index()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/admin/dashboard');
        }
        return view('web/login');
    }

    public function login()
    {
        $userModel       = new UserModel();
        $pengaturanModel = new PengaturanModel();

        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');

        // ✅ Optimasi: Gabungkan pencarian user dan role dalam 1 kueri
        $user = $userModel->select('users.*, roles.nama_role, roles.warna_badge')
            ->join('roles', 'roles.id_role = users.role_id', 'left')
            ->where('username', $username)
            ->first();

        if ($user && password_verify($password, (string)$user['password_hash'])) {

            // ✅ Optimasi: Gunakan Cache untuk pengaturan (Valid 24 jam / 86400 detik)
            $pengaturan = cache()->remember('pengaturan_global', 86400, function () use ($pengaturanModel) {
                return $pengaturanModel->find(1);
            });

            // ✅ Injeksi Konfigurasi Global ke Session
            $sessionData = [
                'id_user'       => $user['id_user'],
                'nama_lengkap'  => $user['nama_lengkap'],
                'username'      => $user['username'],
                'role_id'       => $user['role_id'],
                'nama_role'     => $user['nama_role'] ?? 'User',
                'warna_badge'   => $user['warna_badge'] ?? 'gray',
                'foto'          => $user['foto'],
                'nama_aplikasi' => $pengaturan['nama_aplikasi'] ?? 'GeofenceApp',
                'nama_sekolah'  => $pengaturan['nama_sekolah'] ?? 'Sekolah',
                'isLoggedIn'    => true
            ];

            session()->set($sessionData);
            return redirect()->to('/admin/dashboard')->with('success', 'Selamat datang kembali, ' . $user['nama_lengkap']);
        }

        return redirect()->back()->withInput()->with('error', 'Username atau Kata Sandi salah!');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/admin/login')->with('success', 'Anda berhasil keluar dari sistem.');
    }
}
