<?php
session_start();
require_once 'thu_vien/connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Lấy ID và số lượng người dùng vừa chọn (sanitized)
    $id_sp = isset($_POST['id_sp']) ? (int)$_POST['id_sp'] : 0;
    $so_luong = isset($_POST['so_luong']) ? max(1, (int)$_POST['so_luong']) : 1;

    // Nếu ID không hợp lệ thì chuyển về trang chủ
    if ($id_sp <= 0) {
        header("Location: trang_chu.php");
        exit();
    }

    // === CHECK SỐ LƯỢNG HÀNG CÓ ĐỦ KHÔNG ===
    $sql_check = "SELECT SoLuong FROM sanpham WHERE ID = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $id_sp);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    
    if (!$res_check || $res_check->num_rows == 0) {
        header("Location: trang_chu.php");
        exit();
    }
    
    $row_check = $res_check->fetch_assoc();
    $soLuongTon = (int)$row_check['SoLuong'];
    $stmt_check->close();
    
    // Nếu số lượng yêu cầu > số lượng tồn thì giảm xuống số lượng tồn
    if ($so_luong > $soLuongTon) {
        if ($soLuongTon <= 0) {
            // Hàng hết, chuyển về trang chủ
            header("Location: trang_chu.php");
            exit();
        }
        $so_luong = $soLuongTon; // Chỉ thêm số lượng tồn
    }
    // =====================================

    // 1. Nếu giỏ hàng chưa từng tồn tại thì tạo một mảng (array) rỗng
    if (!isset($_SESSION['gio_hang'])) {
        $_SESSION['gio_hang'] = array();
    }

    // 2. Kiểm tra xem Tivi này đã có trong giỏ hàng trước đó chưa
    if (isset($_SESSION['gio_hang'][$id_sp])) {
        // Nếu đã có, thì cộng dồn số lượng cũ và mới lại
        $tongSoLuong = $_SESSION['gio_hang'][$id_sp] + $so_luong;
        // Nhưng không vượt quá số lượng tồn
        $_SESSION['gio_hang'][$id_sp] = min($tongSoLuong, $soLuongTon);
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