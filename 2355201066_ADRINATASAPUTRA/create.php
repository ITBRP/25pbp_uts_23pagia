<?php
header("Content-Type: application/json; charset=UTF-8");


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Method Salah'
    ]);
    exit;
}

$errors = [];

$title = $author = $publisher = $published_year = $isbn = $fotoNama = null;


if (!isset($_POST['title'])) {
    $errors['title'] = "title tidak dikirim";
} else {
    $title = trim($_POST['title']);
    if ($title === "") {
        $errors['title'] = "title tidak boleh kosong";
    } elseif (strlen($title) < 3) {
        $errors['title'] = "Minimal 3 karakter";
    }
}


if (!isset($_POST['author'])) {
    $errors['author'] = "author tidak dikirim";
} else {
    $author = trim($_POST['author']);
    if ($author === "") {
        $errors['author'] = "author tidak boleh kosong";
    } elseif (preg_match('/[0-9]/', $author)) {
        $errors['author'] = "Tidak boleh mengandung angka";
    }
}


if (!isset($_POST['publisher'])) {
    $errors['publisher'] = "publisher tidak dikirim";
} else {
    $publisher = trim($_POST['publisher']);
    if ($publisher === "") {
        $errors['publisher'] = "publisher tidak boleh kosong";
    } elseif (strlen($publisher) > 100) {
        $errors['publisher'] = "Maksimal 100 karakter";
    }
}


if (!isset($_POST['published_year'])) {
    $errors['published_year'] = "published year tidak dikirim";
} else {
    $published_year = trim($_POST['published_year']);
    
    if ($published_year === "") {
        $errors['published_year'] = "published year tidak boleh kosong";
    } 
    // Memeriksa format 4 digit menggunakan variabel yang benar ($published_year)
    elseif (!preg_match('/^\d{4}$/', $published_year)){
        $errors['published_year'] = "format tahun tidak valid";
    }
}


if (!isset($_POST['isbn'])) {
    $errors['isbn'] = "isbn tidak dikirim";
} else {
    $isbn = trim($_POST['isbn']);
    if ($isbn === "") {
        $errors['isbn'] = "isbn tidak boleh kosong";
    } elseif (strlen($isbn) > 25) {
        
        $errors['isbn'] = "Format minimal 10 karakter, hanya angka dan -_-"; 
    }
}


$fileExt = null;

if (isset($_FILES['cover']) && $_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE) {
    $allowed = ['jpg', 'jpeg', 'png'];
    $fileName = $_FILES['cover']['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExt, $allowed)) {
        $errors['cover'] = "Format cover tidak valid (JPEG, jpg, jpeg, png)";
    }
}


if (count($errors) > 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Data error',
        'errors' => $errors
    ]);
    exit;
}


$koneksi = new mysqli("localhost", "root", "", "uts_pbp");

if ($koneksi->connect_errno) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "msg" => "Server error"
    ]);
    exit;
}

// Simpan cover jika ada upload
if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
    $fotoNama = md5(uniqid()) . "." . $fileExt;
    
    // Pastikan folder 'img/' ada dan bisa ditulisi (permissions)
    if (!move_uploaded_file($_FILES['cover']['tmp_name'], "img/" . $fotoNama)) {
         http_response_code(500);
         echo json_encode([
            "status" => "error",
            "msg" => "Gagal mengunggah file. Cek folder 'img' dan permissions."
         ]);
         exit;
    }
}

// query
$q = "INSERT INTO db_buku (title, author, publisher, published_year, isbn, cover)
      VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $koneksi->prepare($q);

$stmt->bind_param("ssssss", $title, $author, $publisher, $published_year, $isbn, $fotoNama);

// eksekusi dan cek error
if (!$stmt->execute()) {
    echo json_encode([
        "status" => "error",
        "msg" => "Query gagal",
        "sql_error" => $stmt->error,
        "query" => $q
    ]);
    exit;
}

// ambilIDterakhir
$id = $koneksi->insert_id;

// responsukses
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
        "cover" => $fotoNama
    ]
]);

$stmt->close();
$koneksi->close();
?>