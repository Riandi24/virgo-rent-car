<?php
require 'auth_check.php'; // proteksi sesi + timeout + single session (mencegah bypass URL)
require_once '../koneksi.php';

// Nomor WhatsApp Admin (untuk notifikasi pesanan baru)
$WA_ADMIN = "6285121540024";

if(isset($_GET['action']) && isset($_GET['id'])) {
    $id_reservasi = intval($_GET['id']);
    $action = $_GET['action'];
    
    // Ambil data reservasi untuk notifikasi (LEFT JOIN agar pemesanan wisata tanpa mobil tetap terbaca)
    $res_notif = mysqli_query($koneksi, "SELECT r.*, k.nama_mobil, w.nama_paket 
                                         FROM tbl_reservasi r 
                                         LEFT JOIN tbl_kendaraan k ON r.id_kendaraan = k.id_kendaraan 
                                         LEFT JOIN tbl_wisata w ON r.id_wisata = w.id_wisata 
                                         WHERE r.id_reservasi = $id_reservasi");
    $data_notif = mysqli_fetch_assoc($res_notif);
    
    if($action == 'konfirmasi') {
        mysqli_query($koneksi, "UPDATE tbl_reservasi SET status_reservasi='Dikonfirmasi' WHERE id_reservasi=$id_reservasi");
        
        // Notifikasi WA ke pelanggan: redirect ke index dengan parameter wa_konfirmasi
        $wa_number_user = preg_replace('/^0/', '62', $data_notif['no_wa']);
        $wa_text_user = "Halo " . $data_notif['nama_pemesan'] . ", ✅ Pemesanan Anda di Virgo Rent Car telah DIKONFIRMASI!\n\n";
        if (!empty($data_notif['nama_mobil'])) {
            $wa_text_user .= "🚗 Mobil: " . $data_notif['nama_mobil'] . "\n";
        } elseif (!empty($data_notif['nama_paket'])) {
            $wa_text_user .= "🏞 Paket Wisata: " . $data_notif['nama_paket'] . "\n";
        }
        $wa_text_user .= "📅 Tanggal: " . date('d M Y', strtotime($data_notif['tanggal_mulai'])) . "\n";
        $wa_text_user .= "⏱ Durasi: " . $data_notif['durasi_hari'] . " hari\n";
        $wa_text_user .= "💰 Total: Rp " . number_format($data_notif['total_harga'], 0, ',', '.') . "\n\n";
        $wa_text_user .= "Silakan hubungi kami jika ada pertanyaan. Terima kasih! 🙏";
        $wa_url_user = "https://wa.me/" . $wa_number_user . "?text=" . urlencode($wa_text_user);
        
        // Redirect dengan notifikasi WA ke pelanggan (akan muncul popup)
        header("Location: data_pemesanan.php?wa_konfirmasi=" . urlencode($wa_url_user));
        exit();
        
    } elseif($action == 'selesai') {
        // Kembalikan status mobil ke Tersedia (jika pemesanan memakai mobil)
        if (!empty($data_notif['id_kendaraan'])) {
            mysqli_query($koneksi, "UPDATE tbl_kendaraan SET status='Tersedia' WHERE id_kendaraan=" . $data_notif['id_kendaraan']);
        }
        mysqli_query($koneksi, "UPDATE tbl_reservasi SET status_reservasi='Selesai' WHERE id_reservasi=$id_reservasi");
        header("Location: data_pemesanan.php");
        exit();
    } elseif($action == 'batal') {
        // Kembalikan status mobil ke Tersedia (jika pemesanan memakai mobil)
        if (!empty($data_notif['id_kendaraan'])) {
            mysqli_query($koneksi, "UPDATE tbl_kendaraan SET status='Tersedia' WHERE id_kendaraan=" . $data_notif['id_kendaraan']);
        }
        mysqli_query($koneksi, "UPDATE tbl_reservasi SET status_reservasi='Dibatalkan' WHERE id_reservasi=$id_reservasi");
        header("Location: data_pemesanan.php");
        exit();
    }
}

$query = "SELECT r.*, k.nama_mobil, w.nama_paket
          FROM tbl_reservasi r 
          LEFT JOIN tbl_kendaraan k ON r.id_kendaraan = k.id_kendaraan 
          LEFT JOIN tbl_wisata w ON r.id_wisata = w.id_wisata 
          ORDER BY r.id_reservasi DESC";
 $result = mysqli_query($koneksi, $query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pemesanan - Virgo Rent Car</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="p-6 md:p-10">

    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">Data <span class="grad-text">Pemesanan</span></h1>
                <p class="text-slate-500 text-sm mt-1">Data Pemesanan Kendaraan & Paket Wisata</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="button" id="btnAktifkanSuara" onclick="aktifkanSuara()" class="btn-sm" style="background: rgba(249,168,37,0.3); color:#FFD54F; border:1px solid rgba(249,168,37,0.5);">
                    <i class="fas fa-volume-mute"></i> Aktifkan Suara
                </button>
                <a href="index.php" class="btn-sm btn-blue"><i class="fas fa-arrow-left"></i> Dashboard</a>
                <a href="laporan.php" class="btn-sm btn-green"><i class="fas fa-print"></i> Laporan</a>
            </div>
        </div>

        <!-- Tabel Reservasi -->
        <div class="card-glass p-6 md:p-8 overflow-x-auto">
            <h2 class="text-xl font-bold text-white mb-6"><i class="fas fa-calendar-check mr-2 text-blue-500"></i> Data Pemesanan Kendaraan & Paket Wisata</h2>
            
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="border-b border-slate-700/50 text-slate-400 text-sm">
                        <th class="py-3 px-4">Pemesan</th>
                        <th class="py-3 px-4">Kendaraan / Wisata</th>
                        <th class="py-3 px-4">Tanggal Sewa</th>
                        <th class="py-3 px-4 text-right">Total Bayar</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300 text-sm">
                    <?php while($row = mysqli_fetch_assoc($result)): 
                        // Nama item: mobil ATAU paket wisata (karena bisa pemesanan wisata saja)
                        $nama_item = !empty($row['nama_mobil']) ? $row['nama_mobil'] : ($row['nama_paket'] ?? 'Paket Wisata');
                        $jenis_item = !empty($row['nama_mobil']) ? 'mobil' : 'paket wisata';
                        // LOGIKA CHAT WHATSAPP OTOMATIS
                        $wa_number = preg_replace('/^0/', '62', $row['no_wa']); // Mengubah 08xx menjadi 628xx
                        $wa_text = "Halo " . $row['nama_pemesan'] . ", ini Admin Virgo Rent Car. Kami ingin mengonfirmasi pemesanan " . $jenis_item . " " . $nama_item . " Anda tanggal " . date('d M Y', strtotime($row['tanggal_mulai'])) . ". Apakah bisa dikonfirmasi?";
                        $wa_url = "https://wa.me/" . $wa_number . "?text=" . urlencode($wa_text);
                    ?>
                    <tr class="border-b border-slate-800/50 hover:bg-slate-800/30 transition-colors">
                        <td class="py-4 px-4" data-label="Pemesan">
                            <div class="font-semibold text-white"><?= $row['nama_pemesan']; ?></div>
                            <div class="text-xs text-slate-500"><?= $row['no_wa']; ?></div>
                        </td>
                        <td class="py-4 px-4" data-label="Kendaraan / Wisata">
                            <div class="font-semibold text-white"><?= $nama_item; ?></div>
                            <div class="text-xs text-slate-500">
                                <?php if (!empty($row['nama_mobil'])): ?>
                                    <i class="fas fa-car mr-1"></i> Mobil · <?= $row['durasi_hari']; ?> Hari
                                <?php else: ?>
                                    <i class="fas fa-map-marked-alt mr-1"></i> Paket Wisata
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="py-4 px-4" data-label="Tanggal Sewa">
                            <div class="text-white"><?= date('d M Y', strtotime($row['tanggal_mulai'])); ?></div>
                        </td>
                        <td class="py-4 px-4 text-right font-semibold text-white" data-label="Total Bayar">
                            Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?>
                        </td>
                        <td class="py-4 px-4 text-center" data-label="Status">
                            <?php 
                                $status = $row['status_reservasi'];
                                $badge_class = 'badge-yellow';
                                if($status == 'Dikonfirmasi') $badge_class = 'badge-blue';
                                if($status == 'Selesai') $badge_class = 'badge-green';
                                if($status == 'Dibatalkan') $badge_class = 'badge-red';
                            ?>
                            <span class="<?= $badge_class; ?>"><?= $status; ?></span>
                        </td>
                        <td class="py-4 px-4 text-center" data-label="Aksi">
                            <div class="flex justify-center gap-2">
                                <!-- TOMBOL WHATSAPP BARU -->
                                <a href="<?= $wa_url; ?>" target="_blank" class="btn-sm btn-wa" title="Chat WhatsApp Pelanggan">
                                    <i class="fab fa-whatsapp"></i>
                                </a>

                                <?php if($status == 'Menunggu Konfirmasi'): ?>
                                    <a href="data_pemesanan.php?action=konfirmasi&id=<?= $row['id_reservasi']; ?>" class="btn-sm btn-green" title="Konfirmasi"><i class="fas fa-check"></i></a>
                                    <a href="data_pemesanan.php?action=batal&id=<?= $row['id_reservasi']; ?>" class="btn-sm btn-red" title="Batalkan"><i class="fas fa-times"></i></a>
                                <?php elseif($status == 'Dikonfirmasi'): ?>
                                    <a href="data_pemesanan.php?action=selesai&id=<?= $row['id_reservasi']; ?>" class="btn-sm btn-yellow" title="Selesai"><i class="fas fa-flag-checkered"></i></a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(mysqli_num_rows($result) == 0): ?>
                    <tr>
                        <td colspan="6" class="text-center py-10 text-slate-500">
                            <i class="fas fa-inbox text-4xl mb-3 block"></i>
                            Belum ada pemesanan masuk.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Popup WhatsApp Konfirmasi -->
    <?php if(isset($_GET['wa_konfirmasi'])): ?>
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            const waUrl = "<?= $_GET['wa_konfirmasi']; ?>";
            if(confirm('✅ Berhasil dikonfirmasi! Buka WhatsApp untuk memberi tahu pelanggan?')) {
                window.open(waUrl, '_blank');
            }
        });
    </script>
    <?php endif; ?>

