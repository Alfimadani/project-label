<?php
session_start();

// 1. Proteksi Sesi Login
// 1. Proteksi Sesi Login (Mendukung Login User & Guest)
if (!isset($_SESSION['id_user']) && !isset($_SESSION['is_guest'])) {
    header("Location: label/login.php");
    exit;
}



// 2. Koneksi Database untuk Tab Database (List View)
$host = 'localhost';
$user = 'root';
$pass = ''; // Sesuaikan password MySQL jika ada
$db   = 'db_label';

$conn = new mysqli($host, $user, $pass, $db);
$db_connected = !$conn->connect_error;

// 3. Menentukan Tab Aktif dari Parameter Query String (?tab=generator atau ?tab=database)
$activeTab = isset($_GET['tab']) && $_GET['tab'] === 'database' ? 'database' : 'generator';

// 4. Logika Paginasi & Search untuk Tab Database
$limit_options = [10, 25, 50, 100, 250, 500];
$limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], $limit_options) ? (int)$_GET['limit'] : 10;
$page  = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) && $db_connected ? $conn->real_escape_string(trim($_GET['search'])) : '';
$whereClause = "";

if (!empty($search)) {
    $whereClause = "WHERE no_aset LIKE '%$search%' 
                       OR nama_aset LIKE '%$search%' 
                       OR spesifikasi_tipe LIKE '%$search%' 
                       OR pengguna LIKE '%$search%' 
                       OR departemen_lokasi LIKE '%$search%'";
}

$totalData = 0;
$totalPages = 1;
$result = false;

if ($db_connected) {
    $totalSql = "SELECT COUNT(*) AS total FROM asset_label $whereClause";
    $totalResult = $conn->query($totalSql);
    if ($totalResult) {
        $totalData = $totalResult->fetch_assoc()['total'];
        $totalPages = max(1, ceil($totalData / $limit));
    }

    $sql = "SELECT * FROM asset_label $whereClause ORDER BY id DESC LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);
}

$startRecord = $totalData > 0 ? $offset + 1 : 0;
$endRecord   = min($offset + $limit, $totalData);
?>
<!DOCTYPE html>
<html lang="id" class="light scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus Enterprise - Asset Label Generator & DB</title>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+SC:wght@500;700;900&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- CSS khusus cetak label -->
    <link rel="stylesheet" href="label/css/style.css">

    <style>
        body {
            background-color: #F8FAFC;
            background-image:
                radial-gradient(at 0% 0%, rgba(99, 102, 241, 0.06) 0px, transparent 50%),
                radial-gradient(at 100% 0%, rgba(14, 165, 233, 0.06) 0px, transparent 50%);
            background-attachment: fixed;
            color: #334155;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 232, 240, 0.9);
            box-shadow: 0 10px 30px -10px rgba(99, 102, 241, 0.05);
        }
    </style>
</head>

