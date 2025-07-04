<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$socket = '/tmp/mysql.sock'; // Sesuaikan kalau kamu pakai socket
$conn = new mysqli("localhost", "root", "", "pelaporan_sampah", 3306, $socket);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["error" => "Koneksi gagal"]);
    exit;
}

$sql = "SELECT description, latitude, longitude FROM reports";
$result = $conn->query($sql);

$locations = [];
while ($row = $result->fetch_assoc()) {
    $locations[] = [
        "title" => $row["description"],
        "lat" => floatval($row["latitude"]),
        "lng" => floatval($row["longitude"])
    ];
}

echo json_encode($locations);
$conn->close();
