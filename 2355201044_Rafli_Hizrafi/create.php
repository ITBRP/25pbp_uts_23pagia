<?php
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Method Salah !'
    ]);
    exit;
}

$errors = [];

if (!isset($_POST['title'])) {
    $errors['title'] = "title tidak dikirim";
} else {
    if ($_POST['title'] == "") {
        $errors['title'] = "title tidak boleh kosong";
    } else {
        if (strlen($_POST['title']) < 3) {
            $errors['title'] = "title minimal 3 karakter";
        }
    }
}

if (!isset($_POST['author'])) {
    $errors['author'] = "author tidak dikirim";
} else {
    if ($_POST['author'] == "") {
        $errors['author'] = "author tidak boleh kosong";
    } else {
        if (preg_match('/[0-9]/', $_POST['author'])) {
            $errors['author'] = "author tidak boleh mengandung angka";
        }
    }
}


if (!isset($_POST['publisher'])) {
    $errors['publisher'] = "publisher tidak dikirim";
} else {
    if ($_POST['publisher'] == "") {
        $errors['publisher'] = "publisher tidak boleh kosong";
    } else {
        if (strlen($_POST['publisher']) > 100) {
            $errors['publisher'] = "publisher maksimal 100 karakter";
        }
    }
}

if (!isset($_POST['published_year'])) {
    $errors['published_year'] = "published_year tidak dikirim";
} else {
    if ($_POST['published_year'] == "") {
        $errors['published_year'] = "published_year tidak boleh kosong";
    } else {
        if (!preg_match('/^[0-9]{4}$/', $_POST['published_year'])) {
            $errors['published_year'] = "published_year harus 4 digit angka";
        }
    }
}

if (!isset($_POST['isbn'])) {
    $errors['isbn'] = "isbn tidak dikirim";
} else {
    if ($_POST['isbn'] == "") {
        $errors['isbn'] = "isbn tidak boleh kosong";
    } else {
        if (!preg_match('/^[0-9\-]{10,}$/', $_POST['isbn'])) {
            $errors['isbn'] = "isbn minimal 10 karakter dan hanya angka/dash";
        }
    }
}

$coverName = "";
$allowedExt = ['jpg', 'jpeg', 'png'];

if (isset($_FILES['cover'])) {

    // jika user memilih file
    if ($_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE) {

        $fileName = $_FILES['cover']['name'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt)) {
            $errors['cover'] = "File cover harus jpg/jpeg/png";
        } else {
            $coverName = md5(date('dmyhis')) . "." . $ext;
        }
    }
}

if (count($errors) > 0) {
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "msg" => "Data tidak valid",
        "errors" => $errors
    ]);
    exit;
}

mysqli_report(MYSQLI_REPORT_OFF);

$koneksi = new mysqli('localhost', 'root', '', 'databaru');

// Ambil data langsung tanpa trim/escape (sesuai permintaan)
$title = $_POST['title'];
$author = $_POST['author'];
$publisher = $_POST['publisher'];
$published_year = $_POST['published_year'];
$isbn = $_POST['isbn'];

// SIMPAN FILE COVER (JIKA ADA)
if ($coverName != "") {
    move_uploaded_file($_FILES['cover']['tmp_name'], "img/" . $coverName);
}

$q = "INSERT INTO perpustakaan(title, author, publisher, published_year, isbn, cover)
      VALUES (
        '$title',
        '$author',
        '$publisher',
        $published_year,
        '$isbn',
        " . ($coverName ? "'$coverName'" : "NULL") . "
      )";

if (!$koneksi->query($q)) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg" => "Server error"
    ]);
    exit;
}

http_response_code(201);
echo json_encode([
    "status" => "success",
    "msg" => "Process success",
    "data" => [
        "id" => $koneksi->insert_id,
        "title" => $title,
        "author" => $author,
        "publisher" => $publisher,
        "published_year" => $published_year,
        "isbn" => $isbn,
        "cover" => $coverName
    ]
]);
exit;