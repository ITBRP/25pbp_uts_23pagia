<?php
header("Content-Type: application/json; charset=UTF-8");
$method = $_SERVER["REQUEST_METHOD"];
if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
    $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
}

if ($method !== 'PUT') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Method Salah!'
    ]);
    exit;
}

// cek payload
$errors = [];

if (!isset($_POST['title'])) {
    $errors['title'] = "title tidak di kirim";
} else {
    if ($_POST['title'] == '') {
        $errors['title'] = "title tidak boleh kosong";
    } else {
        if (strlen($_POST['title']) < 3) {
            $errors['title'] = "Minimal title harus 3 karakter";
        }
    }
}

if (!isset($_POST['author'])) {
    $errors['author'] = "author tidak di kirim";
} else {
    if ($_POST['author'] == '') {
        $errors['author'] = "author tidak boleh kosong";
    } else {
        if (preg_match('/[0-9]/', $_POST['author'])) {
            $errors['author'] = "Tidak boleh mengandung angka.";
        }
    }
}

if (!isset($_POST['publisher'])) {
    $errors['publisher'] = "publisher tidak di kirim";
} else {
    if ($_POST['publisher'] == '') {
        $errors['publisher'] = "publisher tidak boleh kosong";
    } else {
        if (strlen($_POST['publisher']) > 100) {
            $errors['publisher'] = "Maksimal publisher adalah 100 karakter";
        }
    }
}

if (!isset($_POST['published_year'])) {
    $errors['published_year'] = "published_year tidak di kirim";
} else {
    if ($_POST['published_year'] == '') {
        $errors['published_year'] = "published_year tidak boleh kosong";
    } else {
        if (!preg_match("/^[1-9][0-9]{3}$/", $_POST['published_year'])) {
            $errors['published_year'] = "Format tahun tidak valid";
        }
    }
}

if (!isset($_POST['isbn'])) {
    $errors['isbn'] = "isbn tidak di kirim";
} else {
    if ($_POST['isbn'] == '') {
        $errors['isbn'] = "isbn tidak boleh kosong";
    } else {
        if (!preg_match('/^[0-9-]+$/', $_POST['isbn'])) {
            $errors['isbn'] = "Format ISBN hanya boleh angka dan '-'";
        }else{

        }
    }
}

$anyPhoto = false;
$namaFile = null;
if (isset($_FILES['cover'])) {

    // User memilih file
    if ($_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE) {
        $allowed = ['jpg', 'jpeg', 'png', 'JPEG', 'JPG'];
        $fileName = $_FILES['cover']['name'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, $allowed)) {
            $errors['cover'] = "File harus jpg atau png";
        } else {
            $anyPhoto = true; // photo valid, siap disave
            $namaFile = md5(date('dmyhis')) . "." . $fileExt;
        }
    }
}

if (count($errors) > 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Data tidak valid',
        'errors' => $errors
    ]);
    exit();
}

// insert db
$koneksi = new mysqli('localhost', 'root', '', '25pbuts_pagia');
$title = $_POST['title'];
$author = $_POST['author'];
$publisher = $_POST['publisher'];
$published_year = $_POST['published_year'];
$isbn = $_POST['isbn'];
$id = $_REQUEST['id'];
if ($anyPhoto) {
    $q = "SELECT cover FROM books WHERE id=$id";
    $dtQ = mysqli_query($koneksi,$q);
    $coverLama = (mysqli_fetch_array($dtQ))['cover'];
    if(!is_null($coverLama)){
        unlink('img/'.$coverLama);
    }
    move_uploaded_file($_FILES['cover']['tmp_name'], 'img/' . $namaFile);
}

$q = "UPDATE books SET
        title = '$title',
        author = '$author',
        publisher = '$publisher',
        published_year = '$published_year',
        isbn = '$isbn',
        cover = " . ($namaFile ? "'$namaFile'" : "cover") . "
      WHERE id = $id";


$koneksi->query($q);

echo json_encode([
    'status' => 'success',
    'msg' => 'Proses berhasil',
    'data' => [
        'id' => $id,
        'title' => $title,
        'author' => $author,
        'publisher' => $publisher,
        'published_year' => $published_year,
        'isbn' => $isbn,
        'cover' => $namaFile
    ]
]);
