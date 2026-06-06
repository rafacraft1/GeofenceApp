<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\SiswaModel;
use App\Models\KelasModel;
use App\Models\AbsensiModel;
use App\Models\LogFraudModel;
use App\Services\ExportService;
use PhpOffice\PhpSpreadsheet\IOFactory;

class Siswa extends BaseController
{
    protected SiswaModel $siswaModel;
    protected KelasModel $kelasModel;
    protected AbsensiModel $absensiModel;
    protected LogFraudModel $logFraudModel;
    protected ExportService $exportService;

    public function __construct()
    {
        $this->siswaModel    = new SiswaModel();
        $this->kelasModel    = new KelasModel();
        $this->absensiModel  = new AbsensiModel();
        $this->logFraudModel = new LogFraudModel();
        $this->exportService = new ExportService();
    }

    public function index()
    {
        $isWaliKelas    = session()->get('is_wali_kelas');
        $kelasSessionId = session()->get('kelas_id');

        $kelasFilter = $isWaliKelas ? $kelasSessionId : $this->request->getGet('kelas');
        $search      = trim((string) $this->request->getGet('search'));

        $sort = strtolower(trim((string) $this->request->getGet('sort')));
        $dir  = strtoupper(trim((string) $this->request->getGet('dir')));
        $dir  = in_array($dir, ['ASC', 'DESC']) ? $dir : 'ASC';

        $allowedSorts = [
            'nama_siswa' => 'siswa.nama_siswa',
            'nis'        => 'siswa.nis',
            'device'     => 'siswa.device_id',
            'fraud'      => 'siswa.fraud_count'
        ];
        $sortColumn = $allowedSorts[$sort] ?? 'siswa.nama_siswa';

        $listKelas = $isWaliKelas
            ? $this->kelasModel->where('id_kelas', $kelasSessionId)->findAll()
            : $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();

        // Menggunakan Utilitas Global dari BaseController
        $pg = $this->setupPagination('page', 10);

        $this->siswaModel->select('siswa.*, kelas.nama_kelas')
            ->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');

        if (!empty($kelasFilter)) $this->siswaModel->where('siswa.kelas_id', $kelasFilter);

        if (!empty($search)) {
            $this->siswaModel->groupStart()->like('siswa.nama_siswa', $search)->orLike('siswa.nis', $search)->groupEnd();
        }

        $totalData = $this->siswaModel->countAllResults(false);
        $offset    = ($pg['page'] - 1) * $pg['perPage'];

        $siswa = $this->siswaModel->orderBy($sortColumn, $dir)
            ->orderBy('siswa.nama_siswa', 'ASC')
            ->findAll($pg['perPage'], $offset);

        $data = [
            'title'         => 'Daftar Siswa',
            'siswa'         => $siswa,
            'list_kelas'    => $listKelas,
            'kelas_aktif'   => $kelasFilter,
            'search'        => $search,
            'pager_links'   => $pg['pager']->makeLinks($pg['page'], $pg['perPage'], $totalData, 'default_full'),
            'page'          => $pg['page'],
            'perPage'       => $pg['perPage'],
            'total_data'    => $totalData,
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

        $foto = $this->request->getFile('foto');
        $namaFoto = null;

        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            if (!$this->validate(['foto' => 'max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]'])) {
                return redirect()->back()->withInput()->with('error', 'Format atau ukuran foto tidak valid.');
            }
            $namaFoto = $foto->getRandomName();
            $foto->move(FCPATH . 'uploads/siswa', $namaFoto);
        }

        $nis = $this->request->getPost('nis');
        $this->siswaModel->insert([
            'nis'          => $nis,
            'nama_siswa'   => $this->request->getPost('nama_siswa'),
            'kelas_id'     => $kelasIdPost,
            'password'     => password_hash($nis, PASSWORD_BCRYPT),
            'foto_profil'  => $namaFoto
        ]);

        return redirect()->back()->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function update(string $id)
    {
        $siswaLama = $this->siswaModel->find($id);

        if (!$siswaLama || !$this->checkAksesWaliKelas((int)$siswaLama['kelas_id'])) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan atau Akses Ditolak.');
        }

        $kelasIdPost = (int) $this->request->getPost('kelas_id');
        if (!$this->checkAksesWaliKelas($kelasIdPost)) {
            return redirect()->back()->with('error', 'Akses Ditolak: Anda tidak dapat memindahkan siswa ke kelas lain.');
        }

        $foto = $this->request->getFile('foto');
        $namaFoto = $siswaLama['foto_profil'];

        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            if (!$this->validate(['foto' => 'max_size[foto,2048]|is_image[foto]|mime_in[foto,image/jpg,image/jpeg,image/png]'])) {
                return redirect()->back()->withInput()->with('error', 'Format atau ukuran foto tidak valid.');
            }
            $namaFoto = $foto->getRandomName();
            $foto->move(FCPATH . 'uploads/siswa', $namaFoto);

            if (!empty($siswaLama['foto_profil']) && file_exists(FCPATH . 'uploads/siswa/' . $siswaLama['foto_profil'])) {
                unlink(FCPATH . 'uploads/siswa/' . $siswaLama['foto_profil']);
            }
        }

        $this->siswaModel->update($id, [
            'nis'          => $this->request->getPost('nis'),
            'nama_siswa'   => $this->request->getPost('nama_siswa'),
            'kelas_id'     => $kelasIdPost,
            'foto_profil'  => $namaFoto
        ]);

