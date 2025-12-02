<?php
header("Content-Type: application/json; charset=UTF-8");

// cek method
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "msg"    => "Method Salah !"
    ]);
    exit;
}

// cek ID
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "msg"    => "ID tidak dikirim"
    ]);
    exit;
}
$id = intval($_GET['id']);

// koneksi ke database
$koneksi = new mysqli("localhost", "root", "", "data_buku");

if ($koneksi->connect_errno) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg"    => "Server error"
    ]);
    exit;
}

// cek apakah data ada
$cek = $koneksi->query("SELECT id FROM buku WHERE id=$id LIMIT 1");

if (!$cek) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg"    => "Server error"
    ]);
    exit;
}

if ($cek->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        "status" => "error",
        "msg"    => "Data not found"
    ]);
    exit;
}

// Menghapus data
$delete = $koneksi->query("DELETE FROM buku WHERE id=$id");

if (!$delete) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg"    => "Server error"
    ]);
    exit;
}

// respon sukses
http_response_code(200);
echo json_encode([
    "status" => "success",
    "msg"    => "Delete data success",
    "data"   => [
        "id" => $id
    ]
]);
