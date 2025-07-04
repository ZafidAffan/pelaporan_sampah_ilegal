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

$query = "SELECT latitude, longitude, description, address, status FROM reports";
$result = $conn->query($query);
$markers = [];
while ($row = $result->fetch_assoc()) {
    $markers[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Peta Penyebaran Laporan</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
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
            padding: 20px;
            text-align: center;
        }
        h1 {
            color: #347433;
        }
        #map {
            height: 600px;
            border: 2px solid #347433;
            border-radius: 12px;
            margin: auto;
            width: 90%;
            max-width: 1000px;
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
    <h1><i class="fas fa-map-location-dot"></i> Peta Penyebaran Laporan</h1>
    <div id="map"></div>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    var map = L.map('map').setView([-7.797068, 110.370529], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    var markers = <?= json_encode($markers) ?>;
    markers.forEach(function(marker) {
        let iconColor = 'gray';
        if (marker.status === 'pending') iconColor = 'orange';
        else if (marker.status === 'proses') iconColor = 'blue';
        else if (marker.status === 'selesai') iconColor = 'green';

        const customIcon = L.divIcon({
            className: 'custom-div-icon',
            html: `<div style="background-color:${iconColor};border-radius:8px;padding:6px 10px;color:white;font-size:12px;font-weight:bold;">${marker.status}</div>`,
            iconSize: [60, 30],
            iconAnchor: [30, 15]
        });

        L.marker([marker.latitude, marker.longitude], { icon: customIcon }).addTo(map)
        .bindPopup(`<b>Status:</b> ${marker.status}<br><b>Deskripsi:</b> ${marker.description}<br><b>Alamat:</b> ${marker.address}`);
    });
</script>

</body>
</html>
