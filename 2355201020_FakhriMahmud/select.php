<?php
header("Content-Type: application/json; charset=UTF-8");


if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Method Salah !'
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

$q = "SELECT * FROM buku ORDER BY id DESC";
$cek = $koneksi->query($q);

if (!$cek) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg" => "Server error",
        "sql_error" => $koneksi->error
    ]);
    exit;
}

$data = [];
while ($row = $cek->fetch_assoc()) {
    $data[] = $row;
}

http_response_code(200);
echo json_encode([
    "status" => "success",
    "msg" => "Process success",
    "data" => $data
]);
?>
