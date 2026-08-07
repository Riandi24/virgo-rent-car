<?php
require 'auth_check.php'; // proteksi sesi + timeout + single session (mencegah bypass URL)
require_once '../koneksi.php';

// ============================================================
// MODE LAPORAN: semua | bulanan | tahunan
// ============================================================
$mode    = isset($_GET['mode']) ? $_GET['mode'] : 'semua';
$bulan   = isset($_GET['bulan']) ? intval($_GET['bulan']) : intval(date('n'));
$tahun   = isset($_GET['tahun']) ? intval($_GET['tahun']) : intval(date('Y'));

// Validasi mode
if (!in_array($mode, ['semua', 'bulanan', 'tahunan'])) {
    $mode = 'semua';
}
if ($bulan < 1 || $bulan > 12) $bulan = intval(date('n'));
if ($tahun < 2000 || $tahun > 2100) $tahun = intval(date('Y'));

// Daftar tahun yang tersedia (berdasarkan data transaksi)
$tahun_avail = [];
$res_ta = mysqli_query($koneksi, "SELECT DISTINCT YEAR(tanggal_mulai) AS thn FROM tbl_reservasi ORDER BY thn DESC");
if ($res_ta) {
    while ($x = mysqli_fetch_assoc($res_ta)) $tahun_avail[] = $x['thn'];
}
if (empty($tahun_avail)) $tahun_avail[] = intval(date('Y'));

// ---- Bangun WHERE clause berdasarkan mode ----
$where = "";
if ($mode == 'bulanan') {
    $where = " WHERE YEAR(r.tanggal_mulai) = $tahun AND MONTH(r.tanggal_mulai) = $bulan ";
} elseif ($mode == 'tahunan') {
    $where = " WHERE YEAR(r.tanggal_mulai) = $tahun ";
}

// Ambil data transaksi sesuai filter, gabungkan dengan tabel mobil, driver, dan wisata
$query = "SELECT r.*, k.nama_mobil, d.nama_driver, w.nama_paket 
          FROM tbl_reservasi r 
          LEFT JOIN tbl_kendaraan k ON r.id_kendaraan = k.id_kendaraan 
          LEFT JOIN tbl_driver d ON r.id_driver = d.id_driver 
          LEFT JOIN tbl_wisata w ON r.id_wisata = w.id_wisata 
          $where
          ORDER BY r.tanggal_mulai DESC, r.id_reservasi DESC";
$result = mysqli_query($koneksi, $query);

// ---- Ringkasan status per periode ----
$stat_menunggu  = 0;
$stat_konfirm   = 0;
$stat_selesai   = 0;
$stat_batal     = 0;

// ---- Ringkasan per bulan (khusus mode tahunan) ----
$ringkasan_bulan = array_fill(1, 12, ['jumlah' => 0, 'total' => 0]);
$grand_count = 0; // total jumlah transaksi pada periode saat ini

// ------------------------------------------------------------
// KELOMPOKKAN TRANSAKSI:
//  1) Sewa Mobil Pakai Kunci  = ada mobil + ada driver (sopir)
//  2) Sewa Mobil Lepas Kunci  = ada mobil + tanpa driver
//  3) Paket Wisata            = tanpa mobil (murni paket wisata)
// ------------------------------------------------------------
$grup_mobil_kunci = array(); // Sewa Mobil Pakai Kunci (Dengan Driver)
$grup_mobil_lepas = array(); // Sewa Mobil Lepas Kunci (Tanpa Driver)
$grup_wisata      = array(); // Paket Wisata

// Nama-nama bulan untuk tampilan
$daftar_bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];

while ($row = mysqli_fetch_assoc($result)) {
    $punya_mobil  = !empty($row['id_kendaraan']);
    $punya_driver = !empty($row['id_driver']);

    if ($punya_mobil) {
        if ($punya_driver) {
            $grup_mobil_kunci[] = $row;
        } else {
            $grup_mobil_lepas[] = $row;
        }
    } else {
        $grup_wisata[] = $row;
    }

    // Akumulasi ringkasan status
    $st = $row['status_reservasi'];
    if ($st == 'Menunggu Konfirmasi') $stat_menunggu++;
    elseif ($st == 'Dikonfirmasi')    $stat_konfirm++;
    elseif ($st == 'Selesai')         $stat_selesai++;
    elseif ($st == 'Dibatalkan')      $stat_batal++;

// Akumulasi ringkasan per bulan (mode tahunan)
    $bln = intval(date('n', strtotime($row['tanggal_mulai'])));
    if (isset($ringkasan_bulan[$bln])) {
        $ringkasan_bulan[$bln]['jumlah']++;
        $ringkasan_bulan[$bln]['total'] += $row['total_harga'];
    }

    // Hitung total jumlah transaksi pada periode ini
    $grand_count++;
}

