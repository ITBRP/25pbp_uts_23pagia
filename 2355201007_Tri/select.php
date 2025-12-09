<?php
header("Content-Type: application/json; charset=UTF-8");

// CEK METHOD HARUS GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Method tidak diizinkan'
    ]);
    exit;
}

// KONEKSI DATABASE
$koneksi = new mysqli('localhost', 'root', '', 'pbp_uts');

// CEK KONEKSI ERROR
if ($koneksi->connect_error) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Server error'
    ]);
    exit;
}

// QUERY AMBIL SEMUA DATA
$q = "SELECT * FROM buku ORDER BY id DESC";
$result = $koneksi->query($q);

// CEK QUERY ERROR
if (!$result) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Server error'
    ]);
    exit;
}

$data = [];

while ($row = $result->fetch_assoc()) {

    // Jika ada cover, bentuk URL
    if (!empty($row['cover'])) {
        $row['cover_url'] = "img/" . $row['cover'];
    }

    $data[] = $row;
}

// JIKA DATA KOSONG
if (count($data) == 0) {
    http_response_code(200);
    echo json_encode([
        'status' => 'success',
        'msg' => 'Tidak ada data',
        'data' => []
    ]);
    exit;
}

// BERHASIL AMBIL DATA
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'msg' => 'Data ditemukan',
    'data' => $data
]);
