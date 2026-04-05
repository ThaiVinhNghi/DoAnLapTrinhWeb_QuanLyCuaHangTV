<?php
session_start();
require_once 'connect.php';
require_once 'nhatky_helper.php';

// 1. Kiểm tra giỏ hàng
if (!isset($_SESSION['gio_hang']) || empty($_SESSION['gio_hang'])) {
    header("Location: trang_chu.php");
    exit();
}

// 2. Kiểm tra đăng nhập khách hàng
if (!isset($_SESSION['khach_hang_id']) && !isset($_SESSION['khachhang_id'])) {
    header("Location: login_khach.php");
    exit();
}

$khachHangID = (int)($_SESSION['khach_hang_id'] ?? $_SESSION['khachhang_id']);

// ========================================================
// --- BƯỚC 1: KIỂM TRA TÀI KHOẢN CÓ BỊ NỢ XẤU KHÔNG ---
// ========================================================
$isNoXau = false;
$sql_check_noxau = "SELECT ID FROM tragop WHERE KhachHangID = ? AND TinhTrangTra = 'Nợ xấu' LIMIT 1";
$stmt_nx = $conn->prepare($sql_check_noxau);
$stmt_nx->bind_param("i", $khachHangID);
$stmt_nx->execute();
$res_nx = $stmt_nx->get_result();

if ($res_nx && $res_nx->num_rows > 0) {
    $isNoXau = true; // Bật cờ khóa tài khoản
}
$stmt_nx->close();
// ========================================================


$thongBao = '';
$datHangThanhCong = false;
$tongTienGioHang = 0;

// TÍNH TỔNG TIỀN HIỂN THỊ
if (isset($_SESSION['gio_hang'])) {
    foreach ($_SESSION['gio_hang'] as $id_sp => $so_luong) {
        $id_sp = (int)$id_sp;
        $so_luong = (int)$so_luong;

        $sql_gia = "SELECT DonGia, PhanTramGiam FROM sanpham WHERE ID = ?";
        $stmt_gia = $conn->prepare($sql_gia);
        $stmt_gia->bind_param("i", $id_sp);
        $stmt_gia->execute();
        $res_gia = $stmt_gia->get_result();

        if ($row = $res_gia->fetch_assoc()) {
            $giaGoc = (float)$row['DonGia'];
            $phanTram = isset($row['PhanTramGiam']) ? (float)$row['PhanTramGiam'] : 0;
            $giaBanThucTe = $giaGoc - ($giaGoc * $phanTram / 100);
            $tongTienGioHang += ($giaBanThucTe * $so_luong);
        }

        $stmt_gia->close();
    }
}

