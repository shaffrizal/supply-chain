# 🌍 Supply Chain Dashboard

## Deskripsi

Supply Chain Dashboard adalah aplikasi berbasis Laravel 12 yang dikembangkan sebagai proyek Ujian Akhir Semester (UAS). Aplikasi ini membantu menampilkan dan menganalisis data negara menggunakan beberapa REST API untuk mendukung analisis risiko dalam supply chain.

---

## Fitur

✅ Dashboard

✅ CRUD Data Countries

✅ Import Data Negara dari Excel

✅ Detail Data Negara

✅ World Bank API
- GDP
- Population
- Inflation

✅ Weather API

✅ Exchange Rate API

✅ News API

✅ Supply Chain Risk Score

---

## Teknologi

- Laravel 12
- PHP 8.2
- MySQL
- Bootstrap
- AdminLTE
- REST API

---

## API yang Digunakan

| API | Fungsi |
|------|--------|
| World Bank API | GDP, Population, Inflation |
| OpenWeather API | Informasi Cuaca |
| Exchange Rate API | Nilai Tukar Mata Uang |
| News API | Berita Ekonomi Dunia |

---

## Perhitungan Risk Score

Risk Score dihitung berdasarkan beberapa indikator:

- GDP
- Inflasi
- Populasi
- Kurs USD
- Kondisi Cuaca

Kategori Risk Score:

- 🟢 Low
- 🟡 Medium
- 🔴 High

---

## Cara Menjalankan

Clone repository

bash
git clone https://github.com/USERNAME/supply-chain.git


Masuk ke folder project

bash
cd supply-chain


Install dependency

bash
composer install


Copy file environment

bash
cp .env.example .env


Generate key

bash
php artisan key:generate


Konfigurasi database pada file .env.

Jalankan migrasi

bash
php artisan migrate


Jalankan server

bash
php artisan serve


---

## Pengembang

*Nama:* Shaffrizal Zal

*Universitas:* Universitas Malikussaleh

*Program Studi:* sistem informasi

---

## Status Project

🚧 Dalam Pengembangan

Pengembangan berikutnya:

- Interactive Map
- Dashboard Analytics
- Data Visualization
- Penyempurnaan Tampilan