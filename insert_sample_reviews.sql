-- insert_sample_reviews.sql
-- Script ini menambahkan kolom foto_review jika belum ada,
-- lalu menambahkan 1 kendaraan contoh dan 3 ulasan sample.
-- Ubah nama database di baris USE jika berbeda.

USE `virgo_rent`;

-- 1) Backup ringkas tabel tbl_reviews (opsional)
CREATE TABLE IF NOT EXISTS tbl_reviews_backup AS SELECT * FROM tbl_reviews LIMIT 0;

-- 2) Tambahkan kolom foto_review jika belum ada (MySQL 8.0+ mendukung IF NOT EXISTS)
ALTER TABLE tbl_reviews ADD COLUMN IF NOT EXISTS foto_review VARCHAR(255) NULL;

-- 3) Pastikan ada kendaraan contoh dengan id_kendaraan = 1
-- Sesuaikan kolom lain jika tabel tbl_kendaraan Anda memiliki kolom NOT NULL tambahan
INSERT INTO tbl_kendaraan (id_kendaraan, nama_mobil, gambar)
VALUES (1, 'Toyota Avanza - Contoh', 'sample_car1.jpg')
ON DUPLICATE KEY UPDATE nama_mobil = VALUES(nama_mobil), gambar = VALUES(gambar);

-- 4) Masukkan ulasan sample (foto_review dipisah dengan koma jika banyak foto)
INSERT INTO tbl_reviews (id_kendaraan, nama_pelanggan, rating, komentar, foto_review, status, tanggal)
VALUES
(1, 'Andi', 5, 'Mobil bersih, pelayanan ramah. Sangat direkomendasikan.', 'review_sample1.jpg,review_sample2.jpg', 'Approved', NOW()),
(1, 'Siti', 4, 'Pengalaman oke, catatan: AC agak kurang dingin.', 'review_sample3.jpg', 'Approved', NOW()),
(1, 'Budi', 5, 'Driver tepat waktu, mobil nyaman.', '', 'Approved', NOW());

-- 5) Verifikasi data yang baru dimasukkan
SELECT id_kendaraan, nama_pelanggan, rating, foto_review, tanggal FROM tbl_reviews ORDER BY tanggal DESC LIMIT 10;

-- Catatan:
-- - Jika tabel Anda memiliki kolom NOT NULL tambahan, ubah bagian INSERT agar menyertakan kolom yang diperlukan.
-- - Pastikan file gambar (review_sample1.jpg, review_sample2.jpg, review_sample3.jpg, sample_car1.jpg)
--   diletakkan di folder C:\xampp\htdocs\virgo_rent\uploads\ agar dapat diakses melalui
--   http://localhost/virgo_rent/uploads/<filename>
-- - Untuk mengimpor via phpMyAdmin: buka phpMyAdmin -> pilih database -> Import -> pilih file ini -> Go.
-- - Untuk mengimpor via MySQL CLI: mysql -u root -p < insert_sample_reviews.sql