// Judul periode dinamis
if ($mode == 'bulanan') {
    $judul_periode = $daftar_bulan[$bulan] . ' ' . $tahun;
} elseif ($mode == 'tahunan') {
    $judul_periode = 'Tahun ' . $tahun;
} else {
    $judul_periode = 'Semua Periode';
}

// Helper: menghitung subtotal sebuah kelompok data
function subtotal_grup($rows) {
    $total = 0;
    foreach ($rows as $r) {
        $total += $r['total_harga'];
    }
    return $total;
}

$grand_total = subtotal_grup($grup_mobil_kunci) + subtotal_grup($grup_mobil_lepas) + subtotal_grup($grup_wisata);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Transaksi - Virgo Rent Car</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="p-6 md:p-10">

    <div class="max-w-7xl mx-auto">
        <!-- Header & Tombol Aksi (Tidak ikut dicetak) -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4 no-print">
            <div>
                <h1 class="text-3xl font-bold text-white">Laporan <span class="grad-text">Transaksi</span></h1>
                <p class="text-slate-500 text-sm mt-1">Daftar lengkap transaksi reservasi kendaraan & paket wisata.</p>
            </div>
            <div class="flex gap-3">
                <button onclick="window.print()" class="btn-primary"><i class="fas fa-print"></i> Cetak Laporan</button>
                <a href="index.php" class="btn-primary" style="background: rgba(100, 116, 139, 0.3);"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>
        </div>

