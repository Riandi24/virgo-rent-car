<?php
// ============================================================
// logout.php - Logout Admin (menghancurkan sesi & cookie)
// ============================================================
session_start();

require '../koneksi.php';
require 'single_session_helper.php';

// ------------------------------------------------------------
// SINGLE SESSION: Nonaktifkan token sesi di database terlebih
// dahulu sebelum menghancurkan sesi PHP.
// ------------------------------------------------------------
if (isset($_SESSION['admin_id'], $_SESSION['session_token'])) {
    invalidate_db_session($koneksi, $_SESSION['admin_id'], $_SESSION['session_token']);
}

// Kosongkan semua variabel sesi
$_SESSION = array();

// Hapus cookie sesi jika ada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan sesi
session_destroy();

// Kembali ke halaman login
header("Location: login.php");
exit();
?>
