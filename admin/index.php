<?php
require 'auth_check.php'; // proteksi sesi + timeout + single session (mencegah bypass URL)
require_once '../koneksi.php';

// ============================================================
// STATISTIK REAL-TIME UNTUK DASHBOARD
// ============================================================
// Total kendaraan & status
$q_mobil = mysqli_query($koneksi, "SELECT COUNT(*) t, COALESCE(SUM(status='Tersedia'),0) tersedia, COALESCE(SUM(status='Terpesan'),0) terpesan FROM tbl_kendaraan");
$mobil = mysqli_fetch_assoc($q_mobil);
$total_mobil = (int)$mobil['t'];
$mobil_tersedia = (int)$mobil['tersedia'];
$mobil_terpesan = (int)$mobil['terpesan'];

// Total driver
$q_driver = mysqli_query($koneksi, "SELECT COUNT(*) t FROM tbl_driver");
$total_driver = (int)mysqli_fetch_assoc($q_driver)['t'];

// Total paket wisata
$q_wisata = mysqli_query($koneksi, "SELECT COUNT(*) t FROM tbl_wisata");
$total_wisata = (int)mysqli_fetch_assoc($q_wisata)['t'];

// Total ulasan
$q_review = mysqli_query($koneksi, "SELECT COUNT(*) t FROM tbl_reviews");
$total_review = 0;
if ($q_review) $total_review = (int)mysqli_fetch_assoc($q_review)['t'];

// Pesanan menunggu konfirmasi
$q_menunggu = mysqli_query($koneksi, "SELECT COUNT(*) t FROM tbl_reservasi WHERE status_reservasi = 'Menunggu Konfirmasi'");
$jumlah_menunggu = (int)mysqli_fetch_assoc($q_menunggu)['t'];

// Total pendapatan (semua transaksi non-batal)
$q_pendapatan = mysqli_query($koneksi, "SELECT COALESCE(SUM(total_harga),0) t FROM tbl_reservasi WHERE status_reservasi != 'Dibatalkan'");
$total_pendapatan = (int)mysqli_fetch_assoc($q_pendapatan)['t'];

