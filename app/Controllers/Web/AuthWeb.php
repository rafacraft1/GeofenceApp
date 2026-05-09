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
        // Type-casting string yang ketat untuk keamanan & mencegah P1006
        $username = (string) $this->request->getPost('username');
        $password = (string) $this->request->getPost('password');

        // Mengambil data user lengkap dengan nama role-nya menggunakan JOIN
        $user = $this->db->table('users')
            ->select('users.*, roles.nama_role')
            ->join('roles', 'roles.id_role = users.role_id')
            ->where('username', $username)
            ->get()
            ->getRowArray();

        // Verifikasi keberadaan user dan kecocokan password_hash
        if ($user && password_verify($password, (string) $user['password_hash'])) {
            $this->session->set([
                'user_id'      => (int) $user['id_user'],
                'nama_lengkap' => (string) $user['nama_lengkap'],
                'role_id'      => (int) $user['role_id'], // Ditambahkan untuk memudahkan Filter Akses
                'role'         => (string) $user['nama_role'],
                'logged_in'    => true
            ]);

            // Tambahkan flashdata success untuk disambut oleh Toastr di Dashboard
            return redirect()->to('/admin/dashboard')->with('success', 'Selamat datang kembali, ' . $user['nama_lengkap']);
        }

        // withInput() agar jika view punya fungsi old('username'), username tidak perlu diketik ulang
        return redirect()->to('/admin/login')->withInput()->with('error', 'Username atau Password salah.');
    }

    public function logout()
    {
        $this->session->destroy();
        return redirect()->to('/admin/login');
    }
}
