const STORAGE_KEY = "IT_ASSET_LABELS_DATA_V3";
let saveTimeout = null;

// Data Default Awal
const defaultSampleData = [
    {
        id: 1,
        noAset: "IT-2024-0012",
        namaAset: "Laptop ThinkPad E14",
        spesifikasi: "Core i7 / 16GB / 512GB",
        pengguna: "Budi Santoso",
        deptLokasi: "IT & Systems - Lt. 2",
        waktuPenggunaan: "2024-01-15"
    },
    {
        id: 2,
        noAset: "IT-2024-0088",
        namaAset: "Monitor Dell 24 Inch",
        spesifikasi: "Dell P2419H IPS FHD",
        pengguna: "Siti Rahma",
        deptLokasi: "Finance - Ruang 102",
        waktuPenggunaan: "2024-02-01"
    }
];

let cards = [];

// Muat data dari LocalStorage
function loadSavedCards() {
    try {
        const saved = localStorage.getItem(STORAGE_KEY);
        if (saved) {
            const parsed = JSON.parse(saved);
            if (Array.isArray(parsed) && parsed.length > 0) {
                cards = parsed;
                return;
            }
        }
    } catch (e) {
        console.error("Gagal memuat data dari LocalStorage:", e);
    }
    cards = JSON.parse(JSON.stringify(defaultSampleData));
}

// Simpan data ke LocalStorage
function saveCardsToStorage() {
    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(cards));
        showSaveNotification();
    } catch (e) {
        console.error("Gagal menyimpan data ke LocalStorage:", e);
    }
}

// Indikator Auto-Save
function showSaveNotification() {
    const badge = document.getElementById('saveBadge');
    const statusText = document.getElementById('saveStatusText');
    if (!badge || !statusText) return;

    statusText.textContent = "Menyimpan...";
    badge.className = "inline-flex items-center gap-1 bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-semibold px-2 py-0.5 rounded-full transition-all duration-300";

    clearTimeout(saveTimeout);
    saveTimeout = setTimeout(() => {
        statusText.textContent = "Tersimpan Otomatis";
        badge.className = "inline-flex items-center gap-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-semibold px-2 py-0.5 rounded-full transition-all duration-300";
    }, 500);
}

