-- recreate_tbl_reviews.sql
-- Script untuk (re)buat tabel tbl_reviews dan sampel data bila tabel ulasan terhapus.
-- Sesuaikan nama database pada baris USE jika database Anda berbeda.

USE `virgo_rent`;

-- Pastikan tabel tbl_kendaraan ada; tambahkan satu kendaraan contoh (id_kendaraan = 1) jika belum ada
-- Sesuaikan kolom sesuai skema tbl_kendaraan Anda jika perlu.
INSERT INTO tbl_kendaraan (id_kendaraan, nama_mobil, kategori, transmisi, bahan_bakar, kapasitas_kursi, harga_sewa, status, gambar)
VALUES (1, 'Toyota Avanza - Contoh', 'mpv', 'Manual', 'Bensin', 7, 350000, 'Tersedia', 'sample_car1.jpg')
ON DUPLICATE KEY UPDATE nama_mobil = VALUES(nama_mobil), gambar = VALUES(gambar);

-- Hapus tabel lama jika ada (opsional)
DROP TABLE IF EXISTS `tbl_reviews`;

-- Buat tabel tbl_reviews baru
CREATE TABLE `tbl_reviews` (
  `id_review` int(11) NOT NULL AUTO_INCREMENT,
  `id_kendaraan` int(11) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `rating` tinyint(4) NOT NULL DEFAULT 5,
  `komentar` text DEFAULT NULL,
  `foto_review` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Approved',
  `tanggal` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_review`),
  KEY `idx_review_kendaraan` (`id_kendaraan`),
  KEY `idx_review_status` (`status`),
  CONSTRAINT `fk_review_kendaraan` FOREIGN KEY (`id_kendaraan`) REFERENCES `tbl_kendaraan` (`id_kendaraan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `chk_rating` CHECK (`rating` BETWEEN 1 AND 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Masukkan beberapa ulasan sample (pastikan file gambar ada di folder uploads/)
INSERT INTO `tbl_reviews` (id_kendaraan, nama_pelanggan, rating, komentar, foto_review, status, tanggal)
VALUES
(1, 'Andi', 5, 'Mobil bersih, pelayanan ramah. Sangat direkomendasikan.', 'review_sample1.jpg,review_sample2.jpg', 'Approved', NOW()),
(1, 'Siti', 4, 'Pengalaman oke, catatan: AC agak kurang dingin.', 'review_sample3.jpg', 'Approved', NOW()),
(1, 'Budi', 5, 'Driver tepat waktu, mobil nyaman.', NULL, 'Approved', NOW());

-- Tampilkan 10 ulasan terbaru untuk verifikasi
SELECT id_review, id_kendaraan, nama_pelanggan, rating, foto_review, status, tanggal
FROM tbl_reviews ORDER BY tanggal DESC LIMIT 10;

-- Catatan:
-- - Pastikan folder C:\xampp\htdocs\virgo_rent\uploads\ berisi file gambar: review_sample1.jpg, review_sample2.jpg, review_sample3.jpg, sample_car1.jpg
-- - Jika nama database Anda bukan 'virgo_rent', ubah baris USE `virgo_rent`;
-- - Jika tbl_kendaraan Anda memiliki kolom NOT NULL lain, sesuaikan INSERT kendaraan contoh di atas.
