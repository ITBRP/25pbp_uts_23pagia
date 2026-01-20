<?php
header("Content-Type: application/json; charset=UTF-8");

// cek method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Method tidak diizinkan'
    ]);
    exit;
}

// cek parameter id
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'msg' => 'ID tidak dikirim'
    ]);
    exit;
}

$id = $_GET['id'];

// koneksi ke database
$koneksi = new mysqli('localhost', 'root', '', 'pbp_uts');

// cek gagal koneksi
if ($koneksi->connect_error) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Server error'
    ]);
    exit;
}

// query data by ID
$q = "SELECT * FROM buku WHERE id = $id";
$result = $koneksi->query($q);

// cek query gagal
if (!$result) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Server error'
    ]);
    exit;
}

// cek data tidak ditemukan
if ($result->num_rows == 0) {
    http_response_code(404);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Data tidak ditemukan'
    ]);
    exit;
}

$row = $result->fetch_assoc();

// respon sukses
http_response_code(200);
echo json_encode([
    'status' => 'success',
    'msg' => 'Data ditemukan',
    'data' => [
        'id' => (int)$row['id'],
        'title' => $row['title'],
        'author' => $row['author'],
        'publisher' => $row['publisher'],
        'published_year' => (int)$row['published_year'],
        'isbn' => $row['isbn'],
        'cover' => $row['cover'],
        'cover_url' => $row['cover'] ? 'img/' . $row['cover'] : null
    ]
]);
