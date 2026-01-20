<?php
header("Content-Type: application/json; charset=UTF-8");

// cek method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Method tidak diizinkan'
    ]);
    exit;
}

// cek payload
$errors = [];

// validasi title
if (!isset($_POST['title'])) {
    $errors['title'] = "Title tidak dikirim";
} else {
    if ($_POST['title'] == '') {
        $errors['title'] = "Title tidak boleh kosong";
    } else {
        if (strlen($_POST['title']) < 3) {
            $errors['title'] = "Minimal 3 karakter";
        }
    }
}

// validasi author
if (!isset($_POST['author'])) {
    $errors['author'] = "Author tidak dikirim";
} else {
    if ($_POST['author'] == '') {
        $errors['author'] = "Author tidak boleh kosong";
    } else {
        if (preg_match('/[0-9]/', $_POST['author'])) {
            $errors['author'] = "Tidak boleh mengandung angka";
        }
    }
}

// validasi publisher
if (!isset($_POST['publisher'])) {
    $errors['publisher'] = "Publisher tidak dikirim";
} else {
    if ($_POST['publisher'] == '') {
        $errors['publisher'] = "Publisher tidak boleh kosong";
    } else {
        if (strlen($_POST['publisher']) > 100) {
            $errors['publisher'] = "Maksimal 100 karakter";
        }
    }
}

// published_year
if (!isset($_POST['published_year'])) {
    $errors['published_year'] = "Published year tidak dikirim";
} else {
    if ($_POST['published_year'] == '') {
        $errors['published_year'] = "Published year tidak boleh kosong";
    } else {
        if (!preg_match('/^[0-9]{4}$/', $_POST['published_year'])) {
            $errors['published_year'] = "Format tahun tidak valid";
        }
    }
}

// validasi isbn 
if (!isset($_POST['isbn'])) {
    $errors['isbn'] = "ISBN tidak dikirim";
} else {
    if ($_POST['isbn'] == '') {
        $errors['isbn'] = "ISBN tidak boleh kosong";
    } else {
        if (!preg_match('/^[0-9\-]{10,}$/', $_POST['isbn'])) {
            $errors['isbn'] = "Format minimal 10 karakter, hanya angka dan '-'";
        }
    }
}

// validasi cover
$anyCover = false;
$coverName = '';
$fileExt = '';

if (isset($_FILES['cover'])) {
    if ($_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE) {

        $allowed = ['jpg', 'jpeg', 'png'];
        $fileName = $_FILES['cover']['name'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowed)) {
            $errors['cover'] = "Format file tidak valid (hanya JPEG, jpeg, jpg, png)";
        } else {
            $anyCover = true;
        }
    }
}

// jika ada error validasi
if (count($errors) > 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Data error',
        'errors' => $errors
    ]);
    exit;
}



// koneksi ke database
$koneksi = new mysqli('localhost', 'root', '', 'pbp_uts');

// CEK GAGAL KONEKSI
if ($koneksi->connect_error) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Server error '
    ]);
    exit;
}

// ambil data
$title = $_POST['title'];
$author = $_POST['author'];
$publisher = $_POST['publisher'];
$year = $_POST['published_year'];
$isbn = $_POST['isbn'];

// simpan file cover jika ada
if ($anyCover) {
    $coverName = md5(date('dmyhis')) . "." . $fileExt;
    move_uploaded_file($_FILES['cover']['tmp_name'], 'img/' . $coverName);
}

// query insert
$q = "INSERT INTO buku (title, author, publisher, published_year, isbn, cover)
      VALUES ('$title', '$author', '$publisher', $year, '$isbn', " . ($coverName ? "'$coverName'" : "NULL") . ")";

// JALANKAN QUERY SEKALI SAJA
$result = $koneksi->query($q);

// CEK ERROR QUERY
if (!$result) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Server error'
    ]);
    exit;
}

// respon sukses
http_response_code(201);
echo json_encode([
    'status' => 'success',
    'msg' => 'Process success',
    'data' => [
        'id' => $koneksi->insert_id,
        'title' => $title,
        'author' => $author,
        'publisher' => $publisher,
        'published_year' => (int)$year,
        'isbn' => $isbn,
        'cover' => $coverName
    ]
]);
