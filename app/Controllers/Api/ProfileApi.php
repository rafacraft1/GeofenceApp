<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class ProfileApi extends ResourceController
{
    public function uploadFoto()
    {
        // 1. Tangkap NIS (dikirim dari Flutter via FormData)
        $nis = $this->request->getPost('nis');

        if (empty($nis)) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'NIS tidak ditemukan dalam request.'
            ], 400);
        }

        // 2. Tangkap File Foto
        $fileFoto = $this->request->getFile('foto');

        if (!$fileFoto || !$fileFoto->isValid() || $fileFoto->hasMoved()) {
            return $this->respond([
                'status'  => 'error',
                'message' => 'File tidak valid atau gagal diunggah.'
            ], 400);
        }

        // 3. Validasi Keamanan File (Wajib Gambar, Maks 2MB)
        $aturanValidasi = [
            'foto' => [
                'label' => 'Foto Profil',
                'rules' => 'uploaded[foto]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]|max_size[foto,2048]',
            ],
        ];

        if (!$this->validate($aturanValidasi)) {
            return $this->respond([
                'status'  => 'error',
                'message' => $this->validator->getErrors()
            ], 400);
        }

        // 4. Generate Nama File Acak (agar aman & tidak bentrok)
        $namaFotoBaru = $fileFoto->getRandomName();

        // 5. Pindahkan ke folder public/uploads/siswa/
        $fileFoto->move(FCPATH . 'uploads/siswa', $namaFotoBaru);

        // 6. Proses Database
        $db = \Config\Database::connect();
        $builder = $db->table('siswa');

        // Cek apakah siswa sudah punya foto sebelumnya
        $siswaLama = $builder->where('nis', $nis)->get()->getRow();

        // Hapus foto lama secara fisik dari server agar tidak jadi sampah
        if ($siswaLama && !empty($siswaLama->foto)) {
            $pathFotoLama = FCPATH . 'uploads/siswa/' . $siswaLama->foto;
            if (file_exists($pathFotoLama)) {
                unlink($pathFotoLama);
            }
        }

        // Simpan nama file baru ke tabel siswa
        $builder->where('nis', $nis)->update(['foto' => $namaFotoBaru]);

        // 7. Kembalikan URL lengkap ke Flutter
        return $this->respond([
            'status'   => 'success',
            'message'  => 'Foto profil berhasil diperbarui.',
            'foto_url' => base_url('uploads/siswa/' . $namaFotoBaru)
        ], 200);
    }
}
