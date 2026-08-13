<?php require 'header.php'; ?>

<!-- ==================== DRIVER ==================== -->
<section class="py-24 relative" id="driver">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-12 md:mb-16 fade-up">
            <div class="badge-yellow inline-flex items-center gap-2 mb-4">
                <i class="fas fa-id-card-alt"></i> DRIVER KAMI
            </div>
            <h2 class="section-title font-serif text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-4">
                Pilih <span class="grad-text">Driver</span> Andalan
            </h2>
            <p class="text-slate-400 max-w-xl mx-auto text-sm leading-relaxed">
                Driver profesional, berpengalaman, dan menguasai rute di Pekanbaru serta seluruh wilayah Riau. Anda juga bisa sewa tanpa driver (lepas kunci).
            </p>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php 
            $query_driver = "SELECT * FROM tbl_driver ORDER BY id_driver ASC";
            $result_driver = mysqli_query($koneksi, $query_driver);

            if (mysqli_num_rows($result_driver) == 0): ?>
                <div class="col-span-full text-center py-16">
                    <i class="fas fa-id-card-alt text-5xl text-slate-700 mb-4"></i>
                    <p class="text-slate-500">Belum ada data driver. Silakan tambahkan melalui panel admin.</p>
                </div>
            <?php endif;
            
            while ($driver = mysqli_fetch_assoc($result_driver)) : 
                $tarif_format = $driver['tarif_driver'] == 0 ? 'Gratis' : 'Rp ' . number_format($driver['tarif_driver'] / 1000, 0, ',', '.') . 'K';
                $icon_driver = $driver['tarif_driver'] == 0 ? 'fa-key' : 'fa-user-tie';
            ?>
            <div class="card-glass p-6 text-center fade-up driver-card">
                <div class="w-20 h-20 mx-auto rounded-2xl overflow-hidden mb-4 border-2 border-slate-700 flex items-center justify-center bg-slate-800/50">
                    <?php if(!empty($driver['gambar']) && file_exists('uploads/'.$driver['gambar'])): ?>
                        <img src="uploads/<?= $driver['gambar']; ?>" alt="<?= $driver['nama_driver']; ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <i class="fas <?= $icon_driver ?> text-3xl text-slate-500"></i>
                    <?php endif; ?>
                </div>
                <h3 class="text-white font-bold text-base mb-1"><?= $driver['nama_driver']; ?></h3>
                <p class="text-slate-500 text-xs mb-2"><?= $driver['pengalaman']; ?></p>
                <div class="price-tag mb-4">
                    <div class="text-slate-500 text-[10px] uppercase tracking-wider">Tarif</div>
                    <div class="text-xl font-bold <?= $driver['tarif_driver'] == 0 ? 'text-white' : 'grad-text' ?>"><?= $tarif_format; ?></div>
                    <?php if($driver['tarif_driver'] > 0): ?><div class="text-slate-500 text-xs">/ hari</div><?php endif; ?>
                </div>
                <a href="pemesanan.php?driver=<?= $driver['id_driver']; ?>" class="btn-yellow text-xs py-3 px-5 w-full">
                    <i class="fas fa-check-circle mr-1"></i> Pilih
                </a>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<?php require 'footer.php'; ?>