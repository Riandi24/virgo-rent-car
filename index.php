<?php require 'header.php'; 

// =====================================================
// AMBIL DATA ULASAN UNTUK SECTION TESTIMONI DI BERANDA
// =====================================================
$reviews_data = array();
$total_review = 0;
$rata_rata = 0;
$rating_distribution = array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0);

$cek_tabel = mysqli_query($koneksi, "SHOW TABLES LIKE 'tbl_reviews'");
// Cek apakah kolom status sudah ada (untuk kompatibilitas versi lama)
$kolom_status_ada = false;
if ($cek_tabel && mysqli_num_rows($cek_tabel) > 0) {
    $cek_kolom = mysqli_query($koneksi, "SHOW COLUMNS FROM tbl_reviews LIKE 'status'");
    if ($cek_kolom && mysqli_num_rows($cek_kolom) > 0) {
        $kolom_status_ada = true;
    }
}
if ($cek_tabel && mysqli_num_rows($cek_tabel) > 0) {
    // Filter hanya ulasan Approved (jika kolom status ada)
    $filter_status = $kolom_status_ada ? "WHERE r.status = 'Approved'" : "";

    $query_avg = "SELECT COUNT(*) AS total_review, COALESCE(AVG(rating), 0) AS rata_rata FROM tbl_reviews r $filter_status";
    $result_avg = mysqli_query($koneksi, $query_avg);
    if ($result_avg) {
        $avg_data = mysqli_fetch_assoc($result_avg);
        $total_review = intval($avg_data['total_review']);
        $rata_rata = round(floatval($avg_data['rata_rata']), 1);
    }

    // Distribusi rating (jumlah per bintang - approved only)
    $query_dist = "SELECT rating, COUNT(*) AS jumlah FROM tbl_reviews r $filter_status GROUP BY rating";
    $result_dist = mysqli_query($koneksi, $query_dist);
    if ($result_dist) {
        while ($d = mysqli_fetch_assoc($result_dist)) {
            $rating_distribution[intval($d['rating'])] = intval($d['jumlah']);
        }
    }

    // Ambil 6 ulasan terbaru (approved only) + foto mobil
    $query_reviews = "SELECT r.*, k.nama_mobil, k.gambar AS mobil_gambar
                      FROM tbl_reviews r
                      LEFT JOIN tbl_kendaraan k ON r.id_kendaraan = k.id_kendaraan
                      $filter_status
                      ORDER BY r.tanggal DESC
                      LIMIT 6";
    $result_reviews = mysqli_query($koneksi, $query_reviews);
    if ($result_reviews) {
        while ($r = mysqli_fetch_assoc($result_reviews)) {
            $reviews_data[] = $r;
        }
    }
}

function renderHomeStars($rating) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $html .= '<i class="fas fa-star text-yellow-400 text-sm"></i>';
        } elseif ($i - 0.5 <= $rating) {
            $html .= '<i class="fas fa-star-half-alt text-yellow-400 text-sm"></i>';
        } else {
            $html .= '<i class="far fa-star text-slate-600 text-sm"></i>';
        }
    }
    return $html;
}
?>

