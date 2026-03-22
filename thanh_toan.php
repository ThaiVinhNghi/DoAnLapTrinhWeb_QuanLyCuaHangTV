<?php
session_start();
require_once 'connect.php';

// 1. Kiểm tra giỏ hàng
if (!isset($_SESSION['gio_hang']) || empty($_SESSION['gio_hang'])) {
    header("Location: trang_chu.php");
    exit();
}

// 2. Kiểm tra đăng nhập khách hàng
if (!isset($_SESSION['khach_hang_id'])) {
    header("Location: login_khach.php");
    exit();
}

$thongBao = '';
$datHangThanhCong = false;
$tongTienGioHang = 0; // Khởi tạo biến tổng tiền ở đầu

// Xử lý khi khách bấm đặt hàng
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $khachHangID = $_SESSION['khach_hang_id'];
    $ngayLap = date('Y-m-d H:i:s');
    $ghiChu = isset($_POST['GhiChuHoaDon']) ? trim($_POST['GhiChuHoaDon']) : '';
    $nhanVienID = null;

    $sql_hoadon = "INSERT INTO hoadon (NhanVienID, KhachHangID, NgayLap, GhiChuHoaDon) VALUES (?, ?, ?, ?)";
    $stmt_hd = $conn->prepare($sql_hoadon);
    $stmt_hd->bind_param("iiss", $nhanVienID, $khachHangID, $ngayLap, $ghiChu);

    if ($stmt_hd->execute()) {
        $id_hoadon = $conn->insert_id;
        $loiChiTiet = false;

        foreach ($_SESSION['gio_hang'] as $id_sp => $so_luong) {
            // Lấy thêm PhanTramGiam
            $sql_sp = "SELECT DonGia, SoLuong, PhanTramGiam FROM sanpham WHERE ID = ?";
            $stmt_sp = $conn->prepare($sql_sp);
            $stmt_sp->bind_param("i", $id_sp);
            $stmt_sp->execute();
            $res_sp = $stmt_sp->get_result();

            if ($row_sp = $res_sp->fetch_assoc()) {
                $giaGoc = $row_sp['DonGia'];
                $soLuongTon = $row_sp['SoLuong'];
                $phanTram = isset($row_sp['PhanTramGiam']) ? $row_sp['PhanTramGiam'] : 0;
                
                // TÍNH TOÁN GIÁ THỰC TẾ LÚC BÁN
                $donGiaBanThucTe = $giaGoc - ($giaGoc * $phanTram / 100);

                if ($so_luong > $soLuongTon) {
                    $loiChiTiet = true;
                    $thongBao = "Sản phẩm ID $id_sp không đủ số lượng trong kho.";
                    break;
                }

                // LƯU VÀO CHI TIẾT HÓA ĐƠN LÀ GIÁ ĐÃ GIẢM
                $sql_ct = "INSERT INTO hoadon_chitiet (HoaDonID, SanPhamID, SoLuongBan, DonGiaBan)
                           VALUES (?, ?, ?, ?)";
                $stmt_ct = $conn->prepare($sql_ct);
                $stmt_ct->bind_param("iiid", $id_hoadon, $id_sp, $so_luong, $donGiaBanThucTe);

                if (!$stmt_ct->execute()) {
                    $loiChiTiet = true;
                    $thongBao = "Có lỗi khi lưu chi tiết hóa đơn.";
                    break;
                }

                $sql_update_sp = "UPDATE sanpham SET SoLuong = SoLuong - ? WHERE ID = ?";
                $stmt_update = $conn->prepare($sql_update_sp);
                $stmt_update->bind_param("ii", $so_luong, $id_sp);

                if (!$stmt_update->execute()) {
                    $loiChiTiet = true;
                    $thongBao = "Có lỗi khi cập nhật số lượng sản phẩm.";
                    break;
                }
            } else {
                $loiChiTiet = true;
                $thongBao = "Có sản phẩm không tồn tại trong hệ thống.";
                break;
            }
        }

        if (!$loiChiTiet) {
            unset($_SESSION['gio_hang']);
            $thongBao = "Đặt hàng thành công! Mã đơn hàng của bạn là: <strong class='text-danger fs-4'>#HD{$id_hoadon}</strong>";
            $datHangThanhCong = true;
        }
    } else {
        $thongBao = "Lỗi khi tạo hóa đơn: " . $conn->error;
    }
}

