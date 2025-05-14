# Proyek Kelompok 1 Informatika 2022 SIMBA

# Cara Penggunaan :

1. Silahkan Clone dan install composer
2. Lalu jalankan composer require maatwebsite/excel untuk keperluan export excel
3. Setelah itu jalankan composer require twilio/sdk untuk keperluan api sms.
4. Untuk sementara pengiriman sms tidak bisa dikarenakan regulasi yang panjang dari pihak ketiganya tetapi sistemasinya dalam aplikasi sudah ada.
5. Download tambahkan path cacert.pem pada bagian berikut pada file php.ini:
   ![image](https://github.com/user-attachments/assets/6dc63a1d-81e8-40cf-acb8-064461a3ae1f)

6. Buat file .env dengan rinci berikut :
-   database :  
    DB_CONNECTION=mysql  
    DB_HOST=127.0.0.1  
    DB_PORT=3306  
    DB_DATABASE=kel1  
    DB_USERNAME=root  
    DB_PASSWORD=  

7. jalankan perintah berikut pada terminal : php artisan migrate --seed
8. jalankan perintah berikut untuk menjalankan aplikasi: php artisan serve
9. untuk user manual dapat dilihat pada docs berikut : https://docs.google.com/document/d/1E-acvYaZ3bXgycn88gx5RrXVqX2sboHZl-Q11Sa_DtE/edit?tab=t.0
   ## note : Akun yang digunakan dengan validasi data lengkap hanya yang ada pada seeders. Untuk sementara sistem registrasi belum terintergrasi dengan CIS. 
   
