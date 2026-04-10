<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy ID và số lượng người dùng vừa chọn (sanitized)
    $id_sp = isset($_POST['id_sp']) ? (int)$_POST['id_sp'] : 0;
    $so_luong = isset($_POST['so_luong']) ? max(1, (int)$_POST['so_luong']) : 1;

    // Nếu ID không hợp lệ thì chuyển về trang chủ
    if ($id_sp <= 0) {
        header("Location: trang_chu.php");
        exit();
    }

    // 1. Nếu giỏ hàng chưa từng tồn tại thì tạo một mảng (array) rỗng
    if (!isset($_SESSION['gio_hang'])) {
        $_SESSION['gio_hang'] = array();
    }

    // 2. Kiểm tra xem Tivi này đã có trong giỏ hàng trước đó chưa
    if (isset($_SESSION['gio_hang'][$id_sp])) {
        // Nếu đã có, thì cộng dồn số lượng cũ và mới lại
        $_SESSION['gio_hang'][$id_sp] += $so_luong;
    } else {
        // Nếu chưa có, tạo mới phần tử trong mảng Session
        $_SESSION['gio_hang'][$id_sp] = $so_luong;
    }

    // 3. Sau khi thêm xong, tự động chuyển hướng sang trang xem Giỏ hàng
    header("Location: gio_hang.php");
    exit();
} else {
    // Nếu không có dữ liệu POST gửi lên thì đuổi về trang chủ
    header("Location: trang_chu.php");
    exit();
}
?>