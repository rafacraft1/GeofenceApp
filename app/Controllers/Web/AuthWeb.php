<?php

namespace App\Controllers\Web;

use CodeIgniter\Controller;

class AuthWeb extends Controller
{
    public function index()
    {
        // Jika sudah login, langsung ke dashboard
        if (session()->get('logged_in')) {
            return redirect()->to('/admin/dashboard');
        }
        return view('web/login');
    }

    public function login()
    {
        $db = \Config\Database::connect();
        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $user = $db->table('users')->where('username', $username)->get()->getRow();

        if ($user && password_verify($password, $user->password_hash)) {
            session()->set([
                'user_id'      => $user->id,
                'nama_lengkap' => $user->nama_lengkap,
                'role'         => $user->role,
                'logged_in'    => true
            ]);
            return redirect()->to('/admin/dashboard');
        }

        return redirect()->to('/admin/login')->with('error', 'Username atau Password salah.');
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/admin/login');
    }
}
