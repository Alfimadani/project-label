<?php
session_start();

// 1. Proteksi Sesi Login (Mendukung Login User & Guest)
if (!isset($_SESSION['id_user']) && !isset($_SESSION['is_guest'])) {
    header("Location: label/index.php");
    exit;
}

// 2. Koneksi Database untuk Tab Database (List View)
$host = 'localhost';
$user = 'root';
$pass = ''; // Sesuaikan password MySQL jika ada
$db   = 'db_label';

$conn = new mysqli($host, $user, $pass, $db);
$db_connected = !$conn->connect_error;

// 3. Menentukan Tab Aktif dari Parameter Query String
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'generator';

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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- CSS khusus cetak label -->
    <link rel="stylesheet" href="label/css/style.css">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0f172a;
            background-image:
                radial-gradient(at 10% 10%, rgba(99, 102, 241, 0.08) 0px, transparent 40%),
                radial-gradient(at 90% 90%, rgba(14, 165, 233, 0.06) 0px, transparent 40%);
            background-attachment: fixed;
            color: #334155;
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(226, 232, 240, 0.8);
            box-shadow: 0 10px 35px -5px rgba(15, 23, 42, 0.04);
        }

        .glass-sidebar {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255, 255, 255, 0.08);
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(241, 245, 249, 0.5);
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(203, 213, 225, 0.8);
            border-radius: 9999px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>

    <!-- JSZip & FileSaver untuk fitur Export Drone -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>
</head>

