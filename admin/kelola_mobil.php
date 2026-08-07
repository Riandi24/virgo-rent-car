<?php
require 'auth_check.php'; // proteksi sesi + timeout + single session (mencegah bypass URL)
require_once '../koneksi.php';

// PROSES TAMBAH / EDIT MOBIL
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = intval($_POST['id_kendaraan']);
    $nama_mobil = mysqli_real_escape_string($koneksi, $_POST['nama_mobil']);
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $transmisi = mysqli_real_escape_string($koneksi, $_POST['transmisi']);
    $bahan_bakar = mysqli_real_escape_string($koneksi, $_POST['bahan_bakar']);
    $kapasitas = intval($_POST['kapasitas_kursi']);
    $harga = intval($_POST['harga_sewa']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $gambar_lama = $_POST['gambar_lama'] ?? '';

    $nama_file_gambar = $gambar_lama;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../uploads/";
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $nama_file_gambar = uniqid('mobil_') . '.' . $ext;
        move_uploaded_file($_FILES['gambar']['tmp_name'], $target_dir . $nama_file_gambar);
    }

    if ($id > 0) {
        $query = "UPDATE tbl_kendaraan SET nama_mobil='$nama_mobil', kategori='$kategori', transmisi='$transmisi', bahan_bakar='$bahan_bakar', kapasitas_kursi=$kapasitas, harga_sewa=$harga, status='$status', gambar='$nama_file_gambar' WHERE id_kendaraan=$id";
    } else {
        $query = "INSERT INTO tbl_kendaraan (nama_mobil, kategori, transmisi, bahan_bakar, kapasitas_kursi, harga_sewa, status, gambar) VALUES ('$nama_mobil', '$kategori', '$transmisi', '$bahan_bakar', $kapasitas, $harga, '$status', '$nama_file_gambar')";
    }
    mysqli_query($koneksi, $query);
    header("Location: kelola_mobil.php");
    exit();
}

// PROSES HAPUS MOBIL
if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    $res = mysqli_query($koneksi, "SELECT gambar FROM tbl_kendaraan WHERE id_kendaraan=$id_hapus");
    $data = mysqli_fetch_assoc($res);
    if ($data && $data['gambar'] && file_exists("../uploads/".$data['gambar'])) {
        unlink("../uploads/".$data['gambar']);
    }
    mysqli_query($koneksi, "DELETE FROM tbl_kendaraan WHERE id_kendaraan=$id_hapus");
    header("Location: kelola_mobil.php");
    exit();
}

 $result_mobil = mysqli_query($koneksi, "SELECT * FROM tbl_kendaraan ORDER BY id_kendaraan DESC");
 $data_edit = null;