        return redirect()->back()->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function delete(string $id)
    {
        $siswa = $this->siswaModel->find($id);

        if (!$siswa || !$this->checkAksesWaliKelas((int)$siswa['kelas_id'])) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan atau Akses Ditolak.');
        }

        if (!empty($siswa['foto_profil']) && file_exists(FCPATH . 'uploads/siswa/' . $siswa['foto_profil'])) {
            unlink(FCPATH . 'uploads/siswa/' . $siswa['foto_profil']);
        }

        $this->siswaModel->delete($id);
        return redirect()->back()->with('success', 'Data siswa beserta foto berhasil dihapus.');
    }

    public function resetDevice(string $id)
    {
        $siswa = $this->siswaModel->find($id);
        if (!$siswa || !$this->checkAksesWaliKelas((int)$siswa['kelas_id'])) return redirect()->back()->with('error', 'Akses Ditolak.');

        $this->siswaModel->update($id, ['device_id' => null]);
        return redirect()->back()->with('success', 'Perangkat berhasil di-reset.');
    }

    public function unblock(string $id)
    {
        $siswa = $this->siswaModel->find($id);
        if (!$siswa || !$this->checkAksesWaliKelas((int)$siswa['kelas_id'])) return redirect()->back()->with('error', 'Akses Ditolak.');

        $this->siswaModel->update($id, ['is_blocked' => 0, 'fraud_count' => 0]);
        return redirect()->back()->with('success', 'Akun siswa berhasil di-unblock.');
    }

    public function downloadTemplate()
    {
        $listKelas = session()->get('is_wali_kelas')
            ? $this->kelasModel->where('id_kelas', session()->get('kelas_id'))->findAll()
            : $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();

        // Mendelegasikan tugas ke ExportService
        $this->exportService->downloadTemplateSiswa($listKelas);
    }

    public function export()
    {
        $kelasId = session()->get('is_wali_kelas') ? session()->get('kelas_id') : $this->request->getGet('kelas');

        $this->siswaModel->select('siswa.*, kelas.nama_kelas')->join('kelas', 'kelas.id_kelas = siswa.kelas_id', 'left');
        if (!empty($kelasId)) $this->siswaModel->where('siswa.kelas_id', $kelasId);

        $dataSiswa = $this->siswaModel->orderBy('kelas.nama_kelas', 'ASC')->orderBy('siswa.nama_siswa', 'ASC')->findAll();

        // Mendelegasikan tugas ke ExportService
        $this->exportService->exportDataSiswa($dataSiswa);
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

        foreach ($dataSiswa as $index => $row) {
            if ($index < 3) continue;

            $nis       = isset($row[0]) ? trim((string)$row[0]) : '';
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
            if ($this->siswaModel->where('nis', $nis)->countAllResults() > 0) {
                $skipped++;
                continue;
            }

            $this->siswaModel->insert([
                'nis'        => $nis,
                'nama_siswa' => $nama,
                'kelas_id'   => $kelasId,
                'password'   => password_hash($nis, PASSWORD_BCRYPT)
            ]);
            $inserted++;
        }

        return redirect()->back()->with('success', "Berhasil import $inserted data siswa. $skipped baris dilewati.");
    }

    public function detail(string $idSiswa)
    {
        $siswa = $this->siswaModel->getSiswaWithKelas($idSiswa);

        if (!$siswa || !$this->checkAksesWaliKelas((int)$siswa['kelas_id'])) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan atau Akses Ditolak.');
        }

        $startDate = $this->request->getGet('start_date');
        $endDate   = $this->request->getGet('end_date');

        // Menggunakan Utilitas Global dari BaseController
        $pg = $this->setupPagination('page_absensi', 10);

        $this->absensiModel->where('siswa_id', $idSiswa);
        if (!empty($startDate) && !empty($endDate)) {
            $this->absensiModel->where('tanggal >=', $startDate)->where('tanggal <=', $endDate);
        }

        $totalAbsensi = $this->absensiModel->countAllResults(false);
        $absensi = $this->absensiModel->orderBy('tanggal', 'DESC')->paginate($pg['perPage'], 'absensi');

        $logFraud = $this->logFraudModel->where('siswa_id', $idSiswa)->orderBy('created_at', 'DESC')->findAll(10);

        $this->absensiModel->select('status, COUNT(*) as total')->where('siswa_id', $idSiswa);
        if (!empty($startDate) && !empty($endDate)) {
            $this->absensiModel->where('tanggal >=', $startDate)->where('tanggal <=', $endDate);
        }

        $rekap = $this->absensiModel->groupBy('status')->findAll();
        $stats = ['hadir' => 0, 'terlambat' => 0, 'sakit' => 0, 'izin' => 0, 'alpa' => 0];

        foreach ($rekap as $r) {
            $statusKey = strtolower($r['status']);
            if (isset($stats[$statusKey])) $stats[$statusKey] = (int) $r['total'];
        }
        $stats['total'] = array_sum($stats);

        $data = [
            'title'            => 'Profil 360: ' . $siswa['nama_siswa'],
            'siswa'            => $siswa,
            'absensi'          => $absensi,
            'pager_absensi'    => $this->absensiModel->pager->makeLinks($pg['page'], $pg['perPage'], $totalAbsensi, 'default_full', 0, 'absensi'),
            'page_absensi'     => $pg['page'],
            'per_page_absensi' => $pg['perPage'],
            'total_absensi'    => $totalAbsensi,
            'logFraud'         => $logFraud,
            'start_date'       => $startDate,
            'end_date'         => $endDate,
            'stats'            => $stats
        ];

        return view('web/siswa_detail', $data);
    }
}
