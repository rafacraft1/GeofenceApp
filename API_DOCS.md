# 📚 GeofenceApp API Reference (v1)

Dokumen ini berisi spesifikasi teknis untuk berinteraksi dengan layanan REST API GeofenceApp. API ini dirancang khusus untuk dikonsumsi oleh Aplikasi Mobile Android/iOS (Klien Siswa).

- **Base URL:** `https://smkn1tgb.test/api/v1/`
- **Format Respons:** JSON (`application/json`)
- **Autentikasi:** Bearer Token (JWT)

---

## 🔒 Otentikasi & Keamanan (Wajib)
Semua *request* menuju rute yang diproteksi **WAJIB** menyertakan *header* berikut:
1. `Authorization: Bearer <access_token>`
2. `X-App-Version: <versi_apk>` (Contoh: `1.0.0`)
   *(Jika versi aplikasi kadaluarsa, API akan merespons dengan HTTP 426 Upgrade Required)*

---

## 🟢 1. Layanan Publik (Tanpa Token)

### A. Login
- **Endpoint:** `POST /auth/login`
- **Tujuan:** Mendapatkan *Access Token* dan *Refresh Token*.
- **Body (Form-Data/JSON):**
  - `nis` (String, Wajib)
  - `password` (String, Wajib)
  - `device_id` (String, Opsional) - *ID Unik Perangkat (IMEI/UUID)*
  - `fcm_token` (String, Opsional) - *Token Push Notification Firebase*
- **Respons Sukses (200 OK):**
  ```json
  {
    "status": 200,
    "message": "Login berhasil",
    "access_token": "eyJhbGci...",
    "refresh_token": "def502...",
    "data": { "id_siswa": 1, "nama": "Siswa A", "kelas": "XII RPL 1" }
  }
  ```

### B. Perbarui Token (Refresh)
- **Endpoint:** `POST /auth/refresh`
- **Body:** `refresh_token` (String, Wajib)
- **Respons:** Mengembalikan `access_token` baru.

### C. Waktu Server
- **Endpoint:** `GET /waktu_server`
- **Tujuan:** Mendapatkan waktu server terkini untuk mencocokkan jam perangkat HP siswa.

### D. Pengumuman Terkini
- **Endpoint:** `GET /pengumuman`
- **Tujuan:** Mengambil daftar pengumuman global yang disiarkan oleh Admin.

---

## 🔴 2. Layanan Terproteksi (Butuh Bearer Token)

### A. Absen Masuk
- **Endpoint:** `POST /absen/masuk`
- **Tujuan:** Mencatat presensi kehadiran harian di sekolah/lokasi magang.
- **Header:** `Content-Type: multipart/form-data`
- **Body:**
  - `latitude` (Float, Wajib) - *Koordinat GPS*
  - `longitude` (Float, Wajib) - *Koordinat GPS*
  - `accuracy` (Float, Wajib) - *Akurasi radius GPS dalam meter*
  - `device_timestamp` (Integer, Wajib) - *Unix Timestamp dari HP (Detik/Milidetik)*
  - `is_mock` (Integer, Wajib) - *1 jika menggunakan Fake GPS, 0 jika asli*
  - `foto` (File Image, Wajib) - *Foto selfie*
- **Respons Gagal (403 Forbidden - Anti Fraud):**
  ```json
  {
    "status": 403,
    "message": "Akun diblokir karena penggunaan Fake GPS berulang."
  }
  ```

### B. Absen Pulang
- **Endpoint:** `POST /absen/pulang`
- **Tujuan:** Mencatat kepulangan. Struktur *Body Request* dan Validasi persis sama dengan Absen Masuk.

### C. Riwayat Absensi
- **Endpoint:** `GET /absen/riwayat`
- **Tujuan:** Mengambil 30 data riwayat presensi terakhir milik siswa bersangkutan.

### D. Pelacakan Latar Belakang (Background Tracking)
- **Endpoint:** `POST /tracking/store`
- **Tujuan:** Mengirim koordinat lokasi siswa secara berkala ke server (Digunakan untuk memantau siswa magang/PKL).
- **Body:** `latitude`, `longitude`, `timestamp`

### E. Pengajuan Izin / Sakit
- **Endpoint:** `POST /izin/ajukan`
- **Header:** `Content-Type: multipart/form-data`
- **Body:**
  - `jenis_izin` (String: Sakit/Izin)
  - `keterangan` (String)
  - `tanggal_mulai` (Date)
  - `tanggal_selesai` (Date)
  - `foto` (File Image, Bukti Surat Dokter/Keterangan)

### F. Update Foto Profil
- **Endpoint:** `POST /profile/upload-foto`
- **Body:** `foto` (File Image, Maks 2MB).

### G. Logout (Keluar Sesi)
- **Endpoint:** `POST /auth/logout`
- **Tujuan:** Memasukkan JWT saat ini ke dalam daftar hitam (*Blacklist*). Token tidak akan bisa dipakai lagi.
