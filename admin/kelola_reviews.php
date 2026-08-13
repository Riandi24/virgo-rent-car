<?php
require 'auth_check.php'; // proteksi sesi + timeout + single session
require_once '../koneksi.php';

// ==================== CEK KOLOM STATUS ====================
// Agar halaman tidak error jika kolom 'status' belum ada
// (misalnya database lama / hosting yang belum import SQL update).
$kolom_status_ada = false;
$cek_tabel_awal = mysqli_query($koneksi, "SHOW TABLES LIKE 'tbl_reviews'");
if ($cek_tabel_awal && mysqli_num_rows($cek_tabel_awal) > 0) {
    $cek_kolom = mysqli_query($koneksi, "SHOW COLUMNS FROM tbl_reviews LIKE 'status'");
    if ($cek_kolom && mysqli_num_rows($cek_kolom) > 0) {
        $kolom_status_ada = true;
    }
}

// ==================== PROSES AKSI ====================

// HAPUS ULASAN
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);

    $stmt = mysqli_prepare($koneksi, "DELETE FROM tbl_reviews WHERE id_review = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_hapus);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: kelola_reviews.php");
    exit();
}

// SETUJUI ULASAN
if (isset($_GET['setujui'])) {
    $id_setujui = intval($_GET['setujui']);
    $stmt = mysqli_prepare($koneksi, "UPDATE tbl_reviews SET status = 'Approved' WHERE id_review = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_setujui);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: kelola_reviews.php");
    exit();
}

