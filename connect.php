<?php
header('Content-Type: text/html; charset=utf-8');

$servername = "127.0.0.1";
$username   = "root";
$password   = "";
$dbname     = "QuanLyCuaHangTivi";
$port       = 3307;

$conn = new mysqli($servername, $username, $password, $dbname, $port);

if ($conn->connect_error) {
    die("Kết nối cơ sở dữ liệu thất bại: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

//echo "Kết nối thành công!";
?>