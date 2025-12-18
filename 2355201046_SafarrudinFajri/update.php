<?php
header("Content-Type: application/json; charset=UTF-8");


$_METHOD = $_SERVER['REQUEST_METHOD'];

if ($_METHOD === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'PUT') {
    $_METHOD = "PUT";
}


if ($_METHOD !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Method Salah !'
    ]);
    exit;
}

// CEK PARAMETER ID

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'msg' => 'ID tidak dikirim'
    ]);
    exit;
}

$id = intval($_GET['id']);



$input = $_POST;
$files = $_FILES;

$errors = [];


// VALIDASI


// title
if (!isset($input['title']) || trim($input['title']) === "") {
    $errors['title'] = "Minimal 3 karakter";
} else {
    $title = trim($input['title']);
    if (strlen($title) < 3) $errors['title'] = "Minimal 3 karakter";
}

// author
if (!isset($input["author"])) {
    $errors["author"] = "Author tidak dikirim";
} else {
    $author = trim($input["author"]);
    if ($author === "") $errors["author"] = "Tidak boleh kosong";
    elseif (preg_match('/[0-9]/', $author)) $errors["author"] = "Tidak boleh mengandung angka";
}

// publisher
if (!isset($input["publisher"])) {
    $errors["publisher"] = "Publisher tidak dikirim";
} else {
    $publisher = trim($input["publisher"]);
    if ($publisher === "") $errors["publisher"] = "Tidak boleh kosong";
    elseif (strlen($publisher) > 100) $errors["publisher"] = "Maksimal 100 karakter";
}

// published_year
if (!isset($input["published_year"])) {
    $errors["published_year"] = "Tahun tidak dikirim";
} else {
    $published_year = trim($input["published_year"]);
    if (!preg_match('/^[0-9]{4}$/', $published_year))
        $errors["published_year"] = "Format tahun tidak valid";
}

// isbn
if (!isset($input["isbn"])) {
    $errors["isbn"] = "ISBN tidak dikirim";
} else {
    $isbn = trim($input["isbn"]);
    if ($isbn === "") $errors["isbn"] = "Tidak boleh kosong";
    elseif (strlen($isbn) < 10) $errors["isbn"] = "Format minimal 10 karakter, hanya angka & '-'";
}

// cover optional
$coverNama = null;
$fileExt = null;

if (isset($files['cover']) && $files['cover']['error'] !== UPLOAD_ERR_NO_FILE) {
    $allowed = ['jpg', 'jpeg', 'png'];
    $fileName = $files['cover']['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExt, $allowed)) {
        $errors['cover'] = "Format file tidak valid (hanya JPEG, jpeg, jpg, png)";
    }
}

// Jika ada error validasi
if (count($errors) > 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Data error',
        'errors' => $errors
    ]);
    exit;
}



// KONEKSI DB

$koneksi = new mysqli("localhost", "root", "", "uts");

if ($koneksi->connect_errno) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg" => "Server error"
    ]);
    exit;
}


// Cek apakah ID ada
$cek = $koneksi->query("SELECT * FROM data_buku WHERE id=$id LIMIT 1");
if ($cek->num_rows === 0) {
    http_response_code(404);
    echo json_encode([
        "status" => "error",
        "msg" => "Data not found"
    ]);
    exit;
}

$oldData = $cek->fetch_assoc();
$oldCover = $oldData['cover'];



// PROSES COVER 

if (isset($files['cover']) && $files['cover']['error'] === UPLOAD_ERR_OK) {
    $coverNama = md5(uniqid()) . "." . $fileExt;
    move_uploaded_file($files['cover']['tmp_name'], "img/" . $coverNama);

    // hapus file lama
    if ($oldCover && file_exists("img/" . $oldCover)) {
        unlink("img/" . $oldCover);
    }
} else {
    $coverNama = $oldCover;
}



// UPDATE DB

$q = "UPDATE data_buku SET 
        title='$title',
        author='$author',
        publisher='$publisher',
        published_year='$published_year',
        isbn='$isbn',
        cover='$coverNama'
      WHERE id=$id";

if (!$koneksi->query($q)) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg" => "Server error"
    ]);
    exit;
}


// SUCCESS

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
        "cover" => $coverNama
    ]
]);
