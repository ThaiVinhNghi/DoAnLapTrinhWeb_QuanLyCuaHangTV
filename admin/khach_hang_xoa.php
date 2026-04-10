<?php
session_start();
require_once '../thu_vien/connect.php';

$id_kh = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_kh > 0) {
    $sql = "DELETE FROM khachhang WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_kh);
    
    if ($stmt->execute()) {
        echo "<script>alert('Đã xóa khách hàng thành công!'); window.location.href='khach_hang.php';</script>";
    } else {
        echo "<script>alert('Không thể xóa! Khách hàng này đang có hóa đơn đặt mua trong hệ thống.'); window.location.href='khach_hang.php';</script>";
    }
} else {
    header("Location: khach_hang.php");
}
?>