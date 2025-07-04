<?php
// ------------------------ CORS ------------------------
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: *");
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    exit;
}

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// ------------------------ Ambil & Validasi Input ------------------------
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$required = ['email', 'password'];
foreach ($required as $key) {
    if (!isset($data[$key]) || trim($data[$key]) === '') {
        http_response_code(400);
        echo json_encode(['error' => "Kolom $key wajib diisi"]);
        exit;
    }
}

$email = trim($data['email']);
$password = trim($data['password']);

// ------------------------ Koneksi MySQL ------------------------
$socket = '/tmp/mysql.sock'; // Sesuaikan jika perlu
$conn = new mysqli('localhost', 'root', '', 'pelaporan_sampah', 3306, $socket);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal koneksi ke database']);
    exit;
}

// ------------------------ Query & Autentikasi ------------------------
$sql = "SELECT user_id, password, name FROM user WHERE email = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'SQL Error: ' . $conn->error]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(401);
    echo json_encode(['error' => 'Email tidak ditemukan']);
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Password salah']);
    exit;
}

// ------------------------ Berhasil ------------------------
echo json_encode([
    'message' => 'Login berhasil',
    'userId' => $user['user_id'],
    'displayName' => $user['name']
]);

$stmt->close();
$conn->close();
?>
