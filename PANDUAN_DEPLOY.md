Panduan Deploy Virgo Rent Car

Ringkasan
--------
Panduan ini menjelaskan langkah-langkah yang disarankan untuk men-deploy aplikasi Virgo Rent Car ke server produksi dan menghubungkannya ke domain. Termasuk: persiapan database, konfigurasi .env, virtual host (Apache/Nginx), SSL, permission, backup, dan checklist keamanan.

1) Persiapan awal
-----------------
- Buat server dengan PHP 8.x (direkomendasikan 8.1/8.2) dan MySQL/MariaDB.
- Pasang paket yang diperlukan (Apache atau Nginx, PHP-FPM, MySQL).
- Pastikan modul PHP umum terinstal: mysqli, mbstring, gd (untuk gambar), fileinfo.

2) Database
-----------
- Buat database baru (contoh: db_virgo_rent) dan user khusus (jangan gunakan root):

  mysql -u root -p
  CREATE DATABASE db_virgo_rent CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
  CREATE USER 'virgo_user'@'localhost' IDENTIFIED BY 'GANTI_DENGAN_PASSWORD_YANGKUAT';
  GRANT ALL PRIVILEGES ON db_virgo_rent.* TO 'virgo_user'@'localhost';
  FLUSH PRIVILEGES;

- Import file SQL yang ada di repo (db_virgo_rent.sql):

  mysql -u virgo_user -p db_virgo_rent < db_virgo_rent.sql

3) Konfigurasi .env
-------------------
- Salin file .env.example menjadi .env di root proyek dan isi kredensial produksi.
- Contoh (jangan commit .env ke git):

  DB_HOST=localhost
  DB_USER=virgo_user
  DB_PASS=GANTI_DENGAN_PASSWORD_YANGKUAT
  DB_NAME=db_virgo_rent
  DISPLAY_ERRORS=0

- Pastikan file .env memiliki permission terbaca oleh webserver pemilik (contoh: 640).

4) VirtualHost / Server Block
-----------------------------
- Apache (contoh konfigurasi):

<VirtualHost *:80>
    ServerName contohdomain.com
    ServerAlias www.contohdomain.com
    DocumentRoot /var/www/virgo_rent

    <Directory /var/www/virgo_rent>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/virgo_error.log
    CustomLog ${APACHE_LOG_DIR}/virgo_access.log combined
</VirtualHost>

- Nginx (contoh konfigurasi):

server {
    listen 80;
    server_name contohdomain.com www.contohdomain.com;
    root /var/www/virgo_rent;
    index index.php index.html index.htm;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.1-fpm.sock; # sesuaikan versi
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.ht {
        deny all;
    }
}

5) HTTPS / SSL
--------------
- Gunakan certbot untuk memasang Let's Encrypt:

  sudo apt install certbot python3-certbot-nginx  # atau certbot-apache untuk Apache
  sudo certbot --nginx -d contohdomain.com -d www.contohdomain.com

- Setelah SSL terpasang, pastikan redirect ke HTTPS dan cek mixed-content.

6) Permissions & Security filesystem
-----------------------------------
- Letakkan aplikasi di /var/www/virgo_rent (atau sesuai hosting) dan atur kepemilikan ke user webserver (www-data atau apache):

  sudo chown -R www-data:www-data /var/www/virgo_rent
  sudo find /var/www/virgo_rent -type d -exec chmod 755 {} \;
  sudo find /var/www/virgo_rent -type f -exec chmod 644 {} \;

- Pastikan uploads writable oleh webserver:

  sudo chown -R www-data:www-data /var/www/virgo_rent/uploads
  sudo chmod -R 750 /var/www/virgo_rent/uploads

- Jangan letakkan file backup (.zip) atau .env yang berisi rahasia di repo publik.

7) Cron & Backup
----------------
- Setup cron job untuk backup database dan folder uploads. Contoh script sederhana:

  #!/bin/bash
  BACKUP_DIR=/var/backups/virgo_rent
  mkdir -p $BACKUP_DIR
  mysqldump -u virgo_user -p'PASSWORD' db_virgo_rent | gzip > $BACKUP_DIR/db_$(date +%F).sql.gz
  rsync -a /var/www/virgo_rent/uploads $BACKUP_DIR/uploads_$(date +%F)

- Tambahkan ke crontab (harian):
  0 2 * * * /usr/local/bin/virgo_backup.sh

8) Checklist keamanan sebelum buka domain
----------------------------------------
- [ ] DISPLAY_ERRORS=0 di .env
- [ ] .env tidak di-commit ke repo dan hanya readable oleh webserver
- [ ] Semua data input yang digunakan dalam query dibuat prepared statements atau divalidasi/di-cast
- [ ] Validasi dan batasan upload file (tipe, ukuran), gunakan fileinfo
- [ ] CSRF token pada form yang mengubah data (admin)
- [ ] Proteksi direktori admin (opsional: Basic Auth atau IP allowlist)
- [ ] Backup rutin dan pengujian recovery
- [ ] SSL terpasang (HTTPS)

9) Hal teknis yang diselesaikan oleh tim ini
-------------------------------------------
- Telah diperbaiki sejumlah operasi HAPUS/UPDATE menjadi prepared statements di:
  - admin/kelola_driver.php
  - admin/kelola_wisata.php
  - admin/data_pemesanan.php
  - admin/kelola_mobil.php (hapus diubah ke prepared)
- koneksi.php sudah mendukung .env dan setting DISPLAY_ERRORS.

10) Rekomendasi lanjutan (opsional)
----------------------------------
- Implementasi CSRF dan validasi server-side penuh.
- Gunakan framework (mis. Laravel) kalau ingin skala dan keamanan lebih baik.
- Tempatkan uploads di storage terpisah (S3 atau bucket) untuk skalabilitas.

Jika ingin, saya juga bisa:
- Membuat file .env di repo (tidak aman jika berisi password) — lebih aman Anda yang membuat di server.
- Melanjutkan migrasi semua query tersisa ke prepared statements dan menambahkan CSRF token.
- Membuat draft perintah Git commit & PR jika ingin menyimpan perubahan ini ke version control.

