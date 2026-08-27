<?php require 'header.php'; ?>

<!-- ==================== ARMADA ==================== -->
<section class="py-24 relative" id="armada">
    <div class="max-w-7xl mx-auto px-6">
        <!-- Section Header -->
        <div class="text-center mb-16 fade-up">
            <div class="badge-blue inline-flex items-center gap-2 mb-4">
                <i class="fas fa-car-side"></i> ARMADA KAMI
            </div>
            <h2 class="section-title font-serif text-4xl md:text-5xl font-bold text-white mb-4">
                Pilih <span class="grad-text">Kendaraan</span> Anda
            </h2>
            <p class="text-slate-400 max-w-xl mx-auto text-sm leading-relaxed">
                Armada terawat dengan berbagai pilihan untuk kebutuhan bisnis, keluarga, maupun wisata Anda di Pekanbaru dan sekitarnya.
            </p>
        </div>

        <!-- Filter Tabs -->
        <div class="flex flex-wrap justify-center gap-3 mb-12 fade-up">
            <button class="tab-btn active-blue" onclick="filterCar('semua', this)">Semua</button>
            <button class="tab-btn" onclick="filterCar('city', this)">City Car</button>
            <button class="tab-btn" onclick="filterCar('mpv', this)">MPV</button>
            <button class="tab-btn" onclick="filterCar('suv', this)">SUV</button>
            <button class="tab-btn" onclick="filterCar('hiace', this)">Hiace</button>
        </div>

        <!-- Car Grid (DARI DATABASE) -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8" id="carGrid">
            <?php 
            $query_mobil = "SELECT * FROM tbl_kendaraan ORDER BY id_kendaraan ASC";
            $result_mobil = mysqli_query($koneksi, $query_mobil);
            
            // Pertahankan parameter wisata saat pindah ke pemesanan
            $link_wisata = isset($_GET['wisata']) ? '&wisata=' . intval($_GET['wisata']) : '';

            // Foto galeri tambahan per mobil (map id_kendaraan => array path)
            $foto_mobil_map = [];
            $res_foto = mysqli_query($koneksi, "SELECT id_kendaraan, foto_path FROM tbl_kendaraan_foto ORDER BY id_kendaraan, id_foto ASC");
            while ($f = mysqli_fetch_assoc($res_foto)) {
                $foto_mobil_map[(int)$f['id_kendaraan']][] = $f['foto_path'];
            }

            // Review approved per mobil (map id_kendaraan => array review)
            $review_mobil_map = [];
            $res_review = mysqli_query($koneksi, "SELECT id_kendaraan, nama_pelanggan, rating, komentar, tanggal FROM tbl_reviews WHERE status='Approved' ORDER BY id_kendaraan, id_review DESC");
            while ($r = mysqli_fetch_assoc($res_review)) {
                $review_mobil_map[(int)$r['id_kendaraan']][] = $r;
            }

            if (mysqli_num_rows($result_mobil) == 0): ?>
                <div class="col-span-full text-center py-16">
                    <i class="fas fa-car-side text-5xl text-slate-700 mb-4"></i>
                    <p class="text-slate-500">Belum ada data armada. Silakan tambahkan melalui panel admin.</p>
                </div>
            <?php endif;

            while ($mobil = mysqli_fetch_assoc($result_mobil)) : 
                $harga_format = 'Rp ' . number_format($mobil['harga_sewa'] / 1000, 0, ',', '.') . 'K';
                $id_m = (int)$mobil['id_kendaraan'];
                $gallery = [];
                if ($mobil['gambar']) $gallery[] = 'uploads/' . $mobil['gambar'];
                foreach ($foto_mobil_map[$id_m] ?? [] as $fp) $gallery[] = 'uploads/' . $fp;
                $gallery_json = htmlspecialchars(json_encode($gallery), ENT_QUOTES, 'UTF-8');
                $reviews = $review_mobil_map[$id_m] ?? [];
                $avg_rating = count($reviews) ? array_sum(array_column($reviews, 'rating')) / count($reviews) : 0;
            ?>
            <div class="card-glass overflow-hidden fade-up car-card" data-category="<?= $mobil['kategori']; ?>">
                <div class="relative h-52 overflow-hidden cursor-pointer" onclick="openCarDetail(this)" 
                     data-gallery="<?= $gallery_json; ?>"
                     data-nama="<?= htmlspecialchars($mobil['nama_mobil'], ENT_QUOTES); ?>"
                     data-kategori="<?= ucfirst($mobil['kategori']); ?>"
                     data-transmisi="<?= $mobil['transmisi']; ?>"
                     data-bahan-bakar="<?= $mobil['bahan_bakar']; ?>"
                     data-kursi="<?= $mobil['kapasitas_kursi']; ?>"
                     data-harga="<?= number_format($mobil['harga_sewa'], 0, ',', '.'); ?>"
                     data-status="<?= $mobil['status']; ?>"
                     data-rating="<?= number_format($avg_rating, 1, '.', ''); ?>"
                     data-jumlah-review="<?= count($reviews); ?>"
                     data-reviews="<?= htmlspecialchars(json_encode(array_slice($reviews, 0, 3)), ENT_QUOTES, 'UTF-8'); ?>"
                     data-link-pesan="<?= 'pemesanan.php?mobil=' . $id_m . $link_wisata; ?>">
                    <img src="<?= $mobil['gambar'] ? 'uploads/'.$mobil['gambar'] : 'https://picsum.photos/seed/default/600/400.jpg'; ?>" alt="<?= $mobil['nama_mobil']; ?>" class="w-full h-full object-cover hover:scale-110 transition-transform duration-700">
                    
                    <?php if ($mobil['status'] == 'Tersedia'): ?>
                        <div class="absolute top-4 left-4 badge-green"><?= $mobil['status']; ?></div>
                    <?php else: ?>
                        <div class="absolute top-4 left-4 badge-yellow"><?= $mobil['status']; ?></div>
                    <?php endif; ?>

                    <?php if (count($gallery) > 1): ?>
                        <div class="absolute bottom-4 right-4 badge-blue"><i class="fas fa-images mr-1"></i> <?= count($gallery); ?> Foto</div>
                    <?php endif; ?>
                    <div class="absolute bottom-4 left-4 badge-blue"><i class="fas fa-search mr-1"></i> Lihat Detail</div>
                </div>
                <div class="p-6">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="text-white font-bold text-lg"><?= $mobil['nama_mobil']; ?></h3>
                            <p class="text-slate-500 text-xs"><?= ucfirst($mobil['kategori']); ?> • <?= $mobil['transmisi']; ?></p>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-2 mb-5">
                        <span class="text-xs text-slate-400 bg-slate-800/50 rounded-lg px-3 py-1"><i class="fas fa-user-friends mr-1"></i> <?= $mobil['kapasitas_kursi']; ?> Kursi</span>
                        <span class="text-xs text-slate-400 bg-slate-800/50 rounded-lg px-3 py-1"><i class="fas fa-cog mr-1"></i> <?= $mobil['transmisi']; ?></span>
                        <span class="text-xs text-slate-400 bg-slate-800/50 rounded-lg px-3 py-1"><i class="fas fa-gas-pump mr-1"></i> <?= $mobil['bahan_bakar']; ?></span>
                    </div>
                    <div class="flex items-end justify-between">
                        <div>
                            <div class="text-slate-500 text-[10px] uppercase tracking-wider">Mulai dari</div>
                            <div class="text-2xl font-bold grad-text"><?= $harga_format; ?></div>
                            <div class="text-slate-500 text-xs">/ hari</div>
                        </div>
                        <?php if ($mobil['status'] == 'Tersedia'): ?>
                            <!-- Mengirim ID mobil ke halaman pemesanan (pertahankan pilihan wisata) -->
                            <a href="pemesanan.php?mobil=<?= $mobil['id_kendaraan']; ?><?= $link_wisata; ?>" class="btn-primary text-xs py-3 px-5">
                                <i class="fas fa-check-circle mr-1"></i> Pilih
                            </a>
                        <?php else: ?>
                            <button class="btn-primary text-xs py-3 px-5 opacity-50 cursor-not-allowed" disabled>
                                <i class="fas fa-times-circle mr-1"></i> Dipesan
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<!-- ==================== MODAL DETAIL MOBIL ==================== -->
<div id="carDetailModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/80 p-4 overflow-y-auto" onclick="if(event.target===this) closeCarDetail()">
    <div class="card-glass max-w-4xl w-full my-8 relative">
        <button onclick="closeCarDetail()" class="absolute top-4 right-4 z-10 w-10 h-10 bg-slate-900/80 hover:bg-red-600 text-white rounded-full flex items-center justify-center transition-colors">
            <i class="fas fa-times"></i>
        </button>

        <div class="grid md:grid-cols-2">
            <!-- Galeri Foto -->
            <div class="p-6">
                <div class="relative rounded-2xl overflow-hidden bg-slate-900 aspect-[4/3]">
                    <img id="carMainPhoto" src="" alt="Foto mobil" class="w-full h-full object-cover">
                    <div id="carPhotoCounter" class="absolute bottom-3 right-3 bg-black/60 text-white text-xs px-3 py-1 rounded-full"></div>
                    <button onclick="carPhotoNav(-1)" class="absolute left-3 top-1/2 -translate-y-1/2 w-9 h-9 bg-black/50 hover:bg-black/80 text-white rounded-full flex items-center justify-center"><i class="fas fa-chevron-left"></i></button>
                    <button onclick="carPhotoNav(1)" class="absolute right-3 top-1/2 -translate-y-1/2 w-9 h-9 bg-black/50 hover:bg-black/80 text-white rounded-full flex items-center justify-center"><i class="fas fa-chevron-right"></i></button>
                </div>
                <div id="carThumbRow" class="flex gap-2 mt-3 overflow-x-auto pb-1"></div>
            </div>

            <!-- Info + Review -->
            <div class="p-6 md:border-l border-slate-700/50 flex flex-col">
                <h3 id="carNama" class="text-white font-bold text-2xl"></h3>
                <p id="carKategori" class="text-slate-500 text-sm mt-1"></p>
                <div id="carRating" class="flex items-center gap-2 mt-3"></div>
                <div id="carSpecs" class="flex flex-wrap gap-2 mt-4"></div>
                <div class="flex items-end justify-between mt-6 mb-6">
                    <div>
                        <div class="text-slate-500 text-[10px] uppercase tracking-wider">Harga sewa</div>
                        <div class="text-2xl font-bold grad-text">Rp <span id="carHarga"></span></div>
                        <div class="text-slate-500 text-xs">/ hari</div>
                    </div>
                    <a id="carBtnPesan" href="#" class="btn-primary text-xs py-3 px-5">
                        <i class="fas fa-check-circle mr-1"></i> Pesan Sekarang
                    </a>
                </div>

                <div class="border-t border-slate-700/50 pt-4">
                    <h4 class="text-white font-semibold text-sm mb-3"><i class="fas fa-star text-yellow-400 mr-1"></i> Review Pelanggan</h4>
                    <div id="carReviews" class="space-y-3 max-h-64 overflow-y-auto pr-1"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let carGallery = [];
    let carPhotoIndex = 0;

    function openCarDetail(el) {
        const d = el.dataset;
        carGallery = JSON.parse(d.gallery || '[]');
        carPhotoIndex = 0;

        document.getElementById('carNama').textContent = d.nama;
        document.getElementById('carKategori').textContent = d.kategori + ' • ' + d.transmisi;

        // Rating rata-rata
        const ratingEl = document.getElementById('carRating');
        const avg = parseFloat(d.rating || 0);
        const jml = parseInt(d.jumlahReview || 0);
        let stars = '';
        for (let i = 1; i <= 5; i++) stars += '<i class="fa-star ' + (i <= Math.round(avg) ? 'fas text-yellow-400' : 'far text-slate-600') + '"></i>';
        ratingEl.innerHTML = jml > 0
            ? stars + '<span class="text-slate-400 text-xs ml-1">' + avg.toFixed(1) + ' (' + jml + ' review)</span>'
            : '<span class="text-slate-500 text-xs">Belum ada review</span>';

        document.getElementById('carSpecs').innerHTML =
            '<span class="text-xs text-slate-400 bg-slate-800/50 rounded-lg px-3 py-1"><i class="fas fa-user-friends mr-1"></i> ' + d.kursi + ' Kursi</span>' +
            '<span class="text-xs text-slate-400 bg-slate-800/50 rounded-lg px-3 py-1"><i class="fas fa-cog mr-1"></i> ' + d.transmisi + '</span>' +
            '<span class="text-xs text-slate-400 bg-slate-800/50 rounded-lg px-3 py-1"><i class="fas fa-gas-pump mr-1"></i> ' + d.bahanBakar + '</span>' +
            '<span class="text-xs text-slate-400 bg-slate-800/50 rounded-lg px-3 py-1"><i class="fas fa-circle mr-1"></i> ' + d.status + '</span>';
        document.getElementById('carHarga').textContent = d.harga;

        // Tombol pesan
        const btn = document.getElementById('carBtnPesan');
        if (d.status === 'Tersedia') {
            btn.href = d.linkPesan;
            btn.className = 'btn-primary text-xs py-3 px-5';
            btn.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Pesan Sekarang';
        } else {
            btn.removeAttribute('href');
            btn.className = 'btn-primary text-xs py-3 px-5 opacity-50 cursor-not-allowed';
            btn.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Sedang Dipesan';
        }

        // Review card
        const reviews = JSON.parse(d.reviews || '[]');
        const revEl = document.getElementById('carReviews');
        revEl.innerHTML = reviews.length === 0
            ? '<p class="text-slate-500 text-xs">Jadilah yang pertama memberi review setelah menyewa mobil ini.</p>'
            : reviews.map(r => {
                let st = '';
                for (let i = 1; i <= 5; i++) st += '<i class="fa-star ' + (i <= r.rating ? 'fas text-yellow-400' : 'far text-slate-600') + ' text-[10px]"></i>';
                return '<div class="bg-slate-800/50 rounded-xl p-3">' +
                    '<div class="flex items-center justify-between mb-1"><span class="text-white text-xs font-semibold">' + escapeHtml(r.nama_pelanggan) + '</span><span class="flex gap-0.5">' + st + '</span></div>' +
                    '<p class="text-slate-400 text-xs leading-relaxed">' + escapeHtml(r.komentar) + '</p>' +
                    '</div>';
            }).join('');

        renderCarPhoto();
        const modal = document.getElementById('carDetailModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function renderCarPhoto() {
        if (carGallery.length === 0) {
            document.getElementById('carMainPhoto').src = 'https://picsum.photos/seed/default/600/400.jpg';
            document.getElementById('carPhotoCounter').textContent = '';
            document.getElementById('carThumbRow').innerHTML = '';
            return;
        }
        document.getElementById('carMainPhoto').src = carGallery[carPhotoIndex];
        document.getElementById('carPhotoCounter').textContent = (carPhotoIndex + 1) + ' / ' + carGallery.length;
        document.getElementById('carThumbRow').innerHTML = carGallery.map((src, i) =>
            '<img src="' + src + '" onclick="carGoTo(' + i + ')" class="car-thumb ' + (i === carPhotoIndex ? 'car-thumb-active' : '') + '">'
        ).join('');
    }

    function carPhotoNav(dir) {
        if (carGallery.length === 0) return;
        carPhotoIndex = (carPhotoIndex + dir + carGallery.length) % carGallery.length;
        renderCarPhoto();
    }
    function carGoTo(i) { carPhotoIndex = i; renderCarPhoto(); }

    function closeCarDetail() {
        const modal = document.getElementById('carDetailModal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';
    }

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    document.addEventListener('keydown', e => {
        if (document.getElementById('carDetailModal').classList.contains('hidden')) return;
        if (e.key === 'Escape') closeCarDetail();
        if (e.key === 'ArrowLeft') carPhotoNav(-1);
        if (e.key === 'ArrowRight') carPhotoNav(1);
    });
</script>

<script>
    // Filter Mobil
    function filterCar(category, btn) {
        const cards = document.querySelectorAll('.car-card');
        cards.forEach(card => {
            if (category === 'semua' || card.dataset.category === category) {
                card.style.display = '';
                setTimeout(() => { card.classList.add('visible'); }, 50);
            } else {
                card.style.display = 'none';
            }
        });
        document.querySelectorAll('.tab-btn').forEach(t => t.className = 'tab-btn');
        btn.classList.add('active-blue');
    }
</script>

<?php require 'footer.php'; ?>