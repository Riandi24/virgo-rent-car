# 🐙 PANDUAN: MEMPUBLIKASIKAN WEBSITE VIRGO RENT CAR DENGAN GITHUB

Halo! Dokumen ini menjelaskan cara menggunakan **GitHub** untuk website Virgo Rent Car.
Panduan ini dibuat **ramah pemula** dan langkah demi langkah.

---

## ⚠️ PENTING — Pahami Dulu Konsepnya!

### GitHub Pages TIDAK BISA Menjalankan Website PHP

| Pertanyaan | Jawaban |
|-----------|---------|
| Apakah GitHub Pages bisa hosting website ini? | ❌ **TIDAK BISA** |
| Kenapa? | GitHub Pages hanya melayani file **statis** (HTML, CSS, JS). Ia **tidak punya server PHP** dan **tidak punya MySQL**. |
| Website Virgo Rent butuh apa? | Butuh **PHP** (untuk menjalankan `index.php`, `pemesanan.php`, dll) dan **MySQL** (untuk database `db_virgo_rent`). |

> 🎯 **KESIMPULAN:** GitHub dipakai sebagai **penyimpanan kode** (repository),
> BUKAN sebagai tempat website berjalan. Website tetap di-host di penyedia
> hosting yang mendukung PHP + MySQL (seperti **InfinityFree**).

### Jadi Peran GitHub di Sini Adalah:

1. **Tempat menyimpan kode** (backup aman di cloud)
2. **Manajemen versi** (bisa kembali ke versi lama jika ada error)
3. **Sumber file untuk deploy** (download kode dari GitHub → upload ke hosting)

---

## 🗺️ ALUR KESELURUHAN

```
LANGKAH 1 ──► Buat akun GitHub
LANGKAH 2 ──► Buat Repository (tempat kode)
LANGKAH 3 ──► Upload kode Virgo Rent ke GitHub
LANGKAH 4 ──► Buat hosting PHP+MySQL (InfinityFree)
LANGKAH 5 ──► Download kode dari GitHub → upload ke hosting
LANGKAH 6 ──► Update koneksi database & website LIVE! 🎉
```

---

## ✅ LANGKAH 1 — BUAT AKUN GITHUB

1. Buka **https://github.com** di browser.
2. Klik **"Sign up"**.
3. Isi:
   - **Email** → email aktif Anda
   - **Password** → buat password kuat
   - **Username** → pilih nama (mis. `virgorent`)
4. Verifikasi kode yang dikirim ke email.
5. Klik **"Create account"**.
6. Selesai! Anda sekarang punya akun GitHub.

---

## ✅ LANGKAH 2 — BUAT REPOSITORY

Repository = folder tempat kode Anda disimpan di GitHub.

1. Setelah login, klik tombol **"+"** di pojok kanan atas → pilih **"New repository"**.
2. Isi form:
   - **Repository name**: `virgo-rent` (bebas, tanpa spasi)
   - **Description**: `Website rental mobil Virgo Rent Car`
   - Pilih **"Public"** (bisa dilihat semua orang) atau **"Private"** (hanya Anda)
   - ⚠️ **JANGAN centang** "Add a README file", "Add .gitignore", atau "Choose a license"
     (biarkan kosong, karena kita akan upload kode sendiri)
3. Klik **"Create repository"**.
4. Anda akan melihat halaman berisi perintah git. **JANGAN tutup halaman ini** — kita pakai nanti.

---

## ✅ LANGKAH 3 — UPLOAD KODE KE GITHUB

Ada 2 cara. Pilih salah satu yang paling mudah untuk Anda.

### Metode A — GitHub Desktop (PALING MUDAH untuk pemula) ⭐

1. Download & install **GitHub Desktop**: https://desktop.github.com
2. Buka GitHub Desktop → login dengan akun GitHub Anda.
3. Klik **"File" → "Add local repository"** (atau "Add Existing Repository").
4. Pilih folder proyek: **`C:\xampp\htdocs\virgo_rent`**
5. Jika muncul pertanyaan, pilih bahwa ini bukan repositori git, biarkan GitHub Desktop
   membuatkannya secara otomatis.
6. Centang file yang mau di-upload. Pastikan file berikut **TIDAK ikut ter-upload**
   (karena sudah dikecualikan otomatis oleh file `.gitignore`):
   - Folder `_arsip/`, `sql/`, `docs/`
   - File `koneksi_hosting.php`
7. Tulis **Summary** commit (mis. `Upload website Virgo Rent`).
8. Klik **"Commit to main"**.
9. Klik **"Publish repository"** → pilih akun GitHub → **"Publish"**.
10. Selesai! Kode Anda sudah ada di GitHub.

### Metode B — Git Command Line (via Terminal / CMD)

Buka terminal di folder proyek, lalu jalankan perintah ini satu per satu:

```bash
# 1. Inisialisasi repositori git di folder proyek
git init

# 2. Tambahkan semua file (kecuali yang dikecualikan .gitignore)
git add .

# 3. Buat commit pertama
git commit -m "Upload website Virgo Rent Car"

# 4. Hubungkan ke repository GitHub Anda
git remote add origin https://github.com/NAMA_USER_KAMU/virgo-rent.git

# 5. Upload ke GitHub
git branch -M main
git push -u origin main
```

