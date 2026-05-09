<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;

class User extends BaseController
{
    public function index()
    {
        // Dropdown hanya mengambil role 'Guru'
        $listRoles = $this->db->table('roles')
            ->where('nama_role', 'Guru')
            ->orderBy('nama_role', 'ASC')
            ->get()->getResultArray();

        // Ambil semua user untuk tabel manajemen
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
        $namaLengkap = trim((string) $this->request->getPost('nama_lengkap'));
        $username    = strtolower(trim((string) $this->request->getPost('username')));
        $password    = (string) $this->request->getPost('password');
        $roleIdInput = $this->request->getPost('role_id');

        // Validasi Duplikasi Username
        $builderCek = $this->db->table('users')->where('username', $username);
        if (!empty($idUser)) $builderCek->where('id_user !=', $idUser);
        if ($builderCek->get()->getRowArray()) {
            return redirect()->back()->withInput()->with('error', "Gagal: Username @$username sudah terdaftar.");
        }

        $dataSave = [
            'nama_lengkap' => $namaLengkap,
            'username'     => $username,
            'updated_at'   => date('Y-m-d H:i:s')
        ];

        if (!empty($idUser)) {
            // --- MODE UPDATE ---
            $currentUser = $this->db->table('users')
                ->select('users.role_id, roles.nama_role')
                ->join('roles', 'roles.id_role = users.role_id', 'left')
                ->where('id_user', $idUser)->get()->getRowArray();

            if (!$currentUser) return redirect()->back()->with('error', 'User tidak ditemukan.');

            $namaRoleSaatIni = strtolower((string) ($currentUser['nama_role'] ?? ''));
            $roleIdToSave = empty($roleIdInput) ? $currentUser['role_id'] : (int) $roleIdInput;

            // PROTEKSI 1: Cegah Admin/Superadmin diubah rolenya
            if (in_array($namaRoleSaatIni, ['admin', 'superadmin']) && (int)$currentUser['role_id'] !== $roleIdToSave) {
                return redirect()->back()->withInput()->with('error', 'Akses Ditolak: Akun Admin tidak dapat diubah ke role lain.');
            }

            // PROTEKSI 2: Cegah Guru naik pangkat menjadi Admin
            if ($namaRoleSaatIni === 'guru') {
                $targetRole = $this->db->table('roles')->where('id_role', $roleIdToSave)->get()->getRowArray();
                $namaTargetRole = strtolower((string)($targetRole['nama_role'] ?? ''));
                if ($namaTargetRole !== 'guru') {
                    return redirect()->back()->withInput()->with('error', 'Akses Ditolak: Akun Guru tidak diizinkan menjadi Administrator.');
                }
            }

            $dataSave['role_id'] = $roleIdToSave;
            if (!empty($password)) $dataSave['password_hash'] = password_hash($password, PASSWORD_BCRYPT);

            $this->db->table('users')->where('id_user', $idUser)->update($dataSave);
            $pesan = "Data $namaLengkap berhasil diperbarui.";
        } else {
            // --- MODE TAMBAH BARU ---
            if (empty($roleIdInput) || empty($password)) {
                return redirect()->back()->withInput()->with('error', 'Role dan Password wajib diisi.');
            }

            // PROTEKSI 3: Pastikan user baru WAJIB Guru
            $roleInfo = $this->db->table('roles')->where('id_role', (int) $roleIdInput)->get()->getRowArray();
            if ($roleInfo && strtolower((string)$roleInfo['nama_role']) !== 'guru') {
                return redirect()->back()->withInput()->with('error', 'Akses Ditolak: Anda hanya boleh membuat akun Guru.');
            }

            $dataSave['role_id']       = (int) $roleIdInput;
            $dataSave['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
            $dataSave['created_at']    = date('Y-m-d H:i:s');

            $this->db->table('users')->insert($dataSave);
            $pesan = "Akun Guru $namaLengkap berhasil dibuat.";
        }

        return redirect()->to('/admin/user')->with('success', $pesan);
    }

    public function reset(string $id)
    {
        $user = $this->db->table('users')
            ->select('users.nama_lengkap, roles.nama_role')
            ->join('roles', 'roles.id_role = users.role_id', 'left')
            ->where('id_user', $id)
            ->get()->getRowArray();

        if (!$user) {
            return redirect()->to('/admin/user')->with('error', 'User tidak ditemukan.');
        }

        // PROTEKSI RESET: Cegah reset password Admin/Superadmin
        $roleName = strtolower((string)($user['nama_role'] ?? ''));
        if (in_array($roleName, ['admin', 'superadmin'])) {
            return redirect()->to('/admin/user')->with('error', 'Akses Ditolak: Password Administrator tidak dapat di-reset melalui fitur ini.');
        }

        $this->db->table('users')->where('id_user', $id)->update([
            'password_hash' => password_hash('guru1234', PASSWORD_BCRYPT),
            'updated_at'    => date('Y-m-d H:i:s')
        ]);

        return redirect()->to('/admin/user')->with('success', "Password untuk " . $user['nama_lengkap'] . " berhasil di-reset menjadi: guru1234");
    }

    public function delete(string $id)
    {
        if ((string) session()->get('id_user') === $id) {
            return redirect()->to('/admin/user')->with('error', 'Tidak bisa menghapus akun sendiri.');
        }
        if ((int) $id === 1) {
            return redirect()->to('/admin/user')->with('error', 'Akun Administrator Sistem tidak boleh dihapus.');
        }

        $this->db->table('users')->where('id_user', $id)->delete();
        return redirect()->to('/admin/user')->with('success', 'Akun berhasil dihapus.');
    }
}
