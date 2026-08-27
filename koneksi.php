<?php
// Ini adalah file koneksi ke database MySQL

 $host = "localhost";
 $user = "root";
 $pass = ""; // Default password XAMPP kosong
 $db   = "db_virgo_rent";

// Membuat koneksi
 $koneksi = mysqli_connect($host, $user, $pass, $db);

// Cek apakah koneksi berhasil
if (!$koneksi) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}
?>