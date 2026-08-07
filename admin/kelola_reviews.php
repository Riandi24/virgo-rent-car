<?php
require 'auth_check.php'; // proteksi sesi + timeout + single session
require_once '../koneksi.php';

// PROSES HAPUS ULASAN
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);

    // Gunakan prepared statement untuk keamanan
    $stmt = mysqli_prepare($koneksi, "DELETE FROM tbl_reviews WHERE id_review = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_hapus);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: kelola_reviews.php");
    exit();
}

// AMBIL SEMUA ULASAN (JOIN dengan tabel kendaraan)
// Diberi guard jika tabel tbl_reviews belum diimport
$result = false;
$total_review = 0;
$rata_rata = 0;

$cek_tabel = mysqli_query($koneksi, "SHOW TABLES LIKE 'tbl_reviews'");
if ($cek_tabel && mysqli_num_rows($cek_tabel) > 0) {
    $query = "SELECT r.*, k.nama_mobil
              FROM tbl_reviews r
              LEFT JOIN tbl_kendaraan k ON r.id_kendaraan = k.id_kendaraan
              ORDER BY r.tanggal DESC";
    $result = mysqli_query($koneksi, $query);

    // Hitung rata-rata & total
    $query_avg = "SELECT COUNT(*) AS total_review, COALESCE(AVG(rating), 0) AS rata_rata FROM tbl_reviews";
    $result_avg = mysqli_query($koneksi, $query_avg);
    if ($result_avg) {
        $avg_data = mysqli_fetch_assoc($result_avg);
        $total_review = intval($avg_data['total_review']);
        $rata_rata = round(floatval($avg_data['rata_rata']), 1);
    }
}

// Helper menampilkan bintang
function renderAdminStars($rating) {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $rating
            ? '<i class="fas fa-star text-yellow-400"></i>'
            : '<i class="far fa-star text-slate-600"></i>';
    }
    return $html;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Ulasan - Virgo Rent Car</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="p-6 md:p-10">

    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">Kelola <span class="grad-text">Ulasan</span></h1>
                <p class="text-slate-500 text-sm mt-1">Lihat dan hapus ulasan / testimoni pelanggan.</p>
            </div>
            <a href="index.php" class="btn-primary"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>

        <!-- Ringkasan Rating -->
        <div class="card-glass p-6 md:p-8 mb-8">
            <div class="flex flex-col md:flex-row items-center justify-start gap-8">
                <div class="text-center">
                    <div class="text-4xl font-bold grad-text"><?= $rata_rata; ?></div>
                    <div class="mt-1"><?= renderAdminStars(round($rata_rata)); ?></div>
                    <div class="text-slate-500 text-xs mt-1">Rata-rata Rating</div>
                </div>
                <div class="hidden md:block h-16 w-px bg-slate-700/50"></div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-white"><?= $total_review; ?></div>
                    <div class="text-slate-500 text-xs mt-1 uppercase tracking-wider">Total Ulasan</div>
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Ulasan -->
        <div class="card-glass p-6 md:p-8 overflow-x-auto">
            <h2 class="text-xl font-bold text-white mb-6"><i class="fas fa-star mr-2 text-yellow-500"></i> Daftar Ulasan Pelanggan</h2>
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="border-b border-slate-700/50 text-slate-400 text-sm">
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4">Mobil</th>
                        <th class="py-3 px-4">Rating</th>
                        <th class="py-3 px-4">Komentar</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
<tbody class="text-slate-300 text-sm">
                    <?php if($result && mysqli_num_rows($result) > 0): ?>
                        <?php while($review = mysqli_fetch_assoc($result)): ?>
<tr class="border-b border-slate-800/50 hover:bg-slate-800/30 transition-colors align-top">
                            <td class="py-4 px-4 font-semibold text-white" data-label="Pelanggan"><?= htmlspecialchars($review['nama_pelanggan']); ?></td>
                            <td class="py-4 px-4" data-label="Mobil"><?= htmlspecialchars($review['nama_mobil'] ?? 'Umum'); ?></td>
                            <td class="py-4 px-4 whitespace-nowrap" data-label="Rating"><?= renderAdminStars($review['rating']); ?></td>
                            <td class="py-4 px-4 max-w-xs text-slate-400" data-label="Komentar"><?= htmlspecialchars(mb_substr($review['komentar'], 0, 100)); ?><?= mb_strlen($review['komentar']) > 100 ? '…' : ''; ?></td>
                            <td class="py-4 px-4 whitespace-nowrap" data-label="Tanggal"><?= date('d M Y', strtotime($review['tanggal'])); ?></td>
                            <td class="py-4 px-4 text-center whitespace-nowrap" data-label="Aksi">
                                <a href="kelola_reviews.php?hapus=<?= $review['id_review']; ?>" class="btn-red inline-block" onclick="return confirm('Yakin hapus ulasan ini?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-500">
                                <i class="fas fa-star text-4xl mb-3 block"></i>
                                Belum ada ulasan masuk.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
