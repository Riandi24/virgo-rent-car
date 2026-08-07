<?php
require 'header.php';

// =====================================================
// BACKEND: Proses simpan ulasan (Prepared Statements)
// =====================================================
$error_msg = "";
$success_msg = "";

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
        // Gunakan prepared statement agar aman dari SQL Injection
        $stmt = mysqli_prepare($koneksi, "INSERT INTO tbl_reviews (id_kendaraan, nama_pelanggan, rating, komentar) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            // "i" = integer, "s" = string, "i" = integer, "s" = string
            mysqli_stmt_bind_param($stmt, "isis", $id_kendaraan, $nama, $rating, $komentar);
            if (mysqli_stmt_execute($stmt)) {
                $success_msg = "Terima kasih! Ulasan Anda berhasil dikirim. ⭐";
            } else {
                $error_msg = "Terjadi kesalahan saat menyimpan ulasan: " . mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        } else {
            $error_msg = "Terjadi kesalahan sistem: " . mysqli_error($koneksi);
        }
    }
}

// =====================================================
// AMBIL DATA: daftar mobil (untuk dropdown form)
// =====================================================
$result_mobil = mysqli_query($koneksi, "SELECT * FROM tbl_kendaraan ORDER BY nama_mobil ASC");

// =====================================================
// AMBIL DATA: rata-rata & daftar ulasan
// (dengan JOIN ke tabel kendaraan agar memakai nama mobil)
// Diberi guard jika tabel tbl_reviews belum dibuat/diimport
// =====================================================
$total_review = 0;
$rata_rata = 0;
$result_reviews = false;

