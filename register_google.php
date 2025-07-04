<?php
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    exit;
}

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$required = ['email', 'password', 'displayName'];
foreach ($required as $k) {
    if (!isset($data[$k]) || trim($data[$k]) === '') {
        http_response_code(400);
        echo json_encode(['error' => "Kolom $k wajib diisi"]);
        exit;
    }
}

$email    = trim($data['email']);
$password = trim($data['password']);
$nama     = trim($data['displayName']);
$telepon  = isset($data['phone']) ? trim($data['phone']) : null;

$socket = '/tmp/mysql.sock';
$conn = new mysqli('localhost', 'root', '', 'pelaporan_sampah', 3306, $socket);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal konek ke database']);
    exit;
}

$cek = $conn->prepare("SELECT user_id FROM user WHERE email = ?");
$cek->bind_param("s", $email);
$cek->execute();
$cek->store_result();

if ($cek->num_rows > 0) {
    echo json_encode(['message' => 'User sudah ada']);
    exit;
}
$cek->close();

$sql = "INSERT INTO pengguna (email, password, name, phone) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$hashed = password_hash($password, PASSWORD_BCRYPT);
$stmt->bind_param("ssss", $email, $hashed, $nama, $telepon);

if ($stmt->execute()) {
    echo json_encode(['message' => 'User Google berhasil disimpan', 'userId' => $stmt->insert_id]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal menyimpan data: '.$stmt->error]);
}

$stmt->close();
$conn->close();
?>
