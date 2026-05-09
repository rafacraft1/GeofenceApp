<?php

namespace App\Controllers\Web;

use CodeIgniter\Controller;
use CodeIgniter\Database\BaseConnection;

class AuthWeb extends Controller
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
        /** @var BaseConnection $db */
        $db = \Config\Database::connect();

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        // Mengambil data user lengkap dengan nama role-nya menggunakan JOIN
        $user = $db->table('users')
            ->select('users.*, roles.nama_role')
            ->join('roles', 'roles.id_role = users.role_id')
            ->where('username', $username)
            ->get()
            ->getRowArray();

        if ($user && password_verify($password, $user['password_hash'])) {
            session()->set([
                'user_id'      => $user['id_user'],
                'nama_lengkap' => $user['nama_lengkap'],
                'role'         => $user['nama_role'], // Menggunakan nama_role dari tabel roles
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
