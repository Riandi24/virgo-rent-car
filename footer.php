<!-- ==================== FOOTER ==================== -->
    <footer class="border-t border-slate-800/50 pt-16 pb-8 mt-24">
        <div class="max-w-7xl mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-12 mb-12">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl grad-blue-green flex items-center justify-center">
                            <i class="fas fa-car text-white text-lg"></i>
                        </div>
                        <div>
                            <span class="text-white font-bold text-lg">VIRGO</span>
                            <span class="text-slate-400 font-light text-lg ml-1">Rent Car</span>
                        </div>
                    </div>
                    <p class="text-slate-500 text-sm leading-relaxed max-w-md mb-6">
                        Layanan rental mobil terpercaya di Pekanbaru, Riau. Menyediakan armada berkualitas, driver profesional, dan paket wisata untuk kebutuhan perjalanan Anda.
                    </p>
                    <div class="flex gap-3">
                        <a href="https://www.instagram.com/riandi2404?igsh=MTZudWRnMHBnN2hqZA%3D%3D&utm_source=qr" class="w-10 h-10 rounded-xl bg-slate-800/50 flex items-center justify-center text-slate-400 hover:text-white hover:bg-blue-600/20 transition-all"><i class="fab fa-instagram"></i></a>
                        <a href="https://www.facebook.com/share/1DRKHEuh8a/?mibextid=wwXIfr" class="w-10 h-10 rounded-xl bg-slate-800/50 flex items-center justify-center text-slate-400 hover:text-white hover:bg-blue-600/20 transition-all"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://wa.me/6285121540024" class="w-10 h-10 rounded-xl bg-slate-800/50 flex items-center justify-center text-slate-400 hover:text-white hover:bg-green-600/20 transition-all"><i class="fab fa-whatsapp"></i></a>
                        <a href="https://www.tiktok.com/@ndy_24_?_r=1&_t=ZS-98ZHQv6v7lV" class="w-10 h-10 rounded-xl bg-slate-800/50 flex items-center justify-center text-slate-400 hover:text-white hover:bg-blue-600/20 transition-all"><i class="fab fa-tiktok"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="text-white font-bold text-sm mb-4">Layanan</h4>
                    <ul class="space-y-3">
                        <li><a href="armada.php" class="text-slate-500 text-sm hover:text-white transition-colors">Sewa Mobil</a></li>
                        <li><a href="driver.php" class="text-slate-500 text-sm hover:text-white transition-colors">Sewa dengan Driver</a></li>
<li><a href="wisata.php" class="text-slate-500 text-sm hover:text-white transition-colors">Paket Wisata</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold text-sm mb-4">Kontak</h4>
                    <ul class="space-y-3">
                        <li class="text-slate-500 text-sm flex items-start gap-2"><i class="fas fa-map-marker-alt mt-1 text-blue-500"></i> Jl. uka. Perumahan Graha Bintang Blok B 06, Pekanbaru, Riau</li>
                        <li class="text-slate-500 text-sm flex items-center gap-2"><i class="fab fa-whatsapp text-green-500"></i> 0851-2154-0024</li>
                        <li class="text-slate-500 text-sm flex items-center gap-2"><i class="fas fa-envelope text-yellow-500"></i> info@virgorentcar.com</li>
                    </ul>
                </div>
            </div>
            <div class="section-divider mb-6"></div>
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-slate-600 text-xs">© 2025 Virgo Rent Car Pekanbaru. All rights reserved.</p>
                <p class="text-slate-700 text-xs">Prototaip Aplikasi — Universitas Fort De Kock Bukittinggi</p>
            </div>
        </div>
    </footer>

    <!-- ==================== JAVASCRIPT DASAR ==================== -->
    <script>
        // Navbar Scroll
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('navbar');
            if (window.scrollY > 50) { nav.classList.add('scrolled'); } else { nav.classList.remove('scrolled'); }
        });

        // Mobile Menu
        document.getElementById('menuToggle')?.addEventListener('click', () => {
            document.getElementById('mobileMenu').classList.add('active');
        });
        document.getElementById('menuClose')?.addEventListener('click', () => {
            document.getElementById('mobileMenu').classList.remove('active');
        });

        // Fade up animation
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) { entry.target.classList.add('visible'); }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
        document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));
    </script>
</body>
</html>