<!-- ==================== HERO ==================== -->
<section class="hero-bg min-h-[80vh] flex items-center relative" id="hero">
    <div class="max-w-7xl mx-auto px-6 py-16 md:py-20 w-full relative z-10">
        <div class="grid lg:grid-cols-2 gap-12 md:gap-16 items-center">
            <div>
                <div class="flex items-center gap-3 mb-6 md:mb-8 fade-up">
                    <div class="badge-blue"><i class="fas fa-map-marker-alt mr-1"></i> Pekanbaru, Riau</div>
                    <div class="badge-green">Tersedia 24/7</div>
                </div>
                <h1 class="hero-title font-serif text-4xl md:text-5xl lg:text-6xl xl:text-7xl font-bold text-white leading-[1.05] tracking-tight mb-5 md:mb-6 fade-up" style="transition-delay:0.1s">
                    Solusi Rental<br>
                    <span class="grad-text">Mobil Terpercaya</span><br>
                    di Pekanbaru
                </h1>
                <p class="text-slate-400 text-base md:text-lg leading-relaxed max-w-lg mb-10 fade-up" style="transition-delay:0.2s">
                    Nikmati perjalanan nyaman dengan armada terbaik dan driver berpengalaman. Tersedia juga paket wisata untuk menjelajahi keindahan Riau.
                </p>
                <div class="flex flex-wrap gap-4 mb-12 fade-up" style="transition-delay:0.3s">
                    <a href="pemesanan.php" class="btn-primary text-sm py-4 px-8">
                        <i class="fas fa-calendar-check"></i> Pesan Sekarang
                    </a>
                    <a href="armada.php" class="border border-slate-700 text-white rounded-[14px] py-4 px-8 text-sm font-semibold hover:border-slate-500 hover:bg-white/5 transition-all inline-flex items-center gap-2">
                        <i class="fas fa-eye"></i> Lihat Armada
                    </a>
                </div>
                <div class="grid grid-cols-3 gap-6 fade-up" style="transition-delay:0.4s">
                    <div>
                        <div class="text-2xl md:text-3xl font-bold grad-text">50+</div>
                        <div class="text-slate-500 text-xs mt-1 uppercase tracking-wider">Unit Armada</div>
                    </div>
                    <div>
                        <div class="text-2xl md:text-3xl font-bold grad-text">5K+</div>
                        <div class="text-slate-500 text-xs mt-1 uppercase tracking-wider">Pelanggan Puas</div>
                    </div>
                    <div>
                        <div class="text-2xl md:text-3xl font-bold grad-text">8+</div>
                        <div class="text-slate-500 text-xs mt-1 uppercase tracking-wider">Tahun Pengalaman</div>
                    </div>
                </div>
            </div>
            <div class="relative hidden lg:block fade-up" style="transition-delay:0.3s">
                <div class="relative rounded-3xl overflow-hidden shadow-2xl" style="aspect-ratio:4/3;">
                    <img src="1.png" alt="Virgo Rent Car" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-virgo-darker/60 via-transparent to-transparent"></div>
                </div>
                <div class="absolute -bottom-6 -left-6 card-glass p-5 rounded-2xl">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl grad-green-yellow flex items-center justify-center"><i class="fas fa-shield-alt text-white"></i></div>
                        <div>
                            <div class="text-white font-semibold text-sm">Asuransi Lengkap</div>
                            <div class="text-slate-400 text-xs">Setiap perjalanan</div>
                        </div>
                    </div>
                </div>
<div class="absolute -top-4 -right-4 card-glass p-5 rounded-2xl">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl grad-blue-green flex items-center justify-center"><i class="fas fa-star text-white"></i></div>
                        <div>
                            <div class="text-white font-semibold text-sm">Rating 4.9</div>
                            <div class="text-slate-400 text-xs">Ulasan Pelanggan</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="section-divider"></div>

