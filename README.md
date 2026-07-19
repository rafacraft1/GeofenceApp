# GeofenceApp - Sistem Manajemen Presensi Berbasis Lokasi

GeofenceApp adalah sistem informasi administrasi dan presensi (absensi) sekolah/instansi berbasis lokasi (Geofencing) yang dibangun di atas framework **CodeIgniter 4**, berpadu dengan fungsionalitas **Tailwind CSS** untuk menyajikan antarmuka pengguna (UI/UX) tingkat *Enterprise*.

Sistem ini mendukung fitur validasi lokasi berlapis, manajemen *fraud* (kecurangan), zona magang/PKL yang dinamis, hingga notifikasi siaran *(broadcast)* menggunakan Firebase Cloud Messaging.

---

## 🚀 Log Pembaruan (Changelog) - UI/UX Enterprise Overhaul
*(Juli 2026)*

Sistem baru saja melalui fase perombakan antarmuka pengguna (UI) dan pengalaman pengguna (UX) besar-besaran untuk seluruh modul antarmuka Web Admin. Berikut adalah daftar pembaruan yang telah diterapkan:

### 1. 🛡️ Autentikasi & Gerbang Utama (Login)
- **Aurora Mesh Background:** Latar belakang dirombak menggunakan efek *radial-gradient blob* dinamis bergaya aurora (Biru, Indigo, Emerald).
- **Glassmorphism Panel:** Kartu form login kini menggunakan efek kaca es semi-transparan (`backdrop-blur`).
- **Floating 3D Identity:** Penyematan logo aplikasi bergradien 3D interaktif yang merespons kursor mouse.
- **Konsistensi Ikonografi:** Integrasi penuh FontAwesome untuk semua input field dan *Eye-Toggle* (Intip Sandi).

### 2. 🎛️ Tata Letak Utama (Sidebar & Top Navbar)
- **Active State Gradient:** Menu aktif pada sidebar kini disorot menggunakan *gradient button* dengan efek *Glow Shadow*.
- **Smooth Profile Dropdown:** Animasi menu profil (Kanan Atas) ditingkatkan dari gaya kaku menjadi animasi *Fade & Scale-in* yang sangat halus ala iOS/macOS.
- **Glass Navbar:** Header navigasi atas kini menggunakan efek *backdrop-blur* (transparan-kaca) sehingga tabel di bawahnya terlihat menyatu saat di-*scroll*.
- **Destructive Hover:** Tombol *Logout* kini menyala merah bergradien saat akan diklik sebagai indikator tindakan akhir.

### 3. 👥 Manajemen Siswa & Profil 360
- **Gradient Identity Card:** Halaman detail siswa diperkaya dengan latar belakang bingkai bergradien dinamis di belakang kotak foto profil.
- **Analitik Watermark Ikon:** Kotak ringkasan statistik (Hadir, Alpa, Telat) kini memiliki ikon raksasa transparan (watermark) di latar belakangnya.
- **Bukti Visual (Modal):** Latar belakang *Modal* untuk melihat foto *selfie* masuk/pulang presensi kini dilapisi efek *blur* premium dengan transisi pembesaran foto (*zoom-in*).

### 4. 🏢 Manajemen Mutasi & Kelas
- **Smart Stepper Mutasi:** Halaman mutasi kelas masal kini memiliki antarmuka pencarian cepat (*Live-Search*) dan pemindahan data yang jauh lebih terorganisir.
- **Avatar Otomatis:** Jika siswa tidak memiliki foto profil, sistem otomatis men- *generate* avatar melingkar berdasarkan inisial huruf pertama nama.

### 5. 📍 Zona Kehadiran (Geofence & PKL)
- **Peta Interaktif (Leaflet):** Tampilan modal penentuan titik radius (*Geofencing*) dipoles lebih bersih.
- **Dropdown Aksi Modern:** Menghilangkan jejeran tombol aksi yang berdesakan di tabel, diganti menjadi tombol *Dropdown Options* tiga titik yang minimalis.

### 6. 📅 Rekapitulasi & Hari Libur
- **Progress Bar Kehadiran:** Tabel laporan kini memvisualisasikan persentase kehadiran siswa menggunakan bilah progres (*Progress Bar*) warna-warni secara langsung.
- **Calendar Sheet UX:** Halaman manajemen Hari Libur dibentuk menyerupai carikan kalender nyata (Merah untuk libur).
- **Auto-Dimming Data:** Data hari libur yang sudah lampau (kadaluarsa) otomatis diredupkan (*opacity-50*) agar Admin fokus pada agenda libur mendatang.

### 7. 📢 Pengumuman (Broadcast Firebase)
- **Gradient Badges:** Pengumuman kini dibedakan dengan latar warna gradien khusus berdasarkan tipenya (Biru untuk Info, Oranye untuk Peringatan, Merah untuk Sistem).
- **Anti-Spam Click:** Penyematan animasi `.btn-loading` (Spinner Putar) untuk mencegah Admin mengirim notifikasi ganda ke HP siswa secara tidak sengaja akibat koneksi lambat.

### 8. ⚙️ Konfigurasi Sistem, Hak Akses (RBAC), & Profil Admin
- **Toggle Switch iOS:** Matriks pengaturan Hak Akses (RBAC) tidak lagi menggunakan *checkbox browser default*, melainkan beralih ke *CSS Toggle Switch* mulus layaknya di ponsel pintar.
- **Optimasi Gambar (*Client-Side Compression*):** Saat Admin mengunggah foto profil, foto dipotong dan dikompresi di sisi *browser* (maks 700x700px) sebelum menyentuh *server*, menghemat *bandwidth* secara drastis.
- **Layout Berbasis Form Premium:** Penambahan *inline-icon* FontAwesome secara masif di sebelah kiri setiap kotak input (Nama, Username, Sandi, App ID, Link APK, dll) untuk identitas visual yang solid.

---

## 🛠️ Persyaratan Lingkungan (Environment)
- PHP 8.1 atau lebih baru.
- Ekstensi PHP: `intl`, `mbstring`, `gd`, `curl`, `mysqlnd`.
- Node.js & NPM (Untuk *build Tailwind CSS*).
- MySQL / MariaDB 10.x.

## 📦 Menjalankan Sistem Secara Lokal
1. Konfigurasikan kredensial database di dalam file `.env`.
2. Jalankan perintah `php spark serve` untuk menghidupkan *backend*.
3. (Pilihan) Jalankan perintah `npm run dev` pada tab terminal lain untuk mengaktifkan kompilasi *Tailwind CSS Real-time*.
4. Akses melalui `http://localhost:8080/admin/login`.