<!-- ============================================================ -->
    <!-- NOTIFIKASI BADGE ANGKA PESANAN MENUNGGU (AJAX Polling)       -->
    <!-- ============================================================ -->
<script>
    // ============================================================
    // NOTIFIKASI REAL-TIME
    // - Suara notifikasi (Text-to-Speech) otomatis
    // - Notifikasi Sistem (tampil walau browser minimize)
    // - Badge judul halaman real-time
    // ============================================================
    var jumlahMenunggu = <?php
        // Hitung jumlah pesanan menunggu konfirmasi
        $q_wait = mysqli_query($koneksi, "SELECT COUNT(*) t FROM tbl_reservasi WHERE status_reservasi = 'Menunggu Konfirmasi'");
        $wait_now = 0;
        if ($q_wait) $wait_now = (int)mysqli_fetch_assoc($q_wait)['t'];
        echo $wait_now;
    ?>;
var notifAktif = false;
    var terakhirDiberitahu = 0;
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
            beep();
            setTimeout(function () { synth.speak(u); }, 150);
        } catch (e) { beep(); }
    }

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
                n.close();
            };
            setTimeout(function () { n.close(); }, 8000);
        } catch (e) { /* abaikan */ }
    }

    // Ambil jumlah pesanan menunggu dari server secara berkala
    function cekJumlahMenunggu() {
        fetch('notifikasi_count.php', {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (typeof data.jumlah !== 'undefined') {
                var jumlahBaru = (typeof data.jumlah === 'number') ? data.jumlah : parseInt(data.jumlah) || 0;
                // Jika jumlah bertambah => ada pemesanan baru
if (jumlahBaru > jumlahMenunggu) {
                    // 🔊 Notifikasi SUARA AI otomatis (real-time)
                    bicara('Perhatian! Ada pesanan baru masuk. Segera konfirmasi.');
                    kirimNotifikasiSistem('Ada ' + jumlahBaru + ' pesanan menunggu konfirmasi. Klik untuk membuka.');
                }
jumlahMenunggu = jumlahBaru;

                // ======================================
                // PENGINGAT OTOMATIS (30 DETIK)
                // Jika masih ada pesanan belum dikonfirmasi,
                // suara & notifikasi berbunyi ulang
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

                // Perbarui judul halaman dengan jumlah pending
                var judul = 'Data Pemesanan - Virgo Rent Car';
                if (data.jumlah > 0) {
                    judul = '(' + data.jumlah + ') ' + judul;
                }
                document.title = judul;
            }
        })
        .catch(function (err) {
            console.warn('Gagal memeriksa notifikasi:', err);
        });
    }