// TOLAK ULASAN
if (isset($_GET['tolak'])) {
    $id_tolak = intval($_GET['tolak']);
    $stmt = mysqli_prepare($koneksi, "UPDATE tbl_reviews SET status = 'Rejected' WHERE id_review = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_tolak);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
    header("Location: kelola_reviews.php");
    exit();
}

// ==================== FILTER STATUS ====================
$filter_status = $_GET['status'] ?? 'semua';
$filter_sql = "";
// Filter status hanya dipakai jika kolom status tersedia
if ($filter_status != 'semua' && $kolom_status_ada) {
    $filter_sql = "WHERE r.status = '" . mysqli_real_escape_string($koneksi, $filter_status) . "'";
}

// ==================== AMBIL DATA ====================
$result = false;
$total_review = 0;
$rata_rata = 0;
$total_pending = 0;

$cek_tabel = mysqli_query($koneksi, "SHOW TABLES LIKE 'tbl_reviews'");
if ($cek_tabel && mysqli_num_rows($cek_tabel) > 0) {
    $query = "SELECT r.*, k.nama_mobil
              FROM tbl_reviews r
              LEFT JOIN tbl_kendaraan k ON r.id_kendaraan = k.id_kendaraan
              $filter_sql
              ORDER BY r.tanggal DESC";
    $result = mysqli_query($koneksi, $query);

    // Rata-rata & total (approved only, untuk ringkasan)
    // Hanya filter 'Approved' jika kolom status tersedia
    $query_avg = $kolom_status_ada
        ? "SELECT COUNT(*) AS total_review, COALESCE(AVG(rating), 0) AS rata_rata FROM tbl_reviews r WHERE r.status = 'Approved'"
        : "SELECT COUNT(*) AS total_review, COALESCE(AVG(rating), 0) AS rata_rata FROM tbl_reviews r";
    $result_avg = mysqli_query($koneksi, $query_avg);
    if ($result_avg) {
        $avg_data = mysqli_fetch_assoc($result_avg);
        $total_review = intval($avg_data['total_review']);
        $rata_rata = round(floatval($avg_data['rata_rata']), 1);
    }

    // Jumlah pending (untuk badge) — hanya jika kolom status tersedia
    if ($kolom_status_ada) {
        $q_pending = mysqli_query($koneksi, "SELECT COUNT(*) t FROM tbl_reviews WHERE status = 'Pending'");
        if ($q_pending) $total_pending = (int)mysqli_fetch_assoc($q_pending)['t'];
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

// Helper badge status
function statusBadge($status) {
    switch ($status) {
        case 'Approved': return '<span class="badge-green">Approved</span>';
        case 'Pending':  return '<span class="badge-yellow">Pending</span>';
        case 'Rejected': return '<span class="badge-red">Rejected</span>';
        default:         return '<span class="badge-blue">' . htmlspecialchars($status) . '</span>';
    }
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
                <p class="text-slate-500 text-sm mt-1">Setujui, tolak, atau hapus ulasan pelanggan.</p>
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
                    <div class="text-slate-500 text-xs mt-1 uppercase tracking-wider">Ulasan Disetujui</div>
                </div>
                <div class="hidden md:block h-16 w-px bg-slate-700/50"></div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-yellow-400"><?= $total_pending; ?></div>
                    <div class="text-slate-500 text-xs mt-1 uppercase tracking-wider">Menunggu</div>
                </div>
            </div>
        </div>

        <!-- Filter Status -->
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="kelola_reviews.php?status=semua" class="btn-sm <?= $filter_status == 'semua' ? 'btn-blue' : 'btn-primary' ?>">
                Semua
            </a>
            <a href="kelola_reviews.php?status=Pending" class="btn-sm <?= $filter_status == 'Pending' ? 'btn-yellow' : 'btn-primary' ?>">
                <i class="fas fa-clock mr-1"></i> Pending
            </a>
            <a href="kelola_reviews.php?status=Approved" class="btn-sm <?= $filter_status == 'Approved' ? 'btn-green' : 'btn-primary' ?>">
                <i class="fas fa-check-circle mr-1"></i> Approved
            </a>
            <a href="kelola_reviews.php?status=Rejected" class="btn-sm <?= $filter_status == 'Rejected' ? 'btn-red' : 'btn-primary' ?>">
                <i class="fas fa-times-circle mr-1"></i> Rejected
            </a>
        </div>

        <!-- Tabel Daftar Ulasan -->
        <div class="card-glass p-6 md:p-8 overflow-x-auto">
            <h2 class="text-xl font-bold text-white mb-6"><i class="fas fa-star mr-2 text-yellow-500"></i> Daftar Ulasan Pelanggan</h2>
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="border-b border-slate-700/50 text-slate-400 text-sm">
                        <th class="py-3 px-4">Pelanggan</th>
                        <th class="py-3 px-4">Mobil</th>
                        <th class="py-3 px-4">Rating</th>
                        <th class="py-3 px-4">Komentar</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300 text-sm">
                    <?php if($result && mysqli_num_rows($result) > 0): ?>
                        <?php while($review = mysqli_fetch_assoc($result)): ?>
                        <tr class="border-b border-slate-800/50 hover:bg-slate-800/30 transition-colors align-top">
<td class="py-4 px-4" data-label="Pelanggan">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full grad-blue-green flex items-center justify-center text-white font-bold text-xs"><?= strtoupper(substr($review['nama_pelanggan'], 0, 1)); ?></div>
                                    <div class="font-semibold text-white"><?= htmlspecialchars($review['nama_pelanggan']); ?></div>
                                </div>
                            </td>
                            <td class="py-4 px-4" data-label="Mobil"><?= htmlspecialchars($review['nama_mobil'] ?? 'Umum'); ?></td>
                            <td class="py-4 px-4 whitespace-nowrap" data-label="Rating"><?= renderAdminStars($review['rating']); ?></td>
                            <td class="py-4 px-4 max-w-xs text-slate-400" data-label="Komentar"><?= htmlspecialchars(mb_substr($review['komentar'], 0, 100)); ?><?= mb_strlen($review['komentar']) > 100 ? '…' : ''; ?></td>
                            <td class="py-4 px-4 whitespace-nowrap" data-label="Status"><?= statusBadge($review['status'] ?? 'Pending'); ?></td>
                            <td class="py-4 px-4 whitespace-nowrap" data-label="Tanggal"><?= date('d M Y', strtotime($review['tanggal'])); ?></td>
                            <td class="py-4 px-4 text-center whitespace-nowrap" data-label="Aksi">
                                <div class="flex gap-2 justify-center">
                                    <?php if (($review['status'] ?? '') != 'Approved'): ?>
                                        <a href="kelola_reviews.php?setujui=<?= $review['id_review']; ?>" class="btn-sm btn-green" onclick="return confirm('Setujui ulasan ini?')" title="Setujui">
                                            <i class="fas fa-check"></i>
                                        </a>
                                    <?php endif; ?>
                                    <?php if (($review['status'] ?? '') != 'Rejected'): ?>
                                        <a href="kelola_reviews.php?tolak=<?= $review['id_review']; ?>" class="btn-sm btn-yellow" onclick="return confirm('Tolak ulasan ini?')" title="Tolak">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="kelola_reviews.php?hapus=<?= $review['id_review']; ?>" class="btn-red inline-block" onclick="return confirm('Yakin hapus ulasan ini?')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-10 text-slate-500">
                                <i class="fas fa-star text-4xl mb-3 block"></i>
                                Belum ada ulasan pada kategori ini.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
