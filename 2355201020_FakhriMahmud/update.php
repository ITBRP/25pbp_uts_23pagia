<?php
header("Content-Type: application/json; charset=UTF-8");
if (isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
    $_SERVER['REQUEST_METHOD'] = 'PUT';
}

$id = $_GET['id'] ?? null;

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
$res = $koneksi->query("SELECT cover FROM buku WHERE id = '$id'");
if (!$res || $res->num_rows == 0) {
    http_response_code(404);
    echo json_encode([
        "status" => "error",
        "msg" => "Data not found"
    ]);
    exit;
}

$oldData = $res->fetch_assoc();
$oldCover = $oldData['cover'];


$title          = $_POST['title'] ?? null;
$author         = $_POST['author'] ?? null;
$publisher      = $_POST['publisher'] ?? null;
$published_year = $_POST['published_year'] ?? null;
$isbn           = $_POST['isbn'] ?? null;

$errors = [];

if (strlen($title) < 3) {
    $errors['title'] = "Minimal 3 karakter";
}
if (preg_match('/[0-9]/', $author)) {
    $errors['author'] = "Tidak boleh mengandung angka";
}
if (strlen($publisher) > 100) {
    $errors['publisher'] = "Maksimal 100 karakter";
}
if (!preg_match('/^[0-9]{4}$/', $published_year)) {
    $errors['published_year'] = "Format tahun tidak valid";
}
if (!preg_match('/^(?=.{10,})[0-9\-]+$/', $isbn)) {
    $errors['isbn'] = "Format minimal 10 karakter, hanya angka dan '-'";
}



$coverBaru = $oldCover;
$fileExt = null;

if (isset($_FILES['cover']) && $_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE) {

    if ($_FILES['cover']['error'] !== UPLOAD_ERR_OK) {
        $errors['cover'] = "Terjadi kesalahan saat upload file";
    } else {
        $allowed = ['jpg', 'jpeg', 'png'];
        $fileName = $_FILES['cover']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowed)) {
            $errors['cover'] = "Format file tidak valid (jpg, jpeg, png)";
        }
    }
}


if (!empty($errors)) {
    http_response_code(405);
    echo json_encode([
        "status" => "error",
        "msg" => "Data error",
        "errors" => $errors
    ]);
    exit;
}

if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {

    $coverBaru = md5(uniqid()) . "." . $fileExt;

    move_uploaded_file($_FILES['cover']['tmp_name'], "img/" . $coverBaru);

    // Hapus file lama
    if (!empty($oldCover)) {
        $oldPath = "img/" . $oldCover;
        if (file_exists($oldPath)) {
            unlink($oldPath);
        }
    }
}



$q = "UPDATE buku SET
        title='$title',
        author='$author',
        publisher='$publisher',
        published_year='$published_year',
        isbn='$isbn',
        cover='$coverBaru'
      WHERE id='$id'";

if (!$koneksi->query($q)) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg" => "Server error",
        "sql_error" => $koneksi->error
    ]);
    exit;
}



echo json_encode([
    "status" => "success",
    "msg" => "Process success",
    "data" => [
        "id" => $id,
        "title" => $title,
        "author" => $author,
        "publisher" => $publisher,
        "published_year" => $published_year,
        "isbn" => $isbn,
        "cover" => $coverBaru
    ]
]);