> 🔑 **Catatan:** Saat `git push`, akan diminta **username** dan **password**.
> Password-nya BUKAN password akun, tapi **Personal Access Token (PAT)**.
> Cara buat PAT: GitHub → Foto profil → **Settings** → **Developer settings** →
> **Personal access tokens** → **Tokens (classic)** → **Generate new token** →
> centang `repo` → Generate → salin token-nya.

---

## ✅ LANGKAH 4 — BUAT HOSTING PHP + MySQL

Website Anda butuh hosting yang mendukung PHP + MySQL.
Gunakan **InfinityFree** (gratis, sudah ada panduannya).

📖 Ikuti panduan lengkap di file: **`_arsip/PANDUAN_PUBLIKASI.md`**

Ringkasannya:
1. Daftar di **https://www.infinityfree.com** (verifikasi email)
2. Login ke **https://www.infinityfree.net**
3. Buat **Hosting Account** → subdomain: `virgorent.infinityfreeapp.com`
4. Buat **MySQL Database** → catat: hostname, nama db, username, password
5. Import database `db_virgo_rent.sql` (hasil ekspor dari phpMyAdmin XAMPP)
6. Upload file website ke folder `htdocs/`
7. Update `koneksi.php` dengan kredensial database hosting
8. Test website! ✅

---

## ✅ LANGKAH 5 — DOWNLOAD KODE DARI GITHUB UNTUK DI-UPLOAD KE HOSTING

Setelah kode ada di GitHub, Anda bisa mengunduhnya kapan saja:

1. Buka repository Anda di GitHub (mis. `https://github.com/NAMA_USER_KAMU/virgo-rent`).
2. Klik tombol hijau **"Code"** → **"Download ZIP"**.
3. Ekstrak file ZIP di komputer Anda.
4. Upload isinya ke folder `htdocs/` di InfinityFree (lihat `_arsip/PANDUAN_PUBLIKASI.md` Bagian E).
5. Update `koneksi.php` sesuai kredensial hosting (Bagian F).

> 💡 **Alternatif lebih praktis:** Jika ingin upload langsung dari GitHub ke hosting
> tanpa download, sebagian hosting menyediakan fitur **"Import from URL"** pada
> File Manager. Bisa juga memakai layanan **GitHub Actions** untuk auto-deploy
> (lebih lanjut di Bagian Bonus di bawah).

---

## 🔧 BAGIAN BONUS — AUTO-DEPLOY DENGAN GITHUB ACTIONS

Untuk yang sudah mahir, GitHub Actions bisa otomatis mengirim kode ke hosting
setiap kali Anda melakukan push. Contoh workflow sederhana:

1. Di GitHub, buat file `.github/workflows/deploy.yml` di repository Anda.
2. Isi dengan konfigurasi FTP ke hosting Anda (hosting InfinityFree mendukung FTP).
3. Setiap `git push`, kode otomatis terkirim ke hosting.

> ⚠️ Bagian ini OPSIONAL. Untuk pemula, cukup pakai **Metode Download ZIP + Upload**
> di Langkah 5, yang lebih mudah dan tetap berfungsi dengan baik.

---

## 🛡️ KEAMANAN — FILE YANG JANGAN DI-PUSH KE GITHUB

File-file berikut **TIDAK boleh** masuk ke repository GitHub:

| File / Folder | Alasan |
|---------------|--------|
| `koneksi_hosting.php` | Berisi template kredensial database hosting |
| `_arsip/` | Folder arsip internal |
| `sql/` | Berisi dump database lokal |
| `docs/` | Dokumentasi internal |

> ✅ Semua file di atas sudah **otomatis dikecualikan** oleh file **`.gitignore`**
> yang sudah saya siapkan di folder proyek. Jadi Anda tidak perlu khawatir.

---

## ❓ TROUBLESHOOTING

| Masalah | Solusi |
|---------|--------|
| `git push` gagal "authentication failed" | Gunakan **Personal Access Token** sebagai password, bukan password akun biasa |
| File `_arsip/` ikut ter-upload | Hapus file tersebut dari GitHub via web, atau jalankan `git rm -r --cached _arsip` lalu push ulang |
| Website tidak muncul di GitHub Pages | Memang tidak bisa — website ini PHP. Gunakan InfinityFree (Langkah 4) |
| "Koneksi database gagal" di hosting | Cek `koneksi.php` di hosting — pastikan hostname, username, password, nama db sesuai |
| Lupa cara ekspor database | Lihat `_arsip/PANDUAN_PUBLIKASI.md` Bagian A |

---

## 📌 KESIMPULAN

- **GitHub Pages** → ❌ untuk website PHP (hanya untuk website statis)
- **GitHub Repository** → ✅ untuk menyimpan & membackup kode website
- **InfinityFree (hosting PHP+MySQL)** → ✅ untuk membuat website LIVE

**Alur singkat:** Simpan kode di GitHub → buat hosting InfinityFree →
download ZIP dari GitHub → upload ke hosting → update `koneksi.php` →
**Website Virgo Rent Car LIVE!** 🎉

**Total Biaya: Rp 0** (semua gratis)