<!-- Kop Laporan (Hanya muncul saat dicetak) -->
        <div class="hidden print:block mb-6 text-center">
            <h1 class="text-2xl font-bold">VIRGO RENT CAR</h1>
            <p>Jl. Jend. Sudirman, Pekanbaru, Riau</p>
            <hr class="my-2 border-black">
            <h2 class="text-xl font-bold mt-4">LAPORAN TRANSAKSI RESERVASI</h2>
            <p class="text-sm mt-1">Periode: <strong><?= $judul_periode; ?></strong></p>
        </div>

        <!-- ============================================== -->
        <!-- MENU TAB: SEMUA / BULANAN / TAHUNAN            -->
        <!-- ============================================== -->
        <div class="flex flex-wrap gap-3 mb-6 no-print">
            <a href="laporan.php?mode=semua" class="btn-sm <?= $mode == 'semua' ? 'btn-blue' : ''; ?>" style="<?= $mode != 'semua' ? 'background: rgba(100,116,139,0.3); color:#cbd5e1; border:1px solid rgba(100,116,139,0.4);' : ''; ?>">
                <i class="fas fa-list"></i> Semua
            </a>
            <a href="laporan.php?mode=bulanan&bulan=<?= $bulan; ?>&tahun=<?= $tahun; ?>" class="btn-sm <?= $mode == 'bulanan' ? 'btn-blue' : ''; ?>" style="<?= $mode != 'bulanan' ? 'background: rgba(100,116,139,0.3); color:#cbd5e1; border:1px solid rgba(100,116,139,0.4);' : ''; ?>">
                <i class="fas fa-calendar-alt"></i> Bulanan
            </a>
            <a href="laporan.php?mode=tahunan&tahun=<?= $tahun; ?>" class="btn-sm <?= $mode == 'tahunan' ? 'btn-blue' : ''; ?>" style="<?= $mode != 'tahunan' ? 'background: rgba(100,116,139,0.3); color:#cbd5e1; border:1px solid rgba(100,116,139,0.4);' : ''; ?>">
                <i class="fas fa-calendar-check"></i> Tahunan
            </a>
        </div>

        <!-- ============================================== -->
        <!-- FORM FILTER PERIODE                            -->
        <!-- ============================================== -->
        <div class="card-glass p-6 mb-8 no-print">
            <h2 class="text-lg font-bold text-white mb-4"><i class="fas fa-filter mr-2 text-blue-500"></i> Filter Periode Laporan</h2>

            <!-- Filter Bulanan -->
            <form method="GET" action="laporan.php" class="grid md:grid-cols-4 gap-4 items-end">
                <input type="hidden" name="mode" value="bulanan">
                <div>
                    <label class="form-label text-xs text-slate-400 mb-1 block">Bulan</label>
                    <select name="bulan" class="form-input">
                        <?php foreach($daftar_bulan as $nb => $nb_label): ?>
                        <option value="<?= $nb; ?>" <?= ($mode=='bulanan' && $bulan==$nb) ? 'selected' : ''; ?>><?= $nb_label; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label text-xs text-slate-400 mb-1 block">Tahun</label>
                    <select name="tahun" class="form-input" id="tahunBulanan">
                        <?php foreach($tahun_avail as $ta): ?>
                        <option value="<?= $ta; ?>" <?= ($tahun==$ta) ? 'selected' : ''; ?>><?= $ta; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn-primary w-full"><i class="fas fa-search"></i> Tampilkan Bulanan</button>
                </div>
                <div class="text-xs text-slate-500 md:col-span-1">
                    Laporan transaksi untuk periode <strong class="text-white"><?= $daftar_bulan[$bulan]; ?> <?= $tahun; ?></strong>
                </div>
            </form>

            <hr class="my-6 border-slate-700/50">

            <!-- Filter Tahunan -->
            <form method="GET" action="laporan.php" class="grid md:grid-cols-4 gap-4 items-end">
                <input type="hidden" name="mode" value="tahunan">
                <div>
                    <label class="form-label text-xs text-slate-400 mb-1 block">Tahun</label>
                    <select name="tahun" class="form-input" id="tahunTahunan">
                        <?php foreach($tahun_avail as $ta): ?>
                        <option value="<?= $ta; ?>" <?= ($tahun==$ta) ? 'selected' : ''; ?>><?= $ta; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn-primary w-full"><i class="fas fa-search"></i> Tampilkan Tahunan</button>
                </div>
                <div class="text-xs text-slate-500 md:col-span-2">
                    Laporan transaksi sepanjang tahun <strong class="text-white"><?= $tahun; ?></strong> beserta rekap pendapatan per bulan.
                </div>
            </form>
        </div>

        <!-- ============================================== -->
        <!-- RINGKASAN STATUS PER PERIODE                   -->
        <!-- ============================================== -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="card-glass p-5">
                <div class="text-slate-400 text-xs uppercase tracking-wider mb-1"><i class="fas fa-clock mr-1 text-yellow-500"></i> Menunggu</div>
                <div class="text-2xl font-bold text-yellow-400"><?= $stat_menunggu; ?></div>
            </div>
            <div class="card-glass p-5">
                <div class="text-slate-400 text-xs uppercase tracking-wider mb-1"><i class="fas fa-check-circle mr-1 text-blue-500"></i> Dikonfirmasi</div>
                <div class="text-2xl font-bold text-blue-400"><?= $stat_konfirm; ?></div>
            </div>
            <div class="card-glass p-5">
                <div class="text-slate-400 text-xs uppercase tracking-wider mb-1"><i class="fas fa-flag-checkered mr-1 text-green-500"></i> Selesai</div>
                <div class="text-2xl font-bold text-green-400"><?= $stat_selesai; ?></div>
            </div>
            <div class="card-glass p-5">
                <div class="text-slate-400 text-xs uppercase tracking-wider mb-1"><i class="fas fa-times-circle mr-1 text-red-500"></i> Dibatalkan</div>
                <div class="text-2xl font-bold text-red-400"><?= $stat_batal; ?></div>
            </div>
        </div>

        <!-- ============================================== -->
        <!-- RINGKASAN PER BULAN (KHUSUS MODE TAHUNAN)      -->
        <!-- ============================================== -->
        <?php if($mode == 'tahunan'): ?>
        <div class="card-glass p-6 md:p-8 overflow-x-auto mb-8">
            <h2 class="text-xl font-bold text-white mb-6"><i class="fas fa-chart-bar mr-2 text-cyan-500"></i> Rekap Pendapatan Per Bulan - Tahun <?= $tahun; ?></h2>
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead>
                    <tr class="border-b border-slate-700/50 text-slate-400 text-sm">
                        <th class="py-3 px-4">Bulan</th>
                        <th class="py-3 px-4 text-center">Jumlah Transaksi</th>
                        <th class="py-3 px-4 text-right">Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300 text-sm">
