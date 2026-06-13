<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\PengaturanModel;

class AuthWeb extends BaseController
{
    /**
     * @return mixed
     */
    public function index()
    {
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/admin/dashboard');
        }
        return view('web/login');
    }

    /**
     * @return mixed
     */
    public function login()
    {
        if (!$this->validate([
            'username' => 'required|alpha_numeric',
            'password' => 'required'
        ])) {
            return redirect()->back()->withInput()->with('error', 'Format Username dan Kata Sandi tidak valid!');
        }

        $userModel       = new UserModel();
        $pengaturanModel = new PengaturanModel();

        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');

        $user = $userModel->select('users.*, roles.nama_role, roles.warna_badge')
            ->join('roles', 'roles.id_role = users.role_id', 'left')
            ->where('username', $username)
            ->first();

        if ($user && password_verify($password, (string)$user['password_hash'])) {

            $pengaturan = cache()->remember('pengaturan_global', 86400, function () use ($pengaturanModel) {
                return $pengaturanModel->find(1);
            });

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

    /**
     * @return mixed
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/admin/login')->with('success', 'Anda berhasil keluar dari sistem.');
    }
}
