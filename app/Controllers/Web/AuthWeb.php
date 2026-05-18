<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;
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
        $roleModel       = new RoleModel();
        $pengaturanModel = new PengaturanModel();

        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');

        $user = $userModel->where('username', $username)->first();

        if ($user && password_verify($password, (string)$user['password_hash'])) {

            // ✅ Ambil Data Dinamis dari DB
            $role       = $roleModel->find($user['role_id']);
            $pengaturan = $pengaturanModel->find(1);

            // ✅ Injeksi Konfigurasi Global ke Session
            $sessionData = [
                'id_user'       => $user['id_user'],
                'nama_lengkap'  => $user['nama_lengkap'],
                'username'      => $user['username'],
                'role_id'       => $user['role_id'],
                'nama_role'     => $role['nama_role'] ?? 'User',
                'warna_badge'   => $role['warna_badge'] ?? 'gray',
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
