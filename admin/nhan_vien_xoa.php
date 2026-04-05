<?php
session_start();
require_once '../connect.php';

// Bảo mật: Chỉ admin mới được xóa nhân viên
if (!isset($_SESSION['quyen_han']) || $_SESSION['quyen_han'] != 1) {
    echo "<script>alert('Bạn không có quyền thực hiện hành động này!'); window.location.href='index.php';</script>";
    exit();
}

$id_nv = isset($_GET['id']) ? $_GET['id'] : 0;

if ($id_nv > 0) {
    // Ngăn chặn việc tự xóa tài khoản của chính mình
    if ($id_nv == $_SESSION['nhanvien_id']) {
        echo "<script>alert('Lỗi: Bạn không thể tự xóa tài khoản của chính mình!'); window.location.href='nhan_vien.php';</script>";
        exit();
    }
    
    $sql = "DELETE FROM nhanvien WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_nv);
    
    if ($stmt->execute()) {
        echo "<script>alert('Đã xóa nhân viên!'); window.location.href='nhan_vien.php';</script>";
    } else {
        echo "<script>alert('Không thể xóa! Có thể nhân viên này đang dính với dữ liệu hóa đơn hoặc lịch sử hệ thống.'); window.location.href='nhan_vien.php';</script>";
    }
} else {
    header("Location: nhan_vien.php");
}
?>