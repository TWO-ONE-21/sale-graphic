<?php
// Selalu mulai session di awal, bahkan untuk menghancurkannya.
session_start();

// Hapus semua variabel session.
$_SESSION = array();

// Hancurkan session.
session_destroy();

// Arahkan pengguna kembali ke halaman login.
header("location: index.php");
exit;
?>