<?php $rekap_total = 0; foreach($ringkasan_bulan as $nb => $rb): ?>
                    <tr class="border-b border-slate-800/50">
                        <td class="py-3 px-4 text-white font-semibold" data-label="Bulan"><?= $daftar_bulan[$nb]; ?></td>
                        <td class="py-3 px-4 text-center" data-label="Jumlah Transaksi"><?= $rb['jumlah']; ?></td>
                        <td class="py-3 px-4 text-right font-semibold" data-label="Total Pendapatan">Rp <?= number_format($rb['total'], 0, ',', '.'); ?></td>
                    </tr>
                    <?php $rekap_total += $rb['total']; endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-700/50">
                        <td class="py-4 px-4 font-bold text-white">TOTAL TAHUNAN</td>
                        <td class="py-4 px-4 text-center font-bold text-white"><?= $grand_count; ?></td>
                        <td class="py-4 px-4 text-right font-bold text-lg text-purple-400">Rp <?= number_format($rekap_total, 0, ',', '.'); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>

        <!-- ============================================== -->
        <!-- 1) LAPORAN SEWA MOBIL PAKAI KUNCI            -->
        <!-- ============================================== -->
        <div class="card-glass p-6 md:p-8 overflow-x-auto mb-8">
            <h2 class="text-xl font-bold text-white mb-6">
                <i class="fas fa-user-tie mr-2 text-blue-500"></i> Sewa Mobil Pakai Kunci (Dengan Driver)
                <span class="text-sm font-normal text-slate-500 ml-2">(<?= count($grup_mobil_kunci); ?> transaksi)</span>
            </h2>
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="border-b border-slate-700/50 text-slate-400 text-sm">
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3 px-4">Pemesan</th>
                        <th class="py-3 px-4">Kendaraan</th>
                        <th class="py-3 px-4">Driver</th>
                        <th class="py-3 px-4">Tgl Mulai</th>
                        <th class="py-3 px-4">Durasi</th>
                        <th class="py-3 px-4 text-right">Total</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300 text-sm">
                    <?php foreach($grup_mobil_kunci as $row):
                        $subtotal_kunci += $row['total_harga'];
                        $status = $row['status_reservasi'];
                        $badge_class = 'badge-yellow';
                        if($status == 'Dikonfirmasi') $badge_class = 'badge-blue';
                        if($status == 'Selesai') $badge_class = 'badge-green';
                        if($status == 'Dibatalkan') $badge_class = 'badge-red';
                        $nama_item = $row['nama_mobil'];
                        if (!empty($row['nama_paket'])) $nama_item .= " + " . $row['nama_paket'];
                    ?>
<tr class="border-b border-slate-800/50">
                        <td class="py-4 px-4" data-label="ID">#<?= $row['id_reservasi']; ?></td>
                        <td class="py-4 px-4" data-label="Pemesan">
                            <div class="font-semibold text-white"><?= $row['nama_pemesan']; ?></div>
                            <div class="text-xs text-slate-500"><?= $row['no_wa']; ?></div>
                        </td>
                        <td class="py-4 px-4 text-white" data-label="Kendaraan">
                            <div class="font-semibold text-white"><?= $nama_item; ?></div>
                            <div class="text-xs text-slate-500"><i class="fas fa-car mr-1"></i> Mobil</div>
                        </td>
                        <td class="py-4 px-4 text-xs text-slate-400" data-label="Driver"><?= $row['nama_driver'] ? 'Driver: ' . $row['nama_driver'] : '-'; ?></td>
                        <td class="py-4 px-4 text-white" data-label="Tgl Mulai"><?= date('d M Y', strtotime($row['tanggal_mulai'])); ?></td>
                        <td class="py-4 px-4 text-white" data-label="Durasi"><?= $row['durasi_hari']; ?> Hari</td>
                        <td class="py-4 px-4 text-right font-semibold text-white" data-label="Total">Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></td>
                        <td class="py-4 px-4 text-center" data-label="Status"><span class="<?= $badge_class; ?>"><?= $status; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(count($grup_mobil_kunci) == 0): ?>
                    <tr><td colspan="8" class="text-center py-10 text-slate-500">Belum ada transaksi sewa mobil pakai kunci.</td></tr>
                    <?php endif; ?>
                </tbody>
                <?php if(count($grup_mobil_kunci) > 0): ?>
                <tfoot>
                    <tr class="border-t-2 border-slate-700/50">
                        <td colspan="6" class="py-4 px-4 text-right font-bold text-white">SUBTOTAL SEWA MOBIL PAKAI KUNCI:</td>
                        <td colspan="2" class="py-4 px-4 text-left font-bold text-lg text-blue-400">
                            Rp <?= number_format(subtotal_grup($grup_mobil_kunci), 0, ',', '.'); ?>
                        </td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>

        <!-- ============================================== -->
        <!-- 2) LAPORAN SEWA MOBIL LEPAS KUNCI             -->
        <!-- ============================================== -->
        <div class="card-glass p-6 md:p-8 overflow-x-auto mb-8">
            <h2 class="text-xl font-bold text-white mb-6">
                <i class="fas fa-car-side mr-2 text-green-500"></i> Sewa Mobil Lepas Kunci (Tanpa Driver)
                <span class="text-sm font-normal text-slate-500 ml-2">(<?= count($grup_mobil_lepas); ?> transaksi)</span>
            </h2>
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="border-b border-slate-700/50 text-slate-400 text-sm">
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3 px-4">Pemesan</th>
                        <th class="py-3 px-4">Kendaraan</th>
                        <th class="py-3 px-4">Tgl Mulai</th>
                        <th class="py-3 px-4">Durasi</th>
                        <th class="py-3 px-4 text-right">Total</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300 text-sm">
                    <?php foreach($grup_mobil_lepas as $row):
                        $status = $row['status_reservasi'];
                        $badge_class = 'badge-yellow';
                        if($status == 'Dikonfirmasi') $badge_class = 'badge-blue';
                        if($status == 'Selesai') $badge_class = 'badge-green';
                        if($status == 'Dibatalkan') $badge_class = 'badge-red';
                        $nama_item = $row['nama_mobil'];
                        if (!empty($row['nama_paket'])) $nama_item .= " + " . $row['nama_paket'];
                    ?>