// Render Kartu ke Layar
function renderCards() {
    const container = document.getElementById('cardsContainer');
    if (!container) return;
    container.innerHTML = '';

    cards.forEach((card) => {
        const cutBox = document.createElement('div');
        cutBox.className = 'cut-box group';
        cutBox.innerHTML = `
            <div class="cut-indicator">
                <svg class="w-3.5 h-3.5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="6" cy="6" r="3"></circle>
                    <circle cx="6" cy="18" r="3"></circle>
                    <line x1="20" y1="4" x2="8.12" y2="15.88"></line>
                    <line x1="14.47" y1="14.48" x2="20" y2="20"></line>
                    <line x1="8.12" y1="8.12" x2="12" y2="12"></line>
                </svg>
                <span class="text-[8.5px] font-sans tracking-wide">GARIS POTONG / GUNTING</span>
            </div>

            <div class="asset-card">
                <div class="asset-header">
                    <div class="w-5 flex justify-center items-center z-10">
                        <svg class="w-4 h-4 text-slate-100 drop-shadow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
                            <line x1="8" y1="21" x2="16" y2="21"></line>
                            <line x1="12" y1="17" x2="12" y2="21"></line>
                        </svg>
                    </div>
                    <div class="flex-1 text-center pr-2 z-10">
                        <div class="text-[11px] font-black tracking-wider leading-none text-white drop-shadow-sm" style="font-family: 'Noto Sans SC', sans-serif;">
                            固定资产标识卡
                        </div>
                        <div class="text-[6.5px] font-extrabold tracking-widest text-slate-200 mt-0.5 uppercase">
                            ASSET IT
                        </div>
                    </div>
                    <button onclick="deleteCard(${card.id})" class="no-print opacity-0 group-hover:opacity-100 bg-rose-600 text-white p-0.5 rounded hover:bg-rose-700 transition shadow z-10" title="Hapus Label">
                        <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                    </button>
                </div>

                <div class="asset-table">
                    <!-- Row 1 -->
                    <div class="asset-row">
                        <div class="asset-label">
                            <div class="w-3.5 flex justify-center mr-0.5 text-slate-700">
                                <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                            </div>
                            <div class="leading-none">
                                <div class="text-[6.5px] font-bold text-slate-800" style="font-family: 'Noto Sans SC', sans-serif;">资产编号</div>
                                <div class="text-[5px] font-semibold text-slate-600 uppercase">No. Aset</div>
                            </div>
                        </div>
                        <div class="asset-value">
                            <input type="text" class="asset-input-direct" value="${escapeHtml(card.noAset)}" oninput="updateCardData(${card.id}, 'noAset', this.value)" placeholder="No. Aset...">
                        </div>
                    </div>

                    <!-- Row 2 -->
                    <div class="asset-row">
                        <div class="asset-label">
                            <div class="w-3.5 flex justify-center mr-0.5 text-slate-700">
                                <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                            </div>
                            <div class="leading-none">
                                <div class="text-[6.5px] font-bold text-slate-800" style="font-family: 'Noto Sans SC', sans-serif;">资产名称</div>
                                <div class="text-[5px] font-semibold text-slate-600 uppercase">Nama Aset</div>
                            </div>
                        </div>
                        <div class="asset-value">
                            <input type="text" class="asset-input-direct" value="${escapeHtml(card.namaAset)}" oninput="updateCardData(${card.id}, 'namaAset', this.value)" placeholder="Nama Aset...">
                        </div>
                    </div>

                    <!-- Row 3 -->
                    <div class="asset-row">
                        <div class="asset-label">
                            <div class="w-3.5 flex justify-center mr-0.5 text-slate-700">
                                <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                            </div>
                            <div class="leading-none">
                                <div class="text-[6.5px] font-bold text-slate-800" style="font-family: 'Noto Sans SC', sans-serif;">规格型号</div>
                                <div class="text-[4.5px] font-semibold text-slate-600 uppercase">Spesifikasi/Tipe</div>
                            </div>
                        </div>
                        <div class="asset-value">
                            <input type="text" class="asset-input-direct" value="${escapeHtml(card.spesifikasi)}" oninput="updateCardData(${card.id}, 'spesifikasi', this.value)" placeholder="Spesifikasi / Tipe...">
                        </div>
                    </div>

                    <!-- Row 4 -->
                    <div class="asset-row">
                        <div class="asset-label">
                            <div class="w-3.5 flex justify-center mr-0.5 text-slate-700">
                                <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </div>
                            <div class="leading-none">
                                <div class="text-[6.5px] font-bold text-slate-800" style="font-family: 'Noto Sans SC', sans-serif;">使用人</div>
                                <div class="text-[5px] font-semibold text-slate-600 uppercase">Pengguna</div>
                            </div>
                        </div>
                        <div class="asset-value">
                            <input type="text" class="asset-input-direct" value="${escapeHtml(card.pengguna)}" oninput="updateCardData(${card.id}, 'pengguna', this.value)" placeholder="Pengguna...">
                        </div>
                    </div>

                    <!-- Row 5 -->
                    <div class="asset-row">
                        <div class="asset-label">
                            <div class="w-3.5 flex justify-center mr-0.5 text-slate-700">
                                <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 20V6a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v14"></path><path d="M2 20h20"></path><circle cx="14" cy="12" r="1"></circle></svg>
                            </div>
                            <div class="leading-none">
                                <div class="text-[6px] font-extrabold text-slate-800 leading-tight whitespace-nowrap" style="font-family: 'Noto Sans SC', sans-serif;">使用部门 / 和位置</div>
                                <div class="text-[4px] font-bold text-slate-600 uppercase whitespace-nowrap">DEPARTEMENT / LOKASI</div>
                            </div>
                        </div>
                        <div class="asset-value">
                            <input type="text" class="asset-input-direct" value="${escapeHtml(card.deptLokasi)}" oninput="updateCardData(${card.id}, 'deptLokasi', this.value)" placeholder="Departement / Lokasi...">
                        </div>
                    </div>

                    <!-- Row 6 -->
                    <div class="asset-row">
                        <div class="asset-label">
                            <div class="w-3.5 flex justify-center mr-0.5 text-slate-700">
                                <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            </div>
                            <div class="leading-none">
                                <div class="text-[6.5px] font-bold text-slate-800" style="font-family: 'Noto Sans SC', sans-serif;">使用时间</div>
                                <div class="text-[4.5px] font-semibold text-slate-600 uppercase">Waktu Penggunaan</div>
                            </div>
                        </div>
                        <div class="asset-value">
                            <input type="text" class="asset-input-direct" value="${escapeHtml(card.waktuPenggunaan)}" oninput="updateCardData(${card.id}, 'waktuPenggunaan', this.value)" placeholder="Waktu Penggunaan...">
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.appendChild(cutBox);
    });

    const countElem = document.getElementById('cardCount');
    if (countElem) {
        countElem.textContent = `${cards.length} Label`;
    }

    if (window.lucide) {
        lucide.createIcons();
    }
}

// Update nilai input
function updateCardData(id, field, value) {
    const card = cards.find(c => c.id === id);
    if (card) {
        card[field] = value;
        saveCardsToStorage();
    }
}

// Tambah kartu baru
function addNewCard() {
    const newId = cards.length > 0 ? Math.max(...cards.map(c => c.id)) + 1 : 1;
    cards.push({
        id: newId,
        noAset: "",
        namaAset: "",
        spesifikasi: "",
        pengguna: "",
        deptLokasi: "",
        waktuPenggunaan: new Date().toISOString().split('T')[0]
    });
    saveCardsToStorage();
    renderCards();
}

// Hapus kartu
function deleteCard(id) {
    cards = cards.filter(c => c.id !== id);
    if (cards.length === 0) {
        cards.push({
            id: 1,
            noAset: "",
            namaAset: "",
            spesifikasi: "",
            pengguna: "",
            deptLokasi: "",
            waktuPenggunaan: ""
        });
    }
    saveCardsToStorage();
    renderCards();
}

// Isi sampel data
function fillSampleData() {
    cards = JSON.parse(JSON.stringify([
        ...defaultSampleData,
        {
            id: 3,
            noAset: "IT-2024-0103",
            namaAset: "Printer HP LaserJet",
            spesifikasi: "M404dn Monokrom",
            pengguna: "Shared / Admin",
            deptLokasi: "HRD - Lobby Utama",
            waktuPenggunaan: "2024-02-10"
        },
        {
            id: 4,
            noAset: "IT-2024-0155",
            namaAset: "PC Desktop Tower",
            spesifikasi: "i5-12400 / 16GB / 1TB",
            pengguna: "Andi Wijaya",
            deptLokasi: "Operation - Lt. 1",
            waktuPenggunaan: "2024-03-01"
        }
    ]));
    saveCardsToStorage();
    renderCards();
}

// Kontrol Modal
function confirmClearAllCards() {
    const modal = document.getElementById('clearModal');
    if (modal) modal.classList.remove('hidden');
}

function closeModal() {
    const modal = document.getElementById('clearModal');
    if (modal) modal.classList.add('hidden');
}

function executeClearAllCards() {
    cards = [{
        id: 1,
        noAset: "",
        namaAset: "",
        spesifikasi: "",
        pengguna: "",
        deptLokasi: "",
        waktuPenggunaan: ""
    }];
    saveCardsToStorage();
    renderCards();
    closeModal();
}

// Utility Escape HTML
function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, "&amp;")
              .replace(/</g, "&lt;")
              .replace(/>/g, "&gt;")
              .replace(/"/g, "&quot;")
              .replace(/'/g, "&#039;");
}

// Inisialisasi saat halaman dimuat
window.onload = function() {
    loadSavedCards();
    renderCards();
};

function saveToDatabase() {
    if (!cards || cards.length === 0) {
        alert("Tidak ada data label untuk disimpan!");
        return;
    }

    // 1. Deteksi duplikasi No. Aset di antara kartu yang sedang diisi
    const filledAssetNumbers = cards
        .map(c => c.noAset ? c.noAset.trim() : '')
        .filter(no => no !== '');

    const duplicates = filledAssetNumbers.filter((item, index) => filledAssetNumbers.indexOf(item) !== index);

    if (duplicates.length > 0) {
        // Hilangkan duplikat nama untuk pesan notifikasi
        const uniqueDuplicates = [...new Set(duplicates)];
        alert(`Peringatan: Terdapat Nomor Aset yang ganda/sama!\n\nNo. Aset ganda: ${uniqueDuplicates.join(', ')}\n\nSilakan perbaiki terlebih dahulu sebelum menyimpan.`);
        return; // Hentikan proses simpan
    }

    const badge = document.getElementById('saveBadge');
    const statusText = document.getElementById('saveStatusText');
    
    if (statusText) statusText.textContent = "Menyimpan ke DB...";
    if (badge) badge.className = "inline-flex items-center gap-1 bg-amber-500/20 text-amber-300 border border-amber-500/30 text-[10px] font-semibold px-2 py-0.5 rounded-full transition-all duration-300";

    // 2. Kirim data ke backend jika tidak ada duplikasi
    fetch('api/save_labels.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(cards)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('HTTP Error Status: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            if (statusText) statusText.textContent = "Tersimpan di DB";
            if (badge) badge.className = "inline-flex items-center gap-1 bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-[10px] font-semibold px-2 py-0.5 rounded-full transition-all duration-300";
        } else {
            alert('Gagal menyimpan: ' + data.message);
            if (statusText) statusText.textContent = "Gagal Simpan";
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Terjadi kesalahan koneksi ke server: ' + error.message);
        if (statusText) statusText.textContent = "Error Server";
    });
}