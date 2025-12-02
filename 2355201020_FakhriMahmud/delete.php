<?php
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Method Salah !'
    ]);
    exit;
}

$errors = [];



if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Data error',
        'errors' => $errors
    ]);
    exit;
}


$koneksi = new mysqli("localhost", "root", "", "pbputs");

if ($koneksi->connect_errno) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg" => "Server error"
    ]);
    exit;
}




$id = $koneksi->insert_id;
$sqlcek = "SELECT * from menu where id=$deleteId";
$deleteId = $_REQUEST['id'];

$ambildata = mysqli_fetch_array($query);
$cover = $ambildata['cover'];

// Nama file yang akan dihapus
$namaFile = "../img/$cover";

// Periksa apakah file ada sebelum dihapus
if (file_exists($namaFile)) {
    unlink($namaFile);
    $sqlDelete = "DELETE FROM menu WHERE id=$deleteId";
    mysqli_query($koneksi, $sqlDelete);
    $_SESSION['success'] = "File Berhasil Dihapus";
} else {
    $_SESSION['err']['database'] = "Menu gagal dihapus";
}




echo json_encode([
    "status" => "success",
    "msg" => "Process success",
    "data" => [
        "id"             => $id,
        "title"          => $title,
        "author"         => $author,
        "publisher"      => $publisher,
        "published_year" => $published_year,
        "isbn"           => $isbn,
        "cover"          => $coverNama
    ]
]);