// Cek apakah tabel tbl_reviews sudah ada di database
$cek_tabel = mysqli_query($koneksi, "SHOW TABLES LIKE 'tbl_reviews'");
if ($cek_tabel && mysqli_num_rows($cek_tabel) > 0) {
    $query_reviews = "SELECT r.*, k.nama_mobil
                      FROM tbl_reviews r
                      LEFT JOIN tbl_kendaraan k ON r.id_kendaraan = k.id_kendaraan
                      ORDER BY r.tanggal DESC";
    $result_reviews = mysqli_query($koneksi, $query_reviews);

    // Hitung rata-rata rating dari semua ulasan
    $query_avg = "SELECT COUNT(*) AS total_review, COALESCE(AVG(rating), 0) AS rata_rata FROM tbl_reviews";
    $result_avg = mysqli_query($koneksi, $query_avg);
    if ($result_avg) {
        $avg_data = mysqli_fetch_assoc($result_avg);
        $total_review = intval($avg_data['total_review']);
        $rata_rata = round(floatval($avg_data['rata_rata']), 1);
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
        <div class="text-center mb-16 fade-up">
            <div class="badge-blue inline-flex items-center gap-2 mb-4">
                <i class="fas fa-star"></i> ULASAN &amp; RATING
            </div>
            <h2 class="section-title font-serif text-4xl md:text-5xl font-bold text-white mb-4">
                Testimoni <span class="grad-text">Pelanggan</span>
            </h2>
            <p class="text-slate-400 max-w-xl mx-auto text-sm leading-relaxed">
                Bagikan pengalaman Anda setelah menyewa mobil di Virgo Rent Car. Penilaian Anda sangat berharga bagi kami.
            </p>
        </div>

        <!-- Ringkasan Rata-rata Rating -->
        <div class="card-glass p-8 md:p-10 mb-10 fade-up">
            <div class="flex flex-col md:flex-row items-center justify-center gap-8 text-center">
                <div>
                    <div class="text-5xl md:text-6xl font-bold grad-text"><?= $rata_rata; ?></div>
                    <div class="mt-2"><?= renderStars($rata_rata); ?></div>
                    <div class="text-slate-500 text-xs mt-2">dari 5.0 bintang</div>
                </div>
                <div class="hidden md:block h-20 w-px bg-slate-700/50"></div>
                <div>
                    <div class="text-4xl font-bold text-white"><?= $total_review; ?></div>
                    <div class="text-slate-500 text-xs mt-1 uppercase tracking-wider">Total Ulasan</div>
                </div>
            </div>
        </div>

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

        <div class="grid lg:grid-cols-2 gap-10">
            <!-- ============ FORM ULASAN ============ -->
            <div class="card-glass p-8 fade-up">
                <h3 class="text-white font-bold text-xl mb-2 flex items-center gap-2">
                    <i class="fas fa-pen text-yellow-400"></i> Tulis Ulasan
                </h3>
                <p class="text-slate-500 text-sm mb-6">Bagikan pengalaman sewa mobil Anda.</p>

                <form method="POST" action="" id="reviewForm">
                    <!-- Pilihan Mobil -->
                    <div class="mb-5">
                        <label class="form-label">Pilih Mobil</label>
                        <select name="id_kendaraan" class="form-input" required>
                            <option value="">-- Pilih Mobil yang Diulas --</option>
                            <?php
                            if (mysqli_num_rows($result_mobil) > 0):
                                while ($mobil = mysqli_fetch_assoc($result_mobil)):
                            ?>
                                <option value="<?= $mobil['id_kendaraan']; ?>"><?= htmlspecialchars($mobil['nama_mobil']); ?></option>
                            <?php endwhile; endif; ?>
                        </select>
                    </div>

                    <!-- Nama -->
                    <div class="mb-5">
                        <label class="form-label">Nama Anda</label>
                        <input type="text" name="nama_pelanggan" class="form-input" placeholder="Nama lengkap Anda" required>
                    </div>

                    <!-- Rating Bintang -->
                    <div class="mb-5">
                        <label class="form-label">Rating Anda</label>
                        <div class="star-rating" id="starRating">
                            <input type="hidden" name="rating" id="ratingValue" value="5">
                            <i class="fas fa-star" data-value="1"></i>
                            <i class="fas fa-star" data-value="2"></i>
                            <i class="fas fa-star" data-value="3"></i>
                            <i class="fas fa-star" data-value="4"></i>
                            <i class="fas fa-star" data-value="5"></i>
                        </div>
                        <div class="text-xs text-slate-500 mt-1" id="ratingLabel">Sangat Baik (5)</div>
                    </div>

                    <!-- Komentar -->
                    <div class="mb-6">
                        <label class="form-label">Komentar / Ulasan</label>
                        <textarea name="komentar" class="form-input" rows="4" placeholder="Ceritakan pengalaman Anda menyewa mobil di sini..." required></textarea>
                    </div>

                    <button type="submit" class="btn-primary w-full justify-center py-4">
                        <i class="fas fa-paper-plane"></i> Kirim Ulasan
                    </button>
                </form>
            </div>

            <!-- ============ DAFTAR ULASAN ============ -->
            <div class="fade-up">
                <h3 class="text-white font-bold text-xl mb-6 flex items-center gap-2">
                    <i class="fas fa-comments text-blue-400"></i> Ulasan Masuk (<?= $total_review; ?>)
                </h3>

<div class="space-y-5 max-h-[650px] overflow-y-auto pr-2 review-scroll">
                    <?php if ($result_reviews && mysqli_num_rows($result_reviews) > 0): ?>
                        <?php while ($review = mysqli_fetch_assoc($result_reviews)): ?>
                        <div class="card-glass p-6">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-11 h-11 rounded-full grad-blue-green flex items-center justify-center text-white font-bold">
                                        <?= strtoupper(substr($review['nama_pelanggan'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="text-white font-semibold text-sm"><?= htmlspecialchars($review['nama_pelanggan']); ?></div>
                                        <div class="text-xs text-slate-500">Mobil: <?= htmlspecialchars($review['nama_mobil'] ?? 'Umum'); ?></div>
                                    </div>
                                </div>
                                <div class="text-slate-500 text-xs text-right">
                                    <?= date('d M Y', strtotime($review['tanggal'])); ?>
                                </div>
                            </div>
                            <div class="mt-3">
                                <?= renderStars($review['rating']); ?>
                            </div>
                            <p class="text-slate-400 text-sm mt-3 leading-relaxed"><?= nl2br(htmlspecialchars($review['komentar'])); ?></p>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="card-glass p-8 text-center">
                            <i class="fas fa-star text-4xl text-slate-700 mb-4"></i>
                            <p class="text-slate-500">Belum ada ulasan. Jadilah pelanggan pertama yang memberi ulasan!</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ==================== JAVASCRIPT: BINTANG ==================== -->
<script>
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
</script>

<?php require 'footer.php'; ?>
