<?
header("content-type: application/json; charset=UTF-8");
//get
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(404);
    echo json_encode([
        "status" => "error",
        "msg" => "Data not found",
    ]);
    exit;
}

$koneksi = new mysqli("localhost", "root", "", "pbp_uts");
if ($koneksi->connect_error) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg" => "Server error",
    ]);
    exit;
}
$sql = "SELECT * FROM databuku";
$result = $koneksi->query($sql);
$books = [];
