<?php
header("Content-Type: application/json; charset=UTF-8");

// cek method
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "msg" => "Method tidak diizinkan"
    ]);
    exit;
}

// id dari url
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "msg" => "ID tidak dikirim"
    ]);
    exit;
}

$id = (int)$_GET['id'];
if ($id <= 0) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "msg" => "ID tidak valid"
    ]);
    exit;
}

// koneksi ke database
$koneksi = new mysqli("localhost", "root", "", "pbp_uts");
if ($koneksi->connect_error) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg" => "Server error"
    ]);
    exit;
}

// cek data
$cek = $koneksi->query("SELECT id FROM buku WHERE id=$id");
if ($cek->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        "status" => "error",
        "msg" => "Data not found"
    ]);
    exit;
}

// delete data
$hapus = $koneksi->query("DELETE FROM buku WHERE id=$id");

if (!$hapus) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg" => "Server error"
    ]);
    exit;
}

// respon sukses
http_response_code(200);
echo json_encode([
    "status" => "success",
    "msg" => "Delete data success",
    "data" => [
        "id" => $id
    ]
]);
