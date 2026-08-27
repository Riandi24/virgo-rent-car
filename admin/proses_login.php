<?php
// ============================================================
// proses_login.php - Validasi Login Admin (dipanggil via POST)
// Keamanan:
//  - Prepared Statement  -> mencegah SQL Injection
//  - password_verify()   -> mencegah serangan MD5/brute force
//  - session_regenerate_id -> mencegah session fixation
//  - Migrasi otomatis MD5 -> password_hash saat login sukses
// ============================================================

session_start();
require '../koneksi.php';
require 'single_session_helper.php'; // helper single session (anti login ganda)

function ensure_admin_table_and_default_user($koneksi) {
    if (!$koneksi) {
        return false;
    }

    $exists = mysqli_query($koneksi, "SHOW TABLES LIKE 'tbl_admin'");
    if ($exists && mysqli_num_rows($exists) > 0) {
        $probe = mysqli_query($koneksi, "SELECT 1 FROM tbl_admin LIMIT 1");
        if ($probe === false && mysqli_errno($koneksi) == 1932) {
            mysqli_query($koneksi, "DROP TABLE tbl_admin");
        }
    }

    $create_table = mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS tbl_admin (
        id_admin INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        nama_lengkap VARCHAR(150) DEFAULT NULL,
        email VARCHAR(150) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    if (!$create_table) {
        return false;
    }

    $check_admin = mysqli_query($koneksi, "SELECT id_admin FROM tbl_admin WHERE username = 'admin' LIMIT 1");
    if ($check_admin && mysqli_num_rows($check_admin) === 0) {
        $default_hash = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_default = mysqli_query($koneksi, "INSERT INTO tbl_admin (username, password, nama_lengkap) VALUES ('admin', '$default_hash', 'Administrator')");
        return $insert_default;
    }

    return true;
}

ensure_admin_table_and_default_user($koneksi);
ensure_login_session_table($koneksi);

// Hanya terima request POST (tidak bisa diakses langsung via URL)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$error = "";
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Validasi input tidak kosong
if ($username == "" || $password == "") {
    $error = "Username dan password wajib diisi!";
} else {
    // 1) Ambil data admin berdasarkan username (prepared statement)
    $stmt = $koneksi->prepare("SELECT * FROM tbl_admin WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $hash_db = $admin['password'];
        $password_valid = false;

        // 2a) Jika password lama masih MD5 (32 karakter hex)
        //     -> verifikasi dengan MD5 untuk kompatibilitas,
        //        lalu MIGRASI otomatis ke password_hash() yang aman.
        if (strlen($hash_db) === 32 && ctype_xdigit($hash_db) && md5($password) === $hash_db) {
            $password_valid = true;
            $hash_baru = password_hash($password, PASSWORD_DEFAULT);
            $stmt_update = $koneksi->prepare("UPDATE tbl_admin SET password = ? WHERE id_admin = ?");
            $stmt_update->bind_param("si", $hash_baru, $admin['id_admin']);
            $stmt_update->execute();
        }
        // 2b) Jika password sudah password_hash (bcrypt/argon)
        elseif (password_verify($password, $hash_db)) {
            $password_valid = true;
        }

        if ($password_valid) {
            // 3) Regenerasi ID sesi untuk mencegah session fixation
            session_regenerate_id(true);

            $_SESSION['admin_id']       = $admin['id_admin'];
            $_SESSION['admin_username'] = $admin['username'];
            $_SESSION['last_activity']  = time();
            $_SESSION['created']        = time();

            // ============================================
            // 4) SINGLE SESSION (anti login ganda)
            //    Buat token unik & simpan ke database.
            //    Sesi lama admin ini otomatis dinonaktifkan,
            //    sehingga admin yang login sebelumnya
            //    akan force logout saat refresh halaman.
            // ============================================
            $session_token = generate_session_token();
            register_db_session($koneksi, $admin['id_admin'], $session_token);
            $_SESSION['session_token'] = $session_token;

            // Redirect ke dashboard dengan parameter login_success (untuk popup)
            header("Location: index.php?login_success=1");
            exit();
        } else {
            $error = "Username atau password salah!";
        }
    } else {
        $error = "Username atau password salah!";
    }
}

// Jika gagal -> kembali ke login dengan pesan error
header("Location: login.php?error=" . urlencode($error));
exit();
?>