// Polling real-time cepat: aktif halaman 2 detik, background 5 detik
    var intervalMs = 2000;
    setInterval(function () {
        cekJumlahMenunggu();
        if (document.hidden && intervalMs !== 5000) {
            intervalMs = 5000;
        } else if (!document.hidden && intervalMs !== 2000) {
            intervalMs = 2000;
        }
    }, intervalMs);

// Jalankan sekali saat halaman dimuat
document.addEventListener('DOMContentLoaded', function () {
        mintaIzinNotifikasi();
        pulihkanStatusSuara();

        // Inisialisasi waktu pengingat pertama kali (jika belum pernah ada).
        // Tujuannya agar suara pengingat TIDAK langsung berbunyi saat masuk halaman,
        // melainkan menunggu minimal 30 detik sejak halaman dibuka.
        if (terakhirPengingat === 0) {
            terakhirPengingat = Date.now();
            localStorage.setItem('virgo_pengingat_terakhir', String(terakhirPengingat));
        }

cekJumlahMenunggu();
        // Jika suara pernah diaktifkan, siapkan AudioContext secara SENYAP
        // (tanpa nada) agar suara bisa berbunyi otomatis saat ada pesanan baru
        if (suaraDiaktifkan) {
            setTimeout(function () { bukaAudioContext(); }, 800);
        }
    });
    </script>

</body>
</html>

