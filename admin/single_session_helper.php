<?php
// ============================================================
// single_session_helper.php
// Helper untuk SINGLE SESSION admin (anti login ganda/concurrent)
//
// Prinsip:
//  - Setiap login menyimpan token unik di tabel tbl_login_session.
//  - Hanya sesi dengan token TERBARU (is_active = 1) yang valid.
//  - Saat Admin 2 login dengan akun yang sama, token lama Admin 1
//    di-nonaktifkan (is_active = 0) -> Admin 1 force logout saat
//    refresh halaman.
// ============================================================

// Masa berlaku token sesi (8 jam dalam detik)
define('TOKEN_EXPIRY', 8 * 3600);

// Ruang lingkup single session:
//   'account' -> 1 sesi per akun admin (semua hak akses dianggap sama)
//   'role'    -> 1 sesi per role/hak akses (belum diaktifkan, lihat docs)
define('SINGLE_SESSION_SCOPE', 'account');

function ensure_login_session_table($koneksi) {
    if (!$koneksi) {
        return false;
    }

    $exists = mysqli_query($koneksi, "SHOW TABLES LIKE 'tbl_login_session'");
    if ($exists && mysqli_num_rows($exists) > 0) {
        $probe = mysqli_query($koneksi, "SELECT 1 FROM tbl_login_session LIMIT 1");
        if ($probe === false && mysqli_errno($koneksi) == 1932) {
            mysqli_query($koneksi, "DROP TABLE tbl_login_session");
        }
    }

    $sql = "CREATE TABLE IF NOT EXISTS tbl_login_session (
        id_session INT AUTO_INCREMENT PRIMARY KEY,
        id_admin INT NOT NULL,
        session_token VARCHAR(255) NOT NULL,
        ip_address VARCHAR(45) DEFAULT NULL,
        user_agent VARCHAR(255) DEFAULT NULL,
        login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at DATETIME NOT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        KEY idx_admin_session (id_admin, is_active),
        KEY idx_token (session_token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

    return mysqli_query($koneksi, $sql);
}

/**
 * Membuat token sesi acak yang aman (32 byte hex = 64 karakter)
 */
function generate_session_token() {
    return bin2hex(random_bytes(32));
}

/**
 * Menonaktifkan semua sesi lama milik admin (selain sesi baru).
 */
function invalidate_old_login_sessions($koneksi, $id_admin, $except_token = '') {
    ensure_login_session_table($koneksi);

    $stmt = $koneksi->prepare(
        "UPDATE tbl_login_session
         SET is_active = 0
         WHERE id_admin = ? AND is_active = 1 AND session_token <> ?"
    );
    $stmt->bind_param("is", $id_admin, $except_token);
    $stmt->execute();
}

/**
 * Menyimpan sesi login baru ke database.
 */
function register_db_session($koneksi, $id_admin, $token, $scope = SINGLE_SESSION_SCOPE) {
    ensure_login_session_table($koneksi);

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $ua = substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255);
    $expires = date('Y-m-d H:i:s', time() + TOKEN_EXPIRY);

    // Nonaktifkan sesi lama terlebih dahulu sebelum insert sesi baru
    invalidate_old_login_sessions($koneksi, $id_admin);

    $stmt = $koneksi->prepare(
        "INSERT INTO tbl_login_session
            (id_admin, session_token, ip_address, user_agent, login_time, last_activity, expires_at, is_active)
         VALUES (?, ?, ?, ?, NOW(), NOW(), ?, 1)"
    );
    $stmt->bind_param("issss", $id_admin, $token, $ip, $ua, $expires);
    $stmt->execute();
}

/**
 * Mengecek apakah token sesi saat ini masih valid (terbaru & belum kedaluwarsa).
 * Membandingkan dengan hash_equals() agar aman dari timing attack.
 */
function is_db_session_valid($koneksi, $id_admin, $token) {
    ensure_login_session_table($koneksi);

    $stmt = $koneksi->prepare(
        "SELECT session_token, expires_at
         FROM tbl_login_session
         WHERE id_admin = ? AND is_active = 1
         ORDER BY id_session DESC
         LIMIT 1"
    );
    $stmt->bind_param("i", $id_admin);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return false; // tidak ada sesi aktif -> dianggap login ganda / sudah logout
    }

    $row = $result->fetch_assoc();

    // Bandingkan token dengan aman (timing-safe)
    if (!hash_equals($row['session_token'], $token)) {
        return false; // token tidak cocok -> ada login baru dari perangkat lain
    }

    // Cek kedaluwarsa
    if (strtotime($row['expires_at']) < time()) {
        return false;
    }

    return true;
}

/**
 * Memperbarui waktu last_activity sesi di database.
 */
function update_db_last_activity($koneksi, $id_admin, $token) {
    ensure_login_session_table($koneksi);

    $stmt = $koneksi->prepare(
        "UPDATE tbl_login_session
         SET last_activity = NOW()
         WHERE id_admin = ? AND session_token = ? AND is_active = 1"
    );
    $stmt->bind_param("is", $id_admin, $token);
    $stmt->execute();
}

/**
 * Menonaktifkan sesi saat ini (dipakai saat logout).
 */
function invalidate_db_session($koneksi, $id_admin, $token) {
    ensure_login_session_table($koneksi);

    $stmt = $koneksi->prepare(
        "UPDATE tbl_login_session
         SET is_active = 0
         WHERE id_admin = ? AND session_token = ?"
    );
    $stmt->bind_param("is", $id_admin, $token);
    $stmt->execute();
}

/**
 * Force logout: bersihkan cookie + hancurkan sesi PHP, lalu redirect.
 */
function force_admin_logout($reason = '') {
    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    session_destroy();

    $redirect = "login.php?force_logout=1";
    if ($reason !== '') {
        $redirect .= "&reason=" . urlencode($reason);
    }
    header("Location: " . $redirect);
    exit();
}
?>
