<?php
header("Content-Type: application/json; charset=UTF-8");
//method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Method Salah !'
    ]);
    exit;
}

//cek payload
$errors = [];
//cek validasi

//title
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

//author
if (!isset($_POST["author"])) {
    $errors["author"] = "Author tidak dikirim";
} else {
    $author = trim($_POST["author"]);
    if ($author === "") {
        $errors["author"] = "Author tidak boleh kosong";
    } elseif (preg_match('/[0-9]/', $author)) {
        $errors["author"] = "Tidak boleh mengandung angka";
    }
}


//publisher
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


//published_year
if (!isset($_POST["published_year"])) {
    $errors["published_year"] = "Tahun tidak dikirim";
} else {
    $published_year = trim($_POST["published_year"]);
    if (!preg_match('/^[0-9]{4}$/', $published_year)) {
        $errors["published_year"] = "Format tahun tidak valid";
    }
}

//isbn
if (!isset($_POST['isbn'])) {
    $errors['isbn'] = "ISBN tidak dikirim";
} else {
    $isbn = trim($_POST['isbn']);
    if ($isbn === "") {
        $errors['isbn'] = "ISBN tidak boleh kosong";
    } elseif (strlen($isbn) < 10) {
        $errors['isbn'] = "Format ISBN minimal 10 karakter, hanya angka dan '-'";
    }
}

//cover
$coverNama = null;
$fileExt = null;

if (isset($_FILES['cover']) && $_FILES['cover']['error'] !== UPLOAD_ERR_NO_FILE) {
    $allowed = ['jpg', 'jpeg', 'png'];
    $fileName = $_FILES['cover']['name'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExt, $allowed)) {
        $errors['cover'] = "Format file tidak valid (JPEG, jpg, jpeg, png)";
    }
}


// Jika ada error → stop
if (count($errors) > 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'msg' => 'Data error',
        'errors' => $errors
    ]);
    exit;
}



//koneksi database
$koneksi = new mysqli("localhost", "root", "", "uts");

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
    $coverNama = md5(uniqid()) . "." . $fileExt;
    move_uploaded_file($_FILES['cover']['tmp_name'], "img/" . $coverNama);
}

// query
$q = "INSERT INTO data_buku (title, author, publisher, published_year, isbn, cover)
      VALUES ('$title', '$author', '$publisher', '$published_year', '$isbn', " .
      ($coverNama ? "'$coverNama'" : "NULL") . ")";

// Eksekusi dan cek error
if (!$koneksi->query($q)) {
    echo json_encode([
        "status" => "error",
        "msg" => "Query gagal",
        "sql_error" => $koneksi->error,
        "query" => $q
    ]);
    exit;
}

// Ambil ID terakhir
$id = $koneksi->insert_id;

// Response success
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
?>