<!-- ==================== KEUNGGULAN ==================== -->
<section class="py-24">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12 md:mb-16 fade-up">
            <h2 class="section-title font-serif text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4">
                Mengapa <span class="grad-text">Virgo Rent Car</span>?
            </h2>
        </div>
        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <div class="card-glass p-6 text-center fade-up">
                <div class="w-14 h-14 mx-auto rounded-2xl grad-blue-green flex items-center justify-center mb-4"><i class="fas fa-shield-alt text-white text-xl"></i></div>
                <h4 class="text-white font-bold text-sm mb-2">Asuransi Lengkap</h4>
                <p class="text-slate-500 text-xs leading-relaxed">Setiap kendaraan dilindungi asuransi all risk untuk ketenangan Anda.</p>
            </div>
            <div class="card-glass p-6 text-center fade-up" style="transition-delay:0.1s">
                <div class="w-14 h-14 mx-auto rounded-2xl grad-green-yellow flex items-center justify-center mb-4"><i class="fas fa-wrench text-white text-xl"></i></div>
                <h4 class="text-white font-bold text-sm mb-2">Armada Terawat</h4>
                <p class="text-slate-500 text-xs leading-relaxed">Perawatan rutin berkala memastikan kendaraan selalu prima dan nyaman.</p>
            </div>
            <div class="card-glass p-6 text-center fade-up" style="transition-delay:0.2s">
                <div class="w-14 h-14 mx-auto rounded-2xl grad-blue-yellow flex items-center justify-center mb-4"><i class="fas fa-headset text-white text-xl"></i></div>
                <h4 class="text-white font-bold text-sm mb-2">Layanan 24/7</h4>
                <p class="text-slate-500 text-xs leading-relaxed">Tim kami siap melayani kapan saja, termasuk darurat di perjalanan.</p>
            </div>
<div class="card-glass p-6 text-center fade-up" style="transition-delay:0.3s">
                <div class="w-14 h-14 mx-auto rounded-2xl grad-main flex items-center justify-center mb-4"><i class="fas fa-tag text-white text-xl"></i></div>
                <h4 class="text-white font-bold text-sm mb-2">Harga Transparan</h4>
                <p class="text-slate-500 text-xs leading-relaxed">Tanpa biaya tersembunyi. Apa yang tertera adalah yang Anda bayar.</p>
            </div>
        </div>
    </div>
</section>

<div class="section-divider"></div>

<!-- ==================== TESTIMONI PELANGGAN ==================== -->
<section class="py-24 relative overflow-hidden" id="testimoni">
    <!-- Dekorasi latar -->
    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute -top-20 -left-20 w-72 h-72 rounded-full bg-blue-600/10 blur-3xl"></div>
        <div class="absolute bottom-0 -right-20 w-80 h-80 rounded-full bg-green-600/10 blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full bg-yellow-500/5 blur-3xl"></div>
    </div>

    <div class="max-w-7xl mx-auto px-6 relative">
<!-- Section Header -->
        <div class="text-center mb-12 md:mb-16 fade-up">
            <div class="badge-yellow inline-flex items-center gap-2 mb-4">
                <i class="fas fa-star"></i> TESTIMONI PELANGGAN
            </div>
            <h2 class="section-title font-serif text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4">
                Apa Kata <span class="grad-text">Mereka</span>?
            </h2>
            <p class="text-slate-400 max-w-xl mx-auto text-sm leading-relaxed">
                Kepercayaan Anda adalah prioritas kami. Berikut pengalaman pelanggan yang telah menggunakan layanan Virgo Rent Car.
            </p>
        </div>

        <?php if ($total_review > 0): ?>
        <!-- ===== Ringkasan Rating + Distribusi ===== -->
        <div class="card-glass p-8 md:p-10 mb-14 fade-up">
            <div class="grid lg:grid-cols-2 gap-8 md:gap-12 items-center">
                <!-- Skor rata-rata -->
                <div class="text-center lg:text-left">
                    <div class="flex flex-col lg:flex-row items-center lg:items-end gap-4">
                        <div class="avg-rating-hero"><?= $rata_rata; ?></div>
                        <div class="pb-1">
                            <div class="flex gap-1 mb-2"><?= renderHomeStars($rata_rata); ?></div>
                            <div class="text-slate-400 text-sm">dari 5.0 bintang</div>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center justify-center lg:justify-start gap-3 text-slate-400 text-sm">
                        <i class="fas fa-check-circle text-green-400"></i>
                        <span>Berisi <strong class="text-white"><?= $total_review; ?></strong> ulasan dari pelanggan terpercaya</span>
                    </div>
                    <div class="mt-6 flex flex-wrap gap-3 justify-center lg:justify-start">
                        <a href="ulasan.php" class="btn-yellow !w-auto">
                            <i class="fas fa-pen"></i> Tulis Ulasan
                        </a>
                        <a href="ulasan.php" class="border border-slate-700 text-white rounded-[14px] px-6 py-3 text-sm font-semibold hover:border-slate-500 hover:bg-white/5 transition-all inline-flex items-center gap-2">
                            <i class="fas fa-comments"></i> Lihat Semua
                        </a>
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
                        <span class="text-xs text-slate-500 w-10"><?= $jumlah; ?></span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>

        <!-- ===== Grid Testimoni ===== -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
