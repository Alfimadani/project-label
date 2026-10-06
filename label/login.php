<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Jika tombol Guest diklik, langsung arahkan ke index2.php
if (isset($_POST['guest_login'])) {
    $_SESSION['is_guest'] = true;
    $_SESSION['nama_user'] = 'Guest';
    header("Location: index2.php");
    exit;
}

// Jika pengguna sudah login secara resmi, arahkan ke index.php
if (isset($_SESSION['id_user'])) {
    header("Location: index.php");
    exit;
}

// Jika guest mencoba akses halaman login padahal sudah masuk sebagai guest
if (isset($_SESSION['is_guest'])) {
    header("Location: index2.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['guest_login'])) {
    $host = 'localhost';
    $user = 'root';
    $pass = ''; 
    $db   = 'db_label';

    $conn = @new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        $error = 'Koneksi ke database gagal! Pastikan MySQL di XAMPP/Laragon sudah jalan.';
    } else {
        $username = $conn->real_escape_string(trim($_POST['username']));
        $password = trim($_POST['password']);

        if (!empty($username) && !empty($password)) {
            $sql = "SELECT id_user, username, password, nama_user FROM users WHERE username = '$username' LIMIT 1";
            $result = $conn->query($sql);

            if ($result && $result->num_rows === 1) {
                $userData = $result->fetch_assoc();

                if ($password === $userData['password']) {
                    $_SESSION['id_user'] = $userData['id_user'];
                    $_SESSION['username'] = $userData['username'];
                    $_SESSION['nama_user'] = $userData['nama_user'];
                    unset($_SESSION['is_guest']);

                    header("Location: index.php");
                    exit;
                } else {
                    $error = 'Password yang Anda masukkan salah!';
                }
            } else {
                $error = 'Username tidak ditemukan!';
            }
        } else {
            $error = 'Silakan isi username dan password!';
        }
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Asset Label Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-slate-800 border border-slate-700 rounded-xl shadow-2xl overflow-hidden">
        <div class="bg-slate-900/80 p-6 border-b border-slate-700 text-center">
            <h2 class="text-xl font-bold text-white tracking-wide">Login System</h2>
            <p class="text-xs text-slate-400 mt-1">Akses Sistem Label Aset IT</p>
        </div>

        <div class="p-6">
            <?php if (!empty($error)): ?>
                <div class="mb-4 p-3 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs text-center font-medium">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Username</label>
                    <input type="text" name="username" placeholder="Masukkan username..." 
                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-blue-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1">Password</label>
                    <input type="password" name="password" placeholder="Masukkan password..." 
                        class="w-full bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-blue-500 transition">
                </div>

                <button type="submit" 
                    class="w-full mt-2 bg-blue-600 hover:bg-blue-500 text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition shadow-lg shadow-blue-600/20">
                    Masuk
                </button>
            </form>

            <div class="relative my-4 flex items-center justify-center">
                <div class="border-t border-slate-700 w-full"></div>
                <span class="bg-slate-800 px-3 text-[11px] text-slate-500 font-medium uppercase absolute">atau</span>
            </div>

            <form action="login.php" method="POST">
                <input type="hidden" name="guest_login" value="1">
                <button type="submit" 
                    class="w-full bg-slate-700 hover:bg-slate-600 text-slate-200 font-semibold py-2 px-4 rounded-lg text-xs transition border border-slate-600">
                    Masuk sebagai Guest
                </button>
            </form>
        </div>
    </div>

</body>
</html>