if (isset($_GET['edit'])) {
    $id_edit = intval($_GET['edit']);
    $res_edit = mysqli_query($koneksi, "SELECT * FROM tbl_kendaraan WHERE id_kendaraan=$id_edit");
    if (mysqli_num_rows($res_edit) > 0) $data_edit = mysqli_fetch_assoc($res_edit);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Mobil - Virgo Rent Car</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="p-6 md:p-10">

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">Kelola <span class="grad-text">Armada</span></h1>
                <p class="text-slate-500 text-sm mt-1">Tambah, ubah, atau hapus data kendaraan.</p>
            </div>
            <a href="index.php" class="btn-primary"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>

        <!-- Form Tambah/Edit Mobil -->
        <div class="card-glass p-6 md:p-8 mb-10">
            <h2 class="text-xl font-bold text-white mb-6">
                <i class="fas fa-plus-circle mr-2 text-green-500"></i> <?= $data_edit ? 'Edit Data Mobil' : 'Tambah Mobil Baru'; ?>
            </h2>
            <form method="POST" action="" enctype="multipart/form-data" class="grid md:grid-cols-2 gap-5">
                <input type="hidden" name="id_kendaraan" value="<?= $data_edit['id_kendaraan'] ?? 0; ?>">
                <input type="hidden" name="gambar_lama" value="<?= $data_edit['gambar'] ?? ''; ?>">
                
                <div>
                    <label class="block text-slate-400 text-sm font-semibold mb-2">Nama Mobil</label>
                    <input type="text" name="nama_mobil" class="form-input" value="<?= $data_edit['nama_mobil'] ?? ''; ?>" required>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm font-semibold mb-2">Kategori</label>
                    <select name="kategori" class="form-input" required>
                        <option value="city" <?= ($data_edit['kategori'] ?? '') == 'city' ? 'selected' : ''; ?>>City Car</option>
                        <option value="mpv" <?= ($data_edit['kategori'] ?? '') == 'mpv' ? 'selected' : ''; ?>>MPV</option>
                        <option value="suv" <?= ($data_edit['kategori'] ?? '') == 'suv' ? 'selected' : ''; ?>>SUV</option>
                        <option value="hiace" <?= ($data_edit['kategori'] ?? '') == 'hiace' ? 'selected' : ''; ?>>Hiace</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm font-semibold mb-2">Transmisi</label>
                    <select name="transmisi" class="form-input" required>
                        <option value="Manual" <?= ($data_edit['transmisi'] ?? '') == 'Manual' ? 'selected' : ''; ?>>Manual</option>
                        <option value="Matic" <?= ($data_edit['transmisi'] ?? '') == 'Matic' ? 'selected' : ''; ?>>Matic</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm font-semibold mb-2">Bahan Bakar</label>
                    <select name="bahan_bakar" class="form-input" required>
                        <option value="Bensin" <?= ($data_edit['bahan_bakar'] ?? '') == 'Bensin' ? 'selected' : ''; ?>>Bensin</option>
                        <option value="Solar" <?= ($data_edit['bahan_bakar'] ?? '') == 'Solar' ? 'selected' : ''; ?>>Solar</option>
                    </select>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm font-semibold mb-2">Kapasitas Kursi</label>
                    <input type="number" name="kapasitas_kursi" class="form-input" value="<?= $data_edit['kapasitas_kursi'] ?? 4; ?>" required>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm font-semibold mb-2">Harga Sewa (Rp / hari)</label>
                    <input type="number" name="harga_sewa" class="form-input" value="<?= $data_edit['harga_sewa'] ?? 300000; ?>" required>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm font-semibold mb-2">Status</label>
                    <select name="status" class="form-input" required>
                        <option value="Tersedia" <?= ($data_edit['status'] ?? '') == 'Tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                        <option value="Terpesan" <?= ($data_edit['status'] ?? '') == 'Terpesan' ? 'selected' : ''; ?>>Terpesan</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm font-semibold mb-2">Upload Gambar Mobil (JPG/PNG)</label>
                    <input type="file" name="gambar" class="form-input" accept="image/*">
                    <?php if(isset($data_edit['gambar']) && $data_edit['gambar']): ?>
                        <div class="mt-3 flex items-center gap-3">
                            <img src="../uploads/<?= $data_edit['gambar']; ?>" class="w-24 h-24 object-cover rounded-xl">
                            <span class="text-xs text-slate-500">Gambar saat ini. Biarkan kosong jika tidak ingin ganti.</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="md:col-span-2 flex items-end">
                    <button type="submit" class="btn-primary w-full justify-center py-4">
                        <i class="fas fa-save"></i> <?= $data_edit ? 'Update Data' : 'Simpan Mobil'; ?>
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel Daftar Mobil -->
        <div class="card-glass p-6 md:p-8 overflow-x-auto">
            <h2 class="text-xl font-bold text-white mb-6"><i class="fas fa-car-side mr-2 text-blue-500"></i> Daftar Armada</h2>
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="border-b border-slate-700/50 text-slate-400 text-sm">
                        <th class="py-3 px-4">Gambar</th>
                        <th class="py-3 px-4">Nama Mobil</th>
                        <th class="py-3 px-4">Kategori</th>
                        <th class="py-3 px-4">Harga / Hari</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300 text-sm">
                    <?php if(mysqli_num_rows($result_mobil) > 0): ?>
                        <?php while($mobil = mysqli_fetch_assoc($result_mobil)): ?>
<tr class="border-b border-slate-800/50 hover:bg-slate-800/30 transition-colors">
                            <td class="py-4 px-4">
                                <?php if(!empty($mobil['gambar']) && file_exists('../uploads/'.$mobil['gambar'])): ?>
                                    <img src="../uploads/<?= $mobil['gambar']; ?>" class="w-16 h-16 object-cover rounded-xl">
                                <?php else: ?>
                                    <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center"><i class="fas fa-image text-slate-600"></i></div>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-4 font-semibold text-white" data-label="Nama Mobil"><?= $mobil['nama_mobil']; ?></td>
                            <td class="py-4 px-4" data-label="Kategori"><?= ucfirst($mobil['kategori']); ?></td>
                            <td class="py-4 px-4" data-label="Harga / Hari">Rp <?= number_format($mobil['harga_sewa'], 0, ',', '.'); ?></td>
                            <td class="py-4 px-4 text-center" data-label="Status">
                                <?php if($mobil['status'] == 'Tersedia'): ?>
                                    <span class="text-green-400 font-semibold">Tersedia</span>
                                <?php else: ?>
                                    <span class="text-yellow-400 font-semibold">Terpesan</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-4 text-center whitespace-nowrap" data-label="Aksi">
                                <a href="kelola_mobil.php?edit=<?= $mobil['id_kendaraan']; ?>" class="btn-yellow inline-block"><i class="fas fa-edit"></i></a>
                                <a href="kelola_mobil.php?hapus=<?= $mobil['id_kendaraan']; ?>" class="btn-red inline-block ml-2" onclick="return confirm('Yakin hapus mobil ini?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-10 text-slate-500">
                                <i class="fas fa-inbox text-4xl mb-3 block"></i>
                                Belum ada data mobil di database.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>