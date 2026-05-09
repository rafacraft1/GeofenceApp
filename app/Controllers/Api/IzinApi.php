<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;

class IzinApi extends ResourceController
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
        $token = \str_replace('Bearer ', '', $authHeader);
        if (empty($token)) return null;

        return $this->db->table('siswa')->where('api_token', $token)->get()->getRowArray();
    }

    public function ajukan()
    {
        $siswa = $this->getSiswaAuth();
        if (!$siswa) {
            return $this->failUnauthorized('Sesi berakhir atau token tidak valid.');
        }

        $aturanValidasi = [
            'tanggal_mulai'   => 'required|valid_date[Y-m-d]',
            'tanggal_selesai' => 'required|valid_date[Y-m-d]',
            'jenis'           => 'required|in_list[Sakit,Izin]',
            'alasan'          => 'required|min_length[5]',
            'bukti_foto'      => [
                'rules'  => 'uploaded[bukti_foto]|is_image[bukti_foto]|mime_in[bukti_foto,image/jpg,image/jpeg,image/png]|max_size[bukti_foto,2048]',
                'errors' => [
                    'uploaded' => 'Bukti foto wajib dilampirkan.',
                    'max_size' => 'Ukuran foto maksimal 2MB.',
                    'is_image' => 'File yang diupload bukan gambar valid.'
                ]
            ],
        ];

        if (!$this->validate($aturanValidasi)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $tglMulai = $this->request->getPost('tanggal_mulai');
        $tglSelesai = $this->request->getPost('tanggal_selesai');

        if ($tglMulai > $tglSelesai) {
            return $this->failValidationErrors('Tanggal mulai tidak boleh melewati tanggal selesai.');
        }

        // Proses Upload File
        $fileBukti = $this->request->getFile('bukti_foto');
        $namaBukti = $fileBukti->getRandomName();
        $fileBukti->move(FCPATH . 'uploads/izin', $namaBukti);

        // Simpan ke database
        $this->db->table('pengajuan_izin')->insert([
            'siswa_id'        => $siswa['id_siswa'],
            'tanggal_mulai'   => $tglMulai,
            'tanggal_selesai' => $tglSelesai,
            'jenis'           => $this->request->getPost('jenis'),
            'alasan'          => $this->request->getPost('alasan'),
            'bukti_foto'      => $namaBukti,
            'status'          => 'Pending',
            'created_at'      => date('Y-m-d H:i:s'),
            'updated_at'      => date('Y-m-d H:i:s')
        ]);

        return $this->respondCreated([
            'status'  => 'success',
            'message' => 'Pengajuan berhasil dikirim dan menunggu persetujuan admin.'
        ]);
    }
}
