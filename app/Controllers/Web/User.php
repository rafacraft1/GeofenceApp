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
        ];

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

        if (!empty($id) && (int)$id === 1) {
            $data['role_id'] = 1;
        } else {
            $data['role_id'] = (int) $this->request->getPost('role_id');
        }

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

    public function delete(string $id)
    {
        $currentUserId = session()->get('id_user') ?? session()->get('id') ?? session()->get('user_id');

        if ((int)$id === (int)$currentUserId) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ((int)$id === 1) {
            return redirect()->back()->with('error', 'Akses Ditolak! Akun Administrator Utama dilindungi sistem.');
        }

        $userDB = $this->userModel->find($id);
        if (!empty($userDB['foto']) && file_exists(FCPATH . 'uploads/profiles/' . $userDB['foto'])) {
            unlink(FCPATH . 'uploads/profiles/' . $userDB['foto']);
        }

        $this->userModel->delete($id);
        return redirect()->to(base_url('admin/user'))->with('success', 'User berhasil dihapus.');
    }

    public function reset(string $id)
    {
        $this->userModel->update($id, [
            'password_hash' => password_hash('123456', PASSWORD_BCRYPT)
        ]);

        return redirect()->to(base_url('admin/user'))->with('success', 'Password user berhasil direset ke: 123456');
    }

    public function profile()
    {
        $userId = session()->get('id_user') ?? session()->get('id') ?? session()->get('user_id');

        if ($userId) {
            $user = $this->userModel->find($userId);
        } else {
            $user = $this->userModel->where('nama_lengkap', session()->get('nama_lengkap'))->first();
        }

        if (!$user) {
            $user = [
                'nama_lengkap' => session()->get('nama_lengkap') ?? 'Admin',
                'username'     => session()->get('username') ?? '',
                'foto'         => session()->get('foto') ?? null
            ];
        }

        $data = [
            'title' => 'Pengaturan Profil',
            'user'  => $user
        ];

        return view('web/profile', $data);
    }

    public function updateProfile()
    {
        $userId = session()->get('id_user') ?? session()->get('id') ?? session()->get('user_id');

        if (!$userId) {
            $userDB = $this->userModel->where('nama_lengkap', session()->get('nama_lengkap'))->first();
            $userId = $userDB['id_user'] ?? null;
        }

        if (!$userId) {
            return redirect()->back()->with('error', 'Sesi pengguna tidak valid. Silakan login ulang.');
        }

        $userDB = $this->userModel->find($userId);

        $rules = [
            'nama_lengkap' => 'required|min_length[3]',
            'username'     => "required|min_length[3]|is_unique[users.username,id_user,{$userId}]",
            'foto'         => 'max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]'
        ];

        if ($this->request->getPost('password') || $this->request->getPost('password_lama') || $this->request->getPost('pass_confirm')) {
            $rules['password_lama'] = 'required';
            $rules['password']      = 'required|min_length[6]';
            $rules['pass_confirm']  = 'required|matches[password]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', $this->validator->listErrors());
        }

        $data = [
            'nama_lengkap' => (string) $this->request->getPost('nama_lengkap'),
            'username'     => (string) $this->request->getPost('username'),
        ];

        if ($this->request->getPost('password')) {
            $passwordLama = (string)$this->request->getPost('password_lama');

            // FIX INTELEPHENSE: Explicit string casting untuk hash password dari database
            if (!password_verify($passwordLama, (string) $userDB['password_hash'])) {
                return redirect()->back()->withInput()->with('error', 'Kata sandi lama yang Anda masukkan salah!');
            }

            $data['password_hash'] = password_hash((string)$this->request->getPost('password'), PASSWORD_BCRYPT);
        }

        $fileFoto = $this->request->getFile('foto');
        if ($fileFoto && $fileFoto->isValid() && !$fileFoto->hasMoved()) {
            $namaFotoBaru = $fileFoto->getRandomName();

            if (!empty($userDB['foto']) && file_exists(FCPATH . 'uploads/profiles/' . $userDB['foto'])) {
                unlink(FCPATH . 'uploads/profiles/' . $userDB['foto']);
            }

            $fileFoto->move(FCPATH . 'uploads/profiles', $namaFotoBaru);
            $data['foto'] = $namaFotoBaru;
        }

        $this->userModel->update($userId, $data);

        $sessionData = [
            'nama_lengkap' => $data['nama_lengkap'],
            'username'     => $data['username']
        ];

        if (isset($data['foto'])) {
            $sessionData['foto'] = $data['foto'];
        }

        session()->set($sessionData);

        return redirect()->to(base_url('admin/profile'))->with('success', 'Profil berhasil diperbarui.');
    }

    public function hakAkses()
    {
        $db = \Config\Database::connect();

        $roles     = $db->table('roles')->orderBy('id_role', 'ASC')->get()->getResultArray();
        $menus     = $db->table('menus')->orderBy('urutan', 'ASC')->get()->getResultArray();
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
            return redirect()->back()->with('error', 'Gagal memperbarui konfigurasi hak akses.');
        }

        return redirect()->to(base_url('admin/user/hak-akses'))->with('success', 'Hak akses berhasil diperbarui.');
    }
}