<body class="font-sans antialiased text-slate-700 min-h-screen flex flex-col md:flex-row overflow-x-hidden">

    <!-- SIDEBAR NAVIGATION -->
    <aside id="sidebar" class="fixed md:sticky top-0 left-0 h-screen w-64 glass-card border-r border-slate-200/80 flex flex-col justify-between p-4 z-40 shrink-0 transition-all duration-300 ease-in-out no-print">
        <div>
            <!-- Brand Logo -->
            <div class="flex items-center justify-between px-2 py-3 mb-6">
                <div class="flex items-center gap-3">
                    <div class="relative flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-500/20">
                        <i data-lucide="tag" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h1 class="font-bold text-base tracking-tight text-slate-900">NEXUS</h1>
                        <p class="text-[10px] text-slate-400 font-medium uppercase">Asset System</p>
                    </div>
                </div>
            </div>

            <!-- Navigation Items -->
            <div class="space-y-6">
                <div>
                    <span class="px-3 text-[10px] font-mono uppercase tracking-widest text-slate-400 font-bold">Menu Utama</span>
                    <nav class="mt-2 space-y-1">
                        <a href="dashboard.php?tab=generator"
                            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-xs transition <?= $activeTab === 'generator' ? 'bg-gradient-to-r from-indigo-500 to-violet-600 text-white shadow-md' : 'text-slate-600 hover:bg-indigo-50/60' ?>">
                            <i data-lucide="scissors" class="w-4 h-4 <?= $activeTab === 'generator' ? 'text-white' : 'text-slate-400' ?>"></i>
                            <span>Cetak Stiker Label</span>
                        </a>
                        <a href="dashboard.php?tab=database"
                            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-xs transition <?= $activeTab === 'database' ? 'bg-gradient-to-r from-indigo-500 to-violet-600 text-white shadow-md' : 'text-slate-600 hover:bg-indigo-50/60' ?>">
                            <i data-lucide="database" class="w-4 h-4 <?= $activeTab === 'database' ? 'text-white' : 'text-slate-400' ?>"></i>
                            <span>DB Asset IT</span>
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <!-- User Profile -->
        <div class="mt-6 pt-4 border-t border-slate-200/80">
            <div class="bg-white p-2.5 rounded-xl border border-slate-200 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center font-bold text-xs text-white uppercase">
                        <?= isset($_SESSION['nama_user']) ? substr($_SESSION['nama_user'], 0, 2) : 'US'; ?>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-slate-800">
                            <?= htmlspecialchars($_SESSION['nama_user'] ?? 'Guest'); ?>
                        </p>
                        <p class="text-[10px] text-slate-500 font-mono">
                            <?= isset($_SESSION['is_guest']) ? 'Akses Guest' : 'User Terautentikasi'; ?>
                        </p>
                    </div>
                </div>
                <a href="label/logout.php" title="Logout" class="text-slate-400 hover:text-rose-600 p-1.5 transition">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col min-w-0 min-h-screen">

        <!-- HEADER TOPBAR -->
        <!-- HEADER TOPBAR -->
        <header class="sticky top-0 z-30 glass-card border-b border-slate-200/80 px-6 py-3.5 flex items-center justify-between no-print">
            <div class="flex items-center gap-3">
                <!-- TOMBOL TOGGLE SIDEBAR -->
                <button id="toggleSidebar" onclick="toggleSidebarMenu()" class="p-1.5 rounded-xl text-slate-600 hover:bg-slate-100 transition focus:outline-none">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <span class="text-sm font-bold text-slate-800">
                    <?= $activeTab === 'generator' ? 'Pembuat Stiker Label Aset IT' : 'Database Rekap Aset IT' ?>
                </span>
                <?php if ($activeTab === 'generator'): ?>
                    <span class="text-[10px] font-mono px-2 py-0.5 rounded-full bg-slate-100 border text-slate-600">2.90" x 1.83"</span>
                <?php endif; ?>
            </div>

            <?php if ($activeTab === 'generator'): ?>
                <!-- Action Buttons untuk Stiker Label -->
                <div class="flex items-center gap-2">
                    <div id="saveBadge" class="hidden md:inline-flex items-center gap-1 bg-slate-100 text-slate-600 border border-slate-200 text-[10px] font-semibold px-2 py-0.5 rounded-full">
                        <span id="saveStatusText">Tersimpan Otomatis</span>
                    </div>
                    <button onclick="saveToDatabase()" class="flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-xl text-xs font-semibold transition shadow-sm">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan DB</span>
                    </button>
                    <button onclick="addNewCard()" class="flex items-center gap-1.5 bg-slate-800 hover:bg-slate-700 text-white px-3 py-1.5 rounded-xl text-xs font-medium transition">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Tambah Label</span>
                    </button>
                    <button onclick="window.print()" class="flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded-xl text-xs font-semibold shadow-md transition">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        <span>Cetak 2 Kolom</span>
                    </button>
                </div>
            <?php else: ?>
                <!-- Action Buttons untuk Tab Database -->
                <a href="dashboard.php?tab=generator" class="flex items-center gap-1.5 bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-1.5 rounded-xl text-xs font-semibold shadow-md transition">
                    <i data-lucide="scissors" class="w-4 h-4"></i>
                    <span>Buat Label Baru</span>
                </a>
            <?php endif; ?>
        </header>

        <!-- KONTEN TAB 1: CETAK STIKER LABEL (INDEX2.PHP) -->
        <?php if ($activeTab === 'generator'): ?>
            <div class="p-6 max-w-7xl w-full mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Sidebar Kontrol Label (Kiri) -->
                <aside class="lg:col-span-3 no-print space-y-4">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-4">
                        <h2 class="text-xs font-bold text-slate-800 mb-3 flex items-center gap-2">
                            <i data-lucide="palette" class="w-4 h-4 text-slate-600"></i>
                            <span>Informasi Label Silver</span>
                        </h2>

                        <div class="space-y-2 text-[11px] text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-200 mb-3">
                            <div class="flex justify-between border-b pb-1">
                                <span>Warna Tema:</span>
                                <strong class="text-slate-800">Silver Metallic</strong>
                            </div>
                            <div class="flex justify-between border-b pb-1">
                                <span>Header Chinese:</span>
                                <strong class="text-slate-800">使用部门 / 和位置</strong>
                            </div>
                            <div class="flex justify-between">
                                <span>Ukuran:</span>
                                <strong class="text-slate-800">2.90" × 1.83"</strong>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <button onclick="fillSampleData()" class="w-full text-left px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl text-xs transition flex items-center justify-between">
                                <span>Isi Sample Data</span>
                                <i data-lucide="sparkles" class="w-3.5 h-3.5 text-slate-500"></i>
                            </button>

                            <button onclick="confirmClearAllCards()" class="w-full text-left px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-medium rounded-xl text-xs transition flex items-center justify-between">
                                <span>Reset Semua</span>
                                <i data-lucide="trash-2" class="w-3.5 h-3.5 text-rose-500"></i>
                            </button>
                        </div>
                    </div>
                </aside>

                <!-- Live Preview Container (Kanan) -->
                <section class="lg:col-span-9">
                    <div class="bg-slate-200/60 rounded-2xl p-4 border border-slate-300 min-h-[500px]">
                        <div class="flex justify-between items-center mb-4 no-print">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-2">
                                <i data-lucide="layout-grid" class="w-4 h-4"></i>
                                Live Preview (2 Kolom)
                            </span>
                            <span id="cardCount" class="text-xs font-bold bg-white px-3 py-1 rounded-full text-slate-700 border border-slate-300">
                                0 Label
                            </span>
                        </div>

                        <!-- Container Kartu Label -->
                        <div id="cardsContainer" class="print-container cards-grid">
                            <!-- Kartu akan di-render oleh script.js -->
                        </div>
                    </div>
                </section>

            </div>
        <?php endif; ?>

        <!-- KONTEN TAB 2: LIST VIEW DATABASE ASET (LIST_VIEW.PHP) -->
        <?php if ($activeTab === 'database'): ?>
            <div class="p-6 max-w-7xl w-full mx-auto space-y-6">

                <!-- Top Bar: Search & Status -->
                <div class="sticky top-16 z-20 bg-white/90 backdrop-blur-md p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-wrap justify-between items-center gap-4">
                    <form method="GET" action="dashboard.php" class="flex items-center gap-2 flex-1 max-w-md">
                        <input type="hidden" name="tab" value="database">
                        <input type="hidden" name="limit" value="<?= $limit; ?>">
                        <div class="relative w-full">
                            <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" name="search" value="<?= htmlspecialchars($search); ?>"
                                placeholder="Cari No. Aset, Nama, Pengguna..."
                                class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                        </div>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-xs font-semibold transition shadow-sm">
                            Cari
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="dashboard.php?tab=database&limit=<?= $limit; ?>" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-2 rounded-xl text-xs font-medium transition">
                                Reset
                            </a>
                        <?php endif; ?>
                    </form>

                    <div class="text-xs font-semibold text-slate-600 bg-slate-100 px-3.5 py-2 rounded-xl border border-slate-200">
                        Total Data: <span class="text-indigo-600 font-bold"><?= $totalData; ?></span> Record
                    </div>
                </div>

                <!-- Table Data -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-800 text-slate-200 font-semibold uppercase tracking-wider">
                                    <th class="p-3.5 border-b border-slate-700 w-12 text-center">No</th>
                                    <th class="p-3.5 border-b border-slate-700">No. Aset</th>
                                    <th class="p-3.5 border-b border-slate-700">Nama Aset</th>
                                    <th class="p-3.5 border-b border-slate-700">Spesifikasi / Tipe</th>
                                    <th class="p-3.5 border-b border-slate-700">Pengguna</th>
                                    <th class="p-3.5 border-b border-slate-700">Departemen / Lokasi</th>
                                    <th class="p-3.5 border-b border-slate-700">Waktu Penggunaan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 text-slate-700">
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php $no = $offset + 1;
                                    while ($row = $result->fetch_assoc()): ?>
                                        <tr class="hover:bg-slate-50/80 transition">
                                            <td class="p-3.5 text-center font-medium text-slate-500"><?= $no++; ?></td>
                                            <td class="p-3.5 font-bold text-indigo-600 whitespace-nowrap">
                                                <?= htmlspecialchars($row['no_aset']); ?>
                                            </td>
                                            <td class="p-3.5 font-semibold text-slate-800">
                                                <?= htmlspecialchars($row['nama_aset']); ?>
                                            </td>
                                            <td class="p-3.5 text-slate-600">
                                                <?= htmlspecialchars($row['spesifikasi_tipe']); ?>
                                            </td>
                                            <td class="p-3.5 font-medium text-slate-800">
                                                <?= htmlspecialchars($row['pengguna']); ?>
                                            </td>
                                            <td class="p-3.5 text-slate-600">
                                                <?= htmlspecialchars($row['departemen_lokasi']); ?>
                                            </td>
                                            <td class="p-3.5 text-slate-500 whitespace-nowrap">
                                                <?= htmlspecialchars($row['waktu_penggunaan']); ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="p-8 text-center text-slate-400">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                                                <span>Data aset tidak ditemukan / belum ada data tersimpan.</span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Paginasi -->
                    <div class="p-4 border-t border-slate-200 bg-slate-50 flex flex-wrap justify-between items-center gap-4 text-xs">
                        <div class="flex items-center gap-2">
                            <select onchange="changeLimit(this.value)" class="bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm cursor-pointer">
                                <?php foreach ($limit_options as $opt): ?>
                                    <option value="<?= $opt; ?>" <?= $limit == $opt ? 'selected' : ''; ?>>
                                        <?= $opt; ?> baris
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="text-slate-600 font-medium">per halaman</span>
                        </div>

                        <div class="text-slate-500">
                            Menampilkan <span class="font-bold text-slate-700"><?= $startRecord; ?></span> - <span class="font-bold text-slate-700"><?= $endRecord; ?></span> dari <span class="font-bold text-slate-700"><?= $totalData; ?></span> data
                        </div>

                        <?php if ($totalPages > 1): ?>
                            <div class="flex items-center gap-1">
                                <?php if ($page > 1): ?>
                                    <a href="dashboard.php?tab=database&page=<?= $page - 1; ?>&limit=<?= $limit; ?>&search=<?= urlencode($search); ?>" class="p-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-600">
                                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                                    </a>
                                <?php endif; ?>

                                <span class="px-3 py-1 font-semibold text-slate-700">
                                    Halaman <?= $page; ?> dari <?= $totalPages; ?>
                                </span>

                                <?php if ($page < $totalPages): ?>
                                    <a href="dashboard.php?tab=database&page=<?= $page + 1; ?>&limit=<?= $limit; ?>&search=<?= urlencode($search); ?>" class="p-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-600">
                                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        <?php endif; ?>

    </main>

    <!-- Modal Konfirmasi Reset -->
    <div id="clearModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden no-print">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl text-center">
            <h3 class="text-lg font-bold text-slate-800 mb-2">Reset Semua Label?</h3>
            <p class="text-xs text-slate-600 mb-6">Semua label yang dibuat akan direset kembali ke format kosong.</p>
            <div class="flex gap-3">
                <button onclick="closeModal()" class="flex-1 py-2 bg-slate-100 text-slate-700 text-xs font-semibold rounded-xl">Batal</button>
                <button onclick="executeClearAllCards()" class="flex-1 py-2 bg-rose-600 text-white text-xs font-semibold rounded-xl">Ya, Reset</button>
            </div>
        </div>
    </div>

    <!-- Script JS Utama -->
    <script src="label/js/script.js"></script>

    <script>
        window.addEventListener('DOMContentLoaded', () => {
            if (window.lucide) {
                lucide.createIcons();
            }
        });

        // FUNGSI UNTUK HIDE / SHOW SIDEBAR
        function toggleSidebarMenu() {
            const sidebar = document.getElementById('sidebar');

            if (sidebar.classList.contains('-translate-x-full')) {
                // Munculkan Sidebar
                sidebar.classList.remove('-translate-x-full', 'w-0', 'p-0', 'overflow-hidden');
                sidebar.classList.add('w-64', 'p-4');
            } else {
                // Sembunyikan Sidebar
                sidebar.classList.remove('w-64', 'p-4');
                sidebar.classList.add('-translate-x-full', 'w-0', 'p-0', 'overflow-hidden');
            }
        }

        function changeLimit(value) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('tab', 'database');
            urlParams.set('limit', value);
            urlParams.set('page', '1');
            window.location.search = urlParams.toString();
        }
    </script>
</body>

</html>
<?php
if ($db_connected) {
    $conn->close();
}
?>