<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Content-Type: application/json");

// Koneksi database
$socket = '/tmp/mysql.sock';
$conn = new mysqli("localhost", "root", "", "pelaporan_sampah", 3306, $socket);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Koneksi gagal: " . $conn->connect_error]);
    exit;
}

// Ambil user_id dari parameter GET
$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

if ($user_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "User ID tidak valid"]);
    exit;
}

// Query data user
$sql = "SELECT user_id, name, email, phone, created_at FROM user WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["error" => "User tidak ditemukan"]);
} else {
    $user = $result->fetch_assoc();
    echo json_encode($user);
}

$stmt->close();
$conn->close();
