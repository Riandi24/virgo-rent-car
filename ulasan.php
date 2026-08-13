<?php
require 'header.php';

// =====================================================
// BACKEND: Proses simpan ulasan (Prepared Statements)
// =====================================================
$error_msg = "";
$success_msg = "";

// Cek apakah kolom status sudah ada (untuk kompatibilitas versi lama)
// Ditempatkan di awal agar dapat dipakai oleh proses simpan (INSERT) juga.
$kolom_status_ada = false;
$cek_tabel_awal = mysqli_query($koneksi, "SHOW TABLES LIKE 'tbl_reviews'");
if ($cek_tabel_awal && mysqli_num_rows($cek_tabel_awal) > 0) {
    $cek_kolom = mysqli_query($koneksi, "SHOW COLUMNS FROM tbl_reviews LIKE 'status'");
    if ($cek_kolom && mysqli_num_rows($cek_kolom) > 0) {
        $kolom_status_ada = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $nama = trim($_POST['nama_pelanggan'] ?? '');
    $rating = intval($_POST['rating'] ?? 0);
    $komentar = trim($_POST['komentar'] ?? '');
    $id_kendaraan = intval($_POST['id_kendaraan'] ?? 0);

    // Validasi rating harus 1 - 5
    if ($rating < 1 || $rating > 5) {
        $rating = 5; // default jika tidak valid
    }

    // Validasi input
    if (empty($nama)) {
        $error_msg = "Nama pelanggan wajib diisi.";
    } elseif ($id_kendaraan <= 0) {
        $error_msg = "Silakan pilih mobil yang ingin diulas.";
    } elseif (empty($komentar)) {
        $error_msg = "Komentar / ulasan wajib diisi.";
    } else {
        // Simpan ulasan.
        // Jika kolom status ada -> simpan dengan status 'Pending' (menunggu persetujuan admin).
        // Jika kolom status belum ada (DB lama) -> simpan tanpa kolom status agar tidak error.
        if ($kolom_status_ada) {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO tbl_reviews (id_kendaraan, nama_pelanggan, rating, komentar, status) VALUES (?, ?, ?, ?, 'Pending')");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "isis", $id_kendaraan, $nama, $rating, $komentar);
                $eksekusi = mysqli_stmt_execute($stmt);
                $msg_sukses = "Terima kasih! Ulasan Anda berhasil dikirim & akan ditampilkan setelah disetujui admin. ⭐";
                mysqli_stmt_close($stmt);
            } else {
                $eksekusi = false;
                $error_msg = "Terjadi kesalahan sistem: " . mysqli_error($koneksi);
            }
        } else {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO tbl_reviews (id_kendaraan, nama_pelanggan, rating, komentar) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "isis", $id_kendaraan, $nama, $rating, $komentar);
                $eksekusi = mysqli_stmt_execute($stmt);
                $msg_sukses = "Terima kasih! Ulasan Anda berhasil dikirim. ⭐";
                mysqli_stmt_close($stmt);
            } else {
                $eksekusi = false;
                $error_msg = "Terjadi kesalahan sistem: " . mysqli_error($koneksi);
            }
        }

        if (!empty($eksekusi)) {
            $success_msg = $msg_sukses;
        } elseif (empty($error_msg)) {
            $error_msg = "Terjadi kesalahan saat menyimpan ulasan.";
        }
    }
}

// =====================================================
// AMBIL DATA: daftar mobil (untuk dropdown form)
// =====================================================
$result_mobil = mysqli_query($koneksi, "SELECT * FROM tbl_kendaraan ORDER BY nama_mobil ASC");

// =====================================================
// AMBIL DATA: rata-rata & daftar ulasan (Approved only)
// =====================================================
$total_review = 0;
$rata_rata = 0;
$result_reviews = false;
$rating_distribution = array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0);

// Cek apakah tabel tbl_reviews sudah ada di database
// (nilai $kolom_status_ada sudah dihitung di bagian atas file)
$cek_tabel = mysqli_query($koneksi, "SHOW TABLES LIKE 'tbl_reviews'");

