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

    /**
     * @return array
     */
    private function getSiswaAuth(): array
    {
        return (array) ($this->request->siswaAuth ?? []);
    }

    /**
     * @return mixed
     */
    public function uploadFoto()
    {
        $siswa = $this->getSiswaAuth();

        $aturanValidasi = [
            'foto' => [
                'rules'  => 'uploaded[foto]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]|max_size[foto,2048]',
                'errors' => [
                    'uploaded' => 'File foto wajib dipilih.',
                    'is_image' => 'File harus berupa gambar.',
                    'mime_in'  => 'Hanya file JPG/PNG yang diizinkan.',
                    'max_size' => 'Ukuran foto maksimal 2MB.'
                ]
            ]
        ];

        if (!$this->validate($aturanValidasi)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $foto = $this->request->getFile('foto');

        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $namaFoto = $foto->getRandomName();
            $foto->move(FCPATH . 'uploads/siswa', $namaFoto);

            if (!empty($siswa['foto_profil'])) {
                $safeOldFileName = basename($siswa['foto_profil']);
                $oldFilePath     = FCPATH . 'uploads/siswa/' . $safeOldFileName;

                if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                    unlink($oldFilePath);
                }
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

        return $this->failServerError('Gagal memproses file foto.');
    }
}
