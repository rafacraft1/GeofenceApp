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

    private function getSiswaAuth(): array
    {
        /** @var mixed $request */
        $request = $this->request;
        return (array) $request->siswaAuth;
    }

    public function ajukan()
    {
        $siswa = $this->getSiswaAuth();

        $aturanValidasi = [
            'tanggal_mulai'   => 'required|valid_date[Y-m-d]',
            'tanggal_selesai' => 'required|valid_date[Y-m-d]',
            // PERBAIKAN: Tambahkan Dispensasi ke dalam whitelist validasi
            'jenis'           => 'required|in_list[Sakit,Izin,Dispensasi]',
            'alasan'          => 'required|min_length[5]',
            'bukti_foto'      => [
                'rules'  => 'uploaded[bukti_foto]|is_image[bukti_foto]|mime_in[bukti_foto,image/jpg,image/jpeg,image/png]|max_size[bukti_foto,2048]',
                'errors' => [
                    'uploaded' => 'Bukti foto wajib dilampirkan.',
                    'max_size' => 'Ukuran foto maksimal 2MB.',
                    'is_image' => 'File yang diupload bukan gambar valid.',
                    'mime_in'  => 'Hanya file JPG/PNG yang diizinkan.'
                ]
            ],
        ];

        if (!$this->validate($aturanValidasi)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $tglMulai   = (string) $this->request->getPost('tanggal_mulai');
        $tglSelesai = (string) $this->request->getPost('tanggal_selesai');
        $jenis      = (string) $this->request->getPost('jenis');
        $alasan     = (string) $this->request->getPost('alasan');

        if ($tglMulai > $tglSelesai) {
            return $this->failValidationErrors('Tanggal mulai tidak boleh melewati tanggal selesai.');
        }

        $fileBukti = $this->request->getFile('bukti_foto');

        if ($fileBukti && $fileBukti->isValid() && !$fileBukti->hasMoved()) {
            $namaBukti = $fileBukti->getRandomName();
            $fileBukti->move(FCPATH . 'uploads/izin', $namaBukti);

            try {
                $this->izinModel->insert([
                    'siswa_id'        => $siswa['id_siswa'],
                    'tanggal_mulai'   => $tglMulai,
                    'tanggal_selesai' => $tglSelesai,
                    'jenis'           => $jenis,
                    'alasan'          => $alasan,
                    'bukti_foto'      => $namaBukti,
                    'status'          => 'Pending'
                ]);

                return $this->respondCreated([
                    'status'  => 201,
                    'message' => 'Pengajuan berhasil dikirim dan menunggu persetujuan admin.'
                ]);
            } catch (\Exception $e) {
                if (file_exists(FCPATH . 'uploads/izin/' . $namaBukti)) {
                    unlink(FCPATH . 'uploads/izin/' . $namaBukti);
                }
                return $this->failServerError('Gagal menyimpan data pengajuan izin ke database.');
            }
        }

        return $this->failValidationErrors('Gagal memproses file foto bukti.');
    }

    public function riwayat()
    {
        $siswa = $this->getSiswaAuth();

        $riwayat = $this->izinModel
            ->where('siswa_id', $siswa['id_siswa'])
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->respond([
            'status' => 200,
            'data'   => $riwayat
        ]);
    }
}
