<?php 
// Memulai session agar pilihan mobil/driver tidak hilang saat pindah halaman
session_start(); 
require 'koneksi.php';

// Fungsi untuk menandai menu navbar yang sedang aktif
function isActive($page) {
    return basename($_SERVER['PHP_SELF']) == $page ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Virgo Rent Car - Pekanbaru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'], serif: ['Playfair Display', 'serif'] },
                    colors: {
                        virgo: {
                            blue: '#0D47A1', blueLight: '#1565C0', blueSoft: '#1976D2',
                            green: '#2E7D32', greenLight: '#43A047', greenSoft: '#66BB6A',
                            yellow: '#F9A825', yellowLight: '#FDD835', yellowSoft: '#FFEE58',
                            dark: '#0a1628', darker: '#060e1a',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="grid-pattern">

    <!-- ==================== NAVBAR ==================== -->
<nav class="navbar fixed top-0 left-0 right-0 z-[9999]" id="navbar">
        <div class="max-w-7xl mx-auto px-4 md:px-6 h-16 md:h-20 flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-2 md:gap-3">
                <div class="w-8 h-8 md:w-10 md:h-10 rounded-xl grad-blue-green flex items-center justify-center">
                    <i class="fas fa-car text-white text-sm md:text-lg"></i>
                </div>
                <div>
                    <span class="text-white font-bold text-base md:text-lg tracking-tight">VIRGO</span>
                    <span class="text-slate-400 font-light text-sm md:text-lg ml-1">Rent Car</span>
                </div>
            </a>
            <div class="hidden md:flex items-center gap-8">
                <a href="index.php" class="nav-link <?= isActive('index.php') ?>">Beranda</a>
                <a href="armada.php" class="nav-link <?= isActive('armada.php') ?>">Armada</a>
                <a href="driver.php" class="nav-link <?= isActive('driver.php') ?>">Driver</a>
<a href="wisata.php" class="nav-link <?= isActive('wisata.php') ?>">Wisata</a>
                <a href="pemesanan.php" class="nav-link <?= isActive('pemesanan.php') ?>">Pemesanan</a>
            </div>
<div class="flex items-center gap-4">
                <!-- Tombol Login Admin (logo saja, tanpa tulisan) -->
                <a href="admin/login.php" title="Login Admin" class="hidden sm:flex w-9 h-9 rounded-lg bg-white/10 border border-white/15 items-center justify-center text-slate-300 hover:text-white hover:border-blue-400/50 hover:bg-blue-500/20 transition-all">
                    <i class="fas fa-user-shield"></i>
                </a>
                <a href="https://wa.me/6285121540024" target="_blank" class="hidden sm:flex items-center gap-2 btn-primary text-xs py-3 px-5">
                    <i class="fab fa-whatsapp text-base"></i> Hubungi Kami
                </a>
                <button class="md:hidden text-white text-2xl" id="menuToggle" aria-label="Menu">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div class="mobile-menu" id="mobileMenu">
        <button class="absolute top-6 right-6 text-white text-3xl" id="menuClose" aria-label="Tutup menu">
            <i class="fas fa-times"></i>
        </button>
        <a href="index.php">Beranda</a>
        <a href="armada.php">Armada</a>
        <a href="driver.php">Driver</a>
<a href="wisata.php">Wisata</a>
        <a href="pemesanan.php">Pemesanan</a>
<a href="admin/login.php" title="Login Admin" class="text-slate-400 mt-4">
            <i class="fas fa-user-shield"></i>
        </a>
        <a href="https://wa.me/6285121540024" class="btn-primary mt-4">
            <i class="fab fa-whatsapp text-lg"></i> Hubungi Kami
        </a>
    </div>
    
<!-- Spacer agar konten tidak ketutup navbar fixed -->
    <div class="h-16 md:h-20"></div>
