<?php

namespace App\Controllers\Web;

use CodeIgniter\Controller;
use App\Models\SiswaModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Siswa extends Controller
{
    protected \CodeIgniter\Database\BaseConnection $db;
    protected SiswaModel $siswaModel;

    public function __construct()
    {
        $this->db = \Config\Database::connect();
        $this->siswaModel = new SiswaModel();
    }

    public function index()
    {
        $kelasFilter = $this->request->getGet('kelas');

        // Untuk tabel selain siswa, kita tetap bisa pakai DB Builder atau model masing-masing jika ada
        $listKelas = $this->db->table('kelas')
            ->orderBy('nama_kelas', 'ASC')
            ->get()
            ->getResultArray();

        $pager   = \Config\Services::pager();
        $page    = (int) ($this->request->getGet('page') ?? 1);
        $perPage = 10;

        $this->siswaModel->select('siswa.*, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if (!empty($kelasFilter)) {
            $this->siswaModel->where('siswa.kelas_id', $kelasFilter);
        }

        $totalData = $this->siswaModel->countAllResults(false);
        $offset = ($page - 1) * $perPage;

        $siswa = $this->siswaModel->orderBy('kelas.nama_kelas', 'ASC')
            ->orderBy('siswa.nama_siswa', 'ASC')
            ->findAll($perPage, $offset);

        $data = [
            'title'       => 'Daftar Siswa',
            'siswa'       => $siswa,
            'list_kelas'  => $listKelas,
            'kelas_aktif' => $kelasFilter,
            'pager_links' => $pager->makeLinks($page, $perPage, $totalData, 'default_full'),
            'page'        => $page,
            'perPage'     => $perPage,
            'total_data'  => $totalData
        ];

        return view('web/siswa', $data);
    }

    public function store()
    {
        $foto = $this->request->getFile('foto');
        $namaFoto = null;

        // Validasi dan Upload Foto
        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $aturanValidasi = [
                'foto' => [
                    'label'  => 'Foto Profil',
                    'rules'  => 'max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]',
                    'errors' => [
                        'max_size' => 'Ukuran foto maksimal 2MB.',
                        'is_image' => 'File harus berupa gambar.',
                        'mime_in'  => 'Format foto harus JPG/JPEG/PNG.'
                    ]
                ]
            ];

            if (!$this->validate($aturanValidasi)) {
                return redirect()->back()->withInput()->with('error', $this->validator->getError('foto'));
            }

            $namaFoto = $foto->getRandomName();
            $foto->move('uploads/siswa', $namaFoto);
        }

        $nis = $this->request->getPost('nis');

        // created_at & updated_at akan otomatis diisi oleh model karena useTimestamps = true
        $this->siswaModel->insert([
            'nis'          => $nis,
            'nama_siswa'   => $this->request->getPost('nama_siswa'),
            'kelas_id'     => $this->request->getPost('kelas_id'),
            'password'     => password_hash($nis, PASSWORD_BCRYPT),
            'foto_profil'  => $namaFoto
        ]);

        return redirect()->to('/admin/siswa')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function update(string $id)
    {
        $siswaLama = $this->siswaModel->find($id);

        if (!$siswaLama) {
            return redirect()->to('/admin/siswa')->with('error', 'Data siswa tidak ditemukan.');
        }

        $foto = $this->request->getFile('foto');
        $namaFoto = $siswaLama['foto_profil'];

        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $aturanValidasi = [
                'foto' => [
                    'label'  => 'Foto Profil',
                    'rules'  => 'max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]'
                ]
            ];

            if (!$this->validate($aturanValidasi)) {
                return redirect()->back()->withInput()->with('error', 'Format atau ukuran foto tidak valid (Maks 2MB, JPG/JPEG/PNG).');
            }

            $namaFoto = $foto->getRandomName();
            $foto->move('uploads/siswa', $namaFoto);

            if (!empty($siswaLama['foto_profil']) && file_exists('uploads/siswa/' . $siswaLama['foto_profil'])) {
                unlink('uploads/siswa/' . $siswaLama['foto_profil']);
            }
        }

        $this->siswaModel->update($id, [
            'nis'          => $this->request->getPost('nis'),
            'nama_siswa'   => $this->request->getPost('nama_siswa'),
            'kelas_id'     => $this->request->getPost('kelas_id'),
            'foto_profil'  => $namaFoto
        ]);

        return redirect()->to('/admin/siswa')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function delete(string $id)
    {
        $siswa = $this->siswaModel->find($id);
        if ($siswa) {
            if (!empty($siswa['foto_profil']) && file_exists('uploads/siswa/' . $siswa['foto_profil'])) {
                unlink('uploads/siswa/' . $siswa['foto_profil']);
            }
            $this->siswaModel->delete($id);
        }

        return redirect()->to('/admin/siswa')->with('success', 'Data siswa beserta foto berhasil dihapus.');
    }

    public function resetDevice(string $id)
    {
        $this->siswaModel->update($id, [
            'device_id'  => null
        ]);

        return redirect()->to('/admin/siswa')->with('success', 'Perangkat berhasil di-reset.');
    }

    public function unblock(string $id)
    {
        $this->siswaModel->update($id, [
            'is_blocked'  => 0,
            'fraud_count' => 0
        ]);

        return redirect()->to('/admin/siswa')->with('success', 'Akun siswa berhasil di-unblock dan fraud count di-reset.');
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'TEMPLATE IMPORT DATA SISWA');
        $sheet->setCellValue('A3', 'NIS');
        $sheet->setCellValue('B3', 'NAMA LENGKAP');
        $sheet->setCellValue('C3', 'ID KELAS (Lihat di menu Manajemen Kelas)');

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Template_Siswa.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function export()
    {
        $kelasId = $this->request->getGet('kelas');

        $this->siswaModel->select('siswa.*, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if (!empty($kelasId)) {
            $this->siswaModel->where('siswa.kelas_id', $kelasId);
        }

        $dataSiswa = $this->siswaModel->orderBy('kelas.nama_kelas', 'ASC')
            ->orderBy('siswa.nama_siswa', 'ASC')
            ->findAll();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'NIS');
        $sheet->setCellValue('C1', 'Nama Lengkap');
        $sheet->setCellValue('D1', 'Kelas');

        $row = 2;
        foreach ($dataSiswa as $index => $siswa) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $siswa['nis']);
            $sheet->setCellValue('C' . $row, $siswa['nama_siswa']);
            $sheet->setCellValue('D' . $row, $siswa['nama_kelas']);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Data_Siswa.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function import()
    {
        $file = $this->request->getFile('file_excel');

        if (!$file->isValid() || $file->getExtension() !== 'xlsx') {
            return redirect()->back()->with('error', 'Format file tidak valid. Gunakan file .xlsx');
        }

        $spreadsheet = IOFactory::load($file->getTempName());
        $dataSiswa = $spreadsheet->getActiveSheet()->toArray();

        $inserted = 0;
        $skipped = 0;

        foreach ($dataSiswa as $index => $row) {
            if ($index < 4) continue;

            $nis = isset($row[0]) ? trim($row[0]) : '';
            $nama = isset($row[1]) ? trim($row[1]) : '';
            $kelasId = isset($row[2]) ? (int)trim($row[2]) : 0;

            if (empty($nis) || empty($nama) || empty($kelasId)) continue;

            $cek = $this->siswaModel->where('nis', $nis)->countAllResults();
            if ($cek > 0) {
                $skipped++;
                continue;
            }

            $this->siswaModel->insert([
                'nis'          => $nis,
                'nama_siswa'   => $nama,
                'kelas_id'     => $kelasId,
                'password'     => password_hash($nis, PASSWORD_BCRYPT)
            ]);
            $inserted++;
        }

        return redirect()->to('/admin/siswa')->with('success', "Berhasil import $inserted data baru. $skipped data dilewati (NIS duplikat).");
    }

    public function detail(string $idSiswa)
    {
        // Memanfaatkan fungsi di dalam SiswaModel
        $siswa = $this->siswaModel->getSiswaWithKelas($idSiswa);

        if (!$siswa) {
            return redirect()->to('/admin/siswa')->with('error', 'Data siswa tidak ditemukan.');
        }

        $absensi = $this->db->table('absensi')
            ->where('siswa_id', $idSiswa)
            ->orderBy('tanggal', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        $logFraud = $this->db->table('log_fraud')
            ->where('siswa_id', $idSiswa)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        $statHadir = $this->db->table('absensi')->where(['siswa_id' => $idSiswa, 'status' => 'Hadir'])->countAllResults();
        $statTelat = $this->db->table('absensi')->where(['siswa_id' => $idSiswa, 'status' => 'Terlambat'])->countAllResults();
        $statSakit = $this->db->table('absensi')->where(['siswa_id' => $idSiswa, 'status' => 'Sakit'])->countAllResults();
        $statIzin  = $this->db->table('absensi')->where(['siswa_id' => $idSiswa, 'status' => 'Izin'])->countAllResults();
        $statAlpa  = $this->db->table('absensi')->where(['siswa_id' => $idSiswa, 'status' => 'Alpa'])->countAllResults();

        $data = [
            'title'    => 'Profil 360: ' . $siswa['nama_siswa'],
            'siswa'    => $siswa,
            'absensi'  => $absensi,
            'logFraud' => $logFraud,
            'stats'    => [
                'hadir'     => $statHadir,
                'terlambat' => $statTelat,
                'sakit'     => $statSakit,
                'izin'      => $statIzin,
                'alpa'      => $statAlpa,
                'total'     => $statHadir + $statTelat + $statSakit + $statIzin + $statAlpa
            ]
        ];

        return view('web/siswa_detail', $data);
    }
}
