<?php
header("Content-Type: application/json; charset=UTF-8");

// Method check
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Method Salah !'
    ]);
    exit;
}

// Pastikan ID dikirim
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'msg' => 'ID tidak dikirim'
    ]);
    exit;
}

$id = intval($_GET['id']);

// Koneksi database
mysqli_report(MYSQLI_REPORT_OFF);
$koneksi = new mysqli("localhost", "root", "", "databaru");

if ($koneksi->connect_errno) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg" => "Server error"
    ]);
    exit;
}

// Query Get by ID
$q = "SELECT * FROM perpustakaan WHERE id = $id LIMIT 1";
$result = $koneksi->query($q);

// Jika query error
if (!$result) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg" => "Server error"
    ]);
    exit;
}

// Jika data tidak ditemukan
if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        "status" => "error",
        "msg" => "Data not found"
    ]);
    exit;
}

// Ambil data
$row = $result->fetch_assoc();

// Response success
echo json_encode([
    "status" => "success",
    "msg" => "Process success",
    "data" => [
        "id" => (int)$row['id'],
        "title" => $row['title'],
        "author" => $row['author'],
        "publisher" => $row['publisher'],
        "published_year" => (int)$row['published_year'],
        "isbn" => $row['isbn'],
        "cover" => $row['cover']
    ]
]);
?>