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

$id = $_GET['id'];

if (!$id || !ctype_digit($id)) {
    http_response_code(404);
    echo json_encode([
        "status" => "error",
        "msg" => "Data not found"
    ]);
    exit;
}

$title          = $_POST['title'];
$author         = $_POST['author'];
$publisher      = $_POST['publisher'];
$published_year = $_POST['published_year'];
$isbn           = $_POST['isbn'];

$errors = [];
$pattern_combined = '/^(?=.{10,})[0-9-]+$/';



if (!isset($title)) {
    $errors['title'] = "Title tidak dikirim";
} else if ($title === "") {
    $errors['title'] = "Title tidak boleh kosong";
} else if (strlen($title) < 3) {
    $errors['title'] = "Minimal 3 karakter";
}

if (!isset($author)) {
    $errors['author'] = "Author tidak dikirim";
} else if ($author === "") {
    $errors['author'] = "Author tidak boleh kosong";
} else if (preg_match('/[0-9]/', $author)) {
    $errors['author'] = "Tidak boleh mengandung angka";
}

if (!isset($publisher)) {
    $errors['publisher'] = "Publisher tidak dikirim";
} else if ($publisher === "") {
    $errors['publisher'] = "Publisher tidak boleh kosong";
} else if (strlen($publisher) > 100) {
    $errors['publisher'] = "Maksimal 100 karakter";
}

if (!isset($published_year)) {
    $errors['published_year'] = "published_year tidak dikirim";
} else if ($published_year === "") {
    $errors['published_year'] = "published_year tidak boleh kosong";
} else if (!preg_match('/^[1-9][0-9]{3}$/', $published_year)) {
    $errors['published_year'] = "Format tahun tidak valid";
}

if (!isset($isbn)) {
    $errors['isbn'] = "isbn tidak dikirim";
} else if ($isbn === "") {
    $errors['isbn'] = "isbn tidak boleh kosong";
} else if (!preg_match($pattern_combined, $isbn)) {
    $errors['isbn'] = "Format minimal 10 karakter, hanya angka & -";
}


$coverBaru = null;
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
    http_response_code(400);
    echo json_encode([
        "status" => "error",
        "msg" => "Data error",
        "errors" => $errors
    ]);
    exit;
}



$koneksi = new mysqli("localhost", "root", "", "pbputs");

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

$row = $res->fetch_assoc();
$oldCover = $row['cover'];



if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {

    
    $coverBaru = md5(uniqid()) . "." . $fileExt;

    
    move_uploaded_file($_FILES['cover']['tmp_name'], "img/" . $coverBaru);

    
    if (!empty($oldCover)) {
        $filePath = "img/" . $oldCover;
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

} else {
   
    $coverBaru = $oldCover;
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
?>
