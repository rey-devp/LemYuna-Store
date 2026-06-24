# LumYena Store

Selamat datang di repositori LumYena Store! Proyek ini adalah sebuah platform toko online (katalog) yang dibangun menggunakan Laravel 10 dan MongoDB. 

## 🚀 Fitur Utama
- Katalog Produk & Kategori
- Autentikasi Pengguna & Admin
- Terhubung penuh ke MongoDB
- Dibangun dengan Laravel 10 & Vite (Tailwind CSS)

## 📋 Prasyarat Sistem
Untuk menjalankan proyek ini di komputer Anda, pastikan Anda memiliki salah satu dari lingkungan (*environment*) berikut:

**Opsi A (Gaya Modern / Disarankan):**
- Docker Desktop menyala di background

**Opsi B (Gaya Tradisional):**
- PHP 8.1 atau lebih baru
- Composer
- Node.js & NPM
- MongoDB Server (lokal atau cloud seperti Atlas)
- Ekstensi PHP MongoDB (`ext-mongodb`) diaktifkan di `php.ini` Anda.

---

## 🛠️ Cara Instalasi & Menjalankan Proyek

Pilih salah satu cara di bawah ini sesuai dengan alat yang Anda sukai:

### Opsi 1: Menggunakan Docker (Paling Mudah)
Buka terminal di folder proyek ini dan jalankan perintah berikut:

```bash
# 1. Install dependencies PHP
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php81-composer:latest \
    composer install --ignore-platform-reqs

# 2. Siapkan file environment
cp .env.example .env

# 3. Nyalakan server lokal (Nginx, PHP, dan MongoDB otomatis menyala)
./vendor/bin/sail up -d

# 4. Generate key aplikasi
./vendor/bin/sail artisan key:generate

# 5. Lakukan migrasi dan seeding database (Wajib!)
./vendor/bin/sail artisan migrate:fresh --seed

# 6. Install dan nyalakan server frontend
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev
```

### Opsi 2: Menggunakan XAMPP / Manual
Buka terminal di folder proyek ini dan jalankan perintah berikut:

```bash
# 1. Install dependencies
composer install
npm install

# 2. Siapkan file environment
cp .env.example .env
php artisan key:generate

# 3. PENTING: Buka file .env dan pastikan konfigurasi menunjuk ke MongoDB Anda
# DB_CONNECTION=mongodb
# DB_URI=mongodb://127.0.0.1:27017
# DB_DATABASE=webstore_catalog

# 4. Lakukan migrasi dan seeding database (Wajib!)
php artisan migrate:fresh --seed

# 5. Nyalakan server backend dan frontend di dua terminal berbeda
php artisan serve
npm run dev
```

---

## 🔑 Akun Uji Coba (Dummy Accounts)
Setelah berhasil melakukan *seeding* database, Anda bisa login menggunakan akun bawaan berikut:

**👑 Akun Admin:**
- **Email:** `admin@lumyena.com`
- **Password:** `password`

**👤 Akun Pelanggan:**
- **Email:** `customer@gmail.com`
- **Password:** `password`
