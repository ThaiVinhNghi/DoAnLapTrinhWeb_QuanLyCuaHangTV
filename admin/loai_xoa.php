<?php
session_start();
require_once '../thu_vien/connect.php';
$id = isset($_GET['id']) ? $_GET['id'] : 0;
if ($id > 0) {
    $sql = "DELETE FROM loaisanpham WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<script>alert('Xóa thành công!'); window.location.href='danh_muc.php';</script>";
    } else {
        echo "<script>alert('Không thể xóa! Loại này đang có sản phẩm bên trong.'); window.location.href='danh_muc.php';</script>";
    }
}
?>