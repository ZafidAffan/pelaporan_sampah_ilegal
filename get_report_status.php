<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$socket = '/tmp/mysql.sock';
$conn = new mysqli('localhost', 'root', '', 'pelaporan_sampah', 3306, $socket);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Koneksi gagal']);
    exit;
}

$sql = "SELECT status, COUNT(*) AS jumlah FROM reports GROUP BY status";
$result = $conn->query($sql);

$data = ['dilaporkan' => 0, 'diproses' => 0, 'selesai' => 0];
while ($row = $result->fetch_assoc()) {
    if ($row['status'] === 'pending') {
        $data['dilaporkan'] = (int)$row['jumlah'];
    } elseif ($row['status'] === 'proses') {
        $data['diproses'] = (int)$row['jumlah'];
    } elseif ($row['status'] === 'selesai') {
        $data['selesai'] = (int)$row['jumlah'];
    }
}

echo json_encode($data);
$conn->close();
?>
