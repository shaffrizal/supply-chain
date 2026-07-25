# Supply Chain Intelligence

Platform analisis risiko rantai pasok global berbasis Laravel 12. Aplikasi menggabungkan profil negara, pelabuhan, cuaca, ekonomi, kurs, berita, dan risk score dalam satu dashboard dark intelligence yang responsif.

## Fitur utama

- Dashboard eksekutif dengan indikator risiko, cuaca, kurs, berita, dan jaringan pelabuhan.
- Direktori 250 negara lengkap dengan CRUD, filter, profil, favorit, dan perbandingan.
- Direktori 12.000 pelabuhan dengan CRUD, pencarian, status operasional, kapasitas, dan risiko.
- Satu peta global terpadu untuk negara dan pelabuhan dengan layer, filter risiko, pencarian, clustering, popup, dan fullscreen.
- Direktori cuaca 250 negara dengan pencarian, bendera, kondisi aktual, dan fallback aman.
- Peta cuaca global terpisah dengan radar hujan RainViewer, layer observasi, badai, angin kencang, filter negara, popup, dan fullscreen.
- Indikator dan tren ekonomi World Bank untuk seluruh negara pada dataset, disimpan dalam cache.
- Exchange Rate API dengan pilihan mata uang dasar dan fallback ketika layanan tidak tersedia.
- News intelligence, cache berita, dan analisis sentimen.
- Risk score serta klasifikasi Low, Medium, dan High.
- Watchlist, shipping routes, REST API internal, dan panel administrasi.

## Teknologi

- PHP 8.2 dan Laravel 12
- MySQL untuk pengembangan; SQLite in-memory untuk pengujian
- Bootstrap 5.3, Chart.js, dan Leaflet
- REST Countries, Open-Meteo, RainViewer, World Bank, ExchangeRate API, dan NewsAPI

## Instalasi

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
```

Atur koneksi database pada `.env`, kemudian jalankan:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Aplikasi tersedia di `http://127.0.0.1:8000`.

Panel admin berada di `/login`. Akun awal dari seeder menggunakan `zalhom@gmail.com` dan `zalhom123`; ubah melalui `ADMIN_EMAIL` dan `ADMIN_PASSWORD` sebelum deployment.

## Konfigurasi API

Tambahkan nilai berikut ke `.env` bila tersedia:

```env
OPENWEATHER_API_KEY=
NEWS_API_KEY=
```

API publik tetap memiliki timeout, cache, dan fallback agar halaman tidak gagal ketika jaringan lambat atau layanan eksternal tidak tersedia.

## Bootstrap 5

Seluruh halaman utama dan administrasi menggunakan layout Bootstrap 5.3 native. AdminLTE 3 beserta dependency Bootstrap 4 telah dihapus agar tidak terjadi konflik CSS atau JavaScript. Sidebar, top navigation, modal, alert, form, tabel, pagination, serta utility responsif ditangani oleh shell aplikasi Bootstrap 5.

## Akses dan keamanan data

- Direktori dan detail negara/pelabuhan dapat dibaca publik.
- Create, update, delete, dan sinkronisasi dataset hanya tersedia untuk akun dengan role `Admin`.
- Pengelolaan dataset pelabuhan tersedia di `/admin/ports`.
- Watchlist mendukung tambah/hapus asynchronous melalui Axios dengan perlindungan CSRF dan pemeriksaan kepemilikan.

## Dataset

Seeder utama menghasilkan:

- 250 profil negara
- 12.000 fasilitas pelabuhan
- data awal shipping routes

Untuk memulihkan dataset pengembangan:

```bash
php artisan db:seed --force
```

## Pengujian dan pemeriksaan

```bash
php artisan test
php artisan view:cache
php artisan route:list
```

Pengujian mencakup akses halaman utama, Bootstrap 5, tren data nyata, klasifikasi layer cuaca, radar hujan, direktori dan peta cuaca, fallback Weather/World Bank/News/Currency, risk scoring, sentiment analysis, AJAX watchlist, otorisasi CRUD, validasi data, CRUD pelabuhan, dan halaman administrasi.

Checklist pengumpulan dan deployment tersedia di [`SUBMISSION_CHECKLIST.md`](SUBMISSION_CHECKLIST.md).

## Catatan deployment

- Gunakan `APP_DEBUG=false` pada lingkungan presentasi/produksi.
- Jangan commit file `.env` atau API key.
- Jalankan `php artisan optimize` setelah konfigurasi produksi final.
- Jalankan pengujian sebelum demo atau pengumpulan.

### Scheduler risk trend 90 hari

`risk:update` dijadwalkan setiap hari pukul 01.00 menggunakan zona waktu `Asia/Jakarta`. Setiap negara hanya mempunyai satu snapshot per tanggal; eksekusi ulang pada hari yang sama memperbarui snapshot tersebut dan data yang lebih tua dari 90 hari otomatis dibersihkan.

Jalankan migrasi terbaru terlebih dahulu:

```bash
php artisan migrate --force
php artisan schedule:list
```

Untuk Windows/XAMPP, buka PowerShell sebagai Administrator lalu jalankan:

```powershell
powershell -ExecutionPolicy Bypass -File scripts/install-risk-scheduler.ps1
```

Task Scheduler akan menjalankan `php artisan schedule:run` setiap menit. Untuk Linux, tambahkan cron berikut:

```cron
* * * * * cd /path/to/supply-chain && php artisan schedule:run >> /dev/null 2>&1
```

Saat pengembangan lokal, scheduler dapat dijalankan langsung dengan:

```bash
php artisan schedule:work
```
