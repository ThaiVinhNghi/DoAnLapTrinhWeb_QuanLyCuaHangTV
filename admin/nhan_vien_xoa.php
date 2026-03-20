<?php
session_start();
require_once '../connect.php';

$id_nv = isset($_GET['id']) ? $_GET['id'] : 0;

if ($id_nv > 0) {
    // Tùy chọn: Bạn có thể kiểm tra không cho xóa chính tài khoản Admin đang đăng nhập
    
    $sql = "DELETE FROM nhanvien WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_nv);
    
    if ($stmt->execute()) {
        echo "<script>alert('Đã xóa nhân viên!'); window.location.href='nhan_vien.php';</script>";
    } else {
        echo "<script>alert('Không thể xóa! Có thể nhân viên này đang dính với dữ liệu hóa đơn.'); window.location.href='nhan_vien.php';</script>";
    }
} else {
    header("Location: nhan_vien.php");
}
?>