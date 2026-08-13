<?php
require 'auth_check.php'; // proteksi sesi + timeout + single session (mencegah bypass URL)
require_once '../koneksi.php';
require_once 'csrf.php';

// Upload validation settings
$MAX_UPLOAD_BYTES = 2 * 1024 * 1024; // 2MB
$ALLOWED_MIMES = ['image/jpeg','image/png','image/webp'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF check
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('CSRF token invalid.');
    }

    $id = intval($_POST['id_driver']);
    $nama_driver = mysqli_real_escape_string($koneksi, $_POST['nama_driver']);
    $pengalaman = mysqli_real_escape_string($koneksi, $_POST['pengalaman']);
    $tarif = intval($_POST['tarif_driver']);
    $gambar_lama = $_POST['gambar_lama'] ?? '';

    $nama_file_gambar = $gambar_lama;
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        // Validate upload
        if ($_FILES['gambar']['size'] > $MAX_UPLOAD_BYTES) {
            die('Ukuran file terlalu besar (maks 2MB).');
        }
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($_FILES['gambar']['tmp_name']);
        if (!in_array($mime, $ALLOWED_MIMES)) {
            die('Tipe file tidak diperbolehkan.');
        }
        $target_dir = "../uploads/";
        $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
        $ext = strtolower($ext);
        // Normalize extension for webp
        if ($mime === 'image/jpeg') $ext = 'jpg';
        if ($mime === 'image/png') $ext = 'png';
        if ($mime === 'image/webp') $ext = 'webp';

        $nama_file_gambar = uniqid('driver_') . '.' . $ext;
        if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $target_dir . $nama_file_gambar)) {
            die('Gagal menyimpan file gambar.');
        }
    }

    if ($id > 0) {
        $stmt = $koneksi->prepare("UPDATE tbl_driver SET nama_driver = ?, pengalaman = ?, tarif_driver = ?, gambar = ? WHERE id_driver = ?");
        $stmt->bind_param("ssisi", $nama_driver, $pengalaman, $tarif, $nama_file_gambar, $id);
        $stmt->execute();
        $stmt->close();
    } else {
        $stmt = $koneksi->prepare("INSERT INTO tbl_driver (nama_driver, pengalaman, tarif_driver, gambar) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $nama_driver, $pengalaman, $tarif, $nama_file_gambar);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: kelola_driver.php");
    exit();
}

if (isset($_GET['hapus'])) {
    $id_hapus = intval($_GET['hapus']);
    $stmt = $koneksi->prepare("SELECT gambar FROM tbl_driver WHERE id_driver = ?");
    $stmt->bind_param("i", $id_hapus);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = $res->fetch_assoc();
    $stmt->close();
    if ($data && $data['gambar'] && file_exists("../uploads/".$data['gambar'])) {
        unlink("../uploads/".$data['gambar']);
    }
    $stmt = $koneksi->prepare("DELETE FROM tbl_driver WHERE id_driver = ?");
    $stmt->bind_param("i", $id_hapus);
    $stmt->execute();
    $stmt->close();
    header("Location: kelola_driver.php");
    exit();
}

 $result_driver = mysqli_query($koneksi, "SELECT * FROM tbl_driver ORDER BY id_driver ASC");
 $data_edit = null;