if ($cek_tabel && mysqli_num_rows($cek_tabel) > 0) {

    // --- Filter hanya ulasan Approved (jika kolom status ada) ---
    $filter_status = $kolom_status_ada ? "WHERE r.status = 'Approved'" : "";

    $query_reviews = "SELECT r.*, k.nama_mobil, k.gambar AS mobil_gambar
                      FROM tbl_reviews r
                      LEFT JOIN tbl_kendaraan k ON r.id_kendaraan = k.id_kendaraan
                      $filter_status
                      ORDER BY r.tanggal DESC";
    $result_reviews = mysqli_query($koneksi, $query_reviews);

    // Hitung rata-rata rating dari ulasan Approved
    $query_avg = "SELECT COUNT(*) AS total_review, COALESCE(AVG(rating), 0) AS rata_rata FROM tbl_reviews r $filter_status";
    $result_avg = mysqli_query($koneksi, $query_avg);
    if ($result_avg) {
        $avg_data = mysqli_fetch_assoc($result_avg);
        $total_review = intval($avg_data['total_review']);
        $rata_rata = round(floatval($avg_data['rata_rata']), 1);
    }

    // Distribusi rating (hitung per bintang dari Approved)
    $query_dist = "SELECT rating, COUNT(*) AS jumlah FROM tbl_reviews r $filter_status GROUP BY rating";
    $result_dist = mysqli_query($koneksi, $query_dist);
    if ($result_dist) {
        while ($d = mysqli_fetch_assoc($result_dist)) {
            $rating_distribution[intval($d['rating'])] = intval($d['jumlah']);
        }
    }
} else {
    // Tabel belum ada — tampilkan pesan agar admin mengimport SQL
    $error_msg = "Tabel ulasan belum tersedia. Silakan import file <strong>_arsip/sql/reviews.sql</strong> di phpMyAdmin terlebih dahulu.";
}

// Helper menampilkan bintang berdasarkan rating
function renderStars($rating) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $html .= '<i class="fas fa-star text-yellow-400"></i>';
        } elseif ($i - 0.5 <= $rating) {
            $html .= '<i class="fas fa-star-half-alt text-yellow-400"></i>';
        } else {
            $html .= '<i class="far fa-star text-slate-600"></i>';
        }
    }
    return $html;
}
?>

