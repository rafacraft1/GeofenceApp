<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\UserModel;

class User extends BaseController
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    /**
     * Halaman profil user sendiri.
     */
    public function profile()
    {
        $idUser = (int) session()->get('id_user');
        $user   = $this->userModel->find($idUser);

        $data = [
            'title' => 'Pengaturan Profil',
            'user'  => $user
        ];

        return view('web/profile', $data);
    }

    /**
     * Simpan perubahan profil user sendiri.
     */
    public function updateProfile()
    {
        $idUser = (int) session()->get('id_user');
        $roleId = (int) session()->get('role_id');
        $user   = $this->userModel->find($idUser);

        $rules = [
            'nama_lengkap' => 'required|min_length[3]|max_length[100]',
            'foto'         => 'max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]'
        ];

        if ($roleId !== 1) {
            $rules['username'] = "required|min_length[4]|max_length[50]|is_unique[users.username,id_user,{$idUser}]";
        }

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            return redirect()->back()->withInput()->with('error', reset($errors));
        }

        $updateData = [
            'nama_lengkap' => (string) $this->request->getPost('nama_lengkap'),
        ];

        if ($roleId !== 1) {
            $updateData['username'] = (string) $this->request->getPost('username');
        }

        $password = (string) $this->request->getPost('password');
        if (!empty($password)) {
            $updateData['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $foto = $this->request->getFile('foto');
        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $newName = $foto->getRandomName();
            $foto->move(FCPATH . 'uploads/profiles', $newName);
            $updateData['foto'] = $newName;

            if (!empty($user['foto']) && file_exists(FCPATH . 'uploads/profiles/' . $user['foto'])) {
                unlink(FCPATH . 'uploads/profiles/' . $user['foto']);
            }
            session()->set('foto', $newName);
        }

        $this->userModel->update($idUser, $updateData);

        session()->set(['nama_lengkap' => $updateData['nama_lengkap']]);

        if ($roleId !== 1 && isset($updateData['username'])) {
            session()->set('username', $updateData['username']);
        }

        return redirect()->to('/admin/profile')->with('success', 'Data profil berhasil diperbarui.');
    }

    /**
     * Halaman daftar user/guru dengan info wali kelas (FITUR 3).
     */
    public function index()
    {
        // Join kelas untuk mendapatkan info wali kelas per user (FITUR 3)
        $users = $this->userModel
            ->select('users.*, roles.nama_role, roles.warna_badge, kelas.nama_kelas as wali_kelas_nama, kelas.id_kelas as wali_kelas_id_kelas')
            ->join('roles', 'roles.id_role = users.role_id')
            ->join('kelas', 'kelas.wali_kelas_id = users.id_user', 'left')
            ->orderBy('users.role_id', 'ASC')
            ->orderBy('users.nama_lengkap', 'ASC')
            ->findAll();

        $roles = $this->db->table('roles')->get()->getResultArray();

        // FITUR 4: Hitung jumlah user per role untuk summary counter
        $summary = [];
        foreach ($users as $u) {
            $roleName = $u['nama_role'] ?? 'Unknown';
            $summary[$roleName] = ($summary[$roleName] ?? 0) + 1;
        }

        // Daftar kelas untuk dropdown pilihan wali kelas (FITUR 3)
        $listKelas = $this->db->table('kelas')
            ->select('id_kelas, nama_kelas')
            ->orderBy('nama_kelas', 'ASC')
            ->get()
            ->getResultArray();

        $data = [
            'title'      => 'Data Guru & Administrator',
            'users'      => $users,
            'roles'      => $roles,
            'summary'    => $summary,
            'listKelas'  => $listKelas,
        ];

        return view('web/user', $data);
    }

    /**
     * Tambah atau Edit user (Tambah = tidak ada id_user, Edit = ada id_user).
     * FITUR 2: Mendukung field password + foto.
     */
    public function store()
    {
        $idUser = $this->request->getPost('id_user');
        $isEdit = !empty($idUser);

        $ruleUsername = $isEdit
            ? "required|min_length[4]|max_length[50]|is_unique[users.username,id_user,{$idUser}]"
            : 'required|min_length[4]|max_length[50]|is_unique[users.username]';

        $rules = [
            'nama_lengkap' => 'required|min_length[3]|max_length[100]',
            'username'     => $ruleUsername,
            'role_id'      => 'required|integer',
            'foto'         => 'max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]',
        ];

        // Password wajib saat tambah, opsional saat edit
        if (!$isEdit) {
            $rules['password'] = 'permit_empty|min_length[6]';
        }

        if (!$this->validate($rules)) {
            $errors = $this->validator->getErrors();
            return redirect()->back()->withInput()->with('error', 'Validasi gagal: ' . reset($errors));
        }

        $data = [
            'nama_lengkap' => (string) $this->request->getPost('nama_lengkap'),
            'username'     => (string) $this->request->getPost('username'),
            'role_id'      => (int) $this->request->getPost('role_id'),
        ];

        // --- Penanganan Password (FITUR 2) ---
        $password = trim((string) $this->request->getPost('password'));
        if ($isEdit) {
            // Edit: hanya update jika field password diisi
            if (!empty($password)) {
                $data['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
            }
        } else {
            // Tambah: jika kosong pakai default '123456'
            if (empty($password)) {
                $password = '123456';
            }
            $data['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
        }

        // --- Penanganan Foto (FITUR 7) ---
        $foto = $this->request->getFile('foto');
        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $newName = $foto->getRandomName();
            $foto->move(FCPATH . 'uploads/profiles', $newName);
            $data['foto'] = $newName;

            // Hapus foto lama jika edit
            if ($isEdit) {
                $oldUser = $this->userModel->find((int) $idUser);
                if ($oldUser && !empty($oldUser['foto']) && file_exists(FCPATH . 'uploads/profiles/' . $oldUser['foto'])) {
                    unlink(FCPATH . 'uploads/profiles/' . $oldUser['foto']);
                }
            }
        }

        if ($isEdit) {
            $this->userModel->update((int) $idUser, $data);
            $msg = 'Data pengguna berhasil diperbarui.';
        } else {
            $this->userModel->insert($data);
            $msg = 'Pengguna baru berhasil ditambahkan. Password default: ' . $password;
        }

        return redirect()->to('/admin/user')->with('success', $msg);
    }

    /**
     * Hapus user (dilindungi dari hapus Superadmin & diri sendiri).
     */
    public function delete(string $id)
    {
        $idTarget = (int) $id;

        if ($idTarget === 1 || $idTarget === (int) session()->get('id_user')) {
            return redirect()->back()->with('error', 'Keamanan: Akun Superadmin atau akun Anda sendiri tidak dapat dihapus.');
        }

        // Hapus foto profil jika ada
        $userToDelete = $this->userModel->find($idTarget);
        if ($userToDelete && !empty($userToDelete['foto'])) {
            $fotoPath = FCPATH . 'uploads/profiles/' . $userToDelete['foto'];
            if (file_exists($fotoPath)) {
                unlink($fotoPath);
            }
        }

        $this->userModel->delete($idTarget);

        return redirect()->to('/admin/user')->with('success', 'Akun pengguna berhasil dihapus.');
    }

    /**
     * Reset password user ke default '123456'.
     */
    public function reset(string $id)
    {
        $idTarget = (int) $id;

        if ($idTarget === 1) {
            return redirect()->back()->with('error', 'Keamanan: Kata sandi Superadmin tidak dapat direset dari luar profil.');
        }

        $defaultPassword = password_hash('123456', PASSWORD_BCRYPT);
        $this->userModel->update($idTarget, ['password_hash' => $defaultPassword]);

        return redirect()->to('/admin/user')->with('success', 'Sandi pengguna berhasil direset menjadi: 123456');
    }

    /**
     * Halaman manajemen hak akses per role.
     */
    public function hakAkses()
    {
        $roles    = $this->db->table('roles')->get()->getResultArray();
        $menus    = $this->db->table('menus')->orderBy('urutan', 'ASC')->get()->getResultArray();
        $roleMenus = $this->db->table('role_menus')->get()->getResultArray();

        $akses = [];
        foreach ($roleMenus as $rm) {
            $akses[$rm['id_role']][] = $rm['id_menu'];
        }

        $data = [
            'title'  => 'Pengaturan Hak Akses',
            'roles'  => $roles,
            'menus'  => $menus,
            'akses'  => $akses
        ];

        return view('web/hak_akses', $data);
    }

    /**
     * Simpan konfigurasi hak akses per role.
     */
    public function saveHakAkses()
    {
        $permissions = $this->request->getPost('permissions');

        // Hapus semua hak akses (kecuali Superadmin/role 1)
        $this->db->table('role_menus')->where('id_role !=', 1)->delete();

        if (!empty($permissions) && is_array($permissions)) {
            $insertData = [];

            foreach ($permissions as $roleId => $menuIds) {
                if ($roleId == 1) continue;

                foreach ($menuIds as $menuId) {
                    $insertData[] = [
                        'id_role' => (int) $roleId,
                        'id_menu' => (int) $menuId
                    ];
                }

                cache()->delete('global_menus_role_' . $roleId);
            }

            if (!empty($insertData)) {
                $this->db->table('role_menus')->insertBatch($insertData);
            }
        }

        return redirect()->to('/admin/user/hak-akses')->with('success', 'Pengaturan hak akses berhasil diperbarui.');
    }
}
