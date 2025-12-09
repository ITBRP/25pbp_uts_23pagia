<?php
header("Content-Type: application/json; charset=UTF-8");

// cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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
    echo json_encode(["status" => "error", "msg" => "ID tidak valid"]);
    exit;
}

// ambil data
$title = $_POST['title'] ?? '';
$author = $_POST['author'] ?? '';
$publisher = $_POST['publisher'] ?? '';
$published_year = $_POST['published_year'] ?? '';
$isbn = $_POST['isbn'] ?? '';

// cek payload
$errors = [];


// validasi title
if ($title == '') {
    $errors['title'] = "Title tidak dikirim";
} elseif (strlen($title) < 3) {
    $errors['title'] = "Minimal 3 karakter";
}

// validasi author
if ($author == '') {
    $errors['author'] = "Author tidak dikirim";
} elseif (preg_match('/[0-9]/', $author)) {
    $errors['author'] = "Tidak boleh mengandung angka";
}

// validasi publisher
if ($publisher == '') {
    $errors['publisher'] = "Publisher tidak dikirim";
} elseif (strlen($publisher) > 100) {
    $errors['publisher'] = "Maksimal 100 karakter";
}

// validasi published_year
if ($published_year == '') {
    $errors['published_year'] = "Published year tidak dikirim";
} elseif (!preg_match('/^[0-9]{4}$/', $published_year)) {
    $errors['published_year'] = "Format tahun tidak valid";
}

// validasi isbn
if ($isbn == '') {
    $errors['isbn'] = "ISBN tidak dikirim";
} elseif (!preg_match('/^[0-9\-]{10,}$/', $isbn)) {
    $errors['isbn'] = "Format minimal 10 karakter, hanya angka dan '-'";
}

// validasi cover
$anyCover = false;
$coverName = '';
$fileExt = '';

if (isset($_FILES['cover']) && $_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE) {
    $allowed = ['jpg', 'jpeg', 'png'];
    $fileExt = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));

    if (!in_array($fileExt, $allowed)) {
        $errors['cover'] = "Format file tidak valid (hanya JPEG, jpeg, jpg, png)";
    } else {
        $anyCover = true;
    }
}

// jika ada error
if ($errors) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "msg" => "Data error",
        "errors" => $errors
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
$cek = $koneksi->query("SELECT cover FROM buku WHERE id=$id");
if ($cek->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        "status" => "error",
        "msg" => "Data not found"
    ]);
    exit;
}
$dataLama = $cek->fetch_assoc();

// update cover
$coverSQL = "";
$coverFinal = $dataLama['cover'];

if ($anyCover) {
    $coverName = md5(time()) . "." . $fileExt;
    move_uploaded_file($_FILES['cover']['tmp_name'], "img/" . $coverName);

    if (!empty($dataLama['cover']) && file_exists("img/" . $dataLama['cover'])) {
        unlink("img/" . $dataLama['cover']);
    }

    $coverSQL = ", cover='$coverName'";
    $coverFinal = $coverName;
}

// update
$q = "UPDATE buku SET 
        title='$title',
        author='$author',
        publisher='$publisher',
        published_year=$published_year,
        isbn='$isbn'
        $coverSQL
      WHERE id=$id";

$result = $koneksi->query($q);

if (!$result) {
    http_response_code(500);
    echo json_encode(["status" => "error", "msg" => "Server error"]);
    exit;
}

// respon sukses
http_response_code(200);
echo json_encode([
    "status" => "success",
    "msg" => "Process success",
    "data" => [
        "id" => $id,
        "title" => $title,
        "author" => $author,
        "publisher" => $publisher,
        "published_year" => (int)$published_year,
        "isbn" => $isbn,
        "cover" => $coverFinal
    ]
]);
