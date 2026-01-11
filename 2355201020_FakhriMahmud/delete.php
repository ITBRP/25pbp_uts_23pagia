<?php
header("Content-Type: application/json; charset=UTF-8");


if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "msg" => "Method Salah !"
    ]);
    exit;
}


$id = $_GET['id'];

// Validasi ID
if (!$id || !ctype_digit($id)) {
    http_response_code(404);
    echo json_encode([
        "status" => "error",
        "msg" => "Data not found"
    ]);
    exit;
}


$koneksi = @new mysqli("localhost", "root", "", "pbputs");

if ($koneksi->connect_errno) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg" => "Server error"
    ]);
    exit;
}

$sqlcek = "SELECT * FROM buku WHERE id = $id";
$query  = mysqli_query($koneksi, $sqlcek);
$data   = mysqli_fetch_assoc($query);

if (!$data) {
    http_response_code(404);
    echo json_encode([
        "status" => "error",
        "msg" => "Data not found"
    ]);
    exit;
}

if (!empty($data['cover'])) {
    $filePath = "img/" . $data['cover'];

    if (file_exists($filePath)) {
        unlink($filePath);
    }
}


$sqlDelete = "DELETE FROM buku WHERE id = $id";
$delete = mysqli_query($koneksi, $sqlDelete);


echo json_encode([
    "status" => "success",
    "msg"    => "Delete data success",
    "data"   => [
        "id" => $id,
        
    ]
]);
?>
