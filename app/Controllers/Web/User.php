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

    public function index()
    {
        $data = [
            'title' => 'Manajemen User',
            // FIX P1005: Mengirimkan parameter null/false agar model mengembalikan seluruh data (array)
            'users' => $this->userModel->getUserWithRole(''),
            'roles' => $this->roleModel->findAll()
        ];

        return view('web/user', $data);
    }

    public function store()
    {
        $id = $this->request->getPost('id_user');

        $rules = [
            'nama_lengkap' => 'required|min_length[3]',
            'username'     => "required|min_length[3]|is_unique[users.username,id_user,{$id}]",
            'role_id'      => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $data = [
            'nama_lengkap'  => (string) $this->request->getPost('nama_lengkap'),
            'username'      => (string) $this->request->getPost('username'),
            'role_id'       => (int) $this->request->getPost('role_id'),
        ];

        if (empty($id)) {
            $data['password_hash'] = password_hash('123456', PASSWORD_BCRYPT);
            $this->userModel->insert($data);
            $msg = 'User berhasil ditambahkan. Password default: 123456';
        } else {
            $this->userModel->update($id, $data);
            $msg = 'Data user berhasil diperbarui.';
        }

        return redirect()->to(base_url('admin/user'))->with('success', $msg);
    }

    // FIX P1132: Menambahkan type declaration 'string' pada parameter $id
    public function delete(string $id)
    {
        // Pengecekan agar user tidak bisa menghapus akunnya sendiri yang sedang login
        if ((int)$id === (int)session()->get('id_user')) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $this->userModel->delete($id);
        return redirect()->to(base_url('admin/user'))->with('success', 'User berhasil dihapus.');
    }

    // FIX P1132: Menambahkan type declaration 'string' pada parameter $id
    public function reset(string $id)
    {
        $this->userModel->update($id, ['password_hash' => password_hash('123456', PASSWORD_BCRYPT)]);
        return redirect()->to(base_url('admin/user'))->with('success', 'Password user berhasil direset ke: 123456');
    }

    /**
     * ========================================================
     * FUNGSI MANAJEMEN HAK AKSES (RBAC)
     * ========================================================
     */
    public function hakAkses()
    {
        $db = \Config\Database::connect();

        $roles = $db->table('roles')->orderBy('id_role', 'ASC')->get()->getResultArray();
        $menus = $db->table('menus')->orderBy('urutan', 'ASC')->get()->getResultArray();
        $roleMenus = $db->table('role_menus')->get()->getResultArray();

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

    public function saveHakAkses()
    {
        $db = \Config\Database::connect();
        $permissions = $this->request->getPost('permissions');

        $db->transStart();
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
            return redirect()->back()->with('error', 'Gagal memperbarui database hak akses.');
        }

        return redirect()->to(base_url('admin/user/hak-akses'))->with('success', 'Hak akses berhasil diperbarui.');
    }
}