// TÍNH TỔNG TIỀN HIỂN THỊ (Giao diện) nếu chưa đặt hàng thành công
if (isset($_SESSION['gio_hang']) && !$datHangThanhCong) {
    foreach ($_SESSION['gio_hang'] as $id_sp => $so_luong) {
        // Lấy thêm PhanTramGiam
        $sql_gia = "SELECT DonGia, PhanTramGiam FROM sanpham WHERE ID = ?";
        $stmt_gia = $conn->prepare($sql_gia);
        $stmt_gia->bind_param("i", $id_sp);
        $stmt_gia->execute();
        $res_gia = $stmt_gia->get_result();

        if ($row = $res_gia->fetch_assoc()) {
            $giaGoc = $row['DonGia'];
            $phanTram = isset($row['PhanTramGiam']) ? $row['PhanTramGiam'] : 0;
            
            // TÍNH TOÁN LẠI GIÁ BÁN HIỂN THỊ TRÊN MÀN HÌNH
            $giaBanThucTe = $giaGoc - ($giaGoc * $phanTram / 100);
            $tongTienGioHang += ($giaBanThucTe * $so_luong);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh Toán - TIVI STORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5" style="max-width: 800px;">
    <h2 class="mb-4 text-center text-primary fw-bold">Xác Nhận Đặt Hàng</h2>

    <?php if ($thongBao != ''): ?>
        <div class="alert alert-<?php echo $datHangThanhCong ? 'success' : 'danger'; ?> text-center fs-5 shadow-sm border-0">
            <?php echo $thongBao; ?>
        </div>

        <?php if ($datHangThanhCong): ?>
            <div class="text-center mt-4">
                <a href="trang_chu.php" class="btn btn-primary me-2 px-4 py-2 fw-bold">Tiếp tục mua sắm</a>
                <a href="logout_khach.php" class="btn btn-danger px-4 py-2 fw-bold">Đăng xuất</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!$datHangThanhCong): ?>
        <div class="row">
            <div class="col-md-7 mb-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h5 class="card-title mb-3 fw-bold border-bottom pb-2">Thông tin đơn hàng</h5>
                        <p class="text-muted small">
                            Hệ thống sẽ sử dụng thông tin giao hàng trong hồ sơ tài khoản của bạn.
                        </p>

                        <form action="" method="POST">
                            <div class="mb-4">
                                <label class="form-label fw-bold">Ghi chú cho đơn hàng (Tùy chọn)</label>
                                <textarea name="GhiChuHoaDon" class="form-control bg-light" rows="4" placeholder="Ví dụ: Giao hàng vào giờ hành chính, bọc kỹ hàng giúp mình..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100 fs-5 py-2 fw-bold shadow-sm">XÁC NHẬN ĐẶT HÀNG</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card shadow-sm bg-white border-0">
                    <div class="card-body p-4">
                        <h5 class="mb-3 fw-bold border-bottom pb-2">Tóm tắt giỏ hàng</h5>
                        <p class="d-flex justify-content-between">
                            <span>Số loại sản phẩm:</span> 
                            <span class="fw-bold"><?php echo count($_SESSION['gio_hang']); ?></span>
                        </p>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded">
                            <span class="fs-5 fw-bold">Tổng thanh toán:</span>
                            <span class="fs-4 fw-bold text-danger"><?php echo number_format($tongTienGioHang, 0, ',', '.'); ?> đ</span>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="gio_hang.php" class="text-decoration-none btn btn-outline-secondary w-100">Quay lại giỏ hàng</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>