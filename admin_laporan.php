<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'] ?? 'Admin';

$socket = '/tmp/mysql.sock';
$conn = new mysqli('localhost', 'root', '', 'pelaporan_sampah', 3306, $socket);
$currentPage = basename($_SERVER['PHP_SELF']);

$result = $conn->query("SELECT * FROM reports ORDER BY report_id DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daftar Laporan</title>
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
            padding: 12px 20px;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }
        .nav-links {
            display: flex;
            gap: 12px;
            align-items: center;
        }
        nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 8px 16px;
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
            margin-right: 16px;
        }
        .container {
            padding: 20px;
        }
        h1 {
            color: #347433;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-radius: 12px;
            overflow: hidden;
        }
        th {
            background-color: #347433;
            color: white;
            padding: 12px;
            text-align: left;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            vertical-align: top;
        }
        img {
            max-width: 100px;
            border-radius: 8px;
        }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-proses {
            background-color: #f0ad4e;
            color: white;
        }
        .btn-selesai {
            background-color: #5cb85c;
            color: white;
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
    <h1>Daftar Laporan Pengguna</h1>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Deskripsi</th>
                <th>Gambar</th>
                <th>Alamat</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $result->fetch_assoc()) : ?>
            <tr>
                <td><?= $row['report_id'] ?></td>
                <td><?= htmlspecialchars($row['description']) ?></td>
                <td><?= $row['img_url'] ? "<img src='upload/{$row['img_url']}' alt=''>" : "Tidak ada" ?></td>
                <td><?= htmlspecialchars($row['address']) ?></td>
                <td><?= ucfirst($row['status']) ?></td>
                <td>
                    <?php if ($row['status'] === 'pending') : ?>
                        <form method="POST" action="update_status.php" onsubmit="return confirm('Ubah status menjadi PROSES?');">
                            <input type="hidden" name="report_id" value="<?= $row['report_id'] ?>">
                            <input type="hidden" name="new_status" value="proses">
                            <button class="btn btn-proses">Proses</button>
                        </form>
                    <?php elseif ($row['status'] === 'proses') : ?>
                        <form method="POST" action="update_status.php" onsubmit="return confirm('Ubah status menjadi SELESAI?');">
                            <input type="hidden" name="report_id" value="<?= $row['report_id'] ?>">
                            <input type="hidden" name="new_status" value="selesai">
                            <button class="btn btn-selesai">Selesai</button>
                        </form>
                    <?php else : ?>
                        <span style="color: green; font-weight: bold;">Selesai</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>