// Xử lý khi khách bấm đặt hàng
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ngayLap = date('Y-m-d H:i:s');
    $ghiChu = isset($_POST['GhiChuHoaDon']) ? trim($_POST['GhiChuHoaDon']) : '';
    $hinhThucThanhToan = isset($_POST['HinhThucThanhToan']) ? trim($_POST['HinhThucThanhToan']) : 'thanhtoanhet';

    // ========================================================
    // --- BƯỚC 3: CHẶN BACKEND NẾU CỐ TÌNH HACK HTML ---
    // ========================================================
    if ($hinhThucThanhToan === 'tragop' && $isNoXau == true) {
        echo "<script>alert('Lỗi: Tài khoản của bạn đang có nợ xấu. Hệ thống từ chối tạo hồ sơ trả góp mới!'); window.history.back();</script>";
        exit();
    }
    // ========================================================

    $conn->begin_transaction();

    try {
        // =========================
        // 1. THANH TOÁN HẾT
        // =========================
        if ($hinhThucThanhToan === 'thanhtoanhet') {
            $nhanVienID = null;

            $sql_hoadon = "INSERT INTO hoadon (NhanVienID, KhachHangID, NgayLap, GhiChuHoaDon) VALUES (?, ?, ?, ?)";
            $stmt_hd = $conn->prepare($sql_hoadon);
            $stmt_hd->bind_param("iiss", $nhanVienID, $khachHangID, $ngayLap, $ghiChu);
            $stmt_hd->execute();

            $id_hoadon = $conn->insert_id;
            $stmt_hd->close();

            foreach ($_SESSION['gio_hang'] as $id_sp => $so_luong) {
                $id_sp = (int)$id_sp;
                $so_luong = (int)$so_luong;

                $sql_sp = "SELECT DonGia, SoLuong, PhanTramGiam FROM sanpham WHERE ID = ?";
                $stmt_sp = $conn->prepare($sql_sp);
                $stmt_sp->bind_param("i", $id_sp);
                $stmt_sp->execute();
                $res_sp = $stmt_sp->get_result();

                if (!$row_sp = $res_sp->fetch_assoc()) {
                    throw new Exception("Có sản phẩm không tồn tại trong hệ thống.");
                }

                $giaGoc = (float)$row_sp['DonGia'];
                $soLuongTon = (int)$row_sp['SoLuong'];
                $phanTram = isset($row_sp['PhanTramGiam']) ? (float)$row_sp['PhanTramGiam'] : 0;
                $donGiaBanThucTe = $giaGoc - ($giaGoc * $phanTram / 100);

                if ($so_luong > $soLuongTon) {
                    throw new Exception("Sản phẩm ID $id_sp không đủ số lượng trong kho.");
                }

                $stmt_sp->close();

                $sql_ct = "INSERT INTO hoadon_chitiet (HoaDonID, SanPhamID, SoLuongBan, DonGiaBan)
                           VALUES (?, ?, ?, ?)";
                $stmt_ct = $conn->prepare($sql_ct);
                $stmt_ct->bind_param("iiid", $id_hoadon, $id_sp, $so_luong, $donGiaBanThucTe);
                $stmt_ct->execute();
                $stmt_ct->close();

                $sql_update_sp = "UPDATE sanpham SET SoLuong = SoLuong - ? WHERE ID = ?";
                $stmt_update = $conn->prepare($sql_update_sp);
                $stmt_update->bind_param("ii", $so_luong, $id_sp);
                $stmt_update->execute();
                $stmt_update->close();
            }

            $conn->commit();

            ghiNhatKyKhachHangTuSession(
                $conn,
                'DatHang',
                'hoadon',
                $id_hoadon,
                'Khách hàng đặt hàng thanh toán hết, mã hóa đơn #HD' . $id_hoadon,
                'ThanhCong'
            );

            unset($_SESSION['gio_hang']);
            $thongBao = "Đặt hàng thành công! Mã đơn hàng của bạn là: <strong class='text-danger fs-4'>#HD{$id_hoadon}</strong>";
            $datHangThanhCong = true;
        }

        // =========================
        // 2. TRẢ GÓP
        // =========================
        elseif ($hinhThucThanhToan === 'tragop') {
            $soTienTraTruoc = isset($_POST['SoTienTraTruoc']) ? (float)$_POST['SoTienTraTruoc'] : 0;
            $soThangTraGop = isset($_POST['SoThangTraGop']) ? (int)$_POST['SoThangTraGop'] : 6;
            $laiSuat = isset($_POST['LaiSuat']) ? (float)$_POST['LaiSuat'] : 1.5;

            if ($soTienTraTruoc < 0) {
                throw new Exception("Số tiền trả trước không hợp lệ.");
            }

            if (!in_array($soThangTraGop, [3, 6, 9, 12], true)) {
                throw new Exception("Số tháng trả góp không hợp lệ.");
            }

            if ($soTienTraTruoc >= $tongTienGioHang) {
                throw new Exception("Nếu trả trước bằng hoặc lớn hơn tổng tiền thì nên chọn thanh toán hết.");
            }

            $soTienConLai = $tongTienGioHang - $soTienTraTruoc;
            $tongPhaiTra = $soTienConLai + ($soTienConLai * $laiSuat / 100 * $soThangTraGop);
            $tienGopMoiThang = $tongPhaiTra / $soThangTraGop;
            $soTienDaTra = 0;
            $soLanNhacNho = 0;
            $trangThai = 'Chờ duyệt';
            $tinhTrangTra = 'Chờ duyệt';

            $sql_tragop = "INSERT INTO tragop
                (KhachHangID, NgayDangKy, SoTienTraTruoc, SoThangTraGop, LaiSuat, TongTien, SoTienConLai, TongPhaiTra, TienGopMoiThang, SoTienDaTra, SoLanNhacNho, GhiChu, TrangThai, TinhTrangTra)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt_tg = $conn->prepare($sql_tragop);
            $stmt_tg->bind_param(
                "isdidddddissss",
                $khachHangID,
                $ngayLap,
                $soTienTraTruoc,
                $soThangTraGop,
                $laiSuat,
                $tongTienGioHang,
                $soTienConLai,
                $tongPhaiTra,
                $tienGopMoiThang,
                $soTienDaTra,
                $soLanNhacNho,
                $ghiChu,
                $trangThai,
                $tinhTrangTra
            );
            $stmt_tg->execute();

            $id_tragop = $conn->insert_id;
            $stmt_tg->close();

            foreach ($_SESSION['gio_hang'] as $id_sp => $so_luong) {
                $id_sp = (int)$id_sp;
                $so_luong = (int)$so_luong;

                $sql_sp = "SELECT DonGia, SoLuong, PhanTramGiam FROM sanpham WHERE ID = ?";
                $stmt_sp = $conn->prepare($sql_sp);
                $stmt_sp->bind_param("i", $id_sp);
                $stmt_sp->execute();
                $res_sp = $stmt_sp->get_result();

                if (!$row_sp = $res_sp->fetch_assoc()) {
                    throw new Exception("Có sản phẩm không tồn tại trong hệ thống.");
                }

                $giaGoc = (float)$row_sp['DonGia'];
                $soLuongTon = (int)$row_sp['SoLuong'];
                $phanTram = isset($row_sp['PhanTramGiam']) ? (float)$row_sp['PhanTramGiam'] : 0;
                $donGiaBanThucTe = $giaGoc - ($giaGoc * $phanTram / 100);
                $thanhTien = $donGiaBanThucTe * $so_luong;

                if ($so_luong > $soLuongTon) {
                    throw new Exception("Sản phẩm ID $id_sp không đủ số lượng trong kho.");
                }

                $stmt_sp->close();

                $sql_ct = "INSERT INTO tragop_chitiet (TraGopID, SanPhamID, SoLuong, DonGia, ThanhTien)
                           VALUES (?, ?, ?, ?, ?)";
                $stmt_ct = $conn->prepare($sql_ct);
                $stmt_ct->bind_param("iiidd", $id_tragop, $id_sp, $so_luong, $donGiaBanThucTe, $thanhTien);
                $stmt_ct->execute();
                $stmt_ct->close();

                $sql_update_sp = "UPDATE sanpham SET SoLuong = SoLuong - ? WHERE ID = ?";
                $stmt_update = $conn->prepare($sql_update_sp);
                $stmt_update->bind_param("ii", $so_luong, $id_sp);
                $stmt_update->execute();
                $stmt_update->close();
            }

            $conn->commit();

            ghiNhatKyKhachHangTuSession(
                $conn,
                'DangKyTraGop',
                'tragop',
                $id_tragop,
                'Khách hàng đăng ký hồ sơ trả góp #TG' . $id_tragop,
                'ThanhCong'
            );

            unset($_SESSION['gio_hang']);
            $thongBao = "Đăng ký mua trả góp thành công! Mã hồ sơ trả góp của bạn là: <strong class='text-danger fs-4'>#TG{$id_tragop}</strong>";
            $datHangThanhCong = true;
        }

        else {
            throw new Exception("Hình thức thanh toán không hợp lệ.");
        }
    } catch (Exception $e) {
        $conn->rollback();

        if ($hinhThucThanhToan === 'tragop') {
            ghiNhatKyKhachHangTuSession(
                $conn,
                'DangKyTraGop',
                'tragop',
                null,
                'Đăng ký trả góp thất bại: ' . $e->getMessage(),
                'ThatBai'
            );
        } else {
            ghiNhatKyKhachHangTuSession(
                $conn,
                'DatHang',
                'hoadon',
                null,
                'Đặt hàng thất bại: ' . $e->getMessage(),
                'ThatBai'
            );
        }

        $thongBao = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thanh Toán - TIVI STORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script>
        function toggleTraGopBox() {
            const traGop = document.getElementById('tragop');
            const boxTraGop = document.getElementById('boxTraGop');
            // Kiểm tra xem phần tử có tồn tại không trước khi xử lý (Phòng trường hợp bị disabled)
            if(traGop && boxTraGop) {
                boxTraGop.style.display = traGop.checked ? 'block' : 'none';
            }
        }
    </script>
</head>
<body class="bg-light" onload="toggleTraGopBox()">

<div class="container mt-5" style="max-width: 900px;">
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
                            <div class="mb-3">
                                <label class="form-label fw-bold">Hình thức thanh toán</label>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="HinhThucThanhToan" id="thanhtoanhet" value="thanhtoanhet" checked onclick="toggleTraGopBox()">
                                    <label class="form-check-label" for="thanhtoanhet">
                                        Thanh toán hết (Tiền mặt / Chuyển khoản)
                                    </label>
                                </div>

                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="HinhThucThanhToan" id="tragop" value="tragop" onclick="toggleTraGopBox()" <?php echo $isNoXau ? 'disabled' : ''; ?>>
                                    <label class="form-check-label <?php echo $isNoXau ? 'text-muted' : ''; ?>" for="tragop">
                                        Mua trả góp
                                        <?php if ($isNoXau): ?>
                                            <span class="badge bg-danger ms-2"><i class="bi bi-lock-fill"></i> Bị khóa do có nợ xấu</span>
                                            <div class="small text-danger mt-1 fw-normal" style="font-size: 0.85rem;">Bạn cần thanh toán dứt điểm các hồ sơ nợ xấu trước khi sử dụng lại tính năng này.</div>
                                        <?php endif; ?>
                                    </label>
                                </div>
                                </div>

                            <div id="boxTraGop" style="display:none;">
                                <div class="border rounded p-3 mb-3 bg-light">
                                    <h6 class="fw-bold mb-3 text-primary">Thông tin trả góp</h6>

                                    <div class="mb-3">
                                        <label class="form-label">Số tiền trả trước</label>
                                        <input type="number" name="SoTienTraTruoc" class="form-control" min="0" value="0">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Số tháng trả góp</label>
                                        <select name="SoThangTraGop" class="form-select">
                                            <option value="3">3 tháng</option>
                                            <option value="6" selected>6 tháng</option>
                                            <option value="9">9 tháng</option>
                                            <option value="12">12 tháng</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Lãi suất (% / tháng)</label>
                                        <input type="number" step="0.1" name="LaiSuat" class="form-control" value="1.5">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Ghi chú cho đơn hàng (Tùy chọn)</label>
                                <textarea name="GhiChuHoaDon" class="form-control bg-light" rows="4" placeholder="Ví dụ: Giao hàng vào giờ hành chính, bọc kỹ hàng giúp mình..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-success w-100 fs-5 py-2 fw-bold shadow-sm">
                                XÁC NHẬN ĐẶT HÀNG
                            </button>
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