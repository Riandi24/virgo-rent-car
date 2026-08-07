<?php
// ============================================================
// notifikasi_count.php
// Endpoint AJAX untuk mengembalikan JUMLAH pesanan yang sedang
// "Menunggu Konfirmasi" (untuk badge angka notifikasi di dashboard).
// Dipanggil oleh JavaScript (setInterval) pada admin/index.php.
// ============================================================

// Proteksi sesi (wajib login admin)
require 'auth_check.php';

// Aktifkan output JSON
header('Content-Type: application/json');

$query = "SELECT COUNT(*) AS total 
          FROM tbl_reservasi 
          WHERE status_reservasi = 'Menunggu Konfirmasi'";

$result = mysqli_query($koneksi, $query);
$jumlah = 0;
if ($result) {
    $row = mysqli_fetch_assoc($result);
    $jumlah = (int)$row['total'];
}

echo json_encode([
    'jumlah' => $jumlah
]);
exit;
?>
