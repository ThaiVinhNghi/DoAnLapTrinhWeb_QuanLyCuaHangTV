<?php
session_start();

if (isset($_GET['id'])) {
    // Lấy ID sản phẩm cần xóa từ URL
    $id_sp = $_GET['id'];

    // Kiểm tra giỏ hàng có tồn tại và sản phẩm có trong giỏ hay không
    if (isset($_SESSION['gio_hang']) && isset($_SESSION['gio_hang'][$id_sp])) {
        unset($_SESSION['gio_hang'][$id_sp]);
    }

    // Sau khi xóa xong thì quay lại trang giỏ hàng
    header("Location: gio_hang.php");
    exit();
} else {
    // Nếu không có id thì quay về trang chủ
    header("Location: trang_chu.php");
    exit();
}
?>