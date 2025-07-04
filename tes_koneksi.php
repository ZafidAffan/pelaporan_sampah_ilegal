<?php
$mysqli = new mysqli("localhost", "root", "", "aplikasi_sampah", 3306, "/tmp/mysql.sock"); //karena di lapto aku port nya konflik antara brew sama GUI XAMPP

if ($mysqli->connect_errno) {
    echo "Gagal koneksi: " . $mysqli->connect_error;
} else {
    echo "Berhasil konek ke database!";
}
