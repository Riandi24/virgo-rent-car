<?php require 'header.php'; ?>

<!-- ==================== WISATA ==================== -->
<section class="py-24 relative" id="wisata">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-16 fade-up">
            <div class="badge-green inline-flex items-center gap-2 mb-4">
                <i class="fas fa-map-marked-alt"></i> PAKET WISATA
            </div>
            <h2 class="section-title font-serif text-4xl md:text-5xl font-bold text-white mb-4">
                Jelajahi <span class="grad-text">Wisata Riau</span>
            </h2>
            <p class="text-slate-400 max-w-xl mx-auto text-sm leading-relaxed">
                Paket wisata lengkap dengan kendaraan dan driver. Tinggal duduk manis, nikmati perjalanan ke destinasi wisata terbaik di Riau.
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-8">
            <?php 
            $query_wisata = "SELECT * FROM tbl_wisata ORDER BY id_wisata ASC";
            $result_wisata = mysqli_query($koneksi, $query_wisata);

            if (mysqli_num_rows($result_wisata) == 0): ?>
                <div class="col-span-full text-center py-16">
                    <i class="fas fa-map-marked-alt text-5xl text-slate-700 mb-4"></i>
                    <p class="text-slate-500">Belum ada paket wisata. Silakan tambahkan melalui panel admin.</p>
                </div>
            <?php endif;
            
            while ($wisata = mysqli_fetch_assoc($result_wisata)) : 
                $harga_format = 'Rp ' . number_format($wisata['harga'] / 1000000, 1, ',', '.') . 'Jt';
                // Tampilkan gambar upload dari admin, fallback ke picsum jika kosong
                $gambar_wisata = (!empty($wisata['gambar']) && file_exists('uploads/'.$wisata['gambar'])) 
                    ? 'uploads/' . $wisata['gambar'] 
                    : 'https://picsum.photos/seed/' . urlencode($wisata['nama_paket']) . '/500/400.jpg';
            ?>
            <div class="card-glass overflow-hidden fade-up tour-card">
                <div class="grid md:grid-cols-2">
                    <div class="relative h-56 md:h-auto overflow-hidden">
                        <img src="<?= $gambar_wisata; ?>" alt="<?= $wisata['nama_paket']; ?>" class="w-full h-full object-cover hover:scale-110 transition-transform duration-700">
                        <div class="absolute top-4 left-4 badge-green"><i class="fas fa-clock mr-1"></i> <?= $wisata['durasi']; ?></div>
                    </div>
                    <div class="p-6 flex flex-col justify-between">
                        <div>
                            <h3 class="text-white font-bold text-lg mb-2"><?= $wisata['nama_paket']; ?></h3>
                            <p class="text-slate-400 text-xs leading-relaxed mb-4">
                                Nikmati perjalanan terbaik bersama Virgo Rent Car. Paket ini sudah termasuk mobil dan driver.
                            </p>
                        </div>
                        <div>
                            <div class="flex items-end justify-between mb-3">
                                <div>
                                    <div class="text-slate-500 text-[10px] uppercase tracking-wider">Paket mulai</div>
                                    <div class="text-2xl font-bold grad-text"><?= $harga_format; ?></div>
                                </div>
                                <div class="text-slate-500 text-[10px]">termasuk mobil & driver</div>
                            </div>
                            <a href="pemesanan.php?wisata=<?= $wisata['id_wisata']; ?>" class="btn-secondary text-xs py-3 px-5 w-full">
                                <i class="fas fa-suitcase-rolling mr-1"></i> Pilih Paket
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>