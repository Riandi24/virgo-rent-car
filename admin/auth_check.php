<?php
// ============================================================
// auth_check.php - Proteksi Sesi Halaman Admin
// Fungsi: Mencegah akses langsung via URL tanpa login terlebih
// dahulu. Sertakan file ini di setiap halaman admin.
//
// Cara pakai:  require 'auth_check.php';
// ============================================================

session_start();

// ------------------------------------------------------------
// 1) CEK LOGIN - Jika tidak ada sesi admin, tendang ke login
// ------------------------------------------------------------
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// ============================================================
// 1b) SINGLE SESSION CHECK - Deteksi login ganda / concurrent
//     Setiap request divalidasi ke tabel tbl_login_session:
//     - Jika token database berbeda -> ada login baru dari
//       perangkat lain -> FORCE LOGOUT
//     - Jika tidak ada sesi aktif di DB -> FORCE LOGOUT
//     - Jika token kedaluwarsa -> FORCE LOGOUT
// ============================================================
require_once '../koneksi.php';
require_once 'single_session_helper.php';

$db_valid = is_db_session_valid($koneksi, $_SESSION['admin_id'], $_SESSION['session_token'] ?? '');

if (!$db_valid) {
    force_admin_logout('login_baru');
}

// Perbarui last_activity di database agar jejak sesi tetap segar
update_db_last_activity($koneksi, $_SESSION['admin_id'], $_SESSION['session_token']);

// ------------------------------------------------------------
// 2) TIMEOUT SESI - Auto logout setelah 30 menit tidak aktif
//    (mencegah admin lupa logout & akun dibiarkan terbuka)
// ------------------------------------------------------------
$timeout = 1800; // 30 menit dalam detik
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $timeout)) {
    // Nonaktifkan token di database (single session) lalu hancurkan sesi
    if (isset($_SESSION['session_token'])) {
        invalidate_db_session($koneksi, $_SESSION['admin_id'], $_SESSION['session_token']);
    }
    session_unset();
    session_destroy();
    header("Location: login.php?timeout=1");
    exit();
}
$_SESSION['last_activity'] = time(); // perbarui waktu aktif terakhir

// ------------------------------------------------------------
// 3) ANTI SESSION FIXATION - Regenerasi ID sesi tiap 5 menit
//    agar penyerang tidak bisa membajak ID sesi admin
// ------------------------------------------------------------
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 300) { // 5 menit
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}
?>

