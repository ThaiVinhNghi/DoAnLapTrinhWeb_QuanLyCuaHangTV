<?php
session_start();
require_once '../connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nhanvien_id = $_SESSION['nhanvien_id']; // ID người đang đăng nhập
    $nhacungcap_id = $_POST['NhaCungCapID'];
    $ghi_chu = $_POST['GhiChu'];
    $tong_tien = $_POST['TongTien']; // Tổng tiền tính từ các sản phẩm

    // BƯỚC 1: Lưu vào bảng phieunhap (Bảng Cha)
    $sql_pn = "INSERT INTO phieunhap (NhanVienID, NhaCungCapID, NgayNhap, TongTien, GhiChu) 
               VALUES ('$nhanvien_id', '$nhacungcap_id', NOW(), '$tong_tien', '$ghi_chu')";
    
    if ($conn->query($sql_pn)) {
        // BƯỚC 2: Lấy ID của phiếu nhập vừa tạo xong
        $phieu_nhap_id = $conn->insert_id;

        // BƯỚC 3: Lặp qua danh sách sản phẩm để lưu vào phieunhap_chitiet (Bảng Con)
        // Giả sử bạn gửi mảng SanPhamID, SoLuong, DonGia từ Form
        $danh_sach_sp = $_POST['san_pham']; 

        foreach ($danh_sach_sp as $sp) {
            $sp_id = $sp['id'];
            $sl = $sp['soluong'];
            $gia = $sp['gia'];

            $sql_ct = "INSERT INTO phieunhap_chitiet (PhieuNhapID, SanPhamID, SoLuongNhap, DonGiaNhap) 
                       VALUES ('$phieu_nhap_id', '$sp_id', '$sl', '$gia')";
            $conn->query($sql_ct);

            // BƯỚC 4 (Nên có): Cập nhật số lượng tồn kho trong bảng sanpham
            $sql_update_kho = "UPDATE sanpham SET SoLuong = SoLuong + $sl WHERE ID = $sp_id";
            $conn->query($sql_update_kho);
        }

        echo "<script>alert('Nhập hàng thành công!'); window.location.href='nhap_hang.php';</script>";
    }
}
?>