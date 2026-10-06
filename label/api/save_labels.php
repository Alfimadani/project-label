<?php
header('Content-Type: application/json');

// Konfigurasi Database
$host = 'localhost';
$user = 'root';
$pass = ''; // Sesuaikan password MySQL jika ada
$db   = 'db_label';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(['status' => 'error', 'message' => 'Koneksi database gagal: ' . $conn->connect_error]);
    exit;
}

// Ambil data JSON dari JavaScript
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!empty($data) && is_array($data)) {
    
    // 1. CEK DULU APABILA ADA NO_ASET YANG SUDAH TERDAFTAR DI DATABASE
    $duplicateList = [];

    foreach ($data as $card) {
        if (empty(trim($card['noAset']))) continue;

        $noAset = $conn->real_escape_string(trim($card['noAset']));
        
        $checkSql = "SELECT no_aset FROM asset_label WHERE no_aset = '$noAset'";
        $checkResult = $conn->query($checkSql);

        if ($checkResult && $checkResult->num_rows > 0) {
            $duplicateList[] = $noAset;
        }
    }

    // Jika ditemukan no_aset yang sudah ada di DB, hentikan proses dan berikan notifikasi gagal
    if (!empty($duplicateList)) {
        $duplicatesStr = implode(', ', array_unique($duplicateList));
        echo json_encode([
            'status' => 'error',
            'message' => "Gagal menyimpan! Nomor Aset berikut sudah ada di database: ($duplicatesStr)"
        ]);
        $conn->close();
        exit;
    }

    // 2. JIKA TIDAK ADA DUPLIKASI DI DB, BARU LAKUKAN INSERT
    $successCount = 0;

    foreach ($data as $card) {
        if (empty(trim($card['noAset']))) continue;

        $noAset          = $conn->real_escape_string(trim($card['noAset']));
        $namaAset        = $conn->real_escape_string($card['namaAset']);
        $spesifikasi     = $conn->real_escape_string($card['spesifikasi']);
        $pengguna        = $conn->real_escape_string($card['pengguna']);
        $deptLokasi      = $conn->real_escape_string($card['deptLokasi']);
        $waktuPenggunaan = $conn->real_escape_string($card['waktuPenggunaan']);

        // Murni Murni INSERT tanpa UPDATE
        $sql = "INSERT INTO asset_label (no_aset, nama_aset, spesifikasi_tipe, pengguna, departemen_lokasi, waktu_penggunaan) 
                VALUES ('$noAset', '$namaAset', '$spesifikasi', '$pengguna', '$deptLokasi', '$waktuPenggunaan')";
        
        if ($conn->query($sql)) {
            $successCount++;
        }
    }

    echo json_encode([
        'status' => 'success', 
        'message' => "Berhasil menyimpan $successCount data ke database!"
    ]);

} else {
    echo json_encode(['status' => 'error', 'message' => 'Data label kosong atau format salah']);
}

$conn->close();
?>