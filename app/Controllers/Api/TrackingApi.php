<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\I18n\Time;
use Kreait\Firebase\Factory;

class TrackingApi extends ResourceController
{
    protected $format = 'json';

    public function update_lokasi()
    {
        $authHeader = $this->request->getHeaderLine('Authorization');
        $token      = \str_replace('Bearer ', '', $authHeader);

        if (empty($token)) return $this->failUnauthorized('Token tidak ditemukan.');

        $db = \Config\Database::connect();

        $siswa = $db->table('siswa')
            ->select('siswa.*, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left')
            ->where('api_token', $token)
            ->get()
            ->getRowArray();

        if (!$siswa || $siswa['is_blocked'] == 1) return $this->failUnauthorized('Sesi tidak valid atau akun diblokir.');

        $rawLat = $this->request->getPost('lat');
        $rawLon = $this->request->getPost('long');

        if ($rawLat === null || $rawLon === null || $rawLat === '' || $rawLon === '') {
            return $this->failValidationErrors('Koordinat latitude dan longitude wajib dikirim.');
        }

        $lat   = (float) $rawLat;
        $lon   = (float) $rawLon;
        $waktu = Time::now('Asia/Jakarta')->toDateTimeString();

        // PERBAIKAN: Blok insert riwayat_lokasi Dihapus (Mencegah Fatal Error dan Database Bloat)

        $config = $db->table('pengaturan')->where('id_pengaturan', 1)->get()->getRowArray();

        if (!$config || empty($config['firebase_url'])) {
            return $this->respondCreated(['status' => 200, 'message' => 'Pelacakan lokal diabaikan. Firebase belum disetting.']);
        }

        // PERBAIKAN: Cek apakah file credential JSON benar-benar ada untuk mencegah 500 Server Error
        $credentialPath = APPPATH . 'Config/firebase_credentials.json';
        if (!file_exists($credentialPath)) {
            return $this->respondCreated(['status' => 200, 'message' => 'Credential Firebase tidak ditemukan, tracking dibypass.']);
        }

        try {
            $factory = (new Factory)
                ->withServiceAccount($credentialPath)
                ->withDatabaseUri($config['firebase_url']);

            $database = $factory->createDatabase();

            $database->getReference('live_tracking/' . $siswa['id_siswa'])->set([
                'lat'   => $lat,
                'long'  => $lon,
                'waktu' => $waktu,
                'nama'  => $siswa['nama_siswa'],
                'kelas' => $siswa['nama_kelas'] ?? 'Belum ada kelas'
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Firebase Error: ' . $e->getMessage());
            return $this->respondCreated(['status' => 200, 'message' => 'Gagal sinkronisasi Firebase. (Bypass)']);
        }

        return $this->respondCreated(['status' => 200, 'message' => 'Lokasi berhasil diperbarui dan disinkronkan ke Firebase.']);
    }

    public function ping_siswa($siswa_id = null)
    {
        if (!$siswa_id) return $this->failValidationErrors('ID Siswa wajib diisi.');

        $db = \Config\Database::connect();
        $siswa = $db->table('siswa')->where('id_siswa', $siswa_id)->get()->getRowArray();

        if (!$siswa) return $this->failNotFound('Data siswa tidak ditemukan.');
        if (empty($siswa['fcm_token'])) return $this->fail('Siswa ini belum memiliki FCM Token (Belum login di aplikasi terbaru).');

        $credentialPath = APPPATH . 'Config/firebase_credentials.json';
        if (!file_exists($credentialPath)) {
            return $this->fail('File kredensial Firebase belum dikonfigurasi di server.');
        }

        try {
            $factory = (new Factory)->withServiceAccount($credentialPath);
            $messaging = $factory->createMessaging();

            $message = \Kreait\Firebase\Messaging\CloudMessage::fromArray([
                'token' => $siswa['fcm_token'],
                'data'  => [
                    'action'    => 'TRACKING_REQUEST',
                    'timestamp' => (string)time()
                ],
                'android' => ['priority' => 'high']
            ]);

            $messaging->send($message);

            return $this->respond(['status' => 200, 'message' => 'Sinyal pelacakan berhasil ditembakkan ke HP Siswa. Menunggu respon dari aplikasi...']);
        } catch (\Exception $e) {
            log_message('error', 'Gagal mengirim FCM: ' . $e->getMessage());
            return $this->fail('Gagal mengirim sinyal pelacakan. Error: ' . $e->getMessage());
        }
    }
}
