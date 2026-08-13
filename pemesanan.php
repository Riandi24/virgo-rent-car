<?php 
session_start(); 
require 'header.php'; 

// Mengambil ID dari URL (GET)
 $id_mobil_get = isset($_GET['mobil']) ? intval($_GET['mobil']) : 0;
 $id_driver_get = isset($_GET['driver']) ? intval($_GET['driver']) : 0;
 $id_wisata_get = isset($_GET['wisata']) ? intval($_GET['wisata']) : 0;

// Variabel untuk menyimpan data pilihan
 $data_mobil = null; $harga_mobil = 0;
 $data_driver = null; $harga_driver = 0;
 $data_wisata = null; $harga_wisata = 0;

// Mengambil data mobil dari database berdasarkan ID di URL
if ($id_mobil_get > 0) {
    $res = mysqli_query($koneksi, "SELECT * FROM tbl_kendaraan WHERE id_kendaraan = $id_mobil_get");
    if (mysqli_num_rows($res) > 0) {
        $data_mobil = mysqli_fetch_assoc($res);
        $harga_mobil = $data_mobil['harga_sewa'];
    }
}
// Mengambil data driver jika ada yang dipilih via URL (dari halaman driver.php)
if ($id_driver_get > 0) {
    $res_driver = mysqli_query($koneksi, "SELECT * FROM tbl_driver WHERE id_driver = $id_driver_get");
    if (mysqli_num_rows($res_driver) > 0) {
        $data_driver = mysqli_fetch_assoc($res_driver);
        $harga_driver = $data_driver['tarif_driver'];
    }
}
// Mengambil data wisata
if ($id_wisata_get > 0) {
    $res = mysqli_query($koneksi, "SELECT * FROM tbl_wisata WHERE id_wisata = $id_wisata_get");
    if (mysqli_num_rows($res) > 0) {
        $data_wisata = mysqli_fetch_assoc($res);
        $harga_wisata = $data_wisata['harga'];
    }
}

// Mengambil semua data driver untuk Dropdown di Form
 $result_driver_dd = mysqli_query($koneksi, "SELECT * FROM tbl_driver ORDER BY id_driver ASC");

