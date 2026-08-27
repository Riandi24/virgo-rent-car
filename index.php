<?php require 'header.php'; 

// =====================================================
// AMBIL DATA ULASAN UNTUK SECTION TESTIMONI DI BERANDA
// =====================================================
$reviews_data = array();
$total_review = 0;
$rata_rata = 0;
$rating_distribution = array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0);
$review_photos = [];

$cek_tabel = mysqli_query($koneksi, "SHOW TABLES LIKE 'tbl_reviews'");
if ($cek_tabel && mysqli_num_rows($cek_tabel) > 0) {
    $query_avg = "SELECT COUNT(*) AS total_review, COALESCE(AVG(rating), 0) AS rata_rata FROM tbl_reviews";
    $result_avg = mysqli_query($koneksi, $query_avg);
    if ($result_avg) {
        $avg_data = mysqli_fetch_assoc($result_avg);
        $total_review = intval($avg_data['total_review']);
        $rata_rata = round(floatval($avg_data['rata_rata']), 1);
    }

    // Distribusi rating (jumlah per bintang)
    $query_dist = "SELECT rating, COUNT(*) AS jumlah FROM tbl_reviews GROUP BY rating";
    $result_dist = mysqli_query($koneksi, $query_dist);
    if ($result_dist) {
        while ($d = mysqli_fetch_assoc($result_dist)) {
            $rating_distribution[intval($d['rating'])] = intval($d['jumlah']);
        }
    }

    // Ambil 6 ulasan terbaru
    $query_reviews = "SELECT r.*, k.nama_mobil
                      FROM tbl_reviews r
                      LEFT JOIN tbl_kendaraan k ON r.id_kendaraan = k.id_kendaraan
                      ORDER BY r.tanggal DESC
                      LIMIT 6";
    $result_reviews = mysqli_query($koneksi, $query_reviews);
    if ($result_reviews) {
        while ($r = mysqli_fetch_assoc($result_reviews)) {
            $reviews_data[] = $r;
        }
    }

    $photo_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'tbl_review_foto'");
    if ($photo_table && mysqli_num_rows($photo_table) > 0) {
        $photo_query = mysqli_query($koneksi, "SELECT id_review, foto_path FROM tbl_review_foto ORDER BY id_review_foto ASC");
        if ($photo_query) {
            while ($photo = mysqli_fetch_assoc($photo_query)) {
                $review_photos[intval($photo['id_review'])][] = $photo['foto_path'];
            }
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
    <div class="max-w-7xl mx-auto px-6 py-20 w-full relative z-10">
        <div class="grid lg:grid-cols-2 gap-16 items-center">
            <div>
                <div class="flex items-center gap-3 mb-8 fade-up">
                    <div class="badge-blue"><i class="fas fa-map-marker-alt mr-1"></i> Pekanbaru, Riau</div>
                    <div class="badge-green">Tersedia 24/7</div>
                </div>
                <h1 class="hero-title font-serif text-5xl md:text-6xl lg:text-7xl font-bold text-white leading-[1.05] tracking-tight mb-6 fade-up" style="transition-delay:0.1s">
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
                            <div class="text-slate-400 text-xs">Google Reviews</div>
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
        <div class="text-center mb-16 fade-up">
            <h2 class="section-title font-serif text-4xl md:text-5xl font-bold text-white mb-4">
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
        <div class="text-center mb-16 fade-up">
            <div class="badge-yellow inline-flex items-center gap-2 mb-4">
                <i class="fas fa-star"></i> TESTIMONI PELANGGAN
            </div>
            <h2 class="section-title font-serif text-4xl md:text-5xl font-bold text-white mb-4">
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
            <?php $photos = $review_photos[intval($review['id_review'])] ?? []; ?>
            <div class="card-glass testimonial-card p-6 flex flex-col fade-up">
                <div class="review-card-top">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="testimonial-avatar grad-blue-green"><?= htmlspecialchars(strtoupper(substr($review['nama_pelanggan'], 0, 1))); ?></div>
                        <div class="min-w-0">
                            <div class="text-white font-semibold text-sm truncate"><?= htmlspecialchars($review['nama_pelanggan']); ?></div>
                            <div class="text-slate-500 text-[11px] uppercase tracking-[0.12em]">
                                <?= htmlspecialchars($review['nama_mobil'] ?? 'Rental Mobil'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="review-card-badge">
                        <i class="fas fa-star text-yellow-300"></i>
                        <?= (int)$review['rating']; ?>/5
                    </div>
                </div>

                <div class="flex items-center justify-between mb-4">
                    <div class="quote-icon"><i class="fas fa-quote-left"></i></div>
                    <div class="flex gap-1"><?= renderHomeStars($review['rating']); ?></div>
                </div>

                <div class="review-comment-box flex-1">
                    <p><?= nl2br(htmlspecialchars(mb_substr($review['komentar'], 0, 180))); ?><?= mb_strlen($review['komentar']) > 180 ? '…' : ''; ?></p>
                </div>

                <?php if (!empty($photos)): ?>
                    <?php $gallery_images = array_map(function ($foto) { return 'uploads/' . $foto; }, $photos); ?>
                    <div class="review-photo-grid relative">
                        <img src="<?= htmlspecialchars($gallery_images[0]); ?>"
                             alt="Foto review pelanggan"
                             class="review-photo-trigger review-main-photo"
                             data-gallery='<?= htmlspecialchars(json_encode($gallery_images), ENT_QUOTES, 'UTF-8'); ?>'
                             data-index="0">
                        <?php if (count($gallery_images) > 1): ?>
                            <span class="review-main-photo-badge">
                                <i class="fas fa-images"></i>
                                +<?= count($gallery_images) - 1 ?>
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="review-footer">
                    <div class="inline-flex items-center gap-2 text-slate-500 text-[10px] uppercase tracking-[0.12em]">
                        <i class="fas fa-calendar-check text-slate-400"></i>
                        <?= date('d M Y', strtotime($review['tanggal'])); ?>
                    </div>
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

<div id="reviewImageModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 p-4">
    <div class="relative max-w-4xl w-full">
        <button type="button" id="closeReviewImageModal" class="absolute -top-4 -right-4 w-10 h-10 rounded-full bg-white text-slate-900 flex items-center justify-center shadow-lg hover:bg-slate-200 z-30">
            <i class="fas fa-times"></i>
        </button>
        <button type="button" id="prevReviewImage" class="absolute left-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-slate-900/80 text-white border border-slate-600 flex items-center justify-center shadow-lg hover:bg-slate-800 z-30">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button type="button" id="nextReviewImage" class="absolute right-4 top-1/2 -translate-y-1/2 w-11 h-11 rounded-full bg-slate-900/80 text-white border border-slate-600 flex items-center justify-center shadow-lg hover:bg-slate-800 z-30">
            <i class="fas fa-chevron-right"></i>
        </button>
        <div class="relative overflow-hidden rounded-2xl border border-slate-700 bg-slate-900">
            <img id="reviewImageModalImg" src="" alt="Foto review lengkap" class="w-full max-h-[80vh] object-contain">
        </div>
        <div id="reviewImageCounter" class="mt-3 text-center text-sm text-slate-300"></div>
    </div>
</div>

<script>
    const reviewPhotoTriggers = document.querySelectorAll('.review-photo-trigger');
    const reviewImageModal = document.getElementById('reviewImageModal');
    const reviewImageModalImg = document.getElementById('reviewImageModalImg');
    const reviewImageCounter = document.getElementById('reviewImageCounter');
    const closeReviewImageModal = document.getElementById('closeReviewImageModal');
    const prevReviewImage = document.getElementById('prevReviewImage');
    const nextReviewImage = document.getElementById('nextReviewImage');
    let reviewGalleryImages = [];
    let reviewGalleryIndex = 0;

    function updateReviewGalleryDisplay() {
        if (!reviewGalleryImages.length || !reviewImageModalImg) {
            return;
        }

        reviewImageModalImg.src = reviewGalleryImages[reviewGalleryIndex];
        if (reviewImageCounter) {
            reviewImageCounter.textContent = `${reviewGalleryIndex + 1} / ${reviewGalleryImages.length}`;
        }
    }

    function setReviewCardPreview(trigger, gallery, index) {
        if (!trigger || !gallery.length) return;
        const safeIndex = Math.min(Math.max(index, 0), gallery.length - 1);
        trigger.dataset.gallery = JSON.stringify(gallery);
        trigger.dataset.index = safeIndex;
        trigger.src = gallery[safeIndex];
    }

    function startReviewCardRotation(trigger) {
        if (!trigger) return;

        let gallery = [];
        try {
            gallery = JSON.parse(trigger.dataset.gallery || '[]');
        } catch (e) {
            gallery = [trigger.getAttribute('src')];
        }

        if (gallery.length < 2) return;

        let currentIndex = Number(trigger.dataset.index || 0);

        const rotate = () => {
            currentIndex = (currentIndex + 1) % gallery.length;
            trigger.dataset.index = currentIndex;
            trigger.style.opacity = '0.78';
            setTimeout(() => {
                trigger.src = gallery[currentIndex];
                trigger.style.opacity = '1';
            }, 140);
        };

        trigger._reviewRotateTimer = setInterval(rotate, 2200);
        trigger.addEventListener('mouseenter', () => clearInterval(trigger._reviewRotateTimer));
        trigger.addEventListener('mouseleave', () => {
            if (gallery.length > 1) {
                trigger._reviewRotateTimer = setInterval(rotate, 2200);
            }
        });
    }

    function openReviewGallery(images, index = 0) {
        if (!images || !images.length || !reviewImageModal || !reviewImageModalImg) {
            return;
        }

        reviewGalleryImages = images;
        reviewGalleryIndex = Math.min(Math.max(index, 0), images.length - 1);
        updateReviewGalleryDisplay();
        reviewImageModal.classList.remove('hidden');
        reviewImageModal.classList.add('flex');
    }

    function closeReviewGallery() {
        if (!reviewImageModal) return;
        reviewImageModal.classList.add('hidden');
        reviewImageModal.classList.remove('flex');
        reviewGalleryImages = [];
        reviewGalleryIndex = 0;
        reviewImageModalImg.src = '';
        if (reviewImageCounter) {
            reviewImageCounter.textContent = '';
        }
    }

    if (reviewPhotoTriggers.length && reviewImageModal && reviewImageModalImg && closeReviewImageModal) {
        reviewPhotoTriggers.forEach(trigger => {
            const gallery = (() => {
                try {
                    return JSON.parse(trigger.dataset.gallery || '[]');
                } catch (e) {
                    return [trigger.dataset.image || trigger.getAttribute('src')];
                }
            })();

            if (gallery.length > 1) {
                let currentIndex = Number(trigger.dataset.index || 0);
                setReviewCardPreview(trigger, gallery, currentIndex);
                startReviewCardRotation(trigger);
            }

            trigger.addEventListener('click', () => {
                let galleryForModal = [];
                try {
                    galleryForModal = JSON.parse(trigger.dataset.gallery || '[]');
                } catch (e) {
                    galleryForModal = [trigger.dataset.image || trigger.getAttribute('src')];
                }
                const index = Number(trigger.dataset.index || 0);
                openReviewGallery(galleryForModal, index);
            });
        });

        closeReviewImageModal.addEventListener('click', closeReviewGallery);
        prevReviewImage?.addEventListener('click', () => {
            if (!reviewGalleryImages.length) return;
            reviewGalleryIndex = (reviewGalleryIndex - 1 + reviewGalleryImages.length) % reviewGalleryImages.length;
            updateReviewGalleryDisplay();
        });
        nextReviewImage?.addEventListener('click', () => {
            if (!reviewGalleryImages.length) return;
            reviewGalleryIndex = (reviewGalleryIndex + 1) % reviewGalleryImages.length;
            updateReviewGalleryDisplay();
        });

        document.addEventListener('keydown', (event) => {
            if (reviewImageModal && !reviewImageModal.classList.contains('hidden')) {
                if (event.key === 'ArrowLeft') {
                    prevReviewImage?.click();
                }
                if (event.key === 'ArrowRight') {
                    nextReviewImage?.click();
                }
                if (event.key === 'Escape') {
                    closeReviewGallery();
                }
            }
        });

        reviewImageModal.addEventListener('click', (event) => {
            if (event.target === reviewImageModal) {
                closeReviewGallery();
            }
        });
    }
</script>

<?php require 'footer.php'; ?>
