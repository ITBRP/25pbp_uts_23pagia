<?php
header("Content-Type: application/json; charset=UTF-8");

// method check
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Method Salah !'
    ]);
    exit;
}

// koneksi
$koneksi = new mysqli("localhost", "root", "", "nadia");

if ($koneksi->connect_errno) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg" => "Server error"
    ]);
    exit;
}

// query select semua data
$q = "SELECT * FROM buku ORDER BY id DESC";
$result = $koneksi->query($q);

$data = [];

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {

    
        $data[] = [
            "id" => (int)$row['id'],
            "title" => $row['title'],
            "author" => $row['author'],
            "publisher" => $row['publisher'],
            "published_year" => (int)$row['published_year'],
            "isbn" => $row['isbn'],
            "cover" => $row['cover']
        ];
    }

    echo json_encode([
        "status" => "success",
        "msg" => "Process success",
        "data" => $data
    ]);
    exit;
} else {
    echo json_encode([
        "status" => "success",
        "msg" => "Tidak ada data",
        "data" => []
    ]);
    exit;
}
?>