// PROSES SAAT TOMBOL KIRIM DITEKAN (POST)
 $error_msg = "";
 $success_msg = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $wa = mysqli_real_escape_string($koneksi, $_POST['wa']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $id_kendaraan = intval($_POST['id_kendaraan']);
    $id_driver = intval($_POST['id_driver']); // Diambil dari Dropdown
    $id_wisata = intval($_POST['id_wisata']);
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $durasi = intval($_POST['durasi']);
    $orang = intval($_POST['orang']);
    $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan']);
    $total_harga = intval($_POST['total_harga']);
    $status_reservasi = "Menunggu Konfirmasi";

    // Validasi pilihan: mobil ATAU paket wisata wajib ada
    $punya_mobil = $id_kendaraan > 0;
    $punya_wisata = $id_wisata > 0;

    if (!$punya_mobil && !$punya_wisata) {
        $error_msg = "Silakan pilih armada atau paket wisata terlebih dahulu sebelum mengirim pemesanan.";
    } else {
        // 1. SISTEM PENCEGAHAHAN DOUBLE ORDER (khusus jika ada mobil yang dipilih)
        if ($punya_mobil) {
            $tanggal_selesai_diminta = date('Y-m-d', strtotime($tanggal_mulai . " + $durasi days"));
        
$query_cek = "SELECT * FROM tbl_reservasi 
                  WHERE id_kendaraan = ? 
                          AND status_reservasi NOT IN ('Dibatalkan', 'Selesai')
                  AND tanggal_mulai < ?
                  AND DATE_ADD(tanggal_mulai, INTERVAL durasi_hari DAY) > ?";
$stmt = $koneksi->prepare($query_cek);
$stmt->bind_param("iss", $id_kendaraan, $tanggal_selesai_diminta, $tanggal_mulai);
$stmt->execute();
$result_cek = $stmt->get_result();
if ($result_cek && $result_cek->num_rows > 0) {
    $error_msg = "Maaf, mobil ini sudah dipesan orang lain pada tanggal tersebut. Silakan pilih tanggal atau mobil lain (Sistem Pencegahan Double Order Berhasil Aktif).";
}
$stmt->close();
        } // akhir if punya_mobil

        if (!$error_msg) {
            $id_driver_db = $id_driver > 0 ? $id_driver : "NULL";
            $id_wisata_db = $id_wisata > 0 ? $id_wisata : "NULL";
            // id_kendaraan boleh NULL jika pemesanan khusus paket wisata tanpa mobil
            $id_kendaraan_db = $punya_mobil ? $id_kendaraan : "NULL";
            
            $query_simpan = "INSERT INTO tbl_reservasi (nama_pemesan, no_wa, email, alamat_jemput, id_kendaraan, id_driver, id_wisata, tanggal_mulai, durasi_hari, jumlah_orang, catatan, total_harga, status_reservasi) 
                             VALUES ('$nama', '$wa', '$email', '$alamat', $id_kendaraan_db, $id_driver_db, $id_wisata_db, '$tanggal_mulai', $durasi, $orang, '$catatan', $total_harga, '$status_reservasi')";
            
            if (mysqli_query($koneksi, $query_simpan)) {
                // Ambil ID reservasi yang baru saja disimpan
                $id_reservasi_baru = mysqli_insert_id($koneksi);
                 
                // UPDATE STATUS MOBIL MENJADI "Terpesan" hanya jika ada mobil yang dipilih
                if ($punya_mobil) {
                    $stmt_up = $koneksi->prepare("UPDATE tbl_kendaraan SET status = 'Terpesan' WHERE id_kendaraan = ?");
                    $stmt_up->bind_param("i", $id_kendaraan);
                    $stmt_up->execute();
                    $stmt_up->close();
                }
                
                // Buat notifikasi WA ke Admin (085121540024)
                $wa_admin_number = "6285121540024";
                $wa_text_admin = "🔔 PESANAN BARU MASUK! 🔔\n\n";
                $wa_text_admin .= "👤 Nama: " . $nama . "\n";
                $wa_text_admin .= "📱 WA: " . $wa . "\n";
                if ($punya_mobil) {
                    $wa_text_admin .= "🚗 Mobil: " . ($data_mobil ? $data_mobil['nama_mobil'] : "ID $id_kendaraan") . "\n";
                }
                if ($punya_wisata) {
                    $wa_text_admin .= "🏞 Paket Wisata: " . ($data_wisata ? $data_wisata['nama_paket'] : "ID $id_wisata") . "\n";
                }
                $wa_text_admin .= "📅 Mulai: " . $tanggal_mulai . "\n";
                $wa_text_admin .= "⏱ Durasi: " . $durasi . " hari\n";
                $wa_text_admin .= "💰 Total: Rp " . number_format($total_harga, 0, ',', '.') . "\n\n";
$wa_text_admin .= "🔗 Konfirmasi di: " . (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . "/admin/index.php";
                $wa_url_admin = "https://wa.me/" . $wa_admin_number . "?text=" . urlencode($wa_text_admin);
                
                $success_msg = "Pemesanan berhasil dikirim! Admin akan segera menghubungi Anda untuk konfirmasi.";
                // Sembunyikan form, tampilkan tombol notifikasi
                $data_mobil = null; $harga_mobil = 0;
                $data_wisata = null; $harga_wisata = 0;
            } else {
                $error_msg = "Terjadi kesalahan sistem: " . mysqli_error($koneksi);
            }
        }
    }
}
?>

<!-- ==================== FORM PEMESANAN ==================== -->
<section class="py-24 relative" id="pemesanan">
    <div class="max-w-5xl mx-auto px-6">
        <div class="text-center mb-12 md:mb-16 fade-up">
            <div class="badge-blue inline-flex items-center gap-2 mb-4">
                <i class="fas fa-clipboard-list"></i> FORM PEMESANAN
            </div>
            <h2 class="section-title font-serif text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4">
                Lengkapi <span class="grad-text">Pemesanan</span> Anda
            </h2>
            <p class="text-slate-400 max-w-xl mx-auto text-sm leading-relaxed">
                Isi formulir di bawah ini untuk menyelesaikan pemesanan. Sistem akan otomatis memvalidasi ketersediaan kendaraan secara real-time.
            </p>
        </div>

        <!-- Notifikasi Error / Sukses -->
        <?php if ($error_msg): ?>
            <div class="mb-8 p-4 bg-red-900/30 border border-red-500/50 rounded-xl text-red-300 text-sm fade-up">
                <i class="fas fa-exclamation-triangle mr-2"></i> <?= $error_msg; ?>
            </div>
        <?php endif; ?>
        <?php if ($success_msg): ?>
            <div class="mb-8 p-4 bg-green-900/30 border border-green-500/50 rounded-xl text-green-300 text-sm fade-up">
                <i class="fas fa-check-circle mr-2"></i> <?= $success_msg; ?>
            </div>
            <?php if(isset($wa_url_admin)): ?>
            <div class="mb-8 text-center">
                <a href="<?= $wa_url_admin; ?>" target="_blank" class="btn-yellow text-xs py-3 px-6 inline-flex w-auto">
                    <i class="fab fa-whatsapp text-lg mr-2"></i> Kirim Notifikasi ke Admin via WhatsApp
                </a>
            </div>
            <?php endif; ?>
        <?php endif; ?>

        <!-- POPUP PEMESANAN BERHASIL -->
        <?php if ($success_msg): ?>
        <div id="popupSukses" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background: rgba(0,0,0,0.7); backdrop-filter: blur(4px);">
            <div class="card-glass p-8 max-w-md w-full text-center">
                <div class="w-16 h-16 mx-auto rounded-full bg-green-500/20 border border-green-500/50 flex items-center justify-center mb-4">
                    <i class="fas fa-check text-green-400 text-3xl"></i>
                </div>
                <h3 class="text-white text-xl font-bold mb-2">Pemesanan Berhasil! 🎉</h3>
                <p class="text-slate-400 text-sm mb-6"><?= $success_msg; ?></p>
                <?php if(isset($wa_url_admin)): ?>
                    <a href="<?= $wa_url_admin; ?>" target="_blank" class="btn-yellow text-xs py-3 px-6 inline-flex w-auto mb-3">
                        <i class="fab fa-whatsapp text-lg mr-2"></i> Kirim Notifikasi ke Admin via WhatsApp
                    </a>
                <?php endif; ?>
                <button onclick="closePopupSukses()" class="btn-primary w-full justify-center mt-3">
                    <i class="fas fa-check-circle mr-2"></i> OK, Selesai
                </button>
            </div>
        </div>
        <script>
            function closePopupSukses() {
                document.getElementById('popupSukses').style.display = 'none';
            }
            // Tutup juga jika klik area gelap di luar card
            document.addEventListener('DOMContentLoaded', function() {
                const popup = document.getElementById('popupSukses');
                if (popup) {
                    popup.addEventListener('click', function(e) {
                        if (e.target === this) closePopupSukses();
                    });
                }
            });
        </script>
        <?php endif; ?>

        <div class="card-glass p-8 md:p-12 fade-up">
            <?php if (!$data_mobil && !$data_wisata && !$success_msg): ?>
                <div class="text-center py-10">
                    <i class="fas fa-info-circle text-4xl text-blue-500 mb-4"></i>
                    <h3 class="text-white text-xl font-bold mb-2">Belum Ada Pilihan</h3>
                    <p class="text-slate-400 mb-6">Silakan pilih armada atau paket wisata terlebih dahulu sebelum mengisi form pemesanan.</p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <a href="armada.php" class="btn-primary">Pilih Armada Sekarang</a>
                        <a href="wisata.php" class="btn-secondary">Pilih Paket Wisata</a>
                    </div>
                </div>
            <?php else: ?>
            <?php if ($data_wisata && !$data_mobil): ?>
                <div class="mb-8 p-4 bg-yellow-900/30 border border-yellow-500/50 rounded-xl text-yellow-300 text-sm">
                    <i class="fas fa-info-circle mr-2"></i>
                    Paket wisata ini sudah termasuk mobil &amp; driver, sehingga Anda tidak perlu memilih kendaraan.
                    Ingin menambah mobil sendiri? 
                    <a href="armada.php?wisata=<?= $id_wisata_get; ?>" class="underline font-semibold hover:text-white">klik di sini</a>.
                </div>
            <?php endif; ?>
            <form id="bookingForm" method="POST" action="">
                <!-- Hidden Inputs untuk kirim data ke PHP -->
                <input type="hidden" name="id_kendaraan" value="<?= $id_mobil_get; ?>">
                <input type="hidden" name="id_wisata" value="<?= $id_wisata_get; ?>">
                <input type="hidden" name="total_harga" id="hiddenTotal" value="0">

                <!-- Selected Summary -->
                <div class="mb-10">
                    <h3 class="text-white font-bold text-lg mb-5 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg grad-blue-green flex items-center justify-center">
                            <i class="fas fa-shopping-cart text-white text-xs"></i>
                        </div>
                        Ringkasan Pilihan
                    </h3>
                    <div class="grid md:grid-cols-2 gap-4">
                        <div class="bg-slate-800/30 rounded-xl p-4 border border-slate-700/30 flex flex-col">
                            <div class="flex-1">
                                <div class="text-slate-500 text-[10px] uppercase tracking-wider mb-1"><i class="fas fa-car mr-1"></i> Mobil</div>
                                <div class="text-white text-sm font-semibold"><?= $data_mobil ? $data_mobil['nama_mobil'] : 'Tidak dipilih'; ?></div>
                                <div class="text-slate-400 text-xs mt-1"><?= $data_mobil ? 'Rp ' . number_format($harga_mobil / 1000, 0, ',', '.') . 'K / hari' : 'Kosong (opsional untuk paket wisata)'; ?></div>
                            </div>
                            <a href="armada.php<?= $id_wisata_get > 0 ? '?wisata=' . $id_wisata_get : ''; ?>" class="mt-3 text-xs text-blue-400 hover:text-white transition-colors inline-flex items-center gap-1">
                                <i class="fas fa-<?= $data_mobil ? 'sync-alt' : 'plus-circle'; ?>"></i> <?= $data_mobil ? 'Ganti Mobil' : 'Pilih Mobil'; ?>
                            </a>
                        </div>
                        <div class="bg-slate-800/30 rounded-xl p-4 border border-slate-700/30 flex flex-col">
                            <div class="flex-1">
                                <div class="text-slate-500 text-[10px] uppercase tracking-wider mb-1"><i class="fas fa-map mr-1"></i> Paket Wisata</div>
                                <div class="text-white text-sm font-semibold"><?= $data_wisata ? $data_wisata['nama_paket'] : 'Tidak dipilih'; ?></div>
                                <div class="text-slate-400 text-xs mt-1"><?= $data_wisata ? 'Rp ' . number_format($harga_wisata / 1000000, 1, ',', '.') . 'Jt' : ''; ?></div>
                            </div>
                            <a href="wisata.php?mobil=<?= $id_mobil_get; ?>" class="mt-3 text-xs text-green-400 hover:text-white transition-colors inline-flex items-center gap-1">
                                <i class="fas fa-<?= $data_wisata ? 'sync-alt' : 'plus-circle'; ?>"></i> <?= $data_wisata ? 'Ganti Paket Wisata' : 'Pilih Paket Wisata'; ?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Personal Info -->
                <div class="mb-10">
                    <h3 class="text-white font-bold text-lg mb-5 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg grad-green-yellow flex items-center justify-center">
                            <i class="fas fa-user text-white text-xs"></i>
                        </div>
                        Data Pemesan
                    </h3>
                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama" class="form-input" placeholder="Masukkan nama lengkap" required>
                        </div>
                        <div>
                            <label class="form-label">No. WhatsApp</label>
                            <input type="tel" name="wa" class="form-input" placeholder="Contoh: 081234567890" required>
                        </div>
                        <div>
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-input" placeholder="email@contoh.com">
                        </div>
                        <div>
                            <label class="form-label">Alamat / Penjemputan</label>
                            <input type="text" name="alamat" class="form-input" placeholder="Alamat lengkap penjemputan" required>
                        </div>
                    </div>
                </div>

                <!-- Date, Duration & Driver Selection -->
                <div class="mb-10">
                    <h3 class="text-white font-bold text-lg mb-5 flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg grad-blue-yellow flex items-center justify-center">
                            <i class="fas fa-calendar text-white text-xs"></i>
                        </div>
                        Tanggal, Durasi & Driver
                    </h3>
                    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-5">
                        <div>
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="tanggal_mulai" id="inputTanggal" class="form-input" required onchange="calculateTotal()">
                        </div>
                        <div>
                            <label class="form-label">Durasi Sewa</label>
                            <select name="durasi" id="inputDurasi" class="form-input" required onchange="calculateTotal()">
                                <option value="">Pilih durasi</option>
                                <option value="1">1 Hari</option>
                                <option value="2">2 Hari</option>
                                <option value="3">3 Hari</option>
                                <option value="5">5 Hari</option>
                                <option value="7">7 Hari</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Pilihan Driver</label>
                            <select name="id_driver" id="inputDriver" class="form-input" onchange="calculateTotal()">
                                <option value="0" data-price="0" <?= ($id_driver_get == 0) ? 'selected' : ''; ?>>Tanpa Driver (Lepas Kunci)</option>
                                <?php 
                                if(mysqli_num_rows($result_driver_dd) > 0):
                                    while($d = mysqli_fetch_assoc($result_driver_dd)): 
                                ?>
                                    <option value="<?= $d['id_driver']; ?>" data-price="<?= $d['tarif_driver']; ?>" <?= ($id_driver_get == $d['id_driver']) ? 'selected' : ''; ?>>
                                        <?= $d['nama_driver']; ?> (Rp <?= number_format($d['tarif_driver'], 0, ',', '.'); ?>/hari)
                                    </option>
                                <?php endwhile; endif; ?>
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Jumlah Orang</label>
                            <select name="orang" class="form-input">
                                <option value="1">1 Orang</option>
                                <option value="2">2 Orang</option>
                                <option value="3">3 Orang</option>
                                <option value="4">4 Orang</option>
                                <option value="5">5 Orang</option>
                                <option value="6">6 Orang</option>
                                <option value="7">7 Orang</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Catatan -->
                <div class="mb-10">
                    <label class="form-label">Catatan Tambahan (Opsional)</label>
                    <textarea name="catatan" class="form-input" rows="3" placeholder="Permintaan khusus, tujuan wisata, dll."></textarea>
                </div>

                <!-- Price Calculation -->
                <div class="mb-10 bg-gradient-to-r from-blue-900/20 via-green-900/20 to-yellow-900/20 rounded-2xl p-6 border border-slate-700/30">
                    <h3 class="text-white font-bold text-lg mb-4 flex items-center gap-2">
                        <i class="fas fa-calculator text-yellow-500"></i>
                        Rincian Biaya
                    </h3>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400 text-sm">Sewa Mobil (<span id="labelDurasiMobil">0</span> hari)</span>
                            <span class="text-white font-semibold text-sm" id="priceMobil">Rp 0</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400 text-sm">Tarif Driver (<span id="labelDurasiDriver">0</span> hari)</span>
                            <span class="text-white font-semibold text-sm" id="priceDriver">Rp 0</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-400 text-sm">Paket Wisata</span>
                            <span class="text-white font-semibold text-sm" id="priceTour">Rp 0</span>
                        </div>
                        <div class="h-px bg-gradient-to-r from-blue-500/30 via-green-500/30 to-yellow-500/30 my-2"></div>
                        <div class="flex justify-between items-center">
                            <span class="text-white font-bold text-base">TOTAL ESTIMASI</span>
                            <span class="text-2xl font-bold grad-text" id="priceTotal">Rp 0</span>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="flex flex-col sm:flex-row gap-4">
                    <button type="submit" class="btn-primary py-4 px-10 text-sm flex-1">
                        <i class="fas fa-paper-plane text-lg"></i> Kirim Pemesanan
                    </button>
                    <a href="armada.php<?= $id_wisata_get > 0 ? '?wisata=' . $id_wisata_get : ''; ?>" class="border border-slate-700 text-white rounded-[14px] py-4 px-10 text-sm font-semibold hover:border-slate-500 hover:bg-white/5 transition-all flex items-center justify-center gap-2">
                        <i class="fas fa-arrow-left"></i> Ganti Mobil
                    </a>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Script JS khusus untuk kalkulasi halaman ini -->
<script>
    // Ambil harga dari PHP ke JS
    const carPrice = <?= $harga_mobil ?: 0; ?>;
    const tourPrice = <?= $harga_wisata ?: 0; ?>;
    const isTourSelected = <?= $id_wisata_get > 0 ? 'true' : 'false' ?>;

    function formatPrice(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    }

    function calculateTotal() {
        const inputDurasi = document.getElementById('inputDurasi');
        const inputDriver = document.getElementById('inputDriver');
        // Guard: jika form tidak tampil (belum ada pilihan), hentikan
        if (!inputDurasi || !inputDriver) return;

        const durasi = parseInt(inputDurasi.value) || 0;

        // Ambil harga driver dari dropdown yang dipilih
        const selectedDriverOption = inputDriver.options[inputDriver.selectedIndex];
        const currentDriverPrice = parseInt(selectedDriverOption.getAttribute('data-price')) || 0;

        const mobilTotal = carPrice * durasi;
        const driverTotal = currentDriverPrice * durasi;
        const grandTotal = mobilTotal + driverTotal + tourPrice;

        document.getElementById('labelDurasiMobil').textContent = durasi;
        document.getElementById('labelDurasiDriver').textContent = currentDriverPrice > 0 ? durasi : 0;
        document.getElementById('priceMobil').textContent = `Rp ${formatPrice(mobilTotal)}`;
        document.getElementById('priceDriver').textContent = `Rp ${formatPrice(driverTotal)}`;
        document.getElementById('priceTour').textContent = isTourSelected ? `Rp ${formatPrice(tourPrice)}` : 'Rp 0';
        document.getElementById('priceTotal').textContent = `Rp ${formatPrice(grandTotal)}`;
        
        // Simpan ke hidden input untuk dikirim via POST
        document.getElementById('hiddenTotal').value = grandTotal;
    }

    // Set min date jadi hari ini
    const today = new Date().toISOString().split('T')[0];
    const inputTanggal = document.getElementById('inputTanggal');
    if(inputTanggal) inputTanggal.setAttribute('min', today);
    
    // Panggil sekali untuk inisialisasi tampilan
    calculateTotal();
</script>

<?php require 'footer.php'; ?>
