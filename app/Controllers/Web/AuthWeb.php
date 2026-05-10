<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\UserModel;

class AuthWeb extends BaseController
{
    public function index()
    {
        // Gunakan helper session() global CI4
        if (session()->get('logged_in')) {
            return redirect()->to('/admin/dashboard');
        }
        return view('web/login');
    }

    public function login()
    {
        // 1. Validasi Input Standar CI4
        $aturanValidasi = [
            'username' => 'required',
            'password' => 'required'
        ];

        if (!$this->validate($aturanValidasi)) {
            return redirect()->back()->withInput()->with('error', 'Username dan Password wajib diisi.');
        }

        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');

        // 2. Gunakan Model (Sesuai Konsep MVC)
        $userModel = new UserModel();
        $user      = $userModel->getUserWithRole($username);

        // 3. Verifikasi & Set Session
        if ($user && password_verify($password, (string) $user['password_hash'])) {
            session()->set([
                'user_id'      => (int) $user['id_user'],
                'nama_lengkap' => (string) $user['nama_lengkap'],
                'role_id'      => (int) $user['role_id'],
                'role'         => (string) $user['nama_role'],
                'logged_in'    => true
            ]);

            return redirect()->to('/admin/dashboard')->with('success', 'Selamat datang kembali, ' . $user['nama_lengkap']);
        }

        // Pesan error generic untuk keamanan (tidak memberi tahu apakah username atau password yang salah)
        return redirect()->back()->withInput()->with('error', 'Username atau Password tidak valid.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/admin/login');
    }
}
