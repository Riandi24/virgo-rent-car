<?php
// ============================================================
// notifikasi_check.php
// Endpoint AJAX untuk mengecek pesanan baru yang "Menunggu
// Konfirmasi". Dipanggil oleh JavaScript (setInterval) pada
// dashboard admin. Mengembalikan data dalam format JSON.
// ============================================================

// Proteksi sesi (wajib login admin)
require 'auth_check.php';

// Aktifkan output JSON
header('Content-Type: application/json');

// --- Parameter "last_id" (misal: 0) ---
// Optimasi: hanya kirim pesanan dengan ID lebih besar dari
// pesanan terakhir yang sudah diketahui admin.
$last_id = isset($_GET['last_id']) ? intval($_GET['last_id']) : 0;

// --- Ambil pesanan baru dengan status "Menunggu Konfirmasi" ---
$query = "SELECT r.id_reservasi, r.nama_pemesan, r.no_wa, r.tanggal_mulai,
                 r.durasi_hari, r.total_harga, r.status_reservasi,
                 k.nama_mobil, w.nama_paket
          FROM tbl_reservasi r
          LEFT JOIN tbl_kendaraan k ON r.id_kendaraan = k.id_kendaraan
          LEFT JOIN tbl_wisata w ON r.id_wisata = w.id_wisata
          WHERE r.status_reservasi = 'Menunggu Konfirmasi'
          AND r.id_reservasi > $last_id
          ORDER BY r.id_reservasi ASC
          LIMIT 10";

$result = mysqli_query($koneksi, $query);

$pesanan_baru = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Tentukan nama item (mobil atau paket wisata)
        $nama_item = !empty($row['nama_mobil']) ? $row['nama_mobil'] : ($row['nama_paket'] ?? 'Paket Wisata');
        $jenis_item = !empty($row['nama_mobil']) ? 'mobil' : 'paket wisata';

        $pesanan_baru[] = [
            'id'          => $row['id_reservasi'],
            'nama_pemesan'=> $row['nama_pemesan'],
            'no_wa'       => $row['no_wa'],
            'tanggal'     => date('d M Y', strtotime($row['tanggal_mulai'])),
            'durasi'      => $row['durasi_hari'],
            'total'       => number_format($row['total_harga'], 0, ',', '.'),
            'item'        => $nama_item,
            'jenis'       => $jenis_item,
        ];
    }
}

// --- Kirim respon JSON ---
echo json_encode([
    'ada_pesanan_baru' => count($pesanan_baru) > 0,
    'jumlah'           => count($pesanan_baru),
    'pesanan'          => $pesanan_baru,
    'server_time'      => date('Y-m-d H:i:s'),
]);
exit;
?>
