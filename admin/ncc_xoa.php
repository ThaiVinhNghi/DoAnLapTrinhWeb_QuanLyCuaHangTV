<?php
session_start();
require_once '../thu_vien/connect.php';
$id = isset($_GET['id']) ? $_GET['id'] : 0;
if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM nhacungcap WHERE ID = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "<script>alert('Xóa thành công!'); window.location.href='danh_muc.php';</script>";
    } else {
        echo "<script>alert('Lỗi: Nhà cung cấp này đang dính với dữ liệu sản phẩm!'); window.location.href='danh_muc.php';</script>";
    }
}
?>