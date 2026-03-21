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
            $sql_sp = "SELECT DonGia, SoLuong FROM sanpham WHERE ID = ?";
            $stmt_sp = $conn->prepare($sql_sp);
            $stmt_sp->bind_param("i", $id_sp);
            $stmt_sp->execute();
            $res_sp = $stmt_sp->get_result();

            if ($row_sp = $res_sp->fetch_assoc()) {
                $donGiaBan = $row_sp['DonGia'];
                $soLuongTon = $row_sp['SoLuong'];

                if ($so_luong > $soLuongTon) {
                    $loiChiTiet = true;
                    $thongBao = "Sản phẩm ID $id_sp không đủ số lượng trong kho.";
                    break;
                }

                $sql_ct = "INSERT INTO hoadon_chitiet (HoaDonID, SanPhamID, SoLuongBan, DonGiaBan)
                           VALUES (?, ?, ?, ?)";
                $stmt_ct = $conn->prepare($sql_ct);
                $stmt_ct->bind_param("iiid", $id_hoadon, $id_sp, $so_luong, $donGiaBan);

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
            $thongBao = "Đặt hàng thành công! Mã đơn hàng của bạn là: <strong>#HD{$id_hoadon}</strong>";
            $datHangThanhCong = true;
        }
    } else {
        $thongBao = "Lỗi khi tạo hóa đơn: " . $conn->error;
    }
}

// Tính tổng tiền hiển thị
$tongTienGioHang = 0;

if (isset($_SESSION['gio_hang']) && !$datHangThanhCong) {
    foreach ($_SESSION['gio_hang'] as $id_sp => $so_luong) {
        $sql_gia = "SELECT DonGia FROM sanpham WHERE ID = ?";
        $stmt_gia = $conn->prepare($sql_gia);
        $stmt_gia->bind_param("i", $id_sp);
        $stmt_gia->execute();
        $res_gia = $stmt_gia->get_result();

        if ($row = $res_gia->fetch_assoc()) {
            $tongTienGioHang += ($row['DonGia'] * $so_luong);
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
    <h2 class="mb-4 text-center">Xác Nhận Đặt Hàng</h2>

    <?php if ($thongBao != ''): ?>
        <div class="alert alert-<?php echo $datHangThanhCong ? 'success' : 'danger'; ?> text-center fs-5">
            <?php echo $thongBao; ?>
        </div>

        <?php if ($datHangThanhCong): ?>
            <div class="text-center mt-4">
                <a href="trang_chu.php" class="btn btn-primary me-2">Tiếp tục mua sắm</a>
                <a href="logout_khach.php" class="btn btn-danger">Đăng xuất</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <?php if (!$datHangThanhCong): ?>
        <div class="row">
            <div class="col-md-7 mb-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Thông tin đơn hàng</h5>
                        <p class="text-muted">
                            Vì bạn đã đăng nhập, chúng tôi sẽ sử dụng thông tin giao hàng trong hồ sơ của bạn.
                        </p>

                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Ghi chú cho đơn hàng (Tùy chọn)</label>
                                <textarea name="GhiChuHoaDon" class="form-control" rows="3" placeholder="Ví dụ: Giao hàng vào giờ hành chính..."></textarea>
                            </div>
                            <button type="submit" class="btn btn-success w-100 fs-5">XÁC NHẬN ĐẶT HÀNG</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-5">
                <div class="card shadow-sm bg-white">
                    <div class="card-body">
                        <h5 class="mb-3">Tóm tắt giỏ hàng</h5>
                        <p>Số loại sản phẩm: <?php echo count($_SESSION['gio_hang']); ?></p>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="fs-5">Tổng tiền:</span>
                            <span class="fs-5 fw-bold text-danger"><?php echo number_format($tongTienGioHang, 0, ',', '.'); ?> đ</span>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-3">
                    <a href="gio_hang.php" class="text-decoration-none">Quay lại giỏ hàng</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>