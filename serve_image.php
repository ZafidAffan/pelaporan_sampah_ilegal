<?php
// Izinkan CORS untuk akses dari web
header("Access-Control-Allow-Origin: *");

$filename = $_GET['file'] ?? '';

$path = __DIR__ . "/upload/" . basename($filename);

if (file_exists($path)) {
    $mime = mime_content_type($path);
    header("Content-Type: $mime");
    readfile($path);
} else {
    http_response_code(404);
    echo "File not found";
}
