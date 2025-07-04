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

$count_pending = $conn->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetch_row()[0];
$count_proses  = $conn->query("SELECT COUNT(*) FROM reports WHERE status = 'proses'")->fetch_row()[0];
$count_selesai = $conn->query("SELECT COUNT(*) FROM reports WHERE status = 'selesai'")->fetch_row()[0];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --green: #347433;
            --yellow: #f0ad4e;
            --blue: #5bc0de;
            --success: #5cb85c;
            --background: #F5F0CD;
        }
        * {
            box-sizing: border-box;
        }
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background-color: var(--background);
        }
        nav {
            background-color: var(--green);
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px 24px;
            border-bottom-left-radius: 12px;
            border-bottom-right-radius: 12px;
        }
        .nav-links {
            display: flex;
            gap: 16px;
            align-items: center;
        }
        nav a {
            color: white;
            text-decoration: none;
            font-weight: bold;
            padding: 10px 16px;
            border-radius: 8px;
            transition: background 0.3s ease;
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
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
        }
        h1 {
            color: var(--green);
            font-size: 28px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .cards {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }
        .card {
            background-color: white;
            padding: 30px 24px;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            text-align: center;
            width: 240px;
            transition: transform 0.2s ease;
        }
        .card:hover {
            transform: translateY(-4px);
        }
        .card i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        .card h2 {
            margin: 0;
            font-size: 36px;
            color: var(--green);
        }
        .card p {
            margin-top: 12px;
            font-weight: bold;
            font-size: 16px;
        }
        .pending { color: var(--yellow); }
        .proses { color: var(--blue); }
        .selesai { color: var(--success); }

        /* Chart Container */
        .chart-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            padding: 24px;
            margin-top: 40px;
            width: 90%;
            max-width: 500px;
        }
        .chart-container h3 {
            text-align: center;
            color: var(--green);
            margin-bottom: 20px;
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
    <h1><i class="fas fa-house-user"></i> Selamat Datang, <?= htmlspecialchars($admin_name) ?>!</h1>

    <div class="cards">
        <div class="card pending">
            <i class="fas fa-envelope-open-text"></i>
            <h2><?= $count_pending ?></h2>
            <p>Di Laporkan</p>
        </div>
        <div class="card proses">
            <i class="fas fa-spinner fa-spin"></i>
            <h2><?= $count_proses ?></h2>
            <p>Di Proses</p>
        </div>
        <div class="card selesai">
            <i class="fas fa-check-circle"></i>
            <h2><?= $count_selesai ?></h2>
            <p>Selesai</p>
        </div>
    </div>

    <!-- Diagram Lingkaran -->
    <div class="chart-container">
        <h3><i class="fas fa-chart-pie"></i> Diagram Status Laporan</h3>
        <canvas id="statusChart"></canvas>
    </div>
</div>

<!-- Chart.js Pie Chart -->
<script>
const ctx = document.getElementById('statusChart').getContext('2d');
const statusChart = new Chart(ctx, {
    type: 'pie',
    data: {
        labels: ['Di Laporkan', 'Di Proses', 'Selesai'],
        datasets: [{
            label: 'Status Laporan',
            data: [<?= $count_pending ?>, <?= $count_proses ?>, <?= $count_selesai ?>],
            backgroundColor: [
                '#f0ad4e', // Di Laporkan
                '#5bc0de', // Di Proses
                '#5cb85c'  // Selesai
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: {
                        size: 14
                    }
                }
            }
        }
    }
});
</script>

</body>
</html>