<?php foreach ($reviews_data as $review): ?>
            <div class="card-glass testimonial-card p-6 flex flex-col fade-up">
                <?php if (!empty($review['mobil_gambar'])): ?>
                <!-- Foto mobil -->
                <div class="relative h-32 rounded-xl overflow-hidden mb-4">
                    <img src="uploads/<?= htmlspecialchars($review['mobil_gambar']); ?>" alt="<?= htmlspecialchars($review['nama_mobil'] ?? 'Mobil'); ?>" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-virgo-darker/70 to-transparent"></div>
                    <div class="absolute bottom-2 left-3 flex items-center gap-2">
                        <i class="fas fa-car text-yellow-400"></i>
                        <span class="text-white text-xs font-semibold"><?= htmlspecialchars($review['nama_mobil'] ?? 'Rental Mobil'); ?></span>
                    </div>
                </div>
                <?php endif; ?>
                <div class="flex items-center justify-between mb-4">
                    <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                    <div class="flex gap-1"><?= renderHomeStars($review['rating']); ?></div>
                </div>
                <p class="text-slate-400 text-sm leading-relaxed flex-1 mb-6">
                    <?= nl2br(htmlspecialchars(mb_substr($review['komentar'], 0, 180))); ?><?= mb_strlen($review['komentar']) > 180 ? '…' : ''; ?>
                </p>
<div class="flex items-center gap-3 pt-4 border-t border-slate-800/50">
                    <div class="testimonial-avatar grad-blue-green"><?= htmlspecialchars(strtoupper(substr($review['nama_pelanggan'], 0, 1))); ?></div>
                    <div class="min-w-0">
                        <div class="text-white font-semibold text-sm truncate"><?= htmlspecialchars($review['nama_pelanggan']); ?></div>
                        <div class="text-slate-500 text-xs flex items-center gap-1">
                            <i class="fas fa-car"></i><?= htmlspecialchars($review['nama_mobil'] ?? 'Rental Mobil'); ?>
                        </div>
                    </div>
                    <div class="ml-auto text-slate-600 text-[10px] whitespace-nowrap"><?= date('d M Y', strtotime($review['tanggal'])); ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Tombol Lihat Semua -->
        <div class="text-center mt-12 fade-up">
            <a href="ulasan.php" class="btn-primary">
                <i class="fas fa-comments"></i> Lihat Semua Ulasan
            </a>
        </div>

        <?php else: ?>
        <!-- ===== Jika belum ada ulasan ===== -->
        <div class="card-glass p-12 text-center max-w-lg mx-auto fade-up">
            <div class="w-16 h-16 mx-auto rounded-full bg-yellow-500/10 border border-yellow-500/30 flex items-center justify-center mb-4">
                <i class="fas fa-star text-yellow-400 text-2xl"></i>
            </div>
            <h3 class="text-white text-xl font-bold mb-2">Belum Ada Ulasan</h3>
            <p class="text-slate-500 text-sm mb-6">Jadilah pelanggan pertama yang berbagi pengalaman sewa mobil di Virgo Rent Car.</p>
            <a href="ulasan.php" class="btn-yellow">
                <i class="fas fa-pen"></i> Tulis Ulasan Pertama
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<?php require 'footer.php'; ?>
