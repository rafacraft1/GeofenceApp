<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class User extends BaseController
{
    public function index()
    {
        // Mengambil daftar peran (Role) untuk dropdown
        $listRoles = $this->db->table('roles')->orderBy('nama_role', 'ASC')->get()->getResultArray();

        // Mengambil daftar pengguna beserta nama perannya
        $listUsers = $this->db->table('users')
            ->select('users.*, roles.nama_role')
            ->join('roles', 'roles.id_role = users.role_id', 'left')
            ->orderBy('roles.nama_role', 'ASC')
            ->orderBy('users.nama_lengkap', 'ASC')
            ->get()->getResultArray();

        $data = [
            'title'     => 'Manajemen Pengguna',
            'listRoles' => $listRoles,
            'listUsers' => $listUsers
        ];

        return view('web/user', $data);
    }

    public function store()
    {
        $idUser      = $this->request->getPost('id_user');
        $namaLengkap = (string) $this->request->getPost('nama_lengkap');
        $username    = (string) $this->request->getPost('username');
        $password    = (string) $this->request->getPost('password');
        $roleId      = (int) $this->request->getPost('role_id');

        // Validasi: Cek apakah Username sudah dipakai oleh akun lain
        $builderCek = $this->db->table('users')->where('username', $username);
        if (!empty($idUser)) {
            $builderCek->where('id_user !=', $idUser);
        }
        $cekDuplikat = $builderCek->get()->getRowArray();

        if ($cekDuplikat) {
            return redirect()->back()->withInput()->with('error', 'Gagal: Username "' . $username . '" sudah terdaftar. Silakan gunakan username lain.');
        }

        $dataSave = [
            'nama_lengkap' => $namaLengkap,
            'username'     => $username,
            'role_id'      => $roleId,
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        if (!empty($idUser)) {
            // MODE UPDATE
            if (!empty($password)) {
                // Hanya update password jika field diisi
                $dataSave['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
            }

            $this->db->table('users')->where('id_user', $idUser)->update($dataSave);
            $pesan = "Data pengguna $namaLengkap berhasil diperbarui.";
        } else {
            // MODE TAMBAH BARU
            if (empty($password)) {
                return redirect()->back()->withInput()->with('error', 'Password wajib diisi untuk pengguna baru!');
            }

            $dataSave['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
            $dataSave['created_at']    = date('Y-m-d H:i:s');

            $this->db->table('users')->insert($dataSave);
            $pesan = "Akun baru untuk $namaLengkap berhasil dibuat.";
        }

        return redirect()->to('/admin/user')->with('success', $pesan);
    }

    public function delete(string $id)
    {
        // Pengecekan agar user yang sedang login tidak menghapus akunnya sendiri
        // (Asumsi session id user bernama 'id_user')
        if (session()->get('id_user') === $id) {
            return redirect()->to('/admin/user')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri saat sedang login!');
        }

        $this->db->table('users')->where('id_user', $id)->delete();
        return redirect()->to('/admin/user')->with('success', 'Akun pengguna berhasil dihapus permanen.');
    }
}
