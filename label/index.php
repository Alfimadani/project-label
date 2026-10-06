<?php
session_start();

// Proteksi: Jika belum login, paksa pengguna kembali ke halaman login
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Kartu Label Aset IT - Metallic Silver Theme</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+SC:wght@500;700;900&display=swap"
        rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- External CSS -->
    <link rel="stylesheet" href="css/style.css">

    <link rel="stylesheet" href="css/style.css?v=<?= time(); ?>">
<script src="js/script.js?v=<?= time(); ?>"></script>
</head>

<body class="min-h-screen text-slate-800 flex flex-col">

    <header class="bg-slate-900 text-white p-4 shadow-md no-print border-b border-slate-800">
        <div class="max-w-7xl mx-auto flex flex-wrap justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <div class="p-2.5 bg-slate-700 border border-slate-500 rounded-xl shadow-md">
                    <i data-lucide="scissors" class="w-6 h-6 text-slate-200"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl font-bold tracking-wide">Pembuat Stiker Label Aset IT</h1>
                        <span id="saveBadge"
                            class="inline-flex items-center gap-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-semibold px-2 py-0.5 rounded-full transition-all duration-300">
                            <i data-lucide="check-circle-2" class="w-3 h-3 text-emerald-400"></i>
                            <span id="saveStatusText">Tersimpan Otomatis</span>
                        </span>
                    </div>
                    <p class="text-xs text-slate-400">Tema: <strong class="text-slate-200">Silver Metallic</strong> |
                        Ukuran Label: <strong>2.90" x 1.83"</strong> (2 Kolom)</p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button onclick="addNewCard()"
                    class="flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 px-4 py-2 rounded-xl font-medium transition text-sm border border-slate-700">
                    <i data-lucide="plus-circle" class="w-4 h-4 text-slate-300"></i>
                    <span>Tambah Label</span>
                </button>
                <button onclick="saveToDatabase()"
                    class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-xl font-medium transition text-sm shadow-md border border-emerald-500">
                    <i data-lucide="database" class="w-4 h-4 text-emerald-100"></i>
                    <span>Simpan ke DB Asset</span>
                </button>
                <button onclick="window.print()"
                    class="flex items-center gap-2 bg-slate-700 hover:bg-slate-600 text-white px-5 py-2 rounded-xl font-semibold shadow-lg shadow-slate-900/40 border border-slate-500 transition text-sm">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    <span>Cetak 2 Kolom</span>
                </button>

                <a href="logout.php" 
                    class="flex items-center gap-2 bg-rose-600/20 text-rose-300 border border-rose-500/30 hover:bg-rose-600 hover:text-white px-4 py-2 rounded-xl font-semibold transition text-sm">
                    <i data-lucide="log-out" class="w-4 h-4"></i>
                    <span>Logout</span>
                </a>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-7xl w-full mx-auto p-4 md:p-6 grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Sidebar Controls -->
        <aside class="lg:col-span-3 no-print space-y-4">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-5">
                <h2 class="text-base font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <i data-lucide="palette" class="w-4 h-4 text-slate-600"></i>
                    <span>Informasi Label Silver</span>
                </h2>

                <div
                    class="space-y-2.5 text-xs text-slate-600 bg-slate-50 p-3.5 rounded-xl border border-slate-200 mb-4">
                    <div class="flex justify-between items-center border-b border-slate-200 pb-1.5">
                        <span>Warna Tema:</span>
                        <strong class="text-slate-800 font-semibold flex items-center gap-1">
                            <span class="w-2.5 h-2.5 rounded-full bg-slate-500 inline-block"></span> Metallic Silver
                        </strong>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-200 pb-1.5">
                        <span>Penyimpanan:</span>
                        <strong class="text-emerald-700 font-semibold flex items-center gap-1">
                            <i data-lucide="database" class="w-3 h-3"></i> Auto-Save
                        </strong>
                    </div>
                    <div class="flex justify-between items-center border-b border-slate-200 pb-1.5">
                        <span>Header Baris 5:</span>
                        <strong class="text-slate-800 font-bold">使用部门 / 和位置</strong>
                    </div>
                    <div class="flex justify-between items-center">
                        <span>Ukuran Label:</span>
                        <strong class="text-slate-800">2.90 in × 1.83 in</strong>
                    </div>
                </div>

                <div class="space-y-2">
                    <button onclick="fillSampleData()"
                        class="w-full text-left px-3.5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium rounded-xl text-xs transition flex items-center justify-between">
                        <span>Isi Sample Data (4 Label)</span>
                        <i data-lucide="sparkles" class="w-4 h-4 text-slate-500"></i>
                    </button>
                    <!-- Tambahkan di dalam <aside> tepat di bawah tombol Reset / Hapus Semua -->
                    <div class="pt-2">
                        <a href="list_view.php"
                            class="w-full text-left px-3.5 py-2.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold rounded-xl text-xs transition flex items-center justify-between border border-indigo-200">
                            <span class="flex items-center gap-2">
                                <i data-lucide="database" class="w-4 h-4 text-indigo-600"></i>
                                <span>Lihat Semua Data DB Asset</span>
                            </span>
                            <i data-lucide="arrow-right" class="w-4 h-4 text-indigo-500"></i>
                        </a>
                    </div>
                    <button onclick="confirmClearAllCards()"
                        class="w-full text-left px-3.5 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-medium rounded-xl text-xs transition flex items-center justify-between">
                        <span>Reset / Hapus Semua</span>
                        <i data-lucide="trash-2" class="w-4 h-4 text-rose-500"></i>
                    </button>
                </div>

                <hr class="my-4 border-slate-200">

                <div class="text-xs text-slate-500 space-y-1.5">
                    <p class="font-bold text-slate-700">Petunjuk Cetak:</p>
                    <p>• Garis putus-putus berada 12px di luar label agar mudah dipotong.</p>
                    <p>• Semua data tersimpan otomatis di browser secara real-time.</p>
                </div>
            </div>
        </aside>

        <!-- Live Preview Area -->
        <section class="lg:col-span-9">
            <div class="bg-slate-200/70 rounded-2xl p-4 md:p-6 border border-slate-300 min-h-[500px]">
                <div class="flex justify-between items-center mb-4 no-print">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-600 flex items-center gap-2">
                        <i data-lucide="layout-grid" class="w-4 h-4 text-slate-700"></i>
                        Live Preview Silver (2 Kolom)
                    </span>
                    <span id="cardCount"
                        class="text-xs font-bold bg-white px-3 py-1 rounded-full text-slate-700 border border-slate-300 shadow-sm">
                        2 Label
                    </span>
                </div>

                <div id="cardsContainer" class="print-container cards-grid">
                    <!-- Cards dinamis akan dirender lewat script.js -->
                </div>
            </div>
        </section>
    </main>

    <!-- Modal Konfirmasi Reset -->
    <div id="clearModal"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center hidden no-print">
        <div class="bg-white rounded-2xl p-6 max-w-sm w-full mx-4 shadow-2xl border border-slate-100 text-center">
            <div class="w-12 h-12 bg-rose-100 text-rose-600 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="alert-triangle" class="w-6 h-6"></i>
            </div>
            <h3 class="text-lg font-bold text-slate-800 mb-2">Reset Semua Label?</h3>
            <p class="text-xs text-slate-600 mb-6">Tindakan ini akan menghapus semua label tersimpan dan mengembalikan
                ke format kosong.</p>
            <div class="flex gap-3">
                <button onclick="closeModal()"
                    class="flex-1 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition">
                    Batal
                </button>
                <button onclick="executeClearAllCards()"
                    class="flex-1 py-2.5 bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold rounded-xl transition shadow-lg shadow-rose-600/30">
                    Ya, Reset
                </button>
            </div>
        </div>
    </div>

    <!-- External JavaScript -->
    <script src="js/script.js"></script>
</body>

</html>