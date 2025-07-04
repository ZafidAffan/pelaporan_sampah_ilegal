<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json");

$mysqli = new mysqli("localhost", "root", "", "aplikasi_sampah");

if ($mysqli->connect_error) {
    echo json_encode(["error" => "Koneksi gagal: " . $mysqli->connect_error]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data['user_id'] ?? null;
$name    = $data['name'] ?? null;
$email   = $data['email'] ?? null;
$phone   = $data['phone'] ?? null;

if (!$user_id || !$name || !$email || !$phone) {
    echo json_encode(["error" => "Data tidak lengkap"]);
    exit;
}

$stmt = $mysqli->prepare("UPDATE pengguna SET name = ?, email = ?, phone = ? WHERE user_id = ?");
$stmt->bind_param("sssi", $name, $email, $phone, $user_id);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Akun berhasil diperbarui"]);
} else {
    echo json_encode(["error" => "Gagal memperbarui akun"]);
}

$stmt->close();
$mysqli->close();
