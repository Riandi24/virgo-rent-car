<?php
require 'header.php';

// =====================================================
// BACKEND: Proses simpan ulasan (Prepared Statements)
// =====================================================
$error_msg = "";
$success_msg = "";

function ensureReviewPhotoTable($koneksi) {
    if (!$koneksi) {
        return false;
    }

    $sql = "CREATE TABLE IF NOT EXISTS tbl_review_foto (
        id_review_foto INT AUTO_INCREMENT PRIMARY KEY,
        id_review INT NOT NULL,
        foto_path VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        KEY idx_review_foto (id_review)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    return mysqli_query($koneksi, $sql);
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

    $uploaded_files = [];
    if (isset($_FILES['foto_review']) && !empty($_FILES['foto_review']['name'])) {
        $target_dir = __DIR__ . '/uploads/reviews';
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $max_files = 5;
        $total_files = count($_FILES['foto_review']['name']);
        if ($total_files > $max_files) {
            $error_msg = "Maksimal 5 foto yang bisa diupload dalam satu ulasan.";
        } else {
            for ($i = 0; $i < $total_files; $i++) {
                if (!isset($_FILES['foto_review']['name'][$i]) || empty($_FILES['foto_review']['name'][$i])) {
                    continue;
                }

                if ($_FILES['foto_review']['error'][$i] === UPLOAD_ERR_NO_FILE) {
                    continue;
                }

                if ($_FILES['foto_review']['error'][$i] !== UPLOAD_ERR_OK) {
                    $error_msg = "Ada foto yang gagal diupload. Silakan coba lagi.";
                    break;
                }

                $file_name = $_FILES['foto_review']['name'][$i];
                $tmp_name = $_FILES['foto_review']['tmp_name'][$i];
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

                if (!in_array($ext, $allowed_ext, true)) {
                    $error_msg = "Format foto tidak valid. Gunakan JPG, PNG, atau WEBP.";
                    break;
                }

                $new_name = 'review_' . time() . '_' . $i . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $destination = $target_dir . DIRECTORY_SEPARATOR . $new_name;

                if (!move_uploaded_file($tmp_name, $destination)) {
                    $error_msg = "Gagal menyimpan foto review ke server.";
                    break;
                }

                $uploaded_files[] = 'reviews/' . $new_name;
            }
        }
    }

    // Validasi input
    if (empty($nama)) {
        $error_msg = "Nama pelanggan wajib diisi.";
    } elseif ($id_kendaraan <= 0) {
        $error_msg = "Silakan pilih mobil yang ingin diulas.";
    } elseif (empty($komentar)) {
        $error_msg = "Komentar / ulasan wajib diisi.";
    } elseif (empty($error_msg)) {
        ensureReviewPhotoTable($koneksi);

        // Gunakan prepared statement agar aman dari SQL Injection
        $stmt = mysqli_prepare($koneksi, "INSERT INTO tbl_reviews (id_kendaraan, nama_pelanggan, rating, komentar) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            // "i" = integer, "s" = string, "i" = integer, "s" = string
            mysqli_stmt_bind_param($stmt, "isis", $id_kendaraan, $nama, $rating, $komentar);
            if (mysqli_stmt_execute($stmt)) {
                $review_id = mysqli_insert_id($koneksi);

                if (!empty($uploaded_files)) {
                    $photo_stmt = mysqli_prepare($koneksi, "INSERT INTO tbl_review_foto (id_review, foto_path) VALUES (?, ?)");
                    if ($photo_stmt) {
                        foreach ($uploaded_files as $foto_path) {
                            mysqli_stmt_bind_param($photo_stmt, "is", $review_id, $foto_path);
                            mysqli_stmt_execute($photo_stmt);
                        }
                        mysqli_stmt_close($photo_stmt);
                    }
                }

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
$review_photos = [];

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

   $photo_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'tbl_review_foto'");
   if ($photo_table && mysqli_num_rows($photo_table) > 0) {
       $photo_query = mysqli_query($koneksi, "SELECT id_review, foto_path FROM tbl_review_foto ORDER BY id_review_foto ASC");
       if ($photo_query) {
           while ($photo = mysqli_fetch_assoc($photo_query)) {
               $review_photos[intval($photo['id_review'])][] = $photo['foto_path'];
           }
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

                <form method="POST" action="" id="reviewForm" enctype="multipart/form-data">
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
                    <div class="mb-5">
                        <label class="form-label">Komentar / Ulasan</label>
                        <textarea name="komentar" class="form-input" rows="4" placeholder="Ceritakan pengalaman Anda menyewa mobil di sini..." required></textarea>
                    </div>

                    <!-- Foto Review -->
                    <div class="mb-6">
                        <label class="form-label">Foto Bukti / Dokumentasi (Opsional)</label>
                        <input type="file" id="fotoReviewInput" name="foto_review[]" class="form-input" accept="image/*" multiple>
                        <div class="text-xs text-slate-500 mt-2" id="fotoReviewInfo">Bisa upload lebih dari satu foto. Maksimal 5 foto (JPG, PNG, WEBP).</div>
                        <div id="fotoReviewPreview" class="mt-3 grid grid-cols-2 md:grid-cols-3 gap-3 hidden"></div>
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
                        <?php $photos = $review_photos[intval($review['id_review'])] ?? []; ?>
                        <div class="card-glass testimonial-card p-6">
                           <div class="review-card-top">
                               <div class="flex items-center gap-3 min-w-0">
                                   <div class="testimonial-avatar grad-blue-green">
                                       <?= strtoupper(substr($review['nama_pelanggan'], 0, 1)); ?>
                                   </div>
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
                               <div class="flex gap-1"><?= renderStars($review['rating']); ?></div>
                           </div>

                           <div class="review-comment-box">
                               <p><?= nl2br(htmlspecialchars($review['komentar'])); ?></p>
                           </div>

                           <?php if (!empty($photos)): ?>
                               <?php $gallery_images = array_map(function ($foto) { return 'uploads/' . $foto; }, $photos); ?>
                               <div class="review-photo-grid relative">
                                   <img src="<?= htmlspecialchars($gallery_images[0]); ?>"
                                        alt="Foto ulasan pelanggan"
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

    const fotoReviewInput = document.getElementById('fotoReviewInput');
    const fotoReviewInfo = document.getElementById('fotoReviewInfo');
    const fotoReviewPreview = document.getElementById('fotoReviewPreview');

    if (fotoReviewInput && fotoReviewInfo && fotoReviewPreview) {
        function renderReviewPhotoPreview(files) {
            const validFiles = Array.from(files || []).slice(0, 5);
            fotoReviewPreview.innerHTML = '';

            if (!validFiles.length) {
                fotoReviewInfo.textContent = 'Bisa upload lebih dari satu foto. Maksimal 5 foto (JPG, PNG, WEBP).';
                fotoReviewPreview.classList.add('hidden');
                return;
            }

            fotoReviewInfo.textContent = `${validFiles.length} foto terpilih.`;
            fotoReviewPreview.classList.remove('hidden');

            validFiles.forEach((file, index) => {
                const wrapper = document.createElement('div');
                wrapper.className = 'relative';

                const reader = new FileReader();
                reader.onload = function (event) {
                    const img = document.createElement('img');
                    img.src = event.target.result;
                    img.alt = file.name;
                    img.className = 'w-full h-24 object-cover rounded-xl border border-slate-700/60';

                    const removeBtn = document.createElement('button');
                    removeBtn.type = 'button';
                    removeBtn.className = 'absolute -top-2 -right-2 w-6 h-6 rounded-full bg-red-500 text-white text-xs flex items-center justify-center hover:bg-red-400';
                    removeBtn.innerHTML = '<i class="fas fa-times"></i>';
                    removeBtn.title = 'Hapus foto';
                    removeBtn.addEventListener('click', () => {
                        const currentFiles = Array.from(fotoReviewInput.files || []);
                        const remainingFiles = currentFiles.filter((_, i) => i !== index);
                        const dataTransfer = new DataTransfer();
                        remainingFiles.forEach(fileItem => dataTransfer.items.add(fileItem));
                        fotoReviewInput.files = dataTransfer.files;
                        renderReviewPhotoPreview(fotoReviewInput.files);
                    });

                    wrapper.appendChild(img);
                    wrapper.appendChild(removeBtn);
                    fotoReviewPreview.appendChild(wrapper);
                };
                reader.readAsDataURL(file);
            });
        }

        fotoReviewInput.addEventListener('change', function () {
            renderReviewPhotoPreview(this.files);
        });
    }
</script>

<?php require 'footer.php'; ?>