<!-- ==================== ULASAN & RATING ==================== -->
<section class="py-24 relative" id="ulasan">
    <div class="max-w-6xl mx-auto px-6">
        <!-- Section Header -->
        <div class="text-center mb-12 md:mb-16 fade-up">
            <div class="badge-blue inline-flex items-center gap-2 mb-4">
                <i class="fas fa-star"></i> ULASAN PELANGGAN
            </div>
            <h2 class="section-title font-serif text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4">
                Ulasan <span class="grad-text">Pelanggan</span>
            </h2>
            <p class="text-slate-400 max-w-xl mx-auto text-sm leading-relaxed">
                Lihat pengalaman pelanggan yang telah menggunakan layanan rental mobil kami.
            </p>
        </div>

        <?php if ($total_review > 0): ?>
        <!-- ===== RINGKASAN RATING + DISTRIBUSI ===== -->
        <div class="card-glass p-8 md:p-10 mb-10 fade-up">
            <div class="grid lg:grid-cols-2 gap-8 md:gap-12 items-center">
                <!-- Skor rata-rata -->
                <div class="text-center lg:text-left">
                    <div class="flex flex-col lg:flex-row items-center lg:items-end gap-4">
                        <div class="avg-rating-hero"><?= $rata_rata; ?></div>
                        <div class="pb-1">
                            <div class="flex gap-1 mb-2"><?= renderStars($rata_rata); ?></div>
                            <div class="text-slate-400 text-sm">dari 5.0 bintang</div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-center lg:justify-start gap-3 text-slate-400 text-sm">
                        <i class="fas fa-check-circle text-green-400"></i>
                        <span><strong class="text-white"><?= $total_review; ?></strong> ulasan dari pelanggan terpercaya</span>
                    </div>
                    <div class="mt-6">
                        <button type="button" onclick="bukaFormUlasan()" class="btn-yellow !w-auto">
                            <i class="fas fa-pen"></i> Berikan Ulasan
                        </button>
                    </div>
                </div>

                <!-- Distribusi rating -->
                <div class="space-y-3">
                    <?php for ($i = 5; $i >= 1; $i--):
                        $jumlah = $rating_distribution[$i];
                        $persen = $total_review > 0 ? round(($jumlah / $total_review) * 100) : 0;
                    ?>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-slate-300 font-semibold w-8 text-right"><?= $i; ?></span>
                        <i class="fas fa-star text-yellow-400 text-xs"></i>
                        <div class="rating-bar-track">
                            <div class="rating-bar-fill" style="width: <?= $persen; ?>%;"></div>
                        </div>
                        <span class="text-xs text-slate-500 w-10"><?= $persen; ?>%</span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- ===== Jika belum ada ulasan approved ===== -->
        <div class="card-glass p-12 text-center max-w-lg mx-auto mb-10 fade-up">
            <div class="w-16 h-16 mx-auto rounded-full bg-yellow-500/10 border border-yellow-500/30 flex items-center justify-center mb-4">
                <i class="fas fa-star text-yellow-400 text-2xl"></i>
            </div>
            <h3 class="text-white text-xl font-bold mb-2">Belum Ada Ulasan</h3>
            <p class="text-slate-500 text-sm mb-6">Jadilah pelanggan pertama yang berbagi pengalaman sewa mobil di Virgo Rent Car.</p>
            <button type="button" onclick="bukaFormUlasan()" class="btn-yellow !w-auto">
                <i class="fas fa-pen"></i> Berikan Ulasan
            </button>
        </div>
        <?php endif; ?>

        <!-- Notifikasi -->
        <?php if ($error_msg): ?>
            <div class="mb-8 p-4 bg-red-900/30 border border-red-500/50 rounded-xl text-red-300 text-sm fade-up">
                <i class="fas fa-exclamation-triangle mr-2"></i> <?= $error_msg; ?>
            </div>
        <?php endif; ?>
        <?php if ($success_msg): ?>
            <div class="mb-8 p-4 bg-green-900/30 border border-green-500/50 rounded-xl text-green-300 text-sm fade-up">
                <i class="fas fa-check-circle mr-2"></i> <?= $success_msg; ?>
            </div>
        <?php endif; ?>

        <!-- ===== GRID DAFTAR ULASAN ===== -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if ($result_reviews && mysqli_num_rows($result_reviews) > 0): ?>
                <?php while ($review = mysqli_fetch_assoc($result_reviews)): ?>
                <div class="card-glass review-card p-6 flex flex-col fade-up">
                    <!-- Foto mobil (opsional) -->
                    <?php if (!empty($review['mobil_gambar'])): ?>
                    <div class="review-car-img relative h-36 rounded-xl overflow-hidden mb-4">
                        <img src="uploads/<?= htmlspecialchars($review['mobil_gambar']); ?>" alt="<?= htmlspecialchars($review['nama_mobil'] ?? 'Mobil'); ?>" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-virgo-darker/70 to-transparent"></div>
                        <div class="absolute bottom-3 left-3 flex items-center gap-2">
                            <i class="fas fa-car text-yellow-400"></i>
                            <span class="text-white text-sm font-semibold"><?= htmlspecialchars($review['nama_mobil'] ?? 'Umum'); ?></span>
                        </div>
                    </div>
                    <?php endif; ?>

<div class="flex items-center gap-3 mb-3">
                        <!-- Avatar pelanggan (inisial) -->
                        <div class="w-11 h-11 rounded-full grad-blue-green flex items-center justify-center text-white font-bold">
                            <?= strtoupper(substr($review['nama_pelanggan'], 0, 1)); ?>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="text-white font-semibold text-sm truncate"><?= htmlspecialchars($review['nama_pelanggan']); ?></div>
                            <div class="text-slate-500 text-xs"><?= date('d M Y', strtotime($review['tanggal'])); ?></div>
                        </div>
                    </div>

                    <div class="flex gap-1 mb-3"><?= renderStars($review['rating']); ?></div>

                    <!-- Jika mobil tidak punya foto, tampilkan nama mobil di sini -->
                    <?php if (empty($review['mobil_gambar'])): ?>
                    <div class="flex items-center gap-2 text-xs text-slate-400 mb-3">
                        <i class="fas fa-car text-yellow-400"></i>
                        <span><?= htmlspecialchars($review['nama_mobil'] ?? 'Umum'); ?></span>
                    </div>
                    <?php endif; ?>

                    <p class="text-slate-400 text-sm leading-relaxed flex-1"><?= nl2br(htmlspecialchars($review['komentar'])); ?></p>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-span-full card-glass p-12 text-center">
                    <i class="fas fa-star text-4xl text-slate-700 mb-4"></i>
                    <p class="text-slate-500">Belum ada ulasan yang disetujui. Jadilah yang pertama memberi ulasan!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ==================== MODAL FORM ULASAN ==================== -->
