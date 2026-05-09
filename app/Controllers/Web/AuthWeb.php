<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class AuthWeb extends BaseController
{
    public function index()
    {
        if ($this->session->get('logged_in')) {
            return redirect()->to('/admin/dashboard');
        }
        return view('web/login');
    }

    public function login()
    {
        $username = $this->request->getPost('username');
        $password = (string) $this->request->getPost('password'); // Casting ke string mencegah error P1006

        // Mengambil data user lengkap dengan nama role-nya menggunakan JOIN
        $user = $this->db->table('users')
            ->select('users.*, roles.nama_role')
            ->join('roles', 'roles.id_role = users.role_id')
            ->where('username', $username)
            ->get()
            ->getRowArray();

        if ($user && password_verify($password, $user['password_hash'])) {
            $this->session->set([
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
        $this->session->destroy();
        return redirect()->to('/admin/login');
    }
}
