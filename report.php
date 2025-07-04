<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json");

// Koneksi ke database
$socket = '/tmp/mysql.sock';
$conn = new mysqli('localhost', 'root', '', 'pelaporan_sampah', 3306, $socket);

if ($conn->connect_error) {
    die(json_encode(["error" => "Koneksi gagal: " . $conn->connect_error]));
}

// Cek apakah file gambar dikirim
if (!isset($_FILES['image']) || $_FILES['image']['error'] != UPLOAD_ERR_OK) {
    echo json_encode(["error" => "Gambar tidak ditemukan atau gagal diunggah"]);
    exit;
}

// Pastikan folder upload/ tersedia
$uploadDir = "upload/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true); // buat folder jika belum ada
}

// Simpan gambar
$imageName = time() . '_' . basename($_FILES["image"]["name"]);
$targetFile = $uploadDir . $imageName;

if (!move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile)) {
    echo json_encode(["error" => "Gagal menyimpan gambar"]);
    exit;
}

// Ambil data dari Flutter
$user_id     = $_POST['user_id'] ?? null;
$description = $_POST['description'] ?? null;
$latitude    = $_POST['latitude'] ?? null;
$longitude   = $_POST['longitude'] ?? null;
$address     = $_POST['address'] ?? null;
$status      = "pending";
$created_at  = date("Y-m-d H:i:s");

// Validasi data
if (!$user_id || !$description || !$latitude || !$longitude || !$address) {
    echo json_encode(["error" => "Data tidak lengkap"]);
    exit;
}

// Simpan ke database
$stmt = $conn->prepare("INSERT INTO reports (user_id, description, img_url, latitude, longitude, address, status, created_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("issddsss", $user_id, $description, $imageName, $latitude, $longitude, $address, $status, $created_at);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Laporan berhasil dikirim"]);
} else {
    echo json_encode(["error" => "Gagal menyimpan laporan: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
