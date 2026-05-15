<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\KelasModel;

class User extends BaseController
{
    protected UserModel $userModel;
    protected RoleModel $roleModel;
    protected KelasModel $kelasModel;

    public function __construct()
    {
        $this->userModel  = new UserModel();
        $this->roleModel  = new RoleModel();
        $this->kelasModel = new KelasModel();
    }

    /**
     * Menampilkan daftar user
     */
    public function index()
    {
        $data = [
            'title' => 'Manajemen User',
            // Mengirim string kosong agar Model mengembalikan semua data user
            'users' => $this->userModel->getUserWithRole(''),
            'roles' => $this->roleModel->findAll()
        ];

        return view('web/user', $data);
    }

    /**
     * Simpan atau Update data user
     */
    public function store()
    {
        $id = $this->request->getPost('id_user');

        $rules = [
            'nama_lengkap' => 'required|min_length[3]',
            'username'     => "required|min_length[3]|is_unique[users.username,id_user,{$id}]",
        ];

        // Role wajib diisi kecuali untuk edit Administrator Utama (ID 1)
        if (empty($id) || (int)$id !== 1) {
            $rules['role_id'] = 'required';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $data = [
            'nama_lengkap' => (string) $this->request->getPost('nama_lengkap'),
            'username'     => (string) $this->request->getPost('username'),
        ];

        // PROTEKSI: Jika ID 1, paksa role_id tetap 1 (Admin)
        if (!empty($id) && (int)$id === 1) {
            $data['role_id'] = 1;
        } else {
            $data['role_id'] = (int) $this->request->getPost('role_id');
        }

        if (empty($id)) {
            // Password default untuk user baru
            $data['password_hash'] = password_hash('123456', PASSWORD_BCRYPT);
            $this->userModel->insert($data);
            $msg = 'User berhasil ditambahkan. Password default: 123456';
        } else {
            $this->userModel->update($id, $data);
            $msg = 'Data user berhasil diperbarui.';
        }

        return redirect()->to(base_url('admin/user'))->with('success', $msg);
    }

    /**
     * Hapus data user dengan proteksi sistem
     */
    public function delete(string $id)
    {
        // Proteksi 1: Tidak boleh menghapus diri sendiri
        if ((int)$id === (int)session()->get('id_user')) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Proteksi 2: Tidak boleh menghapus Administrator Utama (ID 1)
        if ((int)$id === 1) {
            return redirect()->back()->with('error', 'Akses Ditolak! Akun Administrator Utama dilindungi sistem.');
        }

        $this->userModel->delete($id);
        return redirect()->to(base_url('admin/user'))->with('success', 'User berhasil dihapus.');
    }

    /**
     * Reset password ke default (123456)
     */
    public function reset(string $id)
    {
        $this->userModel->update($id, [
            'password_hash' => password_hash('123456', PASSWORD_BCRYPT)
        ]);

        return redirect()->to(base_url('admin/user'))->with('success', 'Password user berhasil direset ke: 123456');
    }

    /**
     * ========================================================
     * MANAJEMEN HAK AKSES (RBAC MATRIX)
     * ========================================================
     */

    /**
     * Menampilkan matriks konfigurasi hak akses menu
     */
    public function hakAkses()
    {
        $db = \Config\Database::connect();

        $roles     = $db->table('roles')->orderBy('id_role', 'ASC')->get()->getResultArray();
        $menus     = $db->table('menus')->orderBy('urutan', 'ASC')->get()->getResultArray();
        $roleMenus = $db->table('role_menus')->get()->getResultArray();

        // Mapping data akses ke array [role_id][menu_id]
        $access = [];
        foreach ($roleMenus as $rm) {
            $access[$rm['id_role']][$rm['id_menu']] = true;
        }

        $data = [
            'title'  => 'Konfigurasi Hak Akses',
            'roles'  => $roles,
            'menus'  => $menus,
            'access' => $access
        ];

        return view('web/hak_akses', $data);
    }

    /**
     * Menyimpan perubahan hak akses secara massal
     */
    public function saveHakAkses()
    {
        $db = \Config\Database::connect();
        $permissions = $this->request->getPost('permissions');

        $db->transStart();

        // Reset/Hapus semua relasi role_menus lama
        $db->table('role_menus')->truncate();

        if (!empty($permissions) && is_array($permissions)) {
            $insertData = [];
            foreach ($permissions as $roleId => $menuIds) {
                foreach ($menuIds as $menuId) {
                    $insertData[] = [
                        'id_role' => (int) $roleId,
                        'id_menu' => (int) $menuId
                    ];
                }
            }

            if (!empty($insertData)) {
                $db->table('role_menus')->insertBatch($insertData);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->back()->with('error', 'Gagal memperbarui konfigurasi hak akses.');
        }

        return redirect()->to(base_url('admin/user/hak-akses'))->with('success', 'Hak akses berhasil diperbarui.');
    }
}
