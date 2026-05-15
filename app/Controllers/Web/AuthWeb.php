<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\UserModel;

class AuthWeb extends BaseController
{
    public function index()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/admin/dashboard');
        }
        return view('web/login');
    }

    public function login()
    {
        // 1. Validasi Input
        $aturanValidasi = [
            'username' => 'required',
            'password' => 'required'
        ];

        if (!$this->validate($aturanValidasi)) {
            return redirect()->back()->withInput()->with('error', 'Username dan Password wajib diisi.');
        }

        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');

        // 2. Ambil Data User
        $userModel = new UserModel();
        $user      = $userModel->getUserWithRole($username);

        // 3. Verifikasi Password
        if ($user && password_verify($password, (string) $user['password_hash'])) {

            // 4. Cek Otoritas Wali Kelas
            $waliKelasInfo = $userModel->getWaliKelasInfo((int) $user['id_user']);

            // 5. Set Session dengan Data Isolasi (Row-Level Security)
            session()->set([
                'user_id'       => (int) $user['id_user'],
                'nama_lengkap'  => (string) $user['nama_lengkap'],
                'role_id'       => (int) $user['role_id'],
                'role'          => (string) $user['nama_role'],
                // Penambahan payload session wali kelas
                'is_wali_kelas' => $waliKelasInfo ? true : false,
                'kelas_id'      => $waliKelasInfo ? (int) $waliKelasInfo['id_kelas'] : null,
                'nama_kelas'    => $waliKelasInfo ? (string) $waliKelasInfo['nama_kelas'] : null,
                'logged_in'     => true
            ]);

            return redirect()->to('/admin/dashboard')->with('success', 'Selamat datang kembali, ' . $user['nama_lengkap']);
        }

        return redirect()->back()->withInput()->with('error', 'Username atau Password tidak valid.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/admin/login');
    }
}
