<?php require 'header.php'; ?>

<!-- ==================== ARMADA ==================== -->
<section class="py-24 relative" id="armada">
    <div class="max-w-7xl mx-auto px-6">
        <!-- Section Header -->
        <div class="text-center mb-12 md:mb-16 fade-up">
            <div class="badge-blue inline-flex items-center gap-2 mb-4">
                <i class="fas fa-car-side"></i> ARMADA KAMI
            </div>
            <h2 class="section-title font-serif text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4">
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

            if (mysqli_num_rows($result_mobil) == 0): ?>
                <div class="col-span-full text-center py-16">
                    <i class="fas fa-car-side text-5xl text-slate-700 mb-4"></i>
                    <p class="text-slate-500">Belum ada data armada. Silakan tambahkan melalui panel admin.</p>
                </div>
            <?php endif;

            while ($mobil = mysqli_fetch_assoc($result_mobil)) : 
                $harga_format = 'Rp ' . number_format($mobil['harga_sewa'] / 1000, 0, ',', '.') . 'K';
            ?>
            <div class="card-glass overflow-hidden fade-up car-card" data-category="<?= $mobil['kategori']; ?>">
                <div class="relative h-52 overflow-hidden">
                    <img src="<?= $mobil['gambar'] ? 'uploads/'.$mobil['gambar'] : 'https://picsum.photos/seed/default/600/400.jpg'; ?>" alt="<?= $mobil['nama_mobil']; ?>" class="w-full h-full object-cover hover:scale-110 transition-transform duration-700">
                    
                    <?php if ($mobil['status'] == 'Tersedia'): ?>
                        <div class="absolute top-4 left-4 badge-green"><?= $mobil['status']; ?></div>
                    <?php else: ?>
                        <div class="absolute top-4 left-4 badge-yellow"><?= $mobil['status']; ?></div>
                    <?php endif; ?>
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