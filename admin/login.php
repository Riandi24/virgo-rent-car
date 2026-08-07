<?php
// ============================================================
// login.php - Halaman FORM LOGIN Admin
// Validasi database dipindah ke proses_login.php
// ============================================================
session_start();

// Jika sudah login, langsung ke index
if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}

// Ambil pesan error dari proses_login.php (lewat URL)
$error = $_GET['error'] ?? '';
$timeout_notice = isset($_GET['timeout']) ? "Sesi Anda telah berakhir karena lama tidak aktif. Silakan login kembali." : "";

// Notifikasi SINGLE SESSION (force logout karena login dari perangkat lain)
$force_logout_notice = "";
if (isset($_GET['force_logout'])) {
    $reason = $_GET['reason'] ?? '';
    if ($reason === 'timeout') {
        $force_logout_notice = "Sesi Anda telah berakhir karena lama tidak aktif. Silakan login kembali.";
    } else {
        $force_logout_notice = "Sesi Anda diakhiri otomatis karena akun ini baru saja login dari perangkat / browser lain.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Virgo Rent Car</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto rounded-2xl grad-blue-green flex items-center justify-center mb-4">
                <i class="fas fa-car text-white text-2xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-white">VIRGO <span class="font-light text-slate-400">Rent Car</span></h1>
            <p class="text-slate-500 mt-2 text-sm">Panel Admin Dashboard</p>
        </div>

        <div class="card-glass p-8">
            <?php if($error): ?>
                <div class="mb-4 p-3 bg-red-900/30 border border-red-500/50 rounded-xl text-red-300 text-sm text-center">
                    <i class="fas fa-exclamation-circle mr-1"></i> <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            <?php if($timeout_notice): ?>
                <div class="mb-4 p-3 bg-yellow-900/30 border border-yellow-500/50 rounded-xl text-yellow-300 text-sm text-center">
                    <i class="fas fa-clock mr-1"></i> <?= $timeout_notice; ?>
                </div>
            <?php endif; ?>
            <?php if($force_logout_notice): ?>
                <div class="mb-4 p-3 bg-red-900/30 border border-red-500/50 rounded-xl text-red-300 text-sm text-center">
                    <i class="fas fa-user-shield mr-1"></i> <?= $force_logout_notice; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="proses_login.php">
                <div class="mb-5">
                    <label class="block text-slate-400 text-sm font-semibold mb-2">Username</label>
                    <div class="relative">
                        <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="text" name="username" class="form-input pl-12" placeholder="Masukkan username" required>
                    </div>
                </div>
                <div class="mb-8">
                    <label class="block text-slate-400 text-sm font-semibold mb-2">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-slate-500"></i>
                        <input type="password" name="password" class="form-input pl-12" placeholder="Masukkan password" required>
                    </div>
                </div>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-sign-in-alt mr-2"></i> Login
                </button>
            </form>

            <!-- Tombol Kembali ke Halaman Utama -->
            <a href="../index.php" class="mt-6 flex items-center justify-center gap-2 text-slate-400 hover:text-white text-sm transition-colors group">
                <i class="fas fa-arrow-left group-hover:-translate-x-1 transition-transform"></i> 
                Kembali ke Halaman Utama
            </a>
        </div>
        <p class="text-center text-slate-600 text-xs mt-6">© 2025 Virgo Rent Car - Universitas Fort De Kock</p>
    </div>
</body>
</html>
