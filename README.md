# Proyek Bersama Informatika

# Cara Penggunaan :

1. Silahkan Clone dan install composer
2. Buat file .env dengan rinci berikut :

-   databse :
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=kel1
    DB_USERNAME=root
    DB_PASSWORD=
    mail
-   Email API/SMTP :
  ambil Integrasi pada https://mailtrap.io/ ,dengan code samples PHP: laravel 9+
    MAIL_MAILER=smtp
    MAIL_HOST=sandbox.smtp.mailtrap.io
    MAIL_PORT=587
    MAIL_USERNAME=ba3f109faa13be
    MAIL_PASSWORD=527e88c2aba7b8
    MAIL_ENCRYPTION=tls
    MAIL_FROM_ADDRESS=no-reply@example.com
    MAIL_FROM_NAME="Student Information System"

    berikut contoh file .env yang bisa dibuat sebagai acuan : https://drive.google.com/drive/folders/1iTeUtG3vZoFg0SSN1HxVWpEjyiNVlyz4?usp=sharing

3. jalankan perintah berikut pada terminal : php artisan migrate --seed
4. jalankan perintah berikut untuk menjalankan aplikasi: php artisan serve
5. untuk user manual dapat dilihat pada drive berikut : https://drive.google.com/file/d/1NjbfPxt8BSZOCmvmLa-zNkxa8FFfWvuY/view?usp=sharing
   ## note : Akun yang dapat dibuat dan digunakan hanya untuk mahasiswa angkatan 2020 kebawah 
   
