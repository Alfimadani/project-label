<?php
session_start();

// Jika pengguna sudah login (User atau Guest), arahkan ke dashboard
if (isset($_SESSION['id_user']) || isset($_SESSION['is_guest'])) {
    header("Location: dashboard.php");
    exit;
}

// Jika belum login sama sekali, arahkan ke halaman login
header("Location: label/login.php");
exit;