<body class="antialiased text-slate-700 min-h-screen flex flex-col md:flex-row bg-slate-100 overflow-x-hidden">

    <!-- SIDEBAR NAVIGATION -->
    <aside id="sidebar" class="fixed md:sticky top-0 left-0 h-screen w-64 glass-sidebar flex flex-col justify-between p-4 z-40 shrink-0 transition-all duration-300 ease-in-out no-print">
        <div>
            <!-- Brand Logo -->
            <div class="flex items-center justify-between px-2 py-3 mb-6 border-b border-slate-800/80 pb-5">
                <div class="flex items-center gap-3">
                    <div class="relative flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 via-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-500/25 ring-1 ring-white/20">
                        <i data-lucide="layers" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h1 class="font-extrabold text-base tracking-tight text-white flex items-center gap-1.5">
                            NEXUS <span class="text-[9px] font-mono font-semibold px-1.5 py-0.2 rounded bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">All in One</span>
                        </h1>
                        <p class="text-[10px] text-slate-400 font-medium tracking-wider uppercase">Enterprise Suite</p>
                    </div>
                </div>
            </div>

            <!-- Navigation Items -->
            <div class="space-y-6">
                <!-- MENU UTAMA 1 -->
                <div>
                    <span class="px-3 text-[10px] font-mono uppercase tracking-widest text-slate-500 font-bold">Modul Aset IT</span>
                    <nav class="mt-2.5 space-y-1">
                        <a href="dashboard.php?tab=generator"
                            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-xs transition duration-200 <?= $activeTab === 'generator' ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-600/30 font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' ?>">
                            <i data-lucide="scissors" class="w-4 h-4 <?= $activeTab === 'generator' ? 'text-white' : 'text-slate-400' ?>"></i>
                            <span>Cetak Stiker Label</span>
                        </a>
                        <a href="dashboard.php?tab=database"
                            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-xs transition duration-200 <?= $activeTab === 'database' ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-600/30 font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' ?>">
                            <i data-lucide="database" class="w-4 h-4 <?= $activeTab === 'database' ? 'text-white' : 'text-slate-400' ?>"></i>
                            <span>Database Aset IT</span>
                        </a>
                    </nav>
                </div>

                <!-- MENU UTAMA 2 -->
                <div>
                    <span class="px-3 text-[10px] font-mono uppercase tracking-widest text-slate-500 font-bold">Modul Pemetaan</span>
                    <nav class="mt-2.5 space-y-1">
                        <a href="dashboard.php?tab=drone"
                            class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-medium text-xs transition duration-200 <?= $activeTab === 'drone' ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-600/30 font-semibold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-800/60' ?>">
                            <i data-lucide="plane" class="w-4 h-4 <?= $activeTab === 'drone' ? 'text-white' : 'text-slate-400' ?>"></i>
                            <span>Sistem Watermark Drone</span>
                        </a>
                    </nav>
                </div>
            </div>
        </div>

        <!-- User Profile -->
        <div class="mt-6 pt-4 border-t border-slate-800/80">
            <div class="bg-slate-800/60 backdrop-blur-md p-2.5 rounded-xl border border-slate-700/60 flex items-center justify-between">
                <div class="flex items-center gap-2.5 min-w-0">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-md">
                        <?= isset($_SESSION['nama_user']) ? strtoupper(substr($_SESSION['nama_user'], 0, 2)) : 'US'; ?>
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-semibold text-slate-200 truncate">
                            <?= htmlspecialchars($_SESSION['nama_user'] ?? 'Guest User'); ?>
                        </p>
                        <p class="text-[10px] text-slate-400 font-mono flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>
                            <?= isset($_SESSION['is_guest']) ? 'Guest' : 'Authenticated'; ?>
                        </p>
                    </div>
                </div>
                <a href="label/logout.php" title="Logout" class="text-slate-400 hover:text-rose-400 p-1.5 rounded-lg hover:bg-rose-500/10 transition">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col min-w-0 min-h-screen bg-slate-50/50">

        <!-- HEADER TOPBAR -->
        <header class="sticky top-0 z-30 glass-panel border-b border-slate-200/80 px-6 py-3.5 flex items-center justify-between no-print">
            <div class="flex items-center gap-3">
                <button id="toggleSidebar" onclick="toggleSidebarMenu()" class="p-2 rounded-xl text-slate-600 hover:bg-slate-200/60 transition focus:outline-none">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-bold text-slate-800 tracking-tight">
                        <?php
                        if ($activeTab === 'generator') {
                            echo 'Pembuat Stiker Label Aset IT';
                        } elseif ($activeTab === 'database') {
                            echo 'Database Rekapitulasi Aset IT';
                        } elseif ($activeTab === 'drone') {
                            echo 'Sistem Pemetaan & Batch Watermark Drone';
                        } else {
                            echo 'Dashboard';
                        }
                        ?>
                    </h2>
                    <?php if ($activeTab === 'generator'): ?>
                        <span class="text-[10px] font-mono px-2 py-0.5 rounded-md bg-indigo-50 border border-indigo-200/80 text-indigo-700 font-medium">Standard 2.90" × 1.83"</span>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($activeTab === 'generator'): ?>
                <!-- Action Buttons untuk Stiker Label -->
                <div class="flex items-center gap-2">
                    <div id="saveBadge" class="hidden md:inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 border border-emerald-200/80 text-[10px] font-semibold px-2.5 py-1 rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span id="saveStatusText">Tersimpan Otomatis</span>
                    </div>
                    <button onclick="saveToDatabase()" class="flex items-center gap-1.5 bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-xl text-xs font-semibold transition shadow-md shadow-emerald-600/10 active:scale-95">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        <span>Simpan DB</span>
                    </button>
                    <button onclick="addNewCard()" class="flex items-center gap-1.5 bg-slate-800 hover:bg-slate-900 text-white px-3.5 py-2 rounded-xl text-xs font-medium transition shadow-md active:scale-95">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        <span>Tambah Label</span>
                    </button>
                    <button onclick="window.print()" class="flex items-center gap-1.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-md shadow-indigo-500/20 transition active:scale-95">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        <span>Cetak 2 Kolom</span>
                    </button>
                </div>
            <?php elseif ($activeTab === 'drone'): ?>
                <!-- Action Buttons & Preset Bar untuk Modul Drone -->
                <div class="flex flex-wrap items-center gap-2">
                    <div class="flex items-center bg-slate-100/80 border border-slate-300/80 rounded-xl px-2.5 py-1.5">
                        <span class="text-xs text-slate-500 mr-2 font-medium">Preset:</span>
                        <select id="presetSelect" class="bg-transparent text-xs text-slate-800 font-semibold focus:outline-none max-w-[130px]">
                            <!-- Dynamic options -->
                        </select>
                    </div>

                    <button id="btnLoadPreset" title="Muat Preset" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-900 text-white text-xs font-semibold rounded-xl shadow-sm transition">Muat</button>
                    <button id="btnAddPreset" title="Tambah Preset" class="px-3 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">+ Preset</button>
                    <button id="btnEditPreset" title="Update Preset" class="px-3 py-1.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">Update</button>
                    <button id="btnDownloadPreset" title="Download Preset (.JSON)" class="px-3 py-1.5 bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold rounded-xl shadow-sm transition">Export (.JSON)</button>

                    <label class="px-3 py-1.5 bg-teal-600 hover:bg-teal-700 text-white text-xs font-semibold rounded-xl shadow-sm transition cursor-pointer" title="Upload Preset (.JSON)">
                        Import (.JSON)
                        <input type="file" id="presetFileInput" accept=".json" class="hidden">
                    </label>

                    <button id="btnDeletePreset" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold rounded-xl transition" title="Hapus Preset">
                        <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                    </button>

                    <div class="h-5 w-px bg-slate-300 mx-1"></div>

                    <button id="btnDownloadZip" disabled class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed text-white text-xs font-semibold rounded-xl shadow-md transition flex items-center gap-1.5">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Download ZIP</span>
                    </button>
                </div>
            <?php else: ?>
                <!-- Action Buttons untuk Tab Database -->
                <a href="dashboard.php?tab=generator" class="flex items-center gap-1.5 bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 text-white px-4 py-2 rounded-xl text-xs font-semibold shadow-md shadow-indigo-500/20 transition active:scale-95">
                    <i data-lucide="scissors" class="w-4 h-4"></i>
                    <span>Buat Label Baru</span>
                </a>
            <?php endif; ?>
        </header>

        <!-- KONTEN TAB 1: CETAK STIKER LABEL -->
        <?php if ($activeTab === 'generator'): ?>
            <div class="p-6 max-w-[1600px] w-full mx-auto grid grid-cols-1 lg:grid-cols-12 gap-6">

                <!-- Sidebar Kontrol Label (Kiri) -->
                <aside class="lg:col-span-3 no-print space-y-4">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 p-4">
                        <h2 class="text-xs font-bold uppercase tracking-wider text-slate-800 mb-3 flex items-center gap-2">
                            <i data-lucide="sliders" class="w-4 h-4 text-indigo-600"></i>
                            <span>Pengaturan Stiker Silver</span>
                        </h2>

                        <div class="space-y-2 text-[11px] text-slate-600 bg-slate-50/80 p-3.5 rounded-xl border border-slate-200/80 mb-4 font-mono">
                            <div class="flex justify-between border-b border-slate-200/60 pb-1.5">
                                <span class="text-slate-400">Tipe Silver:</span>
                                <strong class="text-slate-800">Metallic Matte</strong>
                            </div>
                            <div class="flex justify-between border-b border-slate-200/60 pb-1.5">
                                <span class="text-slate-400">Chinese Header:</span>
                                <strong class="text-slate-800">使用部门 / 和位置</strong>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Dimensi Stiker:</span>
                                <strong class="text-slate-800">2.90" × 1.83"</strong>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <button onclick="fillSampleData()" class="w-full text-left px-3.5 py-2.5 bg-slate-100 hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 font-semibold rounded-xl text-xs transition flex items-center justify-between group">
                                <span>Muat Contoh Data</span>
                                <i data-lucide="sparkles" class="w-4 h-4 text-slate-400 group-hover:text-indigo-600"></i>
                            </button>

                            <button onclick="confirmClearAllCards()" class="w-full text-left px-3.5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold rounded-xl text-xs transition flex items-center justify-between">
                                <span>Reset Semua Label</span>
                                <i data-lucide="trash-2" class="w-4 h-4 text-rose-500"></i>
                            </button>
                        </div>
                    </div>
                </aside>

                <!-- Live Preview Container (Kanan) -->
                <section class="lg:col-span-9">
                    <div class="bg-white rounded-2xl p-5 border border-slate-200/80 min-h-[550px] shadow-sm">
                        <div class="flex justify-between items-center mb-4 no-print border-b border-slate-100 pb-3">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                                <i data-lucide="layout-grid" class="w-4 h-4 text-indigo-600"></i>
                                Live Preview Cetak (Format 2 Kolom)
                            </span>
                            <span id="cardCount" class="text-xs font-semibold bg-slate-100 px-3 py-1 rounded-full text-slate-700 border border-slate-200">
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

        <!-- KONTEN TAB 2: LIST VIEW DATABASE ASET -->
        <?php if ($activeTab === 'database'): ?>
            <div class="p-6 max-w-[1600px] w-full mx-auto space-y-5">

                <!-- Top Bar: Search & Status -->
                <div class="sticky top-16 z-20 bg-white/90 backdrop-blur-md p-4 rounded-2xl shadow-sm border border-slate-200/80 flex flex-wrap justify-between items-center gap-4">
                    <form method="GET" action="dashboard.php" class="flex items-center gap-2 flex-1 max-w-md">
                        <input type="hidden" name="tab" value="database">
                        <input type="hidden" name="limit" value="<?= $limit; ?>">
                        <div class="relative w-full">
                            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400"></i>
                            <input type="text" name="search" value="<?= htmlspecialchars($search); ?>"
                                placeholder="Cari berdasarkan No. Aset, Nama, Pengguna..."
                                class="w-full pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-medium text-slate-800 focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none transition">
                        </div>
                        <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white px-4 py-2 rounded-xl text-xs font-semibold transition shadow-sm">
                            Cari
                        </button>
                        <?php if (!empty($search)): ?>
                            <a href="dashboard.php?tab=database&limit=<?= $limit; ?>" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-2 rounded-xl text-xs font-medium transition">
                                Reset
                            </a>
                        <?php endif; ?>
                    </form>

                    <div class="text-xs font-semibold text-slate-600 bg-slate-100/80 px-4 py-2 rounded-xl border border-slate-200/80 flex items-center gap-2">
                        <i data-lucide="layers" class="w-4 h-4 text-indigo-600"></i>
                        <span>Total Tersimpan: <strong class="text-slate-900 font-bold"><?= $totalData; ?></strong> Record</span>
                    </div>
                </div>

                <!-- Table Data -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200/80 overflow-hidden">
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-slate-900 text-slate-300 font-bold uppercase tracking-wider text-[11px]">
                                    <th class="p-4 border-b border-slate-800 w-12 text-center">No</th>
                                    <th class="p-4 border-b border-slate-800">No. Aset</th>
                                    <th class="p-4 border-b border-slate-800">Nama Aset</th>
                                    <th class="p-4 border-b border-slate-800">Spesifikasi / Tipe</th>
                                    <th class="p-4 border-b border-slate-800">Pengguna</th>
                                    <th class="p-4 border-b border-slate-800">Departemen / Lokasi</th>
                                    <th class="p-4 border-b border-slate-800">Waktu Penggunaan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200/80 text-slate-700">
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php $no = $offset + 1;
                                    while ($row = $result->fetch_assoc()): ?>
                                        <tr class="hover:bg-slate-50/80 transition duration-150">
                                            <td class="p-4 text-center font-medium text-slate-400"><?= $no++; ?></td>
                                            <td class="p-4 font-mono font-bold text-indigo-600 whitespace-nowrap">
                                                <span class="bg-indigo-50 text-indigo-700 px-2 py-1 rounded-md border border-indigo-200/60"><?= htmlspecialchars($row['no_aset']); ?></span>
                                            </td>
                                            <td class="p-4 font-semibold text-slate-900">
                                                <?= htmlspecialchars($row['nama_aset']); ?>
                                            </td>
                                            <td class="p-4 text-slate-600">
                                                <?= htmlspecialchars($row['spesifikasi_tipe']); ?>
                                            </td>
                                            <td class="p-4 font-medium text-slate-800">
                                                <div class="flex items-center gap-1.5">
                                                    <i data-lucide="user" class="w-3.5 h-3.5 text-slate-400"></i>
                                                    <span><?= htmlspecialchars($row['pengguna']); ?></span>
                                                </div>
                                            </td>
                                            <td class="p-4 text-slate-600">
                                                <?= htmlspecialchars($row['departemen_lokasi']); ?>
                                            </td>
                                            <td class="p-4 text-slate-500 font-mono text-[11px] whitespace-nowrap">
                                                <?= htmlspecialchars($row['waktu_penggunaan']); ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="p-12 text-center text-slate-400">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 mb-1">
                                                    <i data-lucide="inbox" class="w-6 h-6"></i>
                                                </div>
                                                <span class="font-medium text-slate-600 text-sm">Data aset tidak ditemukan</span>
                                                <span class="text-xs text-slate-400">Belum ada data tersimpan atau kueri pencarian tidak cocok.</span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Paginasi -->
                    <div class="p-4 border-t border-slate-200/80 bg-slate-50/50 flex flex-wrap justify-between items-center gap-4 text-xs">
                        <div class="flex items-center gap-2">
                            <select onchange="changeLimit(this.value)" class="bg-white border border-slate-200 rounded-xl px-3 py-1.5 font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500/20 shadow-sm cursor-pointer">
                                <?php foreach ($limit_options as $opt): ?>
                                    <option value="<?= $opt; ?>" <?= $limit == $opt ? 'selected' : ''; ?>>
                                        <?= $opt; ?> baris
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="text-slate-500 font-medium">per halaman</span>
                        </div>

                        <div class="text-slate-500 font-medium">
                            Menampilkan <span class="font-bold text-slate-800"><?= $startRecord; ?></span> - <span class="font-bold text-slate-800"><?= $endRecord; ?></span> dari <span class="font-bold text-slate-800"><?= $totalData; ?></span> data
                        </div>

                        <?php if ($totalPages > 1): ?>
                            <div class="flex items-center gap-1">
                                <?php if ($page > 1): ?>
                                    <a href="dashboard.php?tab=database&page=<?= $page - 1; ?>&limit=<?= $limit; ?>&search=<?= urlencode($search); ?>" class="p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 transition shadow-sm">
                                        <i data-lucide="chevron-left" class="w-4 h-4"></i>
                                    </a>
                                <?php endif; ?>

                                <span class="px-3.5 py-1.5 font-semibold text-slate-700 bg-slate-200/60 rounded-xl">
                                    Halaman <?= $page; ?> / <?= $totalPages; ?>
                                </span>

                                <?php if ($page < $totalPages): ?>
                                    <a href="dashboard.php?tab=database&page=<?= $page + 1; ?>&limit=<?= $limit; ?>&search=<?= urlencode($search); ?>" class="p-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-600 transition shadow-sm">
                                        <i data-lucide="chevron-right" class="w-4 h-4"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        <?php endif; ?>

        <!-- KONTEN TAB 3: SISTEM DRONE -->
        <?php if ($activeTab === 'drone'): ?>
            <div class="p-6 max-w-[1600px] w-full mx-auto">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

                    <!-- Left Sidebar Controls -->
                    <aside class="lg:col-span-4 xl:col-span-3 space-y-4 text-xs no-print">

                        <!-- Dropzone Upload -->
                        <div class="bg-white border-2 border-dashed border-indigo-200 hover:border-indigo-500 rounded-2xl p-5 text-center transition duration-200 cursor-pointer relative shadow-sm hover:shadow-md group" id="dropZone">
                            <input type="file" id="fileInput" multiple accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full z-10">
                            <div class="w-12 h-12 bg-indigo-50 group-hover:bg-indigo-100 text-indigo-600 rounded-2xl flex items-center justify-center mx-auto mb-3 transition">
                                <i data-lucide="upload-cloud" class="w-6 h-6"></i>
                            </div>
                            <p class="font-bold text-slate-800 text-xs">Unggah / Seret Foto Drone</p>
                            <p class="text-[10px] text-slate-400 mt-1">Mendukung pemrosesan banyak foto sekaligus</p>
                        </div>

                        <!-- Status Progress Upload -->
                        <div id="uploadStatus" class="hidden bg-white border border-indigo-200 rounded-2xl p-4 text-center space-y-2 shadow-sm">
                            <div class="flex items-center justify-center space-x-2 text-indigo-600">
                                <i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i>
                                <span id="uploadStatusText" class="text-xs font-semibold">Memproses 0/0 foto...</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                <div id="uploadProgressBar" class="bg-indigo-600 h-2 rounded-full transition-all duration-150" style="width: 0%"></div>
                            </div>
                        </div>

                        <!-- View Settings -->
                        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 space-y-2.5 shadow-sm">
                            <span class="font-bold text-slate-800 uppercase tracking-wider text-[10px] block mb-1">Tampilan Preview & ZIP</span>
                            <label class="flex items-center space-x-2 cursor-pointer text-slate-600 hover:text-slate-900 transition">
                                <input type="checkbox" id="chkShowOriginal" class="rounded border-slate-300 text-indigo-600 focus:ring-0">
                                <span>Tampilkan Foto Original</span>
                            </label>
                            <label class="flex items-center space-x-2 cursor-pointer text-slate-600 hover:text-slate-900 transition">
                                <input type="checkbox" id="chkIncludeOriginalsZip" checked class="rounded border-slate-300 text-emerald-600 focus:ring-0">
                                <span>Sertakan Folder <code class="text-xs text-indigo-600 font-semibold font-mono">Originals/</code> di ZIP</span>
                            </label>
                        </div>

                        <!-- Watermark Text Template -->
                        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 space-y-3 shadow-sm">
                            <span class="font-bold text-slate-800 uppercase tracking-wider text-[10px] block">Template Teks Watermark</span>
                            <div>
                                <input type="text" id="txtTemplate" value="[DATE] - [TIME] WIT" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 text-xs font-mono focus:bg-white focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 focus:outline-none">
                                <p class="text-[10px] text-slate-400 mt-1 font-mono">Variabel: <code class="text-indigo-600">[DATE]</code>, <code class="text-indigo-600">[TIME]</code>, <code class="text-indigo-600">[FILENAME]</code></p>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-slate-500 mb-1 font-medium">Font Family</label>
                                    <select id="fontFamily" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 text-slate-800">
                                        <option value="Arial">Arial</option>
                                        <option value="Impact">Impact</option>
                                        <option value="Trebuchet MS">Trebuchet MS</option>
                                        <option value="Verdana">Verdana</option>
                                        <option value="Courier New">Courier New</option>
                                        <option value="Times New Roman">Times New Roman</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-slate-500 mb-1 font-medium">Ukuran Base</label>
                                    <input type="number" id="fontSize" value="38" min="10" max="200" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 text-slate-800">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 items-center">
                                <div>
                                    <label class="block text-slate-500 mb-1 font-medium">Warna Teks</label>
                                    <input type="color" id="textColor" value="#ffffff" class="w-full h-8 bg-slate-50 border border-slate-200 rounded-xl p-0.5 cursor-pointer">
                                </div>
                                <div>
                                    <label class="block text-slate-500 mb-1 font-medium">Gaya Teks</label>
                                    <div class="flex gap-3 pt-1">
                                        <label class="flex items-center space-x-1 cursor-pointer font-bold text-slate-700">
                                            <input type="checkbox" id="chkBold" checked class="rounded border-slate-300 text-indigo-600">
                                            <span>B</span>
                                        </label>
                                        <label class="flex items-center space-x-1 cursor-pointer italic text-slate-700">
                                            <input type="checkbox" id="chkItalic" class="rounded border-slate-300 text-indigo-600">
                                            <span>I</span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between text-slate-500 mb-1">
                                    <span class="font-medium">Opacity Teks</span>
                                    <span id="lblTextOpacity" class="font-bold text-slate-700">100%</span>
                                </div>
                                <input type="range" id="textOpacity" min="0" max="100" value="100" class="w-full accent-indigo-600">
                            </div>
                        </div>

                        <!-- Outline Panel -->
                        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 space-y-3 shadow-sm">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 uppercase tracking-wider text-[10px]">Outline (Garis Tepi)</span>
                                <label class="flex items-center space-x-1 cursor-pointer text-slate-600">
                                    <input type="checkbox" id="chkApplyOutline" checked class="rounded border-slate-300 text-indigo-600">
                                    <span>Aktif</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-2 gap-2 items-center">
                                <div>
                                    <label class="block text-slate-500 mb-1">Warna Outline</label>
                                    <input type="color" id="outlineColor" value="#000000" class="w-full h-8 bg-slate-50 border border-slate-200 rounded-xl p-0.5 cursor-pointer">
                                </div>
                                <div>
                                    <label class="block text-slate-500 mb-1">Ketebalan</label>
                                    <input type="number" id="outlineThickness" min="1" max="36" value="6" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 text-slate-800">
                                </div>
                            </div>

                            <div class="space-y-1 text-slate-600">
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="radio" name="outlineMode" value="both" checked class="text-indigo-600 border-slate-300">
                                    <span>Outline + Teks Utama</span>
                                </label>
                                <label class="flex items-center space-x-2 cursor-pointer">
                                    <input type="radio" name="outlineMode" value="outlineOnly" class="text-indigo-600 border-slate-300">
                                    <span>Hanya Outline (Transparan)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Shadow Panel -->
                        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 space-y-3 shadow-sm">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-slate-800 uppercase tracking-wider text-[10px]">Bayangan (Shadow)</span>
                                <label class="flex items-center space-x-1 cursor-pointer text-slate-600">
                                    <input type="checkbox" id="chkApplyShadow" checked class="rounded border-slate-300 text-indigo-600">
                                    <span>Aktif</span>
                                </label>
                            </div>

                            <div class="grid grid-cols-2 gap-2 items-center">
                                <div>
                                    <label class="block text-slate-500 mb-1">Warna Shadow</label>
                                    <input type="color" id="shadowColor" value="#000000" class="w-full h-8 bg-slate-50 border border-slate-200 rounded-xl p-0.5 cursor-pointer">
                                </div>
                                <div>
                                    <label class="block text-slate-500 mb-1">Blur Radius</label>
                                    <input type="number" id="shadowBlur" min="0" max="10" value="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 text-slate-800">
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between text-slate-500 mb-1">
                                    <span>Opacity Shadow</span>
                                    <span id="lblShadowOpacity" class="font-bold text-slate-700">35%</span>
                                </div>
                                <input type="range" id="shadowOpacity" min="0" max="10" value="35" class="w-full accent-indigo-600">
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-slate-500 mb-1">Offset X</label>
                                    <input type="number" id="shadowX" value="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 text-slate-800">
                                </div>
                                <div>
                                    <label class="block text-slate-500 mb-1">Offset Y</label>
                                    <input type="number" id="shadowY" value="2" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 text-slate-800">
                                </div>
                            </div>
                        </div>

                        <!-- Anchor Position -->
                        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 space-y-3 shadow-sm">
                            <span class="font-bold text-slate-800 uppercase tracking-wider text-[10px] block">Posisi Watermark (Anchor)</span>
                            <div>
                                <select id="anchorPosition" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800">
                                    <option value="bottom-right" selected>Kanan Bawah (Bottom Right)</option>
                                    <option value="bottom-left">Kiri Bawah (Bottom Left)</option>
                                    <option value="bottom-center">Tengah Bawah (Bottom Center)</option>
                                    <option value="top-right">Kanan Atas (Top Right)</option>
                                    <option value="top-left">Kiri Atas (Top Left)</option>
                                    <option value="center">Tengah (Center)</option>
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-slate-500 mb-1">Margin X</label>
                                    <input type="number" id="offsetX" value="-30" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 text-slate-800">
                                </div>
                                <div>
                                    <label class="block text-slate-500 mb-1">Margin Y</label>
                                    <input type="number" id="offsetY" value="-25" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-2.5 py-1.5 text-slate-800">
                                </div>
                            </div>
                        </div>

                    </aside>

                    <!-- Right Workspace / Image Preview Grid -->
                    <main class="lg:col-span-8 xl:col-span-9">
                        <!-- Empty State -->
                        <div id="emptyState" class="bg-white rounded-2xl border border-slate-200/80 p-12 text-center flex flex-col items-center justify-center min-h-[500px] shadow-sm">
                            <div class="w-16 h-16 bg-indigo-50 text-indigo-600 rounded-2xl flex items-center justify-center mb-4">
                                <i data-lucide="image" class="w-8 h-8"></i>
                            </div>
                            <h3 class="text-base font-bold text-slate-800">Belum Ada Foto Drone Terpilih</h3>
                            <p class="text-xs text-slate-500 max-w-sm mt-1">Unggah atau seret file foto dari panel sebelah kiri untuk mulai melakukan konversi crop 16:9 & penambahan watermark.</p>
                        </div>

                        <!-- Image List Container -->
                        <div id="imageListContainer" class="space-y-4 hidden">
                            <!-- Rendered dynamically by drone script -->
                        </div>
                    </main>

                </div>
            </div>
        <?php endif; ?>

    </main>

    <!-- Modal Konfirmasi Reset -->
    <div id="clearModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden no-print">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl text-center border border-slate-100">
            <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-3">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
            <h3 class="text-base font-bold text-slate-800 mb-1">Reset Semua Label?</h3>
            <p class="text-xs text-slate-500 mb-6">Semua label yang telah diketik akan direset ke kondisi awal.</p>
            <div class="flex gap-3">
                <button onclick="closeModal()" class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">Batal</button>
                <button onclick="executeClearAllCards()" class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold rounded-xl shadow-md transition">Ya, Reset</button>
            </div>
        </div>
    </div>

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
                sidebar.classList.remove('-translate-x-full', 'w-0', 'p-0', 'overflow-hidden');
                sidebar.classList.add('w-64', 'p-4');
            } else {
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
    <!-- Script JS Utama Label -->
    <script src="label/js/script.js"></script>

    <?php if ($activeTab === 'drone'): ?>
        <!-- Script JS Khusus Sistem Drone -->
        <script src="drone/js/script.js"></script>
    <?php endif; ?>
</body>

</html>
<?php
if ($db_connected) {
    $conn->close();
}
?>