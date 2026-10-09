# BANTULOKAL HUB

BANTULOKAL HUB adalah proyek platform komunitas yang menggabungkan layanan bantuan sehari-hari dan penyewaan alat antarwarga dalam satu tempat. Proyek ini terdiri dari frontend berbasis HTML, CSS, dan JavaScript serta backend Laravel yang menangani API, database, autentikasi admin, pengelolaan permintaan, dan alur pembayaran manual.

> **Catatan status:** Dokumentasi ini menjelaskan alur yang telah dibangun dalam kode sejauh ini. Ketersediaan layanan, integrasi setiap formulir, dan konfigurasi produksi tetap perlu diverifikasi sebelum aplikasi dipublikasikan.

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Teknologi](#teknologi)
- [Struktur Proyek](#struktur-proyek)
- [Persyaratan](#persyaratan)
- [Menjalankan Proyek Secara Lokal](#menjalankan-proyek-secara-lokal)
- [Konfigurasi Backend](#konfigurasi-backend)
- [Menjalankan Frontend](#menjalankan-frontend)
- [Alur Bisnis](#alur-bisnis)
- [Admin dan Keamanan](#admin-dan-keamanan)
- [Pengujian](#pengujian)
- [Email dan Notifikasi](#email-dan-notifikasi)
- [Catatan Pengembangan](#catatan-pengembangan)
- [Hal yang Perlu Diperiksa Sebelum Rilis](#hal-yang-perlu-diperiksa-sebelum-rilis)

## Fitur Utama

### 1. JasaBantuLokal

JasaBantuLokal berfokus pada permintaan jasa harian dari pelanggan kepada freelancer lokal. Kategori layanan yang ditampilkan meliputi:

- Bersih-bersih
- Pindah kos
- Desain poster
- Jasa beli barang
- Jasa antar barang
- Jasa fotografi

Alur backend mendukung pengajuan permintaan, peninjauan oleh admin, persetujuan atau penolakan, serta pengelolaan informasi biaya dan pembayaran. Pengiriman permintaan tidak berarti pekerjaan otomatis disetujui; detail pekerjaan, ketersediaan freelancer, dan biaya perlu dikonfirmasi oleh tim.

Untuk pembagian pendapatan, perhitungan 80/20 diterapkan pada **biaya jasa**, bukan pada biaya tambahan yang telah disetujui.

### 2. SewaAlatLokal

SewaAlatLokal membantu proses penyewaan alat antarwarga. Alur yang didukung meliputi:

1. Pelanggan mengajukan booking.
2. Admin meninjau permintaan dan ketersediaan alat.
3. Admin menetapkan biaya sewa dan deposit final.
4. Pelanggan menerima instruksi pembayaran.
5. Pelanggan membayar melalui transfer bank.
6. Admin memverifikasi pembayaran dan sistem mencatat informasi pembayaran serta nomor invoice.
7. Sistem mengirim konfirmasi pembayaran.
8. Status serah terima, pengembalian, dan penutupan penyewaan dapat dicatat.

**Pembayaran dilakukan melalui transfer bank dan diverifikasi secara manual oleh admin.** Proyek ini tidak boleh dipresentasikan sebagai sistem escrow otomatis atau payment gateway apabila integrasi tersebut belum dibangun.

### 3. Panel Admin

Panel admin menyediakan pengelolaan permintaan dan booking, serta pengelolaan akun administrator. Fitur keamanan yang telah dibangun mencakup:

- Autentikasi admin.
- Pembatasan akses berdasarkan peran admin dan status akun aktif.
- Pembatasan percobaan login yang gagal.
- Regenerasi sesi setelah login dan invalidasi sesi saat logout.
- Pencegahan penonaktifan akun admin sendiri melalui alur manajemen admin.
- Perlindungan agar admin aktif terakhir tidak dapat dinonaktifkan melalui alur tersebut.
- Audit log untuk perubahan manajemen admin.

## Teknologi

Versi yang digunakan selama pengembangan terakhir:

| Komponen | Teknologi |
| --- | --- |
| Frontend | HTML, CSS, JavaScript |
| Backend | Laravel 13 |
| Bahasa backend | PHP 8.4 |
| Database | PostgreSQL 18 |
| Email notifikasi | Endpoint Google Apps Script yang dikonfigurasi di Laravel |
| Pengujian backend | PHPUnit melalui `php artisan test` |
| Version control | Git |

Versi pada komputer pengembang atau server lain dapat berbeda. Periksa versi yang terpasang sebelum menjalankan aplikasi.

## Struktur Proyek

Struktur utama repository:

```text
BANTULOKAL-HUB-website/
├── assets/
│   ├── css/
│   │   └── main.css
│   └── js/
│       └── main.js
├── jasabantulokal/
│   └── index.html
├── sewaalatlokal/
│   ├── index.html
│   ├── katalog.html
│   └── layanan.html
├── backend/
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   └── Middleware/
│   │   ├── Mail/
│   │   └── Models/
│   ├── bootstrap/
│   ├── config/
│   ├── database/
│   │   └── migrations/
│   ├── resources/
│   │   └── views/
│   ├── routes/
│   ├── tests/
│   └── .env
└── README.md
```

Folder `backend/` adalah aplikasi Laravel. Halaman HTML dan aset frontend berada di luar folder tersebut dan dijalankan secara terpisah saat pengembangan lokal.

## Persyaratan

Pastikan perangkat pengembangan memiliki:

- PHP dengan ekstensi yang dibutuhkan Laravel dan driver PostgreSQL.
- Composer.
- PostgreSQL.
- Git.
- Browser modern.

Versi yang digunakan saat pengembangan terakhir adalah PHP 8.4.25, PostgreSQL 18.6, dan Laravel 13.35.0.

## Menjalankan Proyek Secara Lokal

Contoh berikut menggunakan Windows PowerShell.

### 1. Masuk ke repository

```powershell
cd "D:\Nathan\Projects\Bantu Lokal\BANTULOKAL-HUB-website"
```

Sesuaikan path jika repository berada di lokasi lain.

### 2. Pasang dependency backend

```powershell
cd backend
composer install
```

### 3. Siapkan file environment

Jika `.env` belum tersedia, salin file contoh:

```powershell
Copy-Item .env.example .env
```

Kemudian buat application key:

```powershell
php artisan key:generate
```

Jangan menimpa `.env` yang sudah berisi konfigurasi lokal yang benar.

### 4. Konfigurasikan database

Buat database PostgreSQL untuk lingkungan pengembangan, lalu sesuaikan nilai berikut di `backend/.env`:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=nama_database_lokal
DB_USERNAME=nama_user_database
DB_PASSWORD=password_database
```

Gunakan nama database, username, dan password yang benar-benar ada di mesin lokal. Jangan masukkan kredensial asli ke repository.

Setelah koneksi database dikonfigurasi, jalankan migrasi:

```powershell
php artisan migrate
```

> **Penting:** Jangan gunakan `php artisan migrate:fresh`, `php artisan db:wipe`, atau perintah lain yang menghapus tabel untuk sekadar menyiapkan proyek. Gunakan migrasi biasa agar data yang sudah ada tidak terhapus. Untuk database yang sudah berisi data, buat backup sebelum melakukan perubahan skema.

### 5. Jalankan backend

Dari folder `backend/`:

```powershell
php artisan serve
```

Secara default, backend dapat diakses di:

`http://127.0.0.1:8000`

### 6. Jalankan frontend

Buka terminal PowerShell kedua, lalu jalankan dari root repository:

```powershell
cd "D:\Nathan\Projects\Bantu Lokal\BANTULOKAL-HUB-website"
php -S 127.0.0.1:5500
```

Buka frontend melalui:

`http://127.0.0.1:5500`

Jalankan frontend dan backend secara bersamaan saat menguji halaman yang berkomunikasi dengan API. Pastikan konfigurasi URL API pada JavaScript sesuai dengan alamat backend lokal.

## Konfigurasi Backend

Selain koneksi database, periksa konfigurasi berikut di `backend/.env` dan file konfigurasi Laravel yang terkait:

- `APP_URL` harus sesuai dengan URL backend.
- Konfigurasi session dan cookie harus sesuai dengan lingkungan lokal atau produksi.
- Endpoint dan token Google Apps Script harus disimpan sebagai konfigurasi rahasia, bukan ditulis langsung di kode atau README.
- Konfigurasi email perlu diuji agar kegagalan pengiriman tidak disalahartikan sebagai kegagalan penyimpanan atau pembayaran.
- Akun admin harus disiapkan dengan prosedur yang aman. Jangan menjadikan pembuatan akun admin publik tanpa autentikasi sebagai fitur pendaftaran umum.

Untuk perubahan konfigurasi Laravel, jika konfigurasi sebelumnya pernah di-cache, bersihkan cache konfigurasi di lingkungan pengembangan:

```powershell
php artisan config:clear
```

Jangan membagikan isi `.env`, token, password, atau kredensial database dalam issue, commit, tangkapan layar, maupun dokumentasi publik.

## Alur Bisnis

### JasaBantuLokal

Secara ringkas:

```text
Pelanggan mengajukan permintaan
            |
            v
Admin meninjau permintaan
            |
      +-----+-----+
      |           |
   Ditolak      Disetujui
                  |
                  v
      Biaya dan detail dikonfirmasi
                  |
                  v
      Pembayaran dan tindak lanjut
```

Status dan tindakan yang tersedia bergantung pada implementasi controller dan data yang tersimpan. Permintaan yang diajukan belum tentu disetujui.

### SewaAlatLokal

```text
Booking diajukan
       |
       v
Admin meninjau booking
       |
  +----+----+
  |         |
Ditolak   Disetujui
             |
             v
  Biaya sewa dan deposit ditetapkan
             |
             v
  Instruksi transfer bank dikirim
             |
             v
  Admin memverifikasi pembayaran
             |
             v
  Konfirmasi pembayaran dikirim
             |
             v
  Serah terima, pengembalian, penutupan
```

Pembayaran tidak diverifikasi otomatis hanya karena pelanggan mengirim bukti transfer. Verifikasi dilakukan oleh admin sesuai prosedur.

## Admin dan Keamanan

- Rute admin dikelompokkan di bawah prefix `/admin`.
- Halaman admin memerlukan autentikasi dan pemeriksaan hak akses admin.
- Akun nonaktif tidak boleh memperoleh akses admin.
- Percobaan login gagal dibatasi.
- Perubahan status admin dicatat melalui audit log.
- Pembuatan akun admin perlu menggunakan kata sandi kuat dan akses yang dibatasi.
- Jangan menghapus middleware keamanan atau menonaktifkan CSRF untuk mengatasi masalah form tanpa memahami dampaknya.
- Pengaturan lokal seperti `APP_DEBUG=true` tidak boleh dipakai sebagai konfigurasi produksi.

Akun admin awal dan proses provisioning harus mengikuti prosedur yang dikelola tim. Kredensial akun tidak dicantumkan dalam dokumentasi ini.

## Pengujian

Jalankan pengujian backend dari folder `backend/`:

```powershell
php artisan test
```

Test suite mencakup area seperti:

- Persetujuan permintaan jasa dan perhitungan pendapatan.
- Persetujuan booking rental dan verifikasi pembayaran.
- Pengiriman email dan penanganan kegagalannya.
- Login, pembatasan percobaan login, sesi, dan otorisasi admin.
- Pembuatan, aktivasi, dan penonaktifan admin serta audit log.
- Penolakan request yang tidak memenuhi perlindungan CSRF pada pengujian terkait.

Pada pengujian terakhir yang tercatat sebelum perubahan whitespace, seluruh 38 test lulus dengan 212 assertion. Jalankan kembali test suite setelah perubahan kode untuk memastikan hasil terkini.

## Email dan Notifikasi

Laravel menggunakan endpoint Google Apps Script yang dikonfigurasi melalui `config/services.php`. Konfigurasi endpoint dan token harus berasal dari environment atau mekanisme secret management yang sesuai.

Alur notifikasi rental mencakup:

- Email bahwa booking telah diterima.
- Instruksi pembayaran setelah booking disetujui.
- Konfirmasi setelah admin memverifikasi pembayaran.

Aplikasi mencatat waktu pengiriman ketika endpoint mengonfirmasi keberhasilan. Jika email gagal, periksa log Laravel dan gunakan mekanisme retry yang tersedia. Jangan memproses ulang pembayaran hanya karena email konfirmasi gagal.

## Catatan Pengembangan

- Frontend menggunakan HTML, CSS, dan JavaScript tanpa proses build frontend khusus yang diperlukan untuk halaman statis saat ini.
- `assets/js/main.js` berisi inisialisasi fitur untuk halaman berbeda. Saat menambahkan fitur baru, pastikan inisialisasi aman pada halaman yang tidak memiliki elemen terkait.
- Filter kategori statis homepage berbeda dari filter katalog yang memuat data melalui API.
- Tambahkan migration baru untuk perubahan skema database yang sudah diterapkan; jangan mengubah migration lama yang sudah berjalan di database bersama.
- Jangan melakukan perubahan destruktif terhadap database lokal atau bersama tanpa backup dan persetujuan.
- Jalankan test suite setelah perubahan controller, model, middleware, route, atau alur pembayaran.
- Jangan menyimpan `.env`, token, password, data pelanggan, atau foto identitas dalam Git.

## Hal yang Perlu Diperiksa Sebelum Rilis

Sebelum deploy, verifikasi hal-hal berikut:

- [ ] Semua halaman frontend mengarah ke URL API yang benar.
- [ ] Semua form telah diuji di browser dan hasil submit-nya diverifikasi di database.
- [ ] Endpoint email bekerja dan kegagalan pengiriman tercatat dengan benar.
- [ ] Akun admin dan proses pemulihan akses sudah disiapkan dengan aman.
- [ ] `APP_DEBUG=false` dan HTTPS digunakan di produksi.
- [ ] Cookie sesi, trusted hosts, CORS, dan konfigurasi CSRF diperiksa sesuai arsitektur deployment.
- [ ] Database dan file penting memiliki backup serta prosedur pemulihan.
- [ ] Kebijakan privasi menjelaskan pengumpulan, akses, penyimpanan, dan penghapusan data pribadi.
- [ ] Prosedur KTP saat serah terima rental memiliki aturan akses, penyimpanan, dan retensi yang jelas.
- [ ] Tidak ada klaim tentang escrow otomatis, payment gateway, verifikasi otomatis, atau fitur lain yang belum benar-benar tersedia.
- [ ] Test suite dijalankan kembali dan hasilnya lulus.
- [ ] `git diff --check` bersih dan tidak ada rahasia yang ikut di-commit.

---

## Lisensi dan Kontribusi

Tambahkan informasi lisensi jika proyek akan didistribusikan secara publik. Untuk kerja tim, gunakan branch atau pull request sesuai kesepakatan tim dan sertakan ringkasan perubahan serta hasil pengujian.

**BANTULOKAL HUB** — satu platform komunitas untuk menghubungkan kebutuhan layanan harian dan penyewaan alat antarwarga.
