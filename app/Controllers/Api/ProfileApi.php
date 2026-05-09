<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class ProfileApi extends ResourceController
{
    protected $format = 'json';
    protected \CodeIgniter\Database\BaseConnection $db;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
    }

    private function getSiswaAuth()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token      = str_replace('Bearer ', '', $authHeader);

        if (empty($token)) return null;

        return $this->db->table('siswa')->where('api_token', $token)->get()->getRowArray();
    }

    public function uploadFoto()
    {
        $siswa = $this->getSiswaAuth();
        if (!$siswa) {
            return $this->failUnauthorized('Sesi berakhir atau token tidak valid.');
        }

        $foto = $this->request->getFile('foto');

        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $namaFoto = $foto->getRandomName();
            $foto->move(FCPATH . 'uploads/siswa', $namaFoto);

            // Hapus foto lama jika ada
            if (!empty($siswa['foto_profil']) && file_exists(FCPATH . 'uploads/siswa/' . $siswa['foto_profil'])) {
                unlink(FCPATH . 'uploads/siswa/' . $siswa['foto_profil']);
            }

            $this->db->table('siswa')->where('id_siswa', $siswa['id_siswa'])->update([
                'foto_profil' => $namaFoto,
                'updated_at'  => date('Y-m-d H:i:s')
            ]);

            return $this->respond([
                'status'      => 200,
                'message'     => 'Foto profil berhasil diperbarui.',
                'foto_profil' => $namaFoto
            ]);
        }

        return $this->failValidationErrors('File foto tidak valid atau terlalu besar.');
    }
}
