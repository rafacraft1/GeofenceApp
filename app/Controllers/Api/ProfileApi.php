<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class ProfileApi extends ResourceController
{
    public function uploadFoto()
    {
        $nis = $this->request->getPost('nis');
        if (empty($nis)) return $this->respond(['status' => 'error', 'message' => 'NIS tidak ditemukan.'], 400);

        $fileFoto = $this->request->getFile('foto');
        if (!$fileFoto || !$fileFoto->isValid() || $fileFoto->hasMoved()) {
            return $this->respond(['status' => 'error', 'message' => 'File tidak valid.'], 400);
        }

        $aturanValidasi = [
            'foto' => [
                'rules' => 'uploaded[foto]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]|max_size[foto,2048]',
            ],
        ];

        if (!$this->validate($aturanValidasi)) return $this->respond(['status' => 'error', 'message' => $this->validator->getErrors()], 400);

        $namaFotoBaru = $fileFoto->getRandomName();
        $fileFoto->move(FCPATH . 'uploads/siswa', $namaFotoBaru);

        $db = \Config\Database::connect();
        $builder = $db->table('siswa');

        $siswaLama = $builder->where('nis', $nis)->get()->getRowArray();

        if ($siswaLama && !empty($siswaLama['foto_profil'])) {
            $pathFotoLama = FCPATH . 'uploads/siswa/' . $siswaLama['foto_profil'];
            if (file_exists($pathFotoLama)) unlink($pathFotoLama);
        }

        $builder->where('nis', $nis)->update(['foto_profil' => $namaFotoBaru]);

        return $this->respond([
            'status'   => 'success',
            'message'  => 'Foto profil berhasil diperbarui.',
            'foto_url' => base_url('uploads/siswa/' . $namaFotoBaru)
        ], 200);
    }
}
