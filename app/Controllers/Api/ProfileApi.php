<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class ProfileApi extends ResourceController
{
    private function getSiswaAuth()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token = \str_replace('Bearer ', '', $authHeader);
        if (empty($token)) return null;

        $db = \Config\Database::connect();
        return $db->table('siswa')->where('api_token', $token)->get()->getRowArray();
    }

    public function uploadFoto()
    {
        // PERBAIKAN KEAMANAN: Autentikasi berdasarkan Token, bukan input NIS dari POST
        $siswaAuth = $this->getSiswaAuth();
        if (!$siswaAuth) {
            return $this->respond(['status' => 'error', 'message' => 'Sesi tidak valid atau token kadaluarsa.'], 401);
        }

        $fileFoto = $this->request->getFile('foto');
        if (!$fileFoto || !$fileFoto->isValid() || $fileFoto->hasMoved()) {
            return $this->respond(['status' => 'error', 'message' => 'File gambar tidak ditemukan atau rusak.'], 400);
        }

        $aturanValidasi = [
            'foto' => [
                'rules' => 'uploaded[foto]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]|max_size[foto,2048]',
                'errors' => [
                    'max_size' => 'Ukuran foto maksimal 2MB.',
                    'mime_in'  => 'Format foto harus berupa JPG atau PNG.',
                    'is_image' => 'File yang diupload bukan gambar valid.'
                ]
            ],
        ];

        if (!$this->validate($aturanValidasi)) {
            return $this->respond(['status' => 'error', 'message' => $this->validator->getErrors()], 400);
        }

        $namaFotoBaru = $fileFoto->getRandomName();
        $fileFoto->move(FCPATH . 'uploads/siswa', $namaFotoBaru);

        $db = \Config\Database::connect();

        // Hapus file lama jika ada
        if (!empty($siswaAuth['foto_profil'])) {
            $pathFotoLama = FCPATH . 'uploads/siswa/' . $siswaAuth['foto_profil'];
            if (file_exists($pathFotoLama)) {
                unlink($pathFotoLama);
            }
        }

        $db->table('siswa')->where('id_siswa', $siswaAuth['id_siswa'])->update([
            'foto_profil' => $namaFotoBaru,
            'updated_at'  => date('Y-m-d H:i:s')
        ]);

        return $this->respond([
            'status'   => 'success',
            'message'  => 'Foto profil berhasil diperbarui.',
            'foto_url' => base_url('uploads/siswa/' . $namaFotoBaru)
        ], 200);
    }
}
