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
     * @return mixed
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
     * @return mixed
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
     * @return mixed
     */
    public function index()
    {
        $users = $this->userModel->select('users.*, roles.nama_role, roles.warna_badge')
            ->join('roles', 'roles.id_role = users.role_id')
            ->orderBy('users.role_id', 'ASC')
            ->orderBy('users.nama_lengkap', 'ASC')
            ->findAll();

        $roles = $this->db->table('roles')->get()->getResultArray();

        $data = [
            'title' => 'Data Guru & Administrator',
            'users' => $users,
            'roles' => $roles
        ];

        return view('web/user', $data);
    }

    /**
     * @return mixed
     */
    public function store()
    {
        $idUser = $this->request->getPost('id_user');

        $ruleUsername = empty($idUser) ? 'required|is_unique[users.username]' : "required|is_unique[users.username,id_user,{$idUser}]";

        $rules = [
            'nama_lengkap' => 'required',
            'username'     => $ruleUsername,
            'role_id'      => 'required|integer'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Validasi gagal. Pastikan username unik dan form terisi benar.');
        }

        $data = [
            'nama_lengkap' => (string) $this->request->getPost('nama_lengkap'),
            'username'     => (string) $this->request->getPost('username'),
            'role_id'      => (int) $this->request->getPost('role_id'),
        ];

        if (empty($idUser)) {
            $password = (string) $this->request->getPost('password');
            if (empty($password)) $password = '123456';
            $data['password_hash'] = password_hash($password, PASSWORD_BCRYPT);

            $this->userModel->insert($data);
            $msg = 'Pengguna baru berhasil ditambahkan.';
        } else {
            $password = (string) $this->request->getPost('password');
            if (!empty($password)) {
                $data['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
            }
            $this->userModel->update($idUser, $data);
            $msg = 'Data pengguna berhasil diperbarui.';
        }

        return redirect()->to('/admin/user')->with('success', $msg);
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function delete(string $id)
    {
        $idTarget = (int) $id;

        if ($idTarget === 1 || $idTarget === (int) session()->get('id_user')) {
            return redirect()->back()->with('error', 'Keamanan: Akun Superadmin atau akun Anda sendiri tidak dapat dihapus.');
        }

        $this->userModel->delete($idTarget);

        return redirect()->to('/admin/user')->with('success', 'Akun pengguna berhasil dihapus.');
    }

    /**
     * @param string $id
     * @return mixed
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
     * @return mixed
     */
    public function hakAkses()
    {
        $roles = $this->db->table('roles')->get()->getResultArray();
        $menus = $this->db->table('menus')->orderBy('urutan', 'ASC')->get()->getResultArray();
        $roleMenus = $this->db->table('role_menus')->get()->getResultArray();

        $akses = [];
        foreach ($roleMenus as $rm) {
            $akses[$rm['id_role']][] = $rm['id_menu'];
        }

        $data = [
            'title' => 'Pengaturan Hak Akses',
            'roles' => $roles,
            'menus' => $menus,
            'akses' => $akses
        ];

        return view('web/hak_akses', $data);
    }

    /**
     * @return mixed
     */
    public function saveHakAkses()
    {
        // PERBAIKAN: Ambil data matriks permissions dari form
        $permissions = $this->request->getPost('permissions');

        // Hapus semua hak akses terlebih dahulu (Kecuali Role 1 / Superadmin)
        $this->db->table('role_menus')->where('id_role !=', 1)->delete();

        if (!empty($permissions) && is_array($permissions)) {
            $insertData = [];

            // Looping berdasarkan role
            foreach ($permissions as $roleId => $menuIds) {
                // Lewati role 1 karena bersifat mutlak
                if ($roleId == 1) continue;

                // Looping berdasarkan menu yang dicentang pada role tersebut
                foreach ($menuIds as $menuId) {
                    $insertData[] = [
                        'id_role' => (int) $roleId,
                        'id_menu' => (int) $menuId
                    ];
                }

                // Hapus cache untuk role ini (jika Anda menggunakan sistem caching menu)
                cache()->delete('global_menus_role_' . $roleId);
            }

            // Simpan batch ke database
            if (!empty($insertData)) {
                $this->db->table('role_menus')->insertBatch($insertData);
            }
        }

        return redirect()->to('/admin/user/hak-akses')->with('success', 'Pengaturan hak akses berhasil diperbarui.');
    }
}
