<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function ghiNhatKy($conn, $loaiNguoiDung, $nguoiDungID, $tenDangNhap, $hoTen, $hanhDong, $bangTacDong = null, $banGhiID = null, $moTa = null, $trangThai = 'ThanhCong')
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';

    $sql = "INSERT INTO nhatky_hethong
            (LoaiNguoiDung, NguoiDungID, TenDangNhap, HoTen, HanhDong, BangTacDong, BanGhiID, MoTa, TrangThai, DiaChiIP)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param(
            $stmt,
            "sissssisss",
            $loaiNguoiDung,
            $nguoiDungID,
            $tenDangNhap,
            $hoTen,
            $hanhDong,
            $bangTacDong,
            $banGhiID,
            $moTa,
            $trangThai,
            $ip
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }
}

function ghiNhatKyTuSession($conn, $hanhDong, $bangTacDong = null, $banGhiID = null, $moTa = null, $trangThai = 'ThanhCong')
{
    $loaiNguoiDung = 'Khach';
    $nguoiDungID = null;
    $tenDangNhap = null;
    $hoTen = null;

    if (isset($_SESSION['admin_id'])) {
        $loaiNguoiDung = 'Admin';
        $nguoiDungID = $_SESSION['admin_id'];
        $tenDangNhap = $_SESSION['admin_tendangnhap'] ?? null;
        $hoTen = $_SESSION['admin_hoten'] ?? null;
    } elseif (isset($_SESSION['nhanvien_id'])) {
        $loaiNguoiDung = 'NhanVien';
        $nguoiDungID = $_SESSION['nhanvien_id'];
        $tenDangNhap = $_SESSION['nhanvien_tendangnhap'] ?? null;
        $hoTen = $_SESSION['nhanvien_hoten'] ?? null;
    } elseif (isset($_SESSION['khach_hang_id']) || isset($_SESSION['khachhang_id'])) {
        $loaiNguoiDung = 'KhachHang';
        $nguoiDungID = $_SESSION['khach_hang_id'] ?? $_SESSION['khachhang_id'];
        $tenDangNhap = $_SESSION['khach_hang_tendangnhap'] ?? $_SESSION['khachhang_tendangnhap'] ?? null;
        $hoTen = $_SESSION['khach_hang_hoten'] ?? $_SESSION['khach_hang_ten'] ?? $_SESSION['khachhang_hoten'] ?? null;
    }

    ghiNhatKy(
        $conn,
        $loaiNguoiDung,
        $nguoiDungID,
        $tenDangNhap,
        $hoTen,
        $hanhDong,
        $bangTacDong,
        $banGhiID,
        $moTa,
        $trangThai
    );
}

function ghiNhatKyKhachHangTuSession($conn, $hanhDong, $bangTacDong = null, $banGhiID = null, $moTa = null, $trangThai = 'ThanhCong')
{
    $nguoiDungID = $_SESSION['khach_hang_id'] ?? $_SESSION['khachhang_id'] ?? null;
    $tenDangNhap = $_SESSION['khach_hang_tendangnhap'] ?? $_SESSION['khachhang_tendangnhap'] ?? null;
    $hoTen = $_SESSION['khach_hang_hoten'] ?? $_SESSION['khach_hang_ten'] ?? $_SESSION['khachhang_hoten'] ?? null;

    ghiNhatKy(
        $conn,
        'KhachHang',
        $nguoiDungID,
        $tenDangNhap,
        $hoTen,
        $hanhDong,
        $bangTacDong,
        $banGhiID,
        $moTa,
        $trangThai
    );
}
?>