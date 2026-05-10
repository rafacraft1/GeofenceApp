<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use App\Models\SiswaModel;

class TrackingApi extends ResourceController
{
    protected $format = 'json';
    protected SiswaModel $siswaModel;

    public function __construct()
    {
        $this->siswaModel = new SiswaModel();
    }

    private function getSiswaAuth(): array
    {
        /** @var mixed $request */
        $request = $this->request;
        return (array) $request->siswaAuth;
    }

    public function updateLokasi()
    {
        $siswa = $this->getSiswaAuth();

        $aturanValidasi = [
            'lat'  => 'required|numeric',
            'long' => 'required|numeric'
        ];

        if (!$this->validate($aturanValidasi)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // Pemicu update timestamps (Internal Monitoring)
        // Kita force isi array data agar CI4 mentrigger event beforeUpdate/afterUpdate
        $this->siswaModel->update($siswa['id_siswa'], [
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        return $this->respond(['status' => 200, 'message' => 'Lokasi berhasil diperbarui.']);
    }

    public function pingSiswa(string $targetId)
    {
        helper('fcm');

        $siswa = $this->siswaModel->find($targetId);

        if (!$siswa || empty($siswa['fcm_token'])) {
            return $this->failNotFound('Siswa tidak ditemukan atau perangkat sedang offline.');
        }

        $result = send_fcm_notification(
            (string) $siswa['fcm_token'],
            "PING_LOCATION",
            "Permintaan lokasi real-time dari Admin.",
            ['action' => 'fetch_location']
        );

        if ($result) {
            return $this->respond(['status' => 200, 'message' => 'Sinyal ping terkirim ke perangkat siswa.']);
        }

        return $this->failServerError('Gagal mengirim sinyal ping.');
    }
}