<tr class="border-b border-slate-800/50">
                        <td class="py-4 px-4" data-label="ID">#<?= $row['id_reservasi']; ?></td>
                        <td class="py-4 px-4" data-label="Pemesan">
                            <div class="font-semibold text-white"><?= $row['nama_pemesan']; ?></div>
                            <div class="text-xs text-slate-500"><?= $row['no_wa']; ?></div>
                        </td>
                        <td class="py-4 px-4 text-white" data-label="Kendaraan">
                            <div class="font-semibold text-white"><?= $nama_item; ?></div>
                            <div class="text-xs text-slate-500"><i class="fas fa-car mr-1"></i> Mobil</div>
                        </td>
                        <td class="py-4 px-4 text-white" data-label="Tgl Mulai"><?= date('d M Y', strtotime($row['tanggal_mulai'])); ?></td>
                        <td class="py-4 px-4 text-white" data-label="Durasi"><?= $row['durasi_hari']; ?> Hari</td>
                        <td class="py-4 px-4 text-right font-semibold text-white" data-label="Total">Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></td>
                        <td class="py-4 px-4 text-center" data-label="Status"><span class="<?= $badge_class; ?>"><?= $status; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(count($grup_mobil_lepas) == 0): ?>
                    <tr><td colspan="7" class="text-center py-10 text-slate-500">Belum ada transaksi sewa mobil lepas kunci.</td></tr>
                    <?php endif; ?>
                </tbody>
                <?php if(count($grup_mobil_lepas) > 0): ?>
                <tfoot>
                    <tr class="border-t-2 border-slate-700/50">
                        <td colspan="5" class="py-4 px-4 text-right font-bold text-white">SUBTOTAL SEWA MOBIL LEPAS KUNCI:</td>
                        <td colspan="2" class="py-4 px-4 text-left font-bold text-lg text-green-400">
                            Rp <?= number_format(subtotal_grup($grup_mobil_lepas), 0, ',', '.'); ?>
                        </td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>

        <!-- ============================================== -->
        <!-- 3) LAPORAN PAKET WISATA                        -->
        <!-- ============================================== -->
        <div class="card-glass p-6 md:p-8 overflow-x-auto mb-8">
            <h2 class="text-xl font-bold text-white mb-6">
                <i class="fas fa-map-marked-alt mr-2 text-yellow-500"></i> Paket Wisata
                <span class="text-sm font-normal text-slate-500 ml-2">(<?= count($grup_wisata); ?> transaksi)</span>
            </h2>
            <table class="w-full text-left border-collapse min-w-[900px]">
                <thead>
                    <tr class="border-b border-slate-700/50 text-slate-400 text-sm">
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3 px-4">Pemesan</th>
                        <th class="py-3 px-4">Paket Wisata</th>
                        <th class="py-3 px-4">Tgl Mulai</th>
                        <th class="py-3 px-4">Durasi</th>
                        <th class="py-3 px-4 text-right">Total</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300 text-sm">
                    <?php foreach($grup_wisata as $row):
                        $status = $row['status_reservasi'];
                        $badge_class = 'badge-yellow';
                        if($status == 'Dikonfirmasi') $badge_class = 'badge-blue';
                        if($status == 'Selesai') $badge_class = 'badge-green';
                        if($status == 'Dibatalkan') $badge_class = 'badge-red';
                    ?>
