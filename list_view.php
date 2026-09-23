<?php
// Konfigurasi Database
$host = 'localhost';
$user = 'root';
$pass = ''; // Sesuaikan jika MySQL menggunakan password
$db   = 'db_label';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// 1. Pengaturan Paginasi & Limit Rows per Page
$limit_options = [10, 25, 50, 100, 250, 500, 1000, 999999];
$limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], $limit_options) ? (int)$_GET['limit'] : 10;
$page  = isset($_GET['page']) && (int)$_GET['page'] > 0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// 2. Fitur Pencarian Data
$search = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';
$whereClause = "";

if (!empty($search)) {
    $whereClause = "WHERE no_aset LIKE '%$search%' 
                       OR nama_aset LIKE '%$search%' 
                       OR spesifikasi_tipe LIKE '%$search%' 
                       OR pengguna LIKE '%$search%' 
                       OR departemen_lokasi LIKE '%$search%'";
}

// 3. Hitung Total Data untuk Paginasi
$totalSql = "SELECT COUNT(*) AS total FROM asset_label $whereClause";
$totalResult = $conn->query($totalSql);
$totalData = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalData / $limit);

// 4. Ambil Data dengan Limit & Offset
$sql = "SELECT * FROM asset_label $whereClause ORDER BY id DESC LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);

$startRecord = $totalData > 0 ? $offset + 1 : 0;
$endRecord   = min($offset + $limit, $totalData);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Data Aset IT - List View</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
</head>

<body class="bg-slate-100 min-h-screen text-slate-800 flex flex-col font-['Inter']">

    <!-- Header Navigation -->
    <header class="bg-slate-900 text-white p-4 shadow-md border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-indigo-600 border border-indigo-500 rounded-xl shadow-md">
                    <i data-lucide="database" class="w-6 h-6 text-white"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold tracking-wide">Daftar Aset IT (Database)</h1>
                    <p class="text-xs text-slate-400">Menampilkan seluruh data label aset yang tersimpan di sistem</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="index.html"
                    class="flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 px-4 py-2 rounded-xl font-medium transition text-sm border border-slate-700">
                    <i data-lucide="printer" class="w-4 h-4 text-slate-300"></i>
                    <span>Kembali ke Cetak Label</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 md:p-6 space-y-6">

        <!-- Top Bar: Search & Status (Sticky / Mengikut saat di-scroll) -->
        <div class="sticky top-0 z-10 bg-white/95 backdrop-blur-md p-4 rounded-2xl shadow-md border border-slate-200 flex flex-wrap justify-between items-center gap-4 transition-all">
            <!-- Form Pencarian -->
            <form method="GET" action="list_view.php" class="flex items-center gap-2 flex-1 max-w-md">
                <input type="hidden" name="limit" value="<?= $limit; ?>">
                <div class="relative w-full">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" name="search" value="<?= htmlspecialchars($search); ?>"
                        placeholder="Cari No. Aset, Nama, Pengguna..."
                        class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                </div>
                <button type="submit"
                    class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded-xl text-xs font-semibold transition shadow-sm">
                    Cari
                </button>
                <?php if (!empty($search)): ?>
                    <a href="list_view.php?limit=<?= $limit; ?>" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-2 rounded-xl text-xs font-medium transition">
                        Reset
                    </a>
                <?php endif; ?>
            </form>

            <!-- Total Data Counter -->
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
                            <?php $no = $offset + 1; while ($row = $result->fetch_assoc()): ?>
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

            <!-- Footer: Rows per page & Pagination -->
            <div class="p-4 border-t border-slate-200 bg-slate-50 flex flex-wrap justify-between items-center gap-4 text-xs">
                
                <!-- Rows per page Selector -->
                <div class="flex items-center gap-2">
                    <select onchange="changeLimit(this.value)" class="bg-white border border-slate-300 rounded-lg px-2.5 py-1.5 font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 shadow-sm cursor-pointer">
                        <?php foreach ($limit_options as $opt): ?>
                            <option value="<?= $opt; ?>" <?= $limit == $opt ? 'selected' : ''; ?>>
                                <?= $opt == 999999 ? 'Semua (999999)' : $opt; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="text-slate-600 font-medium">rows / page</span>
                </div>

                <!-- Showing Records Text -->
                <div class="text-slate-500">
                    Showing <span class="font-bold text-slate-700"><?= $startRecord; ?></span> to <span class="font-bold text-slate-700"><?= $endRecord; ?></span> of <span class="font-bold text-slate-700"><?= $totalData; ?></span> rows
                </div>

                <!-- Pagination Buttons -->
                <?php if ($totalPages > 1): ?>
                    <div class="flex items-center gap-1">
                        <!-- Prev Page -->
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1; ?>&limit=<?= $limit; ?>&search=<?= urlencode($search); ?>" class="p-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-600">
                                <i data-lucide="chevron-left" class="w-4 h-4"></i>
                            </a>
                        <?php endif; ?>

                        <!-- Page Numbers -->
                        <span class="px-3 py-1 font-semibold text-slate-700">
                            Halaman <?= $page; ?> dari <?= $totalPages; ?>
                        </span>

                        <!-- Next Page -->
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1; ?>&limit=<?= $limit; ?>&search=<?= urlencode($search); ?>" class="p-1.5 rounded-lg border border-slate-300 bg-white hover:bg-slate-100 text-slate-600">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </main>

    <script>
        // Inisialisasi Lucide Icons
        if (window.lucide) {
            lucide.createIcons();
        }

        // Handler untuk mengubah jumlah baris per halaman
        function changeLimit(value) {
            const urlParams = new URLSearchParams(window.location.search);
            urlParams.set('limit', value);
            urlParams.set('page', '1'); // Reset ke halaman 1 jika limit berubah
            window.location.search = urlParams.toString();
        }
    </script>
</body>

</html>

<?php $conn->close(); ?>