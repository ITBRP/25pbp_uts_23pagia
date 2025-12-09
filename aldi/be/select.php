<?php
header("Content-Type: application/json; charset=UTF-8");
// validasi method
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Method Salah !'
    ]);
    exit;
}

// $koneksi = new mysqli('localhost', 'root', '', '25pbuts_pagia');
$koneksi = mysqli_connect('localhost', 'root', '', '25pbuts_pagia');

// NULL jika tidak upload file
$q = "SELECT * FROM books";
$dtQuery = mysqli_query($koneksi, $q);
$data = mysqli_fetch_all($dtQuery, MYSQLI_ASSOC);

echo json_encode([
    'status' => 'success',
    'msg' => 'Proses berhasil',
    'data' => $data
]);