<div class="review-modal" id="formUlasanModal">
    <div class="review-modal-backdrop" onclick="tutupFormUlasan()"></div>
    <div class="review-modal-box">
        <button type="button" class="review-modal-close" onclick="tutupFormUlasan()" aria-label="Tutup">
            <i class="fas fa-times"></i>
        </button>

<div class="text-center mb-5">
            <div class="review-modal-icon grad-blue-green mx-auto mb-3">
                <i class="fas fa-star"></i>
            </div>
            <h3 class="text-white font-bold text-lg leading-tight">Berikan Ulasan</h3>
            <p class="text-slate-500 text-xs mt-1">Bagikan pengalaman sewa mobil Anda</p>
        </div>

        <form method="POST" action="">
            <!-- Rating Bintang -->
            <div class="mb-4">
                <label class="form-label">Rating Anda</label>
                <div class="rating-select" id="starRating">
                    <input type="hidden" name="rating" id="ratingValue" value="5">
                    <?php for ($r = 1; $r <= 5; $r++): ?>
                    <i class="fas fa-star" data-value="<?= $r; ?>"></i>
                    <?php endfor; ?>
                </div>
                <div class="text-xs text-slate-500 mt-2" id="ratingLabel">
                    <span class="inline-flex items-center gap-1"><i class="fas fa-smile text-yellow-400"></i> Sangat Baik (5)</span>
                </div>
            </div>

            <!-- Nama + Mobil (2 kolom) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="form-label">Nama Anda</label>
                    <input type="text" name="nama_pelanggan" class="form-input" placeholder="Nama lengkap" required>
                </div>
                <div>
                    <label class="form-label">Mobil yang Diulas</label>
                    <select name="id_kendaraan" class="form-input" required>
                        <option value="">-- Pilih Mobil --</option>
                        <?php
                        if ($result_mobil && mysqli_num_rows($result_mobil) > 0):
                            while ($mobil = mysqli_fetch_assoc($result_mobil)):
                        ?>
                            <option value="<?= $mobil['id_kendaraan']; ?>"><?= htmlspecialchars($mobil['nama_mobil']); ?></option>
                        <?php endwhile; endif; ?>
                    </select>
                </div>
            </div>

            <!-- Komentar -->
            <div class="mb-4">
                <label class="form-label">Ulasan</label>
                <textarea name="komentar" class="form-input" rows="3" placeholder="Tulis komentar Anda di sini..." required></textarea>
            </div>

            <button type="submit" class="btn-yellow w-full justify-center py-3">
                <i class="fas fa-paper-plane"></i> Kirim Ulasan
            </button>
        </form>
    </div>
</div>

<!-- ==================== JAVASCRIPT: BINTANG & MODAL ==================== -->
<script>
    // ---- Bintang interaktif ----
    const stars = document.querySelectorAll('#starRating i');
    const ratingInput = document.getElementById('ratingValue');
    const ratingLabel = document.getElementById('ratingLabel');

    const labels = ['', 'Sangat Buruk (1)', 'Buruk (2)', 'Cukup (3)', 'Baik (4)', 'Sangat Baik (5)'];

    function fillStars(value) {
        stars.forEach(star => {
            const val = parseInt(star.dataset.value);
            if (val <= value) {
                star.classList.remove('far');
                star.classList.add('fas', 'text-yellow-400');
            } else {
                star.classList.remove('fas', 'text-yellow-400');
                star.classList.add('far');
            }
        });
    }

    stars.forEach(star => {
        star.addEventListener('click', () => {
            const value = parseInt(star.dataset.value);
            ratingInput.value = value;
            ratingLabel.textContent = labels[value];
            fillStars(value);
        });
    });

    // Inisialisasi tampilan awal (rating 5)
    fillStars(5);
    ratingLabel.textContent = labels[5];

    // ---- Modal form ulasan ----
    function bukaFormUlasan() {
        document.getElementById('formUlasanModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function tutupFormUlasan() {
        document.getElementById('formUlasanModal').classList.remove('active');
        document.body.style.overflow = '';
    }
</script>

<?php require 'footer.php'; ?>
