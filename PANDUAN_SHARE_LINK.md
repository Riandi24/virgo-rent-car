# 🔗 PANDUAN: CARA MEMBUAT LINK UNTUK DIBAGIKAN KE ORANG LAIN

Website Virgo Rent Car adalah **PHP + MySQL**, jadi ada **beberapa cara** untuk
membuatnya bisa diakses orang lain lewat link. Pilih yang paling sesuai kebutuhan Anda.

---

## 🧭 RINGKASAN 3 CARA

| Cara | Link Bersifat | Biaya | Komputer Harus Menyala? | Tingkat Kesulitan |
|------|--------------|------|------------------------|-------------------|
| **Cara 1 — ngrok (TUNNEL)** | Sementara (berubah tiap start) | Gratis | ✅ Ya | ⭐ Paling Mudah |
| **Cara 2 — InfinityFree (HOSTING)** | Tetap/permanen | Gratis | ❌ Tidak | ⭐⭐ Sedang |
| **Cara 3 — 000webhost (HOSTING)** | Tetap/permanen | Gratis | ❌ Tidak | ⭐⭐ Sedang |

> 💡 **Pilih Cara 1** jika ingin **cepat** melihat link-nya sekarang juga.
> **Pilih Cara 2/3** jika ingin link **permanen** yang bisa dibuka kapan saja
> tanpa harus menyalakan komputer.

---

# ✅ CARA 1 — ngrok (PALING CEPAT, LANGSUNG DAPAT LINK)

Cara ini "membocorkan" localhost XAMPP Anda ke internet sehingga orang lain
bisa membukanya lewat link. **Komputer harus tetap menyala** selama link dipakai.

### Langkah 1 — Nyalakan XAMPP
1. Buka **XAMPP Control Panel**.
2. Klik **Start** pada **Apache** dan **MySQL** (harus hijau/Running).
3. Test dulu di browser: buka `http://localhost/virgo_rent` — pastikan website tampil.

### Langkah 2 — Download & Install ngrok
1. Buka **https://ngrok.com/download**
2. Download versi **Windows**.
3. Ekstrak file `ngrok.exe` ke folder mana saja (mis. `C:\ngrok`).

### Langkah 3 — Daftar Akun ngrok (gratis)
1. Buka **https://dashboard.ngrok.com/signup**
2. Daftar dengan email (bisa juga login pakai Google/GitHub).
3. Setelah login, buka halaman **"Your Authtoken"**:
   https://dashboard.ngrok.com/get-started/your-authtoken
4. Salin **authtoken** Anda (kode panjang).

### Langkah 4 — Hubungkan ngrok dengan Authtoken
Buka **Command Prompt (CMD)**, lalu jalankan:

```cmd
cd C:\ngrok
ngrok config add-authtoken KODE_AUTHTOKEN_ANDA
```

### Langkah 5 — Jalankan Tunnel untuk XAMPP
Di CMD yang sama, jalankan:

```cmd
ngrok http 80
```

> ⚠️ Pastikan port 80 adalah port Apache XAMPP Anda. Jika Apache Anda memakai port
> lain (mis. 8080), ganti perintahnya menjadi `ngrok http 8080`.

### Langkah 6 — Dapatkan LINK Anda! 🎉
1. Setelah perintah di atas berjalan, ngrok menampilkan kotak info.
2. Cari baris **"Forwarding"** — di sana ada link seperti:
   ```
   https://a1b2c3d4.ngrok-free.app
   ```
   (ada juga versi `http://`, gunakan yang `https://`)
3. **Link itu** adalah link publik Anda!

### Langkah 7 — Bagikan ke Orang Lain
Kirim link tersebut (mis. `https://a1b2c3d4.ngrok-free.app`) ke siapa saja
via WhatsApp, Instagram, dll. Mereka bisa langsung membukanya.

> ⚠️ **Catatan ngrok:**
> - Link **berubah** setiap kali Anda menjalankan ulang `ngrok http 80`.
> - Komputer **harus tetap menyala** selama link dipakai.
> - Ada kemungkinan muncul halaman peringatan ngrok — pengunjung tinggal klik
>   **"Visit Site"**. (Bisa dihilangkan dengan paket berbayar.)

---

# ✅ CARA 2 — INFINITYFREE (LINK PERMANEN)

Cara ini membuat website "dipindahkan" ke hosting gratis sehingga link-nya
**tetap** dan bisa dibuka kapan saja, tanpa komputer Anda menyala.

📖 **Ikuti panduan lengkap di file `_arsip/PANDUAN_PUBLIKASI.md`** (Bagian A–H).

### Ringkasan:
1. Daftar di **https://www.infinityfree.com** → verifikasi email.
2. Login di **https://www.infinityfree.net**.
3. Buat hosting account → subdomain `virgorent` →
   link Anda: **`https://virgorent.infinityfreeapp.com`**
4. Buat **MySQL Database** → catat hostname, username, password, nama db.
5. Ekspor database dari XAMPP (`db_virgo_rent.sql`) → import di phpMyAdmin hosting.
6. Upload semua file website ke folder `htdocs/`.
7. Edit `koneksi.php` di hosting dengan kredensial database hosting.
8. Test: buka **`https://virgorent.infinityfreeapp.com`** → share link itu!

---

# ✅ CARA 3 — 000WEBHOST (ALTERNATIF HOSTING GRATIS)

Mirip InfinityFree, gratis, mendukung PHP + MySQL, dan ada panelnya.

### Langkah:
1. Buka **https://www.000webhost.com** → klik **"Sign Up"**.
2. Login dengan akun **Hostinger** atau daftar baru (email + password).
3. Buat website baru → pilih **"Free Website"**.
4. Nanti Anda dapat subdomain gratis, mis. **`virgorent.000webhostapp.com`**.
5. Buka **"File Manager"** → upload semua file website ke folder `public_html/`.
6. Buka menu **"Databases"** → buat database baru → catat kredensialnya.
7. Import file `db_virgo_rent.sql`.
8. Edit `koneksi.php` dengan kredensial database 000webhost.
9. Test link Anda → share!

> ⚠️ 000webhost kadang menampilkan "under construction" untuk subdomain baru
> selama beberapa jam. Tunggu sebentar atau chat support mereka.

---

## 🆚 KAPAN PAKAI CARA YANG MANA?

| Kebutuhan Anda | Pakai Cara |
|----------------|-----------|
| Ingin **cepat lihat hasil** sekarang, sekadar demo sebentar | **Cara 1 (ngrok)** |
| Ingin link **permanen** yang bisa dibuka kapan saja | **Cara 2 (InfinityFree)** |
| InfinityFree bermasalah / ingin alternatif | **Cara 3 (000webhost)** |

---

## ⚠️ PENTING UNTUK SEMUA CARA

- **Database tetap perlu disiapkan.** Data armada/driver/wisata/pemesanan tersimpan
  di MySQL. Untuk Cara 1, pakai database lokal XAMPP (sudah otomatis). Untuk
  Cara 2 & 3, harus import `db_virgo_rent.sql`.
- **Jangan bagikan file `koneksi.php` / `koneksi_hosting.php`** yang sudah berisi
  password database ke orang lain.

---

## 🎉 KESIMPULAN

- Ingin **cepat** → pakai **ngrok** (5 menit dapat link, tapi sementara)
- Ingin **permanen** → pakai **InfinityFree** atau **000webhost** (gratis, link tetap)

Pilih salah satu yang paling cocok, lalu ikuti langkahnya. Selamat mencoba! 🚀

