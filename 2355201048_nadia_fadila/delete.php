<?php
header("Content-Type: application/json; charset=UTF-8");

// method check
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Method Salah !' // Method not allowed
    ]);
    exit;
}

// Get the ID from the query parameter
parse_str($_SERVER['QUERY_STRING'], $params);
$id = isset($params['id']) ? (int)$params['id'] : 0;

if ($id <= 0) {
    http_response_code(400); // Bad request if ID is not valid
    echo json_encode([
        'status' => 'error',
        'msg' => 'ID is required or invalid'
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

// query delete data based on ID
$q = "DELETE FROM buku WHERE id = ?";
$stmt = $koneksi->prepare($q);

if ($stmt === false) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Error preparing query'
    ]);
    exit;
}

$stmt->bind_param("i", $id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode([
        "status" => "success",
        "msg" => "Delete data success",
        "data" => [
            "id" => $id
        ]
    ]);
} else {
    http_response_code(404); // Not Found if no rows affected
    echo json_encode([
        "status" => "error",
        "msg" => "Data not found"
    ]);
}

$stmt->close();
$koneksi->close();
?>
