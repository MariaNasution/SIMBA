# Proyek Kelompok 1 Informatika 2022 SIMBA

# Cara Penggunaan :

1. Silahkan Clone dan install composer
2. Lalu jalankan composer require maatwebsite/excel untuk keperluan export excel
3. Setelah itu jalankan composer require twilio/sdk untuk keperluan api sms.
4. Untuk sementara pengiriman sms tidak bisa dikarenakan regulasi yang panjang dari pihak ketiganya tetapi sistemasinya dalam aplikasi sudah ada. 
5. Buat file .env dengan rinci berikut :

-   database :
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=kel1
    DB_USERNAME=root
    DB_PASSWORD=

6. jalankan perintah berikut pada terminal : php artisan migrate --seed
7. jalankan perintah berikut untuk menjalankan aplikasi: php artisan serve
8. untuk user manual dapat dilihat pada docs berikut : https://docs.google.com/document/d/1E-acvYaZ3bXgycn88gx5RrXVqX2sboHZl-Q11Sa_DtE/edit?tab=t.0
   ## note : Akun yang digunakan dengan validasi data lengkap hanya yang ada pada seeders. Untuk sementara sistem registrasi belum terintergrasi dengan CIS. 
   

# Menjalankan dengan Docker

## Persyaratan Khusus Proyek
- PHP 8.3 (FPM Alpine, untuk Laravel)
- Node.js 20 (untuk build asset Laravel)
- Node.js 22 (untuk React frontend Simba-chatting)
- Go 1.24 (untuk backend Simba-chatting)
- MySQL (default: database `kel1`, user `root`, tanpa password)

## Variabel Lingkungan Penting
- File `.env` pada root proyek untuk aplikasi Laravel (lihat contoh di atas)
- File `.env` pada `Simba-chatting` dan `Simba-chatting/backend` jika diperlukan (opsional, sesuaikan kebutuhan)
- Untuk backend Go Simba-chatting, file `service-account.json` digunakan untuk kredensial Google (bisa di-mount atau gunakan default di image)

## Build & Jalankan
1. Pastikan Docker dan Docker Compose sudah terinstall.
2. Jalankan perintah berikut di root proyek:

   ```bash
   docker compose up --build
   ```

   Semua service akan otomatis dibuild dan dijalankan sesuai konfigurasi.

## Service & Port
- **php-app** (Laravel): http://localhost:8000
- **go-simba-chat-backend** (Go backend Simba-chatting): http://localhost:8080
- **js-simba-chatting** (React frontend Simba-chatting): http://localhost:3000
- **mysql-db** (MySQL): port 3306 (akses internal, default user: root, db: kel1)

## Konfigurasi Khusus
- Untuk pengembangan lokal, file `.env` dan `service-account.json` harus tersedia sesuai kebutuhan masing-masing service.
- Jika ingin menggunakan file `.env` pada service tertentu, hapus tanda komentar pada bagian `env_file` di `docker-compose.yml`.
- Data MySQL akan disimpan secara persisten di volume `mysql-data`.
- Untuk build asset Laravel, proses otomatis dijalankan di Dockerfile, tidak perlu build manual.
- Untuk backend Go Simba-chatting, file `service-account.json` dapat di-mount dari host jika diperlukan (lihat komentar pada compose file).

## Catatan
- Pastikan port 8000, 8080, dan 3000 tidak digunakan aplikasi lain.
- Untuk pengiriman SMS dan Recaptcha, pastikan variabel lingkungan sudah diisi dengan kredensial yang valid.
- Untuk pengujian dan pengembangan, gunakan akun yang sudah ada di seeder.
