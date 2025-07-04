<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$admin_name = $_SESSION['admin_name'] ?? 'Admin';

$socket = '/tmp/mysql.sock';
$conn = new mysqli('localhost', 'root', '', 'pelaporan_sampah', 3306, $socket);
$currentPage = basename($_SERVER['PHP_SELF']);

$query = "
    SELECT r.*, a.name AS admin_name 
    FROM riwayat_laporan r
    LEFT JOIN admin a ON r.admin_id = a.admin_id
    ORDER BY r.completed_at DESC
";
$result = $conn->query($query);

function safe($value) {
    return htmlspecialchars($value ?? '');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Riwayat Laporan</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #F5F0CD;
            margin: 0;
        }
        nav {
            background-color: #347433;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 24px;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }
        .nav-links {
            display: flex;
            gap: 14px;
            align-items: center;
        }
        nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 10px 16px;
            border-radius: 8px;
        }
        nav a:hover,
        nav a.active {
            background-color: #285f28;
        }
        .logout-btn {
            background-color: #b72e2e;
            padding: 8px 14px;
            border-radius: 8px;
        }
        .welcome {
            color: white;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .container {
            padding: 30px 20px;
        }
        h1 {
            color: #347433;
            text-align: center;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 10px;
            border-bottom: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
        }
        th {
            background-color: #347433;
            color: white;
        }
        td img {
            max-width: 80px;
            height: auto;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<nav>
    <div class="nav-links">
        <a href="admin_dashboard.php" class="<?= $currentPage == 'admin_dashboard.php' ? 'active' : '' ?>"><i class="fas fa-chart-pie"></i> Dashboard</a>
        <a href="admin_laporan.php" class="<?= $currentPage == 'admin_laporan.php' ? 'active' : '' ?>"><i class="fas fa-list"></i> Daftar Laporan</a>
        <a href="admin_riwayat_laporan.php" class="<?= $currentPage == 'admin_riwayat_laporan.php' ? 'active' : '' ?>"><i class="fas fa-clock-rotate-left"></i> Riwayat</a>
        <a href="admin_peta_laporan.php" class="<?= $currentPage == 'admin_peta_laporan.php' ? 'active' : '' ?>"><i class="fas fa-map-location-dot"></i> Peta</a>
    </div>
    <div class="nav-links">
        <span class="welcome"><i class="fas fa-user-shield"></i> <?= htmlspecialchars($admin_name) ?></span>
        <a href="admin_logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</nav>

<div class="container">
    <h1><i class="fas fa-clock-rotate-left"></i> Riwayat Laporan Selesai</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Deskripsi</th>
                <th>Gambar</th>
                <th>Alamat</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Admin</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()) : ?>
            <tr>
                <td><?= $row['report_id'] ?></td>
                <td><?= safe($row['description']) ?></td>
                <td><?= $row['img_url'] ? "<img src='upload/{$row['img_url']}' alt=''>" : "Tidak ada" ?></td>
                <td><?= safe($row['address']) ?></td>
                <td><i class="fas fa-check-circle" style="color: #5cb85c;"></i> <?= ucfirst($row['status']) ?></td>
                <td><?= $row['completed_at'] ?></td>
                <td><?= safe($row['admin_name']) ?: '-' ?></td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>
