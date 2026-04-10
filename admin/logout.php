<?php
session_start();
require_once '../thu_vien/connect.php';
require_once '../thu_vien/nhatky_helper.php';

// Ghi nhật ký trước khi xóa session
if (isset($_SESSION['nhanvien_id'])) {
    $id = (int)$_SESSION['nhanvien_id'];
    $tenDangNhap = $_SESSION['nhanvien_tendangnhap'] ?? $_SESSION['ten_dang_nhap'] ?? null;
    $hoTen = $_SESSION['nhanvien_hoten'] ?? $_SESSION['ho_ten'] ?? null;
    $laAdmin = isset($_SESSION['admin_id']) || ((int)($_SESSION['quyen_han'] ?? 0) === 1);

    ghiNhatKy(
        $conn,
        $laAdmin ? 'Admin' : 'NhanVien',
        $id,
        $tenDangNhap,
        $hoTen,
        'DangXuat',
        'nhanvien',
        $id,
        $laAdmin ? 'Đăng xuất hệ thống quản trị' : 'Nhân viên đăng xuất khỏi hệ thống',
        'ThanhCong'
    );
}

// Xóa session sau khi đã ghi log
unset($_SESSION['admin_id']);

unset($_SESSION['nhanvien_id']);
unset($_SESSION['nhan_vien_id']);

unset($_SESSION['nhanvien_hoten']);
unset($_SESSION['ho_ten']);

unset($_SESSION['nhanvien_tendangnhap']);
unset($_SESSION['ten_dang_nhap']);

unset($_SESSION['quyen_han']);

// nếu muốn xóa sạch session hiện tại thì mở dòng dưới
// session_destroy();

header("Location: ../login.php");
exit();
?>