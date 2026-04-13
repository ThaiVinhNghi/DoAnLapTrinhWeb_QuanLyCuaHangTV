<?php
session_start();
require_once 'thu_vien/connect.php';
require_once 'thu_vien/nhatky_helper.php';

if (isset($_SESSION['khach_hang_id'])) {
    ghiNhatKyKhachHangTuSession(
        $conn,
        'DangXuat',
        'khachhang',
        (int)$_SESSION['khach_hang_id'],
        'Khách hàng đăng xuất khỏi hệ thống',
        'ThanhCong'
    );
}

unset($_SESSION['khach_hang_id']);
unset($_SESSION['khach_hang_ten']);
unset($_SESSION['khach_hang_tendangnhap']);

header("Location: trang_chu.php");
exit();
?>