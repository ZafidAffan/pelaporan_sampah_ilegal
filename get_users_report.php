<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

// Gunakan socket sesuai konfigurasi server
$socket = '/tmp/mysql.sock';
$conn = new mysqli("localhost", "root", "", "pelaporan_sampah", 3306, $socket);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Koneksi gagal: " . $conn->connect_error]);
    exit;
}

$user_id = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;

// Query untuk mengambil data laporan berdasarkan user_id
$sql = "SELECT report_id, description, address, status, img_url, created_at FROM reports WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$reports = [];

while ($row = $result->fetch_assoc()) {
    $reports[] = [
        "report_id"   => $row["report_id"],
        "description" => $row["description"],
        "address"     => $row["address"],
        "status"      => $row["status"],
        "image_path"  => $row["img_url"],  // Tetap gunakan key 'image_path' untuk frontend konsistensi
        "created_at"  => $row["created_at"]
    ];
}

echo json_encode($reports);

$stmt->close();
$conn->close();
