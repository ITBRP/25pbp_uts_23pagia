<?php

header("content-type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        "status" => "Data error",
        "msg" => "Method Salah",

    ]);
    exit;
}

// cek paylod
$errors = [];

if (!isset($_POST['title'])) {
    $errors['title'] = "Title tidak dikirim";
} else {
    if (strlen($_POST['title']) < 3) {
        $errors['title'] = "Minimal 3 karakter";
    }
}

if (!isset($_POST['author'])) {
    $errors['author'] = "Author tidak dikirim";
} else {
    if ($_POST['author'] == "") {
        $errors['author'] = "Author tidak boleh kosong";
    } else {
        if (preg_match('/[0-9]/', $_POST['author'])) {
            $errors['author'] = "Tidak boleh mengandung angka";
        }
    }
}

if (!isset($_POST['publisher'])) {
    $errors['publisher'] = "Publisher tidak dikirim";
} else {
    if (strlen($_POST['publisher'])  > 100) {
        $errors['publisher'] = "minimal 100 karakter";
    }
}

if (!isset($_POST['published_year'])) {
    $errors['published_year'] = "tahun tidak dikirim";
} else {
    if (!preg_match('/^[0-9]{4}$/', $_POST['published_year'])) {
        $errors['published_year'] = "Format tahun tidak valid";
    }
}

if (!isset($_POST['isbn'])) {
    $errors['isbn'] = "isbn tidak dikirim";
} else {
    if (!preg_match('/^[0-9\-]{10,}$/', $_POST['isbn'])) {
        $errors['isbn'] = "Format minimal 10 karakter, hanya angka dan '-'";
    }
}

// validasi cover
$anyCover = false;
$namaCover = '';
$fileExt = '';

if (isset($_FILES['cover'])) {

    if ($_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE) {

        $allowed = ['JPEG', 'jpg', 'jpeg', 'png'];
        $fileName = $_FILES['cover']['name'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowed)) {
            $errors['cover'] = "Format file tidak valid (hanya JPEG, jpeg, jpg, png)";
        } else {
            $anyCover = true;
        }
    }
}

if (count($errors) > 0) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "msg" => "data eror",
        "errors" => $errors
    ]);
    exit;
}

$koneksi = new mysqli("localhost", "root", "", "pbp_uts");
$title = $_POST['title'];
$author = $_POST['author'];
$publisher = $_POST['publisher'];
$published_year = $_POST['published_year'];
$isbn = $_POST['isbn'];
if ($anyCover) {
    $namaCover = md5(date('dmyhis')) . '.' . $fileExt;
    move_uploaded_file($_FILES['cover']['tmp_name'], 'img/' . $namaCover);
}

$q = "INSERT INTO databuku (title, author, publisher, published_year, isbn, cover) 
    VALUES ('$title', '$author', '$publisher', $published_year, '$isbn', " . ($namaCover ? "'$namaCover'" : "NULL") . ")";


$koneksi->query($q);
http_response_code(201);
echo json_encode([
    'status' => 'success',
    'msg' => 'Buku berhasil ditambahkan',
    'data' => [
        'id' => $koneksi->insert_id,
        'title' => $title,
        'author' => $author,
        'publisher' => $publisher,
        'published_year' => $published_year,
        'isbn' => $isbn,
        'cover' => $coverName
    ]
]);
