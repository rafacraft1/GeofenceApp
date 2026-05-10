<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\SiswaModel;

class ProfileApi extends ResourceController
{
    protected $format = 'json';
    protected SiswaModel $siswaModel;

    public function __construct()
    {
        $this->siswaModel = new SiswaModel();
    }

    private function getSiswaAuth()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token      = str_replace('Bearer ', '', $authHeader);

        if (empty($token)) return null;

        return $this->siswaModel->where('api_token', $token)->first();
    }

    public function uploadFoto()
    {
        $siswa = $this->getSiswaAuth();
        if (!$siswa) {
            return $this->failUnauthorized('Sesi berakhir atau token tidak valid.');
        }

        $foto = $this->request->getFile('foto');

        // Validasi tambahan untuk keamanan upload dari API
        if ($foto && $foto->isValid() && !$foto->hasMoved()) {

            if (!$foto->getMimeType() || !in_array($foto->getMimeType(), ['image/jpg', 'image/jpeg', 'image/png'])) {
                return $this->failValidationErrors('Hanya file JPG/PNG yang diizinkan.');
            }

            $namaFoto = $foto->getRandomName();
            $foto->move('uploads/siswa', $namaFoto);

            // Hapus foto lama jika ada
            if (!empty($siswa['foto_profil']) && file_exists('uploads/siswa/' . $siswa['foto_profil'])) {
                unlink('uploads/siswa/' . $siswa['foto_profil']);
            }

            $this->siswaModel->update($siswa['id_siswa'], [
                'foto_profil' => $namaFoto
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
