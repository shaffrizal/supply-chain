# Supply Chain Intelligence

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white)](https://www.php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)](https://getbootstrap.com)
[![Deployment](https://img.shields.io/badge/Deployment-Railway-0B0D0E?logo=railway&logoColor=white)](https://railway.com)

Platform intelijen risiko rantai pasok global yang menyatukan profil negara, jaringan pelabuhan, cuaca, indikator ekonomi, kurs, berita, dan skor risiko dalam satu dashboard responsif.

**Demo:** [supply-chain-production-6560.up.railway.app](https://supply-chain-production-6560.up.railway.app)

> Aplikasi mewajibkan autentikasi. Pengunjung dapat membuat akun pengguna melalui halaman registrasi. Akun administrator dibuat menggunakan environment variable dan tidak dipublikasikan di repository.

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Hak Akses](#hak-akses)
- [Teknologi](#teknologi)
- [Arsitektur](#arsitektur)
- [Instalasi Lokal](#instalasi-lokal)
- [Konfigurasi Environment](#konfigurasi-environment)
- [Dataset dan Sinkronisasi](#dataset-dan-sinkronisasi)
- [Deployment Railway](#deployment-railway)
- [Pengujian](#pengujian)
- [Keamanan](#keamanan)
- [Pengembang](#pengembang)

## Fitur Utama

- Dashboard eksekutif dengan ringkasan risiko, cuaca, kurs, berita, negara, pelabuhan, dan rute pengiriman.
- Direktori 250 negara dengan pencarian, filter, profil, watchlist, dan perbandingan.
- Direktori 12.000 fasilitas pelabuhan dengan status operasional, kapasitas, lokasi, dan tingkat risiko.
- Peta global berbasis Leaflet dengan marker clustering, layer negara/pelabuhan, filter risiko, popup, dan mode layar penuh.
- Direktori dan peta cuaca global dengan OpenWeatherMap, Open-Meteo, serta radar hujan RainViewer.
- Indikator ekonomi World Bank: GDP, populasi, inflasi, pertumbuhan, perdagangan, ekspor, dan impor.
- Informasi nilai tukar, berita global, cache berita, serta analisis sentimen.
- Perhitungan risk score berbobot dengan klasifikasi `Low`, `Medium`, dan `High`.
- Watchlist personal dan visualisasi jaringan rute pengiriman.
- Report Center dengan print-to-PDF dan ekspor CSV yang kompatibel dengan Excel.
- Panel administrasi untuk mengelola pengguna, negara, pelabuhan, dan artikel.
- Antarmuka Indonesia/English serta dukungan penerjemahan bahasa global.
- REST API internal dengan rate limit 60 request per menit per client.

## Hak Akses

| Kemampuan | User | Admin |
|---|:---:|:---:|
| Dashboard dan intelligence modules | Ya | Ya |
| Profil negara, pelabuhan, cuaca, dan peta | Ya | Ya |
| Watchlist dan laporan umum | Ya | Ya |
| Kelola negara dan pelabuhan | Tidak | Ya |
| Kelola pengguna dan artikel | Tidak | Ya |
| Laporan pengguna dan artikel | Tidak | Ya |

Pengguna yang belum terautentikasi akan diarahkan ke `/login`. Registrasi publik selalu membuat akun dengan role `User`; role administrator hanya dikelola melalui konfigurasi dan panel admin.

## Teknologi

| Lapisan | Teknologi |
|---|---|
| Backend | PHP 8.2+, Laravel 12 |
| Frontend | Blade, Bootstrap 5.3, Vite |
| Database | MySQL; SQLite in-memory untuk pengujian |
| Visualisasi | Chart.js, Leaflet, MarkerCluster |
| Data eksternal | REST Countries, World Bank, OpenWeatherMap, Open-Meteo, RainViewer, NewsAPI, GNews |
| Deployment | Railway, Railpack |

## Arsitektur

```text
Browser / Mobile
       |
Laravel Routes + Authentication + Authorization
       |
Controllers ---- Services / External API Providers
       |                         |
Eloquent Models             Cached responses
       |
MySQL Database
```

Struktur utama repository:

```text
app/                 controller, model, service, command, dan policy
bootstrap/           bootstrap aplikasi Laravel
config/              konfigurasi aplikasi dan provider
database/            migration, seeder, factory, dan dataset
public/              entry point serta aset publik
resources/           Blade views, stylesheet, dan JavaScript
routes/              web routes, API routes, dan scheduler
railway/             initialization script untuk deployment
tests/               automated feature tests
```

## Instalasi Lokal

Prasyarat:

- PHP 8.2 atau lebih baru
- Composer
- Node.js 20 atau lebih baru
- MySQL 8

```bash
git clone https://github.com/shaffrizal/supply-chain.git
cd supply-chain
composer install
npm install
copy .env.example .env
php artisan key:generate
```

Atur koneksi database pada `.env`, lalu jalankan:

```bash
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Aplikasi lokal tersedia di `http://127.0.0.1:8000`.

## Konfigurasi Environment

Konfigurasi minimum:

```env
APP_NAME="Supply Chain Intelligence"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=supply_chain
DB_USERNAME=root
DB_PASSWORD=

ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=use-a-unique-strong-password
```

Integrasi eksternal bersifat opsional karena aplikasi memiliki timeout, cache, dan fallback:

```env
OPENWEATHER_API_KEY=
NEWS_API_KEY=
GNEWS_API_KEY=
SEED_REFRESH_RISK_SCORES=false
```

Jangan menyimpan `.env`, password, maupun API key ke Git.

## Dataset dan Sinkronisasi

Seeder utama menyiapkan 250 profil negara, 12.000 fasilitas pelabuhan, rute pengiriman, serta akun administrator dari environment variable.

```bash
php artisan db:seed --force
php artisan worldbank:sync --fresh
php artisan risk:update
```

Sebagian fasilitas pelabuhan diperkaya dengan data simulasi untuk demonstrasi analisis akademik skala besar; data tersebut bukan klaim bahwa seluruh 12.000 fasilitas berasal langsung dari API eksternal.

Scheduler menggunakan zona waktu `Asia/Jakarta`:

- `risk:update` setiap hari pukul 01.00.
- `worldbank:sync` setiap hari pukul 02.00.
- Snapshot risiko disimpan satu kali per negara per hari dan dipertahankan selama 90 hari.

Untuk memeriksa jadwal:

```bash
php artisan schedule:list
```

## Deployment Railway

Repository sudah menyediakan [`railway.json`](railway.json) dan initialization script [`railway/init-app.sh`](railway/init-app.sh). Proses pre-deploy menjalankan migration produksi serta membangun cache Laravel. Health check tersedia pada `/up`.

Ringkasan deployment:

1. Hubungkan repository GitHub ke Railway.
2. Tambahkan service MySQL.
3. Hubungkan variabel database MySQL ke service aplikasi.
4. Isi `APP_KEY`, `APP_URL`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`, dan API key yang diperlukan.
5. Pastikan `APP_ENV=production` dan `APP_DEBUG=false`.
6. Deploy aplikasi, lalu jalankan `php artisan db:seed --force` satu kali melalui Railway Shell.

Setelah mengubah kredensial admin pada environment, jalankan kembali seeder agar akun diperbarui.

## Pengujian

```bash
php artisan test
php artisan route:list
php artisan view:cache
```

Test suite mencakup autentikasi dan registrasi, otorisasi role, CRUD admin, dashboard, data ekonomi, cuaca, berita, risk scoring, watchlist AJAX, pelabuhan, report, API fallback, serta perlindungan akses guest.

Checklist pemeriksaan akhir tersedia di [`SUBMISSION_CHECKLIST.md`](SUBMISSION_CHECKLIST.md).

## Keamanan

- Seluruh halaman platform dilindungi autentikasi.
- Operasi administratif dilindungi authorization gate.
- Password disimpan menggunakan hashing Laravel.
- Session ID diregenerasi setelah autentikasi.
- Login dan registrasi dilindungi rate limiting.
- Form state-changing dilindungi CSRF.
- API publik dibatasi 60 request per menit per client.
- Mode produksi menggunakan `APP_DEBUG=false`.
- Secret hanya disimpan melalui environment variable Railway.

Jika menemukan masalah keamanan, jangan mempublikasikan kredensial atau detail eksploitasi melalui issue publik.

## Pengembang

Dikembangkan oleh [shaffrizal](https://github.com/shaffrizal) sebagai platform demonstrasi dan analisis supply-chain intelligence berbasis Laravel.

---

Dokumentasi ini mencerminkan konfigurasi aplikasi pada branch `main`.