// Total seluruh transaksi
$q_total_transaksi = mysqli_query($koneksi, "SELECT COUNT(*) t FROM tbl_reservasi");
$total_transaksi = (int)mysqli_fetch_assoc($q_total_transaksi)['t'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Virgo Rent Car</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #060e1a;
            color: #e2e8f0;
            min-height: 100vh;
            background-image:
                radial-gradient(ellipse at 20% 10%, rgba(13,71,161,0.35) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 90%, rgba(46,125,50,0.25) 0%, transparent 50%),
                linear-gradient(rgba(66,165,245,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(66,165,245,0.04) 1px, transparent 1px);
            background-size: 100% 100%, 100% 100%, 60px 60px, 60px 60px;
        }
        .grad-blue-green { background: linear-gradient(135deg, #0D47A1 0%, #1565C0 40%, #2E7D32 100%); }
        .grad-text { background: linear-gradient(135deg, #42A5F5, #66BB6A, #FDD835); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

        .dashboard-wrap { max-width: 1200px; margin: 0 auto; padding: 40px 24px; }

        /* Header atas */
        .topbar {
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;
            background: rgba(13, 22, 40, 0.7); backdrop-filter: blur(20px);
            border: 1px solid rgba(66, 165, 245, 0.12);
            border-radius: 20px; padding: 16px 24px; margin-bottom: 32px;
        }
        .brand { display: flex; align-items: center; gap: 14px; }
        .brand-logo {
            width: 48px; height: 48px; border-radius: 14px; flex-shrink: 0;
            background: linear-gradient(135deg, #0D47A1, #1565C0, #2E7D32);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 8px 25px rgba(13, 71, 161, 0.4);
        }

        /* Kartu statistik */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 36px; }
        .stat-card {
            position: relative; overflow: hidden;
            background: rgba(13, 22, 40, 0.75); backdrop-filter: blur(20px);
            border: 1px solid rgba(66, 165, 245, 0.12); border-radius: 18px;
            padding: 22px; transition: all 0.3s ease;
        }
        .stat-card:hover { transform: translateY(-4px); border-color: rgba(66,165,245,0.35); box-shadow: 0 18px 40px rgba(13,71,161,0.3); }
        .stat-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #42A5F5, #66BB6A); opacity: 0.6; }
        .stat-top { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .stat-label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 4px; }
        .stat-value { font-size: 26px; font-weight: 800; color: #fff; line-height: 1.1; }
        .stat-sub { font-size: 11px; color: #64748b; margin-top: 4px; }

        /* Menu grid */
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 24px; }
        .menu-card {
            position: relative;
            background: rgba(13, 22, 40, 0.8); backdrop-filter: blur(20px);
            border: 1px solid rgba(66, 165, 245, 0.12); border-radius: 20px;
            padding: 32px 20px; text-decoration: none;
            display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 16px;
            transition: all 0.3s ease;
        }
        .menu-card:hover { transform: translateY(-6px); box-shadow: 0 22px 50px rgba(13,71,161,0.4); border-color: rgba(66,165,245,0.4); }
        .menu-icon { width: 64px; height: 64px; border-radius: 18px; display: flex; align-items: center; justify-content: center; font-size: 26px; transition: transform 0.3s ease; }
        .menu-card:hover .menu-icon { transform: scale(1.1); }
        .menu-title { font-size: 19px; font-weight: 800; color: #fff; letter-spacing: -0.02em; }
        .menu-desc { font-size: 12px; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.08em; }

        /* Badge notifikasi angka */
        .notif-badge-count {
            position: absolute; top: -8px; right: -8px; min-width: 28px; height: 28px; padding: 0 6px;
            border-radius: 999px; background: #ef4444; color: #fff; font-size: 13px; font-weight: 800;
            display: none; align-items: center; justify-content: center; border: 3px solid #060e1a;
            box-shadow: 0 4px 12px rgba(239,68,68,0.5); animation: pulseBadge 1.2s infinite;
        }
        .notif-badge-count.show { display: flex; }
        @keyframes pulseBadge { 0% { transform: scale(1); } 50% { transform: scale(1.15); } 100% { transform: scale(1); } }

        .btn-sm { padding: 8px 12px; border-radius: 10px; font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-red { background: rgba(198, 40, 40, 0.3); color: #EF9A9A; border: 1px solid rgba(198, 40, 40, 0.5); }
        .btn-red:hover { background: rgba(198, 40, 40, 0.5); }
    </style>
</head>
<body>

    <div class="dashboard-wrap">

        <!-- ======= TOPBAR ======= -->
        <div class="topbar">
            <div class="brand">
                <div class="brand-logo"><i class="fas fa-car text-white text-xl"></i></div>
                <div>
                    <div class="text-white font-extrabold text-lg leading-tight">Virgo <span class="grad-text">Rent Car</span></div>
                    <div class="text-slate-500 text-[11px] uppercase tracking-[0.2em]">Pekanbaru — Riau</div>
                </div>
            </div>
<div class="flex items-center gap-3">
                <span class="hidden md:inline text-slate-400 text-sm">👋 Selamat datang, <span class="text-white font-semibold"><?= htmlspecialchars($_SESSION['admin_username']); ?></span></span>
                <button type="button" id="btnAktifkanSuara" onclick="aktifkanSuara()" class="btn-sm" style="background: rgba(249,168,37,0.3); color:#FFD54F; border:1px solid rgba(249,168,37,0.5);">
                    <i class="fas fa-volume-mute"></i> Aktifkan Suara
                </button>
                <a href="logout.php" class="btn-sm btn-red"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>

        <!-- ======= JUDUL HALAMAN ======= -->
        <div class="mb-8">
            <h1 class="text-3xl md:text-4xl font-extrabold text-white">Dashboard <span class="grad-text">Admin</span></h1>
            <p class="text-slate-500 text-sm mt-2">Ringkasan dan menu pengelolaan Virgo Rent Car Pekanbaru.</p>
        </div>

        <!-- ======= KARTU STATISTIK ======= -->
        <div class="stats-grid">
            <!-- Total Pendapatan -->
            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label"><i class="fas fa-wallet mr-1 text-yellow-500"></i> Pendapatan</span>
                    <div class="stat-icon" style="background: linear-gradient(135deg,#F9A825,#F57C00);"><i class="fas fa-coins text-white"></i></div>
                </div>
                <div class="stat-value" style="font-size:18px;">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></div>
                <div class="stat-sub">Total transaksi (non-batal)</div>
            </div>

            <!-- Pesanan Menunggu -->
            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label"><i class="fas fa-bell mr-1 text-red-500"></i> Menunggu</span>
                    <div class="stat-icon" style="background: linear-gradient(135deg,#ef4444,#dc2626);"><i class="fas fa-clock text-white"></i></div>
                </div>
                <div class="stat-value"><?= $jumlah_menunggu; ?></div>
                <div class="stat-sub">Pesanan menunggu konfirmasi</div>
            </div>

            <!-- Total Transaksi -->
            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label"><i class="fas fa-clipboard-list mr-1 text-blue-500"></i> Transaksi</span>
                    <div class="stat-icon grad-blue-green"><i class="fas fa-file-invoice text-white"></i></div>
                </div>
                <div class="stat-value"><?= $total_transaksi; ?></div>
                <div class="stat-sub">Total seluruh pemesanan</div>
            </div>

            <!-- Total Armada -->
            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label"><i class="fas fa-car mr-1 text-green-500"></i> Armada</span>
                    <div class="stat-icon" style="background: linear-gradient(135deg,#2E7D32,#43A047);"><i class="fas fa-car text-white"></i></div>
                </div>
                <div class="stat-value"><?= $total_mobil; ?></div>
                <div class="stat-sub"><?= $mobil_tersedia; ?> tersedia · <?= $mobil_terpesan; ?> terpesan</div>
            </div>

            <!-- Total Driver -->
            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label"><i class="fas fa-id-card-alt mr-1 text-blue-400"></i> Driver</span>
                    <div class="stat-icon" style="background: linear-gradient(135deg,#0D47A1,#1976D2);"><i class="fas fa-id-card-alt text-white"></i></div>
                </div>
                <div class="stat-value"><?= $total_driver; ?></div>
                <div class="stat-sub">Driver tersedia</div>
            </div>

            <!-- Total Wisata -->
            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label"><i class="fas fa-map-marked-alt mr-1 text-yellow-400"></i> Wisata</span>
                    <div class="stat-icon" style="background: linear-gradient(135deg,#2E7D32,#66BB6A);"><i class="fas fa-map-marked-alt text-white"></i></div>
                </div>
                <div class="stat-value"><?= $total_wisata; ?></div>
                <div class="stat-sub">Paket wisata</div>
            </div>
        </div>

        <!-- ======= MENU FITUR ======= -->
        <div class="menu-grid">
            <!-- Armada -->
            <a href="kelola_mobil.php" class="menu-card">
                <div class="menu-icon grad-blue-green"><i class="fas fa-car"></i></div>
                <div>
                    <div class="menu-title">Armada</div>
                    <div class="menu-desc">Kelola Kendaraan</div>
                </div>
            </a>

            <!-- Driver -->
            <a href="kelola_driver.php" class="menu-card">
                <div class="menu-icon" style="background: linear-gradient(135deg,#0D47A1,#1976D2);"><i class="fas fa-id-card-alt"></i></div>
                <div>
                    <div class="menu-title">Driver</div>
                    <div class="menu-desc">Kelola Driver</div>
                </div>
            </a>

            <!-- Wisata -->
            <a href="kelola_wisata.php" class="menu-card">
                <div class="menu-icon" style="background: linear-gradient(135deg,#2E7D32,#43A047);"><i class="fas fa-map-marked-alt"></i></div>
                <div>
                    <div class="menu-title">Wisata</div>
                    <div class="menu-desc">Paket Wisata</div>
                </div>
            </a>

            <!-- Pemesanan + Badge Notifikasi -->
            <a href="data_pemesanan.php" class="menu-card" id="cardPemesanan">
                <div class="menu-icon" style="background: linear-gradient(135deg,#F9A825,#F57C00);"><i class="fas fa-clipboard-list"></i></div>
                <div>
                    <div class="menu-title">Pemesanan</div>
                    <div class="menu-desc">Data Pemesanan</div>
                </div>
                <span class="notif-badge-count" id="notifBadge"><?= $jumlah_menunggu > 0 ? $jumlah_menunggu : ''; ?></span>
            </a>

            <!-- Laporan -->
            <a href="laporan.php" class="menu-card">
                <div class="menu-icon" style="background: linear-gradient(135deg,#6A1B9A,#8E24AA);"><i class="fas fa-chart-bar"></i></div>
                <div>
                    <div class="menu-title">Laporan</div>
                    <div class="menu-desc">Bulanan & Tahunan</div>
                </div>
            </a>

            <!-- Ulasan -->
            <a href="kelola_reviews.php" class="menu-card">
                <div class="menu-icon" style="background: linear-gradient(135deg,#F9A825,#FFB300);"><i class="fas fa-star"></i></div>
                <div>
                    <div class="menu-title">Ulasan</div>
                    <div class="menu-desc">Kelola Ulasan</div>
                </div>
            </a>
        </div>
    </div>

<script>
    // ============================================================
    // NOTIFIKASI REAL-TIME
    // - Suara notifikasi (Text-to-Speech) otomatis
    // - Notifikasi Sistem (tampil walau browser minimize)
    // - Badge angka real-time
    // ============================================================
var jumlahMenunggu = <?= $jumlah_menunggu; ?>;
    var badge = document.getElementById('notifBadge');
var notifAktif = false;
    var terakhirDiberitahu = 0; // mencegah spam notifikasi
    // Waktu pengingat terakhir disimpan di localStorage agar tidak hilang
    // saat pindah halaman / reload (mencegah suara langsung berbunyi tiap buka halaman)
    var terakhirPengingat = parseInt(localStorage.getItem('virgo_pengingat_terakhir') || '0', 10);
var synth = window.speechSynthesis;
    // Status suara disimpan di localStorage agar SELALU aktif
    // meski halaman di-reload / pindah halaman / keluar-masuk web
    var suaraDiaktifkan = (localStorage.getItem('virgo_suara_aktif') === '1');

    // ============================================================
    // SUARA NOTIFIKASI AI (Text-to-Speech) + Beep Cadangan
    // ============================================================
    // PENTING: Chrome/Edge memblokir suara otomatis sampai pengguna
    // berinteraksi. Maka admin harus KLIK tombol "Aktifkan Suara"
    // sekali. Setelah itu suara akan berbunyi otomatis & real-time.
    // ------------------------------------------------------------

// Beep cadangan via Web Audio API (tidak butuh koneksi/suara TTS)
    var audioCtx = null;
    function beep() {
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') audioCtx.resume();
            var o = audioCtx.createOscillator();
            var g = audioCtx.createGain();
            o.connect(g); g.connect(audioCtx.destination);
            o.type = 'sine'; o.frequency.value = 880;
            g.gain.setValueAtTime(0.5, audioCtx.currentTime);
            g.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.6);
            o.start(); o.stop(audioCtx.currentTime + 0.6);
        } catch (e) { /* abaikan */ }
    }

    // Buka/mulakan AudioContext secara SENYAP (tanpa nada).
    // Tujuannya hanya agar audio "siap" dipakai, TANPA berbunyi saat masuk halaman.
    function bukaAudioContext() {
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (audioCtx.state === 'suspended') audioCtx.resume();
        } catch (e) { /* abaikan */ }
    }

    function pilihSuaraIndonesia() {
        try {
            var voices = synth.getVoices();
            for (var i = 0; i < voices.length; i++) {
                if (voices[i].lang && voices[i].lang.indexOf('id') === 0) return voices[i];
            }
            for (var j = 0; j < voices.length; j++) {
                if (voices[j].name && voices[j].name.indexOf('Google') !== -1) return voices[j];
            }
        } catch (e) {}
        return null;
    }
    if (synth) {
        synth.onvoiceschanged = function () { pilihSuaraIndonesia(); };
    }

    function bicara(teks) {
        if (!suaraDiaktifkan) return; // hanya aktif setelah tombol ditekan
        try {
            if (synth.paused) synth.resume();
            synth.cancel();
            var u = new SpeechSynthesisUtterance(teks);
            u.lang = 'id-ID';
            u.rate = 1.0;
            u.pitch = 1.0;
            u.volume = 1.0;
            var suara = pilihSuaraIndonesia();
            if (suara) u.voice = suara;
            // Mainkan beep dulu, lalu TTS
            beep();
            setTimeout(function () { synth.speak(u); }, 150);
        } catch (e) { beep(); }
    }

// Tombol untuk mengaktifkan suara (memenuhi syarat user-gesture)
    function aktifkanSuara() {
        suaraDiaktifkan = true;
        // Simpan status SELAMANYA di localStorage (tidak hilang saat reload/pindah halaman)
        localStorage.setItem('virgo_suara_aktif', '1');
        try {
            var u = new SpeechSynthesisUtterance('Notifikasi suara diaktifkan.');
            u.lang = 'id-ID'; u.volume = 1.0;
            synth.speak(u);
        } catch (e) {}
        beep();
        var btn = document.getElementById('btnAktifkanSuara');
        if (btn) {
            btn.innerHTML = '<i class="fas fa-volume-up"></i> Suara AKTIF';
            btn.style.background = 'rgba(46,125,50,0.4)';
            btn.style.color = '#81C784';
        }
    }

    // Saat halaman dimuat: tampilkan tombol sesuai status tersimpan
    function pulihkanStatusSuara() {
        var btn = document.getElementById('btnAktifkanSuara');
        if (btn && suaraDiaktifkan) {
            btn.innerHTML = '<i class="fas fa-volume-up"></i> Suara AKTIF';
            btn.style.background = 'rgba(46,125,50,0.4)';
            btn.style.color = '#81C784';
        }
    }

    // ---- NOTIFIKASI SISTEM (tampil walau browser minimize) ----
    function mintaIzinNotifikasi() {
        if (!('Notification' in window)) {
            console.warn('Browser tidak mendukung notifikasi sistem.');
            return;
        }
        if (Notification.permission === 'granted') {
            notifAktif = true;
        } else if (Notification.permission !== 'denied') {
            Notification.requestPermission().then(function (p) {
                notifAktif = (p === 'granted');
            }).catch(function () { /* abaikan jika pengguna menutup prompt */ });
        }
    }

    function kirimNotifikasiSistem(teks) {
        if (!notifAktif || !('Notification' in window)) return;
        // Cegah spam: minimal 20 detik antar notifikasi
        var sekarang = Date.now();
        if (sekarang - terakhirDiberitahu < 20000) return;
        terakhirDiberitahu = sekarang;
        try {
            var n = new Notification('🔔 Pesanan Baru — Virgo Rent Car', {
                body: teks,
                icon: '../uploads/mobil_6a6e0ae550ba1.jpg'
            });
            n.onclick = function () {
                window.focus();
                window.location.href = 'data_pemesanan.php';
                n.close();
            };
            // Tutup otomatis setelah 8 detik
            setTimeout(function () { n.close(); }, 8000);
        } catch (e) { /* abaikan */ }
    }

    function updateBadge() {
        if (jumlahMenunggu > 0) {
            badge.textContent = jumlahMenunggu;
            badge.classList.add('show');
        } else {
            badge.classList.remove('show');
        }
    }

    // Cek jumlah pesanan setiap 3 detik (real-time, tanpa refresh)
function cekNotifikasi() {
        fetch('notifikasi_count.php', {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (typeof data.jumlah !== 'undefined') {
                var jumlahBaru = (typeof data.jumlah === 'number') ? data.jumlah : parseInt(data.jumlah) || 0;
if (jumlahBaru > jumlahMenunggu) {
                    // 🔊 Notifikasi SUARA AI otomatis (real-time)
                    bicara('Perhatian! Ada pesanan baru masuk. Segera konfirmasi.');
                    // Notifikasi sistem (tampil walau minimize)
                    kirimNotifikasiSistem('Ada ' + jumlahBaru + ' pesanan menunggu konfirmasi. Klik untuk membuka.');
                }
                jumlahMenunggu = jumlahBaru;
                updateBadge();

// ======================================
                // PENGINGAT OTOMATIS (30 DETIK)
                // Jika masih ada pesanan belum dikonfirmasi,
                // suara & notifikasi akan berbunyi ulang
                // setiap 30 detik sampai admin mengonfirmasi.
                // ======================================
                if (jumlahBaru > 0) {
                    var sekarang = Date.now();
                    if (sekarang - terakhirPengingat >= 30000) {
                        terakhirPengingat = sekarang;
                        // Simpan waktu pengingat agar tidak hilang saat pindah halaman
                        localStorage.setItem('virgo_pengingat_terakhir', String(terakhirPengingat));
                        bicara('Peringatan! Ada ' + jumlahBaru + ' pesanan yang belum dikonfirmasi. Mohon segera konfirmasi.');
                        kirimNotifikasiSistem('⚠️ ' + jumlahBaru + ' pesanan belum dikonfirmasi. Segera konfirmasi!');
                    }
                } else {
                    // Semua sudah dikonfirmasi, reset waktu pengingat
                    terakhirPengingat = 0;
                    localStorage.removeItem('virgo_pengingat_terakhir');
                }
            }
        })
        .catch(function (err) { console.warn('Gagal memeriksa notifikasi:', err); });
    }

// Polling real-time cepat: aktif halaman 2 detik, background 5 detik
    var intervalMs = 2000;
    setInterval(function () {
        cekNotifikasi();
        // Jika tab tidak aktif (minimize), perlambat sedikit agar tetap berjalan ringan
        if (document.hidden && intervalMs !== 5000) {
            intervalMs = 5000;
        } else if (!document.hidden && intervalMs !== 2000) {
            intervalMs = 2000;
        }
    }, intervalMs);

document.addEventListener('DOMContentLoaded', function () {
        updateBadge();
        pulihkanStatusSuara();
        mintaIzinNotifikasi();

        // Inisialisasi waktu pengingat pertama kali (jika belum pernah ada).
        // Tujuannya agar suara pengingat TIDAK langsung berbunyi saat masuk halaman,
        // melainkan menunggu minimal 30 detik sejak halaman dibuka.
        if (terakhirPengingat === 0) {
            terakhirPengingat = Date.now();
            localStorage.setItem('virgo_pengingat_terakhir', String(terakhirPengingat));
        }

cekNotifikasi();
        // Jika suara pernah diaktifkan, siapkan AudioContext secara SENYAP
        // (tanpa nada) agar suara bisa berbunyi otomatis saat ada pesanan baru
        if (suaraDiaktifkan) {
            setTimeout(function () { bukaAudioContext(); }, 800);
        }
    });
    </script>

</body>
</html>
