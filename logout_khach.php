<?php
session_start();
require_once 'connect.php';
require_once 'nhatky_helper.php';

if (isset($_SESSION['khach_hang_id']) || isset($_SESSION['khachhang_id'])) {
    ghiNhatKyKhachHangTuSession(
        $conn,
        'DangXuat',
        'khachhang',
        (int)($_SESSION['khach_hang_id'] ?? $_SESSION['khachhang_id']),
        'Khách hàng đăng xuất khỏi hệ thống',
        'ThanhCong'
    );
}

unset($_SESSION['khach_hang_id']);
unset($_SESSION['khachhang_id']);
unset($_SESSION['khach_hang_ten']);
unset($_SESSION['khach_hang_hoten']);
unset($_SESSION['khachhang_hoten']);
unset($_SESSION['khach_hang_tendangnhap']);
unset($_SESSION['khachhang_tendangnhap']);

header("Location: trang_chu.php");
exit();
?>