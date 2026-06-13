<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\PengajuanIzinModel;

class IzinApi extends ResourceController
{
    protected $format = 'json';
    protected PengajuanIzinModel $izinModel;

    public function __construct()
    {
        $this->izinModel = new PengajuanIzinModel();
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
    public function ajukan()
    {
        $siswa = $this->getSiswaAuth();

        $aturanValidasi = [
            'tanggal_mulai'   => 'required|valid_date[Y-m-d]',
            'tanggal_selesai' => 'required|valid_date[Y-m-d]',
            'jenis'           => 'required|in_list[Sakit,Izin,Dispensasi]',
            'alasan'          => 'required|min_length[5]',
            'bukti_foto'      => [
                'rules'  => 'uploaded[bukti_foto]|is_image[bukti_foto]|mime_in[bukti_foto,image/jpg,image/jpeg,image/png]|max_size[bukti_foto,2048]',
                'errors' => [
                    'uploaded' => 'Bukti foto wajib dilampirkan.',
                    'is_image' => 'File harus berupa gambar.',
                    'mime_in'  => 'Hanya format JPG/PNG yang diizinkan.',
                    'max_size' => 'Ukuran foto maksimal 2MB.'
                ]
            ]
        ];

        if (!$this->validate($aturanValidasi)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $tglMulai   = (string) $this->request->getPost('tanggal_mulai');
        $tglSelesai = (string) $this->request->getPost('tanggal_selesai');

        if (strtotime($tglSelesai) < strtotime($tglMulai)) {
            return $this->failValidationErrors(['tanggal_selesai' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.']);
        }

        $fotoBukti = $this->request->getFile('bukti_foto');

        if ($fotoBukti && $fotoBukti->isValid() && !$fotoBukti->hasMoved()) {
            $namaBukti = $fotoBukti->getRandomName();
            $fotoBukti->move(FCPATH . 'uploads/izin', $namaBukti);

            try {
                $this->izinModel->insert([
                    'siswa_id'        => $siswa['id_siswa'],
                    'tanggal_mulai'   => $tglMulai,
                    'tanggal_selesai' => $tglSelesai,
                    'jenis'           => (string) $this->request->getPost('jenis'),
                    'alasan'          => (string) $this->request->getPost('alasan'),
                    'bukti_foto'      => $namaBukti,
                    'status'          => 'Pending'
                ]);

                return $this->respondCreated([
                    'status'  => 201,
                    'message' => 'Pengajuan berhasil dikirim dan menunggu persetujuan admin.'
                ]);
            } catch (\Exception $e) {
                $filePath = FCPATH . 'uploads/izin/' . $namaBukti;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                return $this->failServerError('Gagal menyimpan data pengajuan izin ke database.');
            }
        }

        return $this->failValidationErrors('Gagal memproses file foto bukti.');
    }

    /**
     * @return mixed
     */
    public function riwayat()
    {
        $siswa = $this->getSiswaAuth();

        $riwayat = $this->izinModel
            ->where('siswa_id', $siswa['id_siswa'])
            ->orderBy('created_at', 'DESC')
            ->findAll(20);

        return $this->respond([
            'status' => 200,
            'data'   => $riwayat
        ]);
    }
}
