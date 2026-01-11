<?php
header("Content-Type: application/json; charset=UTF-8");

// Hanya izinkan PUT
if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "msg" => "Method Not Allowed"
    ]);
    exit;
}

// Ambil ID dari query string
$id = $_GET['id'] ?? null;
if (!$id || !is_numeric($id)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "msg" => "Data error",
        "errors" => ["id" => "ID tidak valid"]
    ]);
    exit;
}

// Baca JSON dari body
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

// JSON invalid
if ($data === null) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "msg" => "Data error",
        "errors" => ["json" => "Format JSON tidak valid"]
    ]);
    exit;
}

// Koneksi
$koneksi = new mysqli("localhost", "root", "", "pbp_uts");
if ($koneksi->connect_errno) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg" => "Server error"
    ]);
    exit;
}

$errors = [];

// VALIDASI
// TITLE minimal 3 karakter
if (empty($data['title'])) {
    $errors['title'] = "Minimal 3 karakter";
} else if (strlen($data['title']) < 3) {
    $errors['title'] = "Minimal 3 karakter";
}

// AUTHOR tidak boleh angka
if (empty($data['author'])) {
    $errors['author'] = "Tidak boleh mengandung angka";
} else if (preg_match('/[0-9]/', $data['author'])) {
    $errors['author'] = "Tidak boleh mengandung angka";
}

// PUBLISHER maks 100
if (empty($data['publisher'])) {
    $errors['publisher'] = "Maksimal 100 karakter";
} else if (strlen($data['publisher']) > 100) {
    $errors['publisher'] = "Maksimal 100 karakter";
}

// PUBLISHED YEAR format tahun (4 digit)
if (empty($data['published_year']) || !preg_match('/^\d{4}$/', $data['published_year'])) {
    $errors['published_year'] = "Format tahun tidak valid";
}

// ISBN minimal 10, hanya angka dan -
if (empty($data['isbn'])) {
    $errors['isbn'] = "Format minimal 10 karakter, hanya angka dan '-'";
} else if (!preg_match('/^[0-9\-]{10,}$/', $data['isbn'])) {
    $errors['isbn'] = "Format minimal 10 karakter, hanya angka dan '-'";
}

// COVER optional
if (!empty($data['cover'])) {
    if (!preg_match('/\.(jpeg|jpg|png)$/i', $data['cover'])) {
        $errors['cover'] = "Format file tidak valid (hanya JPEG, jpeg, jpg, png)";
    }
}

// Jika ada error validasi
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "msg" => "Data error",
        "errors" => $errors
    ]);
    exit;
}

// Cek apakah data exist
$cek = $koneksi->query("SELECT * FROM data_buku WHERE id=$id");
if (!$cek || $cek->num_rows == 0) {
    http_response_code(404);
    echo json_encode([
        "status" => "error",
        "msg" => "Data not found"
    ]);
    exit;
}

// UPDATE DATA
$q = "
    UPDATE data_buku SET
        title = '".$koneksi->real_escape_string($data['title'])."',
        author = '".$koneksi->real_escape_string($data['author'])."',
        publisher = '".$koneksi->real_escape_string($data['publisher'])."',
        published_year = '".$koneksi->real_escape_string($data['published_year'])."',
        isbn = '".$koneksi->real_escape_string($data['isbn'])."',
        cover = '".($data['cover'] ?? "")."'
    WHERE id = $id
";

if (!$koneksi->query($q)) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg" => "Server error"
    ]);
    exit;
}

// RESPONSE SUCCESS
http_response_code(200);

echo json_encode([
    "status" => "success",
    "msg" => "Process success",
    "data" => [
        "id" => (int) $id,
        "title" => $data['title'],
        "author" => $data['author'],
        "publisher" => $data['publisher'],
        "published_year" => (int)$data['published_year'],
        "isbn" => $data['isbn'],
        "cover" => $data['cover'] ?? null
    ]
]);