if (isset($_GET['edit'])) {
    $id_edit = intval($_GET['edit']);
    $res_edit = mysqli_query($koneksi, "SELECT * FROM tbl_driver WHERE id_driver=$id_edit");
    if (mysqli_num_rows($res_edit) > 0) $data_edit = mysqli_fetch_assoc($res_edit);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Driver - Virgo Rent Car</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="p-6 md:p-10">

    <div class="max-w-7xl mx-auto">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white">Kelola <span class="grad-text">Driver</span></h1>
                <p class="text-slate-500 text-sm mt-1">Tambah, ubah, atau hapus data driver.</p>
            </div>
            <a href="index.php" class="btn-primary"><i class="fas fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>

        <div class="card-glass p-6 md:p-8 mb-10">
            <h2 class="text-xl font-bold text-white mb-6">
                <i class="fas fa-plus-circle mr-2 text-green-500"></i> <?= $data_edit ? 'Edit Data Driver' : 'Tambah Driver Baru'; ?>
            </h2>
            <form method="POST" action="" enctype="multipart/form-data" class="grid md:grid-cols-2 gap-5">
                <input type="hidden" name="id_driver" value="<?= $data_edit['id_driver'] ?? 0; ?>">
                <input type="hidden" name="gambar_lama" value="<?= $data_edit['gambar'] ?? ''; ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(generate_csrf_token()); ?>">
                
                <div>
                    <label class="block text-slate-400 text-sm font-semibold mb-2">Nama Driver</label>
                    <input type="text" name="nama_driver" class="form-input" value="<?= $data_edit['nama_driver'] ?? ''; ?>" required>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm font-semibold mb-2">Pengalaman</label>
                    <input type="text" name="pengalaman" class="form-input" placeholder="Contoh: 5 Tahun Pengalaman" value="<?= $data_edit['pengalaman'] ?? ''; ?>" required>
                </div>
                <div>
                    <label class="block text-slate-400 text-sm font-semibold mb-2">Tarif Driver (Rp / hari)</label>
                    <input type="number" name="tarif_driver" class="form-input" value="<?= $data_edit['tarif_driver'] ?? 0; ?>" required>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-slate-400 text-sm font-semibold mb-2">Upload Foto Driver (JPG/PNG)</label>
                    <input type="file" name="gambar" class="form-input" accept="image/*">
                    <?php if(isset($data_edit['gambar']) && $data_edit['gambar']): ?>
                        <div class="mt-3 flex items-center gap-3">
                            <img src="../uploads/<?= $data_edit['gambar']; ?>" class="w-24 h-24 object-cover rounded-xl">
                            <span class="text-xs text-slate-500">Foto saat ini. Biarkan kosong jika tidak ingin ganti.</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="md:col-span-2 flex items-end">
                    <button type="submit" class="btn-primary w-full justify-center py-4">
                        <i class="fas fa-save"></i> <?= $data_edit ? 'Update Data' : 'Simpan Driver'; ?>
                    </button>
                </div>
            </form>
        </div>

        <div class="card-glass p-6 md:p-8 overflow-x-auto">
            <h2 class="text-xl font-bold text-white mb-6"><i class="fas fa-id-card-alt mr-2 text-yellow-500"></i> Daftar Driver</h2>
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="border-b border-slate-700/50 text-slate-400 text-sm">
                        <th class="py-3 px-4">Foto</th>
                        <th class="py-3 px-4">Nama Driver</th>
                        <th class="py-3 px-4">Pengalaman</th>
                        <th class="py-3 px-4">Tarif</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-slate-300 text-sm">
                    <?php if(mysqli_num_rows($result_driver) > 0): ?>
                        <?php while($driver = mysqli_fetch_assoc($result_driver)): ?>
<tr class="border-b border-slate-800/50">
                            <td class="py-4 px-4">
                                <?php if(!empty($driver['gambar']) && file_exists('../uploads/'.$driver['gambar'])): ?>
                                    <img src="../uploads/<?= $driver['gambar']; ?>" class="w-16 h-16 object-cover rounded-xl">
                                <?php else: ?>
                                    <div class="w-16 h-16 bg-slate-800 rounded-xl flex items-center justify-center"><i class="fas fa-user text-slate-600"></i></div>
                                <?php endif; ?>
                            </td>
                            <td class="py-4 px-4 font-semibold text-white" data-label="Nama Driver"><?= $driver['nama_driver']; ?></td>
                            <td class="py-4 px-4" data-label="Pengalaman"><?= $driver['pengalaman']; ?></td>
                            <td class="py-4 px-4" data-label="Tarif">Rp <?= number_format($driver['tarif_driver'], 0, ',', '.'); ?></td>
                            <td class="py-4 px-4 text-center whitespace-nowrap" data-label="Aksi">
                                <a href="kelola_driver.php?edit=<?= $driver['id_driver']; ?>" class="btn-yellow inline-block"><i class="fas fa-edit"></i></a>
                                <a href="kelola_driver.php?hapus=<?= $driver['id_driver']; ?>" class="btn-red inline-block ml-2" onclick="return confirm('Yakin hapus driver ini?')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center py-10 text-slate-500">Belum ada data driver.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>