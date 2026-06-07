<?php

namespace App\Controllers\Web;

use CodeIgniter\Controller;
use App\Models\SiswaModel;
use App\Models\KelasModel;
use App\Models\AbsensiModel;
use App\Models\LogFraudModel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class Siswa extends Controller
{
    protected SiswaModel $siswaModel;
    protected KelasModel $kelasModel;
    protected AbsensiModel $absensiModel;
    protected LogFraudModel $logFraudModel;

    public function __construct()
    {
        $this->siswaModel    = new SiswaModel();
        $this->kelasModel    = new KelasModel();
        $this->absensiModel  = new AbsensiModel();
        $this->logFraudModel = new LogFraudModel();
    }

    /**
     * PRIVATE HELPER: Memastikan keamanan akses Row-Level Security
     */
    private function checkAksesWaliKelas(int $targetKelasId): bool
    {
        if (session()->get('is_wali_kelas')) {
            return $targetKelasId === session()->get('kelas_id');
        }
        return true;
    }

    private function handleUploadFoto(?\CodeIgniter\HTTP\Files\UploadedFile $foto, ?string $fotoLama = null): array
    {
        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $aturanFoto = [
                'foto' => [
                    'label'  => 'Foto Profil',
                    'rules'  => 'max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]'
                ]
            ];

            if (!$this->validate($aturanFoto)) {
                return ['status' => false, 'error' => $this->validator->getError('foto')];
            }

            $namaFoto = $foto->getRandomName();
            $foto->move(FCPATH . 'uploads/siswa', $namaFoto);

            if (!empty($fotoLama) && file_exists(FCPATH . 'uploads/siswa/' . $fotoLama)) {
                unlink(FCPATH . 'uploads/siswa/' . $fotoLama);
            }

            return ['status' => true, 'data' => $namaFoto];
        }

        return ['status' => true, 'data' => $fotoLama];
    }

    public function index()
    {
        $isWaliKelas    = session()->get('is_wali_kelas');
        $kelasSessionId = session()->get('kelas_id');

        $kelasFilter  = $isWaliKelas ? $kelasSessionId : $this->request->getGet('kelas');
        $searchFilter = $this->request->getGet('search');
        $page         = (int) ($this->request->getGet('page') ?? 1);
        $perPage      = 10;

        $listKelas = $isWaliKelas
            ? $this->kelasModel->where('id_kelas', $kelasSessionId)->findAll()
            : $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();

        $siswa = $this->siswaModel->getPaginatedSiswa($kelasFilter, $searchFilter, $perPage);

        $data = [
            'title'         => 'Daftar Siswa',
            'siswa'         => $siswa,
            'list_kelas'    => $listKelas,
            'kelas_aktif'   => $kelasFilter,
            'search_aktif'  => $searchFilter,
            'pager_links'   => $this->siswaModel->pager->links('default', 'default_full'),
            'total_data'    => $this->siswaModel->pager->getTotal('default'),
            'page'          => $page,
            'perPage'       => $perPage,
            'is_wali_kelas' => $isWaliKelas
        ];

        return view('web/siswa', $data);
    }

    public function store()
    {
        $kelasIdPost = (int) $this->request->getPost('kelas_id');

        if (!$this->checkAksesWaliKelas($kelasIdPost)) {
            return redirect()->back()->with('error', 'Akses Ditolak: Anda tidak dapat menambahkan siswa ke kelas lain.');
        }

        $aturanValidasi = [
            'nis'        => [
                'rules'  => 'required|is_unique[siswa.nis]',
                'errors' => ['is_unique' => 'NIS sudah terdaftar. Gunakan NIS lain.']
            ],
            'nama_siswa' => 'required'
        ];

        if (!$this->validate($aturanValidasi)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getError('nis') ?: 'Mohon lengkapi semua data wajib.');
        }

        $uploadFoto = $this->handleUploadFoto($this->request->getFile('foto'));
        if (!$uploadFoto['status']) {
            return redirect()->back()->withInput()->with('error', $uploadFoto['error']);
        }

        $nis = $this->request->getPost('nis');

        $this->siswaModel->insert([
            'nis'          => $nis,
            'nama_siswa'   => $this->request->getPost('nama_siswa'),
            'kelas_id'     => $kelasIdPost,
            'password'     => password_hash($nis, PASSWORD_BCRYPT),
            'foto_profil'  => $uploadFoto['data']
        ]);

        return redirect()->to('/admin/siswa')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function update(string $id)
    {
        $siswaLama = $this->siswaModel->find($id);

        if (!$siswaLama || !$this->checkAksesWaliKelas((int)$siswaLama['kelas_id'])) {
            return redirect()->to('/admin/siswa')->with('error', 'Data siswa tidak ditemukan atau Akses Ditolak.');
        }

        $kelasIdPost = (int) $this->request->getPost('kelas_id');
        if (!$this->checkAksesWaliKelas($kelasIdPost)) {
            return redirect()->back()->with('error', 'Akses Ditolak: Anda tidak dapat memindahkan siswa ke kelas lain.');
        }

        $aturanValidasi = [
            'nis'        => [
                'rules'  => "required|is_unique[siswa.nis,id_siswa,{$id}]",
                'errors' => ['is_unique' => 'NIS tersebut sudah dipakai oleh siswa lain.']
            ],
            'nama_siswa' => 'required'
        ];

        if (!$this->validate($aturanValidasi)) {
            return redirect()->back()->withInput()->with('error', $this->validator->getError('nis') ?: 'Cek kembali isian Anda.');
        }

        $uploadFoto = $this->handleUploadFoto($this->request->getFile('foto'), $siswaLama['foto_profil']);
        if (!$uploadFoto['status']) {
            return redirect()->back()->withInput()->with('error', $uploadFoto['error']);
        }

        $this->siswaModel->update($id, [
            'nis'          => $this->request->getPost('nis'),
            'nama_siswa'   => $this->request->getPost('nama_siswa'),
            'kelas_id'     => $kelasIdPost,
            'foto_profil'  => $uploadFoto['data']
        ]);

        return redirect()->to('/admin/siswa')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function delete(string $id)
    {
        $siswa = $this->siswaModel->find($id);

        if (!$siswa || !$this->checkAksesWaliKelas((int)$siswa['kelas_id'])) {
            return redirect()->to('/admin/siswa')->with('error', 'Data siswa tidak ditemukan atau Akses Ditolak.');
        }

        $this->siswaModel->db->transStart();
        $this->siswaModel->delete($id);
        $this->siswaModel->db->transComplete();

        if ($this->siswaModel->db->transStatus() === false) {
            return redirect()->to('/admin/siswa')->with('error', 'Gagal menghapus data siswa dari database.');
        }

        if (!empty($siswa['foto_profil']) && file_exists(FCPATH . 'uploads/siswa/' . $siswa['foto_profil'])) {
            unlink(FCPATH . 'uploads/siswa/' . $siswa['foto_profil']);
        }

        return redirect()->to('/admin/siswa')->with('success', 'Data siswa beserta foto berhasil dihapus permanen.');
    }

    public function resetDevice(string $id)
    {
        $siswa = $this->siswaModel->find($id);
        if (!$siswa || !$this->checkAksesWaliKelas((int)$siswa['kelas_id'])) {
            return redirect()->to('/admin/siswa')->with('error', 'Akses Ditolak.');
        }

        $this->siswaModel->update($id, ['device_id' => null]);
        return redirect()->to('/admin/siswa')->with('success', 'Perangkat berhasil di-reset.');
    }

    public function unblock(string $id)
    {
        $siswa = $this->siswaModel->find($id);
        if (!$siswa || !$this->checkAksesWaliKelas((int)$siswa['kelas_id'])) {
            return redirect()->to('/admin/siswa')->with('error', 'Akses Ditolak.');
        }

        $this->siswaModel->update($id, [
            'is_blocked'  => 0,
            'fraud_count' => 0
        ]);
        return redirect()->to('/admin/siswa')->with('success', 'Akun siswa berhasil di-unblock dan fraud count di-reset.');
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();

        if (session()->get('is_wali_kelas')) {
            $listKelas = $this->kelasModel->where('id_kelas', session()->get('kelas_id'))->findAll();
        } else {
            $listKelas = $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();
        }

        $totalKelas = count($listKelas);

        if ($totalKelas > 0) {
            $refSheet = $spreadsheet->createSheet();
            $refSheet->setTitle('DataKelas');
            $refRow = 1;
            foreach ($listKelas as $k) {
                $refSheet->setCellValue('A' . $refRow++, $k['nama_kelas']);
            }
            $spreadsheet->getSheetByName('DataKelas')->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_VERYHIDDEN);
        }

        $spreadsheet->setActiveSheetIndex(0);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Data Siswa');

        $sheet->setCellValue('A1', 'TEMPLATE IMPORT DATA SISWA');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(12);
        $sheet->setCellValue('A2', 'Pilih kelas melalui dropdown di kolom C. Jangan mengetik manual di kolom Kelas.');
        $sheet->getStyle('A2')->getFont()->setItalic(true)->getColor()->setARGB('FFD97706');

        $sheet->setCellValue('A3', 'NIS');
        $sheet->setCellValue('B3', 'NAMA LENGKAP');
        $sheet->setCellValue('C3', 'KELAS (Klik untuk pilih)');
        $sheet->getStyle('A3:C3')->getFont()->setBold(true);
        $sheet->getStyle('A3:C3')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0E0E0');

        if ($totalKelas > 0) {
            $validation = $sheet->getDataValidation('C4:C500');
            $validation->setType(DataValidation::TYPE_LIST);
            $validation->setErrorStyle(DataValidation::STYLE_STOP);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input Salah');
            $validation->setError('Silakan pilih kelas yang tersedia dari daftar!');
            $validation->setPromptTitle('Pilih Kelas');
            $validation->setPrompt('Klik panah di samping untuk memilih kelas.');
            $validation->setFormula1('DataKelas!$A$1:$A$' . $totalKelas);
        }

        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Template_Import_Siswa_V2.xlsx"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    public function export()
    {
        if (session()->get('is_wali_kelas')) {
            $kelasId = session()->get('kelas_id');
        } else {
            $kelasId = $this->request->getGet('kelas');
        }

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
            $sheet->setCellValueExplicit('B' . $row, $siswa['nis'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
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
        $skipped  = 0;

        $allKelas = $this->kelasModel->findAll();
        $kelasMap = [];
        foreach ($allKelas as $k) {
            $kelasMap[strtolower(trim((string)$k['nama_kelas']))] = $k['id_kelas'];
        }

        $this->siswaModel->db->transStart();

        foreach ($dataSiswa as $index => $row) {
            if ($index < 3) continue;

            $nis       = isset($row[0]) ? preg_replace('/\s+/', '', (string)$row[0]) : '';
            $nama      = isset($row[1]) ? trim((string)$row[1]) : '';
            $namaKelas = isset($row[2]) ? strtolower(trim((string)$row[2])) : '';

            if (empty($nis) || empty($nama) || empty($namaKelas)) continue;

            if (!isset($kelasMap[$namaKelas])) {
                $skipped++;
                continue;
            }
            $kelasId = (int) $kelasMap[$namaKelas];

            if (!$this->checkAksesWaliKelas($kelasId)) {
                $skipped++;
                continue;
            }

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

        $this->siswaModel->db->transComplete();

        if ($this->siswaModel->db->transStatus() === false) {
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat proses import. Data dibatalkan.');
        }

        return redirect()->to('/admin/siswa')->with('success', "Berhasil import $inserted data siswa. $skipped baris dilewati (NIS duplikat, beda kelas, atau format tidak valid).");
    }

    public function detail(string $idSiswa)
    {
        $siswa = $this->siswaModel->getSiswaWithKelas($idSiswa);

        if (!$siswa || (!$this->checkAksesWaliKelas((int)$siswa['kelas_id']))) {
            return redirect()->to('/admin/siswa')->with('error', 'Data siswa tidak ditemukan atau Akses Ditolak.');
        }

        $absensi = $this->absensiModel->where('siswa_id', $idSiswa)
            ->orderBy('tanggal', 'DESC')
            ->findAll(10);

        $logFraud = $this->logFraudModel->where('siswa_id', $idSiswa)
            ->orderBy('created_at', 'DESC')
            ->findAll(10);

        $statHadir = $this->absensiModel->where(['siswa_id' => $idSiswa, 'status' => 'Hadir'])->countAllResults();
        $statTelat = $this->absensiModel->where(['siswa_id' => $idSiswa, 'status' => 'Terlambat'])->countAllResults();
        $statSakit = $this->absensiModel->where(['siswa_id' => $idSiswa, 'status' => 'Sakit'])->countAllResults();
        $statIzin  = $this->absensiModel->where(['siswa_id' => $idSiswa, 'status' => 'Izin'])->countAllResults();
        $statAlpa  = $this->absensiModel->where(['siswa_id' => $idSiswa, 'status' => 'Alpa'])->countAllResults();

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
