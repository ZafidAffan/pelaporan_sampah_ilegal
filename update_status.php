<?php
// ======= Koneksi Database =======
$socket = '/tmp/mysql.sock'; // Sesuaikan jika perlu (terutama untuk Mac)
$conn = new mysqli('localhost', 'root', '', 'pelaporan_sampah', 3306, $socket);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// ======= Validasi Data POST =======
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['report_id'], $_POST['new_status'])) {
    $report_id = intval($_POST['report_id']);
    $new_status = $_POST['new_status'];
    $admin_id = 1; // Bisa diganti dari session admin login jika tersedia
    $completed_at = date("Y-m-d H:i:s");

    // Validasi status yang diperbolehkan
    $valid_status = ['pending', 'proses', 'selesai'];
    if (!in_array($new_status, $valid_status)) {
        die("Status tidak valid.");
    }

    // Ambil data laporan berdasarkan ID
    $stmt = $conn->prepare("SELECT * FROM reports WHERE report_id = ?");
    $stmt->bind_param("i", $report_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Laporan tidak ditemukan.");
    }

    $report = $result->fetch_assoc();

    // Update status di tabel reports
    $update = $conn->prepare("UPDATE reports SET status = ? WHERE report_id = ?");
    $update->bind_param("si", $new_status, $report_id);
    $update->execute();

    // Jika status selesai, simpan ke riwayat_laporan
    if ($new_status === 'selesai') {
        $description = $report['description'] ?? null;
        $img_url = $report['img_url'] ?? null;
        $address = $report['address'] ?? null;

        $insert = $conn->prepare("
            INSERT INTO riwayat_laporan 
            (report_id, admin_id, status, completed_at, description, img_url, address)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $insert->bind_param("iisssss", 
            $report_id, $admin_id, $new_status, $completed_at,
            $description, $img_url, $address
        );
        $insert->execute();
    }

    // Redirect kembali ke dashboard
    header("Location: admin_dashboard.php");
    exit;
} else {
    echo "Permintaan tidak valid.";
}
?>
