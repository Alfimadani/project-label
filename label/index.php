<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Jika pengguna sudah login (User Resmi ATAU Guest), langsung lempar ke dashboard
if (isset($_SESSION['id_user']) || isset($_SESSION['is_guest'])) {
    header("Location: ../dashboard.php");
    exit;
}

// Opsi Login Guest
if (isset($_POST['guest_login'])) {
    $_SESSION['is_guest'] = true;
    $_SESSION['nama_user'] = 'Guest';
    header("Location: ../dashboard.php");
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
        $error = 'Koneksi ke database gagal! Pastikan MySQL di XAMPP/Laragon sudah berjalan.';
    } else {
        $username = $conn->real_escape_string(trim($_POST['username']));
        $password = trim($_POST['password']);

        if (!empty($username) && !empty($password)) {
            // Ambil user berdasarkan username
            $sql = "SELECT id_user, username, password, nama_user FROM users WHERE username = '$username' LIMIT 1";
            $result = $conn->query($sql);

            if ($result && $result->num_rows === 1) {
                $userData = $result->fetch_assoc();

                // Verifikasi password (Mendukung password plain-text & password_hash PHP)
                $passwordValid = false;
                if (password_verify($password, $userData['password']) || $password === $userData['password']) {
                    $passwordValid = true;
                }

                if ($passwordValid) {
                    $_SESSION['id_user']   = $userData['id_user'];
                    $_SESSION['username']  = $userData['username'];
                    $_SESSION['nama_user'] = $userData['nama_user'];
                    unset($_SESSION['is_guest']);

                    header("Location: ../dashboard.php");
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-slate-800 border border-slate-700 rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-slate-900/80 p-6 border-b border-slate-700 text-center">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-indigo-600 text-white mb-3 shadow-lg shadow-indigo-500/30">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12.5 22H18a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v10" />
                    <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                    <path d="M2 15h10" />
                    <path d="m9 18 3-3-3-3" />
                </svg>
            </div>
            <h2 class="text-xl font-bold text-white tracking-wide">Login System</h2>
            <p class="text-xs text-slate-400 mt-1">Akses Sistem Label Aset IT</p>
        </div>

        <div class="p-6">
            <?php if (!empty($error)): ?>
                <div class="mb-4 p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs text-center font-medium">
                    <?= htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form action="index.php" method="POST" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Username</label>
                    <input type="text" name="username" required placeholder="Masukkan username..."
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-300 mb-1.5">Password</label>
                    <input type="password" name="password" required placeholder="Masukkan password..."
                        class="w-full bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-slate-100 placeholder-slate-500 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition">
                </div>

                <button type="submit"
                    class="w-full mt-2 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold py-2.5 px-4 rounded-xl text-xs transition shadow-lg shadow-indigo-600/20">
                    Masuk
                </button>
            </form>

            <div class="relative my-5 flex items-center justify-center">
                <div class="border-t border-slate-700 w-full"></div>
                <span class="bg-slate-800 px-3 text-[10px] text-slate-500 font-semibold uppercase absolute">Atau</span>
            </div>

            <form action="index.php" method="POST">
                <input type="hidden" name="guest_login" value="1">
                <button type="submit"
                    class="w-full bg-slate-700/60 hover:bg-slate-700 text-slate-300 font-medium py-2 px-4 rounded-xl text-xs transition border border-slate-600/80">
                    Masuk sebagai Guest
                </button>
            </form>
        </div>
    </div>

</body>

</html>