<tr class="border-b border-slate-800/50">
                        <td class="py-4 px-4" data-label="ID">#<?= $row['id_reservasi']; ?></td>
                        <td class="py-4 px-4" data-label="Pemesan">
                            <div class="font-semibold text-white"><?= $row['nama_pemesan']; ?></div>
                            <div class="text-xs text-slate-500"><?= $row['no_wa']; ?></div>
                        </td>
                        <td class="py-4 px-4 text-white" data-label="Paket Wisata">
                            <div class="font-semibold text-white"><?= $row['nama_paket'] ?? 'Paket Wisata'; ?></div>
                            <div class="text-xs text-slate-500"><i class="fas fa-map-marked-alt mr-1"></i> Paket Wisata</div>
                        </td>
                        <td class="py-4 px-4 text-white" data-label="Tgl Mulai"><?= date('d M Y', strtotime($row['tanggal_mulai'])); ?></td>
                        <td class="py-4 px-4 text-white" data-label="Durasi"><?= $row['durasi_hari']; ?> Hari</td>
                        <td class="py-4 px-4 text-right font-semibold text-white" data-label="Total">Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></td>
                        <td class="py-4 px-4 text-center" data-label="Status"><span class="<?= $badge_class; ?>"><?= $status; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if(count($grup_wisata) == 0): ?>
                    <tr><td colspan="7" class="text-center py-10 text-slate-500">Belum ada transaksi paket wisata.</td></tr>
                    <?php endif; ?>
                </tbody>
                <?php if(count($grup_wisata) > 0): ?>
                <tfoot>
                    <tr class="border-t-2 border-slate-700/50">
                        <td colspan="5" class="py-4 px-4 text-right font-bold text-white">SUBTOTAL PAKET WISATA:</td>
                        <td colspan="2" class="py-4 px-4 text-left font-bold text-lg text-yellow-400">
                            Rp <?= number_format(subtotal_grup($grup_wisata), 0, ',', '.'); ?>
                        </td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>

        <!-- ============================================== -->
        <!-- RINGKASAN TOTAL                                -->
        <!-- ============================================== -->
        <div class="card-glass p-6 md:p-8 mb-8">
            <h2 class="text-xl font-bold text-white mb-6"><i class="fas fa-chart-pie mr-2 text-purple-500"></i> Ringkasan Pendapatan</h2>
            <div class="grid md:grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-5">
                    <div class="text-slate-400 text-xs uppercase tracking-wider mb-1"><i class="fas fa-user-tie mr-1"></i> Sewa Pakai Kunci</div>
                    <div class="text-2xl font-bold text-blue-400">Rp <?= number_format(subtotal_grup($grup_mobil_kunci), 0, ',', '.'); ?></div>
                    <div class="text-xs text-slate-500"><?= count($grup_mobil_kunci); ?> transaksi</div>
                </div>
                <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-5">
                    <div class="text-slate-400 text-xs uppercase tracking-wider mb-1"><i class="fas fa-car-side mr-1"></i> Sewa Lepas Kunci</div>
                    <div class="text-2xl font-bold text-green-400">Rp <?= number_format(subtotal_grup($grup_mobil_lepas), 0, ',', '.'); ?></div>
                    <div class="text-xs text-slate-500"><?= count($grup_mobil_lepas); ?> transaksi</div>
                </div>
                <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-5">
                    <div class="text-slate-400 text-xs uppercase tracking-wider mb-1"><i class="fas fa-map-marked-alt mr-1"></i> Paket Wisata</div>
                    <div class="text-2xl font-bold text-yellow-400">Rp <?= number_format(subtotal_grup($grup_wisata), 0, ',', '.'); ?></div>
                    <div class="text-xs text-slate-500"><?= count($grup_wisata); ?> transaksi</div>
                </div>
            </div>
            <div class="flex justify-between items-center p-5 rounded-xl bg-gradient-to-r from-blue-500/10 via-green-500/10 to-yellow-500/10 border border-slate-700/30">
                <span class="font-bold text-white text-lg">TOTAL KESELURUHAN PENDAPATAN:</span>
                <span class="font-bold text-2xl grad-text">Rp <?= number_format($grand_total, 0, ',', '.'); ?></span>
            </div>
        </div>
    </div>

</body>
</html>
