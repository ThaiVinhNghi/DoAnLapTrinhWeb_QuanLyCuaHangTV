<?php
session_start();
require_once '../connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['nhanvien_id'])) {
    header("Location: ../login.php");
    exit();
}

// Kiểm tra id
if (!isset($_GET['id']) || (int)$_GET['id'] <= 0) {
    echo "<script>alert('Thiếu mã phiếu nhập!'); window.location.href='nhap_hang.php';</script>";
    exit();
}

$phieu_nhap_id = (int)$_GET['id'];

$conn->begin_transaction();

try {
    // Kiểm tra phiếu nhập tồn tại
    $sql_check = "SELECT ID FROM phieunhap WHERE ID = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $phieu_nhap_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows == 0) {
        throw new Exception("Phiếu nhập không tồn tại.");
    }

    // Lấy chi tiết phiếu nhập để trừ lại kho
    $sql_ct = "SELECT SanPhamID, SoLuongNhap FROM phieunhap_chitiet WHERE PhieuNhapID = ?";
    $stmt_ct = $conn->prepare($sql_ct);
    $stmt_ct->bind_param("i", $phieu_nhap_id);
    $stmt_ct->execute();
    $result_ct = $stmt_ct->get_result();

    $sql_update_kho = "UPDATE sanpham SET SoLuong = SoLuong - ? WHERE ID = ?";
    $stmt_update_kho = $conn->prepare($sql_update_kho);

    while ($ct = $result_ct->fetch_assoc()) {
        $sl = (int)$ct['SoLuongNhap'];
        $sp_id = (int)$ct['SanPhamID'];

        $stmt_update_kho->bind_param("ii", $sl, $sp_id);
        if (!$stmt_update_kho->execute()) {
            throw new Exception("Không thể cập nhật tồn kho.");
        }
    }

    // Xóa chi tiết phiếu nhập
    $sql_delete_ct = "DELETE FROM phieunhap_chitiet WHERE PhieuNhapID = ?";
    $stmt_delete_ct = $conn->prepare($sql_delete_ct);
    $stmt_delete_ct->bind_param("i", $phieu_nhap_id);

    if (!$stmt_delete_ct->execute()) {
        throw new Exception("Không thể xóa chi tiết phiếu nhập.");
    }

    // Xóa phiếu nhập
    $sql_delete_pn = "DELETE FROM phieunhap WHERE ID = ?";
    $stmt_delete_pn = $conn->prepare($sql_delete_pn);
    $stmt_delete_pn->bind_param("i", $phieu_nhap_id);

    if (!$stmt_delete_pn->execute()) {
        throw new Exception("Không thể xóa phiếu nhập.");
    }

    $conn->commit();
    echo "<script>alert('Xóa phiếu nhập thành công!'); window.location.href='nhap_hang.php';</script>";
    exit();
} catch (Exception $e) {
    $conn->rollback();
    echo "<script>alert('Lỗi: " . addslashes($e->getMessage()) . "'); window.location.href='nhap_hang.php';</script>";
    exit();
}
?>