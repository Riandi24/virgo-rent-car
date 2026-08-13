# TODO - Fitur "Ulasan Pelanggan" (Professional)

## Langkah
- [x] 1. Buat file SQL update: tambah kolom `status` ke `tbl_reviews` + data contoh
- [x] 2. Redesign `ulasan.php` (halaman publik): header, ringkasan rating, distribusi, grid card ulasan + foto mobil, tombol "Berikan Ulasan" (modal form), hanya tampil Approved
- [x] 3. Update `admin/kelola_reviews.php`: tambah kolom status, aksi Setujui/Tolak/Hapus, filter status
- [x] 4. Update `index.php`: query testimoni hanya Approved + tambah foto mobil pada card
- [x] 5. Tambah CSS baru di `css/style.css` (mengikuti design system existing)
- [x] 6. Hapus fitur upload foto pelanggan (sesuai permintaan user) - avatar pakai inisial nama
- [x] 7. Update `db_virgo_rent.sql` agar konsisten (tambah kolom status + data)
- [x] 8. Validasi PHP syntax (ulasan.php, admin/kelola_reviews.php, index.php) — semua "No syntax errors detected"
- [x] 9. Perkecil form ulasan (modal) menjadi ringkas seperti form komentar (sesuai feedback user)
- [x] 10. Perbaiki error "Unknown column 'status'": INSERT dibuat dinamis (cek kolom status ada/tidak) agar tidak error di database lama
- [x] 11. Pengecekan sistem menyeluruh (SYSTEM CHECK):
  - Semua file PHP lolos validasi syntax (`php -l`) — tidak ada error
  - Import SQL `reviews_update.sql` sukses → kolom `status` ditambahkan ke `tbl_reviews`
  - Verifikasi isi DB: 5 ulasan Approved + 2 Pending (benar)
  - Semua halaman frontend (index, ulasan, armada, driver, wisata, pemesanan) → HTTP 200, tanpa Fatal error/Warning/Unknown column
  - Halaman `ulasan.php`: badge "ULASAN PELANGGAN", tombol "Berikan Ulasan", modal form, rating-select, review-card, data "Karya Motor" semua tampil
  - Uji POST kirim ulasan → status 200, pesan sukses muncul, tersimpan dengan status **Pending** (menunggu persetujuan admin), tanpa error
  - Data uji coba dibersihkan (tidak meninggalkan data sampah)
  - Halaman admin `kelola_reviews.php` dilindungi auth (redirect ke login) + guard dinamis kolom status (tidak error walau kolom belum ada)

## Status: AMAN & BERJALAN
- [x] Database sudah di-update (kolom `status` aktif)
- [x] Fitur Ulasan Pelanggan berjalan end-to-end tanpa error
- [x] Tampilan form ulasan sudah ringkas & profesional (rating interaktif, 2 kolom nama+mobil)
- File `1.png` adalah gambar hero yang sudah dipakai, bukan gambar referensi baru
