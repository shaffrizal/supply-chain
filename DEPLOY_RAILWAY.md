# Deploy Supply Chain Intelligence ke Railway

Dokumen ini menggunakan arsitektur berikut:

- `App`: aplikasi Laravel yang dapat diakses melalui browser.
- `MySQL`: database production.
- `Risk Update`: cron harian untuk memperbarui skor risiko.
- `World Bank Sync`: cron harian untuk memperbarui indikator ekonomi.

## 1. Siapkan repository

Pastikan perubahan terbaru sudah di-commit dan di-push ke branch `main` GitHub.

## 2. Buat project dan database

1. Login ke Railway.
2. Pilih **New Project** lalu **Deploy from GitHub repo**.
3. Pilih repository `shaffrizal/supply-chain`.
4. Pada project canvas, klik **+ New** → **Database** → **Add MySQL**.
5. Ubah nama service aplikasi menjadi `App` dan database menjadi `MySQL`.

## 3. Isi variables service App

Masuk ke service **App** → **Variables** → **Raw Editor**, kemudian isi:

```dotenv
APP_NAME="Supply Chain Intelligence"
APP_ENV=production
APP_KEY=GANTI_DENGAN_APP_KEY
APP_DEBUG=false
APP_URL=https://${{RAILWAY_PUBLIC_DOMAIN}}
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stderr
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_URL=${{MySQL.MYSQL_URL}}

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database

ADMIN_NAME=Administrator
ADMIN_EMAIL=GANTI_DENGAN_EMAIL_ADMIN
ADMIN_PASSWORD=GANTI_DENGAN_PASSWORD_KUAT

OPENWEATHER_API_KEY=GANTI_JIKA_TERSEDIA
NEWS_API_KEY=GANTI_JIKA_TERSEDIA
EXCHANGE_RATE_API_KEY=GANTI_JIKA_TERSEDIA
SEED_REFRESH_RISK_SCORES=false
```

Buat `APP_KEY` di komputer lokal dari folder project:

```powershell
php artisan key:generate --show
```

Salin hasil lengkap yang diawali `base64:`. Jangan memakai password admin
`zalhom123` di production; gunakan password unik minimal 12 karakter.

## 4. Deploy aplikasi

1. Review perubahan variables Railway, lalu klik **Deploy**.
2. Tunggu build dan pre-deploy selesai.
3. Service **App** → **Settings** → **Networking** → **Generate Domain**.
4. Buka domain tersebut dan pastikan `/up` memberikan respons berhasil.

File `railway.json` akan menjalankan build Vite, migrasi database, cache
production Laravel, dan health check secara otomatis.

## 5. Isi data production satu kali

Pada project canvas, klik kanan service **App** → **Copy SSH Command**.
Jalankan command tersebut di PowerShell. Setelah masuk ke container, jalankan:

```sh
sh ./railway/initialize-data.sh
```

Tunggu sampai seeding, sinkronisasi World Bank, dan pembaruan risk score selesai.
Perintah ini hanya untuk inisialisasi pertama, bukan untuk setiap deploy.

## 6. Buat cron Risk Update

1. Klik **+ New** → **GitHub Repo** dan pilih repository yang sama.
2. Beri nama service `Risk Update`.
3. Buka **Settings** → **Config File Path**.
4. Isi `/railway.cron-risk.json`.
5. Salin variables dari service App, terutama `APP_KEY`, `DB_CONNECTION`,
   `DB_URL`, konfigurasi cache, dan API keys.
6. Deploy service.

Cron berjalan pukul 01.00 WIB (`0 18 * * *` dalam UTC).

## 7. Buat cron World Bank Sync

1. Klik **+ New** → **GitHub Repo** dan pilih repository yang sama.
2. Beri nama service `World Bank Sync`.
3. Buka **Settings** → **Config File Path**.
4. Isi `/railway.cron-worldbank.json`.
5. Salin variables yang sama dari service App.
6. Deploy service.

Cron berjalan pukul 02.00 WIB (`0 19 * * *` dalam UTC).

## 8. Pemeriksaan akhir

- Buka halaman login, dashboard, map, economy, news, risk score, dan report.
- Login sebagai admin dengan kredensial production.
- Cetak satu report PDF dari browser.
- Pastikan log service tidak memiliki error.
- Pastikan service MySQL tidak memiliki public TCP proxy jika tidak diperlukan.
- Seal `APP_KEY`, password, dan seluruh API key melalui menu tiga titik variable.

