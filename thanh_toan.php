<?php
session_start();
require_once 'thu_vien/connect.php';
require_once 'thu_vien/nhatky_helper.php';

// === KIỂM TRA SESSION TIMEOUT ===
if (!checkSessionTimeout()) {
    header("Location: login_khach.php?session_expired=1");
    exit();
}

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

$khachHangID = (int)$_SESSION['khach_hang_id'];

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
            
            // Nhận dữ liệu CCCD và Ngày sinh từ form (có thể dùng để cập nhật vào bảng nếu cần)
            $cccd = isset($_POST['CCCD']) ? trim($_POST['CCCD']) : '';
            $ngaySinh = isset($_POST['NgaySinh']) ? trim($_POST['NgaySinh']) : '';

            if ($soTienTraTruoc < 0) {
                throw new Exception("Số tiền trả trước không hợp lệ.");
            }

            if ($soTienTraTruoc > $tongTienGioHang) {
                throw new Exception("Số tiền trả trước không thể lớn hơn tổng tiền hàng.");
            }

            if (!in_array($soThangTraGop, [3, 6, 9, 12], true)) {
                throw new Exception("Số tháng trả góp không hợp lệ.");
            }

            if ($soTienTraTruoc >= $tongTienGioHang) {
                throw new Exception("Nếu trả trước bằng hoặc lớn hơn tổng tiền thì nên chọn thanh toán hết.");
            }

            $soTienConLai = $tongTienGioHang - $soTienTraTruoc;
            $tongPhaiTra = $soTienConLai + ($soTienConLai * $laiSuat / 100 / 12 * $soThangTraGop);
            $tienGopMoiThang = $tongPhaiTra / $soThangTraGop;
            $soTienDaTra = 0;
            $soLanNhacNho = 0;
            $trangThai = 'Chờ duyệt';
            $tinhTrangTra = 'Chờ duyệt';

            // Bỏ việc gộp vào Ghi chú, giờ chúng ta lưu thẳng vào 2 cột mới tạo
            $sql_tragop = "INSERT INTO tragop
                (KhachHangID, CCCD, NgaySinh, NgayDangKy, SoTienTraTruoc, SoThangTraGop, LaiSuat, TongTien, SoTienConLai, TongPhaiTra, TienGopMoiThang, SoTienDaTra, SoLanNhacNho, GhiChu, TrangThai, TinhTrangTra)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt_tg = $conn->prepare($sql_tragop);
            $stmt_tg->bind_param(
                "isssdidddddissss", // Thêm 2 chữ 's' (string) cho CCCD và NgaySinh
                $khachHangID,
                $cccd,              // Lưu trực tiếp vào cột CCCD
                $ngaySinh,          // Lưu trực tiếp vào cột NgaySinh
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
                $ghiChu,            // Trả lại sự trong sáng cho cột GhiChu
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán - N&U Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="tai_nguyen/css/style.css">
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-dark navbar-premium sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4 text-white" href="trang_chu.php"><i class="bi bi-tv text-danger"></i> N&U</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="trang_chu.php">Khám Phá</a></li>
                    <li class="nav-item"><a class="nav-link" href="san_pham.php">Sản Phẩm</a></li>
                    <li class="nav-item"><a class="nav-link" href="trang_chu.php#tin-tuc">Tin Tức</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <a href="gio_hang.php" class="text-white text-decoration-none fw-bold"><i class="bi bi-bag"></i> Giỏ hàng</a>
                </div>
            </div>
        </div>
    </nav>

<div class="container mt-5" style="max-width: 1100px;">
    <h2 class="premium-section-title">Xác Nhận Đặt Hàng</h2>

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

                        <form action="" method="POST" onsubmit="return validateThanhToan()">
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
                                        <input type="number" id="soTienTraTruoc" name="SoTienTraTruoc" class="form-control" min="0" value="0" oninput="calculateInstallment()">
                                        <small class="text-muted">Tối đa: <?php echo number_format($tongTienGioHang, 0, ',', '.')?> đ</small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Số tháng trả góp</label>
                                        <select id="soThangTraGop" name="SoThangTraGop" class="form-select" onchange="calculateInstallment()">
                                            <option value="3">3 tháng</option>
                                            <option value="6" selected>6 tháng</option>
                                            <option value="9">9 tháng</option>
                                            <option value="12">12 tháng</option>
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Lãi suất (% / năm)</label>
                                        <input type="number" id="laiSuat" step="0.1" name="LaiSuat" class="form-control" value="1.5" oninput="calculateInstallment()">
                                    </div>
                                    
                                    <div class="row g-3 mb-3 mt-1 border-top pt-3">
                                        <h6 class="fw-bold text-primary mb-1"><i class="bi bi-person-vcard"></i> Thông tin pháp lý (Bắt buộc)</h6>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Số CCCD / CMND <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="CCCD" id="cccd" placeholder="Nhập 9-12 số" maxlength="12">
                                        </div>
                                        
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Ngày tháng năm sinh <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="NgaySinh" id="ngay_sinh" onchange="kiemTraTuoiHopLe()">
                                            <small id="thong_bao_tuoi" class="text-danger fw-bold d-none mt-1">
                                                <i class="bi bi-x-circle"></i> Bạn chưa đủ 18 tuổi!
                                            </small>
                                        </div>
                                    </div>
                                    <hr>

                                    <div id="resultBox" style="display:none;" class="alert alert-info rounded p-3">
                                        <div class="mb-2">
                                            <span>Số tiền còn lại trả:</span>
                                            <span class="fw-bold float-end" id="soTienConLai">0 đ</span>
                                        </div>
                                        <div class="mb-2">
                                            <span>Tính lãi:</span>
                                            <span class="fw-bold float-end" id="tienLai">0 đ</span>
                                        </div>
                                        <hr>
                                        <div class="mb-2 fs-5">
                                            <span>Tổng phải trả:</span>
                                            <span class="fw-bold text-danger float-end" id="tongPhaiTra">0 đ</span>
                                        </div>
                                        <hr>
                                        <div>
                                            <span>Góp mỗi tháng:</span>
                                            <span class="fw-bold text-success float-end" id="tienGopMoiThang">0 đ</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Ghi chú cho đơn hàng (Tùy chọn)</label>
                                <textarea name="GhiChuHoaDon" class="form-control bg-light" rows="4" placeholder="Ví dụ: Giao hàng vào giờ hành chính, bọc kỹ hàng giúp mình..."></textarea>
                            </div>

                            <button type="submit" id="btnDatHang" class="btn btn-success w-100 fs-5 py-2 fw-bold shadow-sm">
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

                        <ul class="list-group mb-3">
                            <?php
                            foreach ($_SESSION['gio_hang'] as $id_sp => $so_luong) {
                                $id_sp = (int)$id_sp;
                                $sql_item = "SELECT TenSanPham, HinhAnh, DonGia, PhanTramGiam FROM sanpham WHERE ID = ?";
                                $stmt_item = $conn->prepare($sql_item);
                                $stmt_item->bind_param("i", $id_sp);
                                $stmt_item->execute();
                                $res_item = $stmt_item->get_result();
                                if ($row_item = $res_item->fetch_assoc()) {
                                    $hinh = !empty($row_item['HinhAnh']) ? 'uploads/'.$row_item['HinhAnh'] : 'uploads/no-image.jpg';
                                    $giaGoc = (float)$row_item['DonGia'];
                                    $phanTram = isset($row_item['PhanTramGiam']) ? (float)$row_item['PhanTramGiam'] : 0;
                                    $gia = $giaGoc - ($giaGoc * $phanTram / 100);
                                    $subtotal = $gia * (int)$so_luong;
                                    echo '<li class="list-group-item d-flex gap-3 align-items-center">'
                                        . '<img src="'.$hinh.'" alt="" width="60" class="rounded">'
                                        . '<div class="flex-grow-1">'
                                            . '<div class="fw-bold">'.htmlspecialchars($row_item['TenSanPham']).'</div>'
                                            . '<div class="small text-muted">Số lượng: '.(int)$so_luong.' • Giá: '.number_format($gia,0,',','.').' đ</div>'
                                        . '</div>'
                                        . '<div class="text-end fw-bold text-danger">'.number_format($subtotal,0,',','.').' đ</div>'
                                    . '</li>';
                                }
                                $stmt_item->close();
                            }
                            ?>
                        </ul>

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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ===== JAVASCRIPT TÍNH TOÁN & VALIDATION =====
const TONG_TIEN_GIO_HANG = <?php echo $tongTienGioHang; ?>;

// 1. Tắt/Bật vùng Trả góp
function toggleTraGopBox() {
    const tragop = document.getElementById('tragop');
    const boxTraGop = document.getElementById('boxTraGop');
    
    if (tragop.checked) {
        boxTraGop.style.display = 'block';
        calculateInstallment();
        kiemTraTuoiHopLe(); // Chạy lại kiểm tra khi bật trả góp lên
    } else {
        boxTraGop.style.display = 'none';
        document.getElementById('resultBox').style.display = 'none';
        
        // Nếu chọn thanh toán hết thì mở khóa nút đặt hàng luôn
        const btnDatHang = document.getElementById('btnDatHang');
        if(btnDatHang) btnDatHang.disabled = false;
    }
}

// 2. Tính toán tiền trả góp Real-time
function calculateInstallment() {
    const soTienTraTruoc = parseFloat(document.getElementById('soTienTraTruoc').value) || 0;
    const soThangTraGop = parseInt(document.getElementById('soThangTraGop').value) || 6;
    const laiSuat = parseFloat(document.getElementById('laiSuat').value) || 1.5;
    
    if (soTienTraTruoc < 0 || soTienTraTruoc > TONG_TIEN_GIO_HANG) {
        document.getElementById('resultBox').style.display = 'none';
        return;
    }
    
    const soTienConLai = TONG_TIEN_GIO_HANG - soTienTraTruoc;
    const tienLai = soTienConLai * (laiSuat / 100 / 12) * soThangTraGop;
    const tongPhaiTra = soTienConLai + tienLai;
    const tienGopMoiThang = tongPhaiTra / soThangTraGop;
    
    document.getElementById('soTienConLai').textContent = formatCurrency(soTienConLai);
    document.getElementById('tienLai').textContent = formatCurrency(tienLai);
    document.getElementById('tongPhaiTra').textContent = formatCurrency(tongPhaiTra);
    document.getElementById('tienGopMoiThang').textContent = formatCurrency(tienGopMoiThang);
    
    document.getElementById('resultBox').style.display = 'block';
}

function formatCurrency(value) {
    return Math.floor(value).toLocaleString('vi-VN') + ' đ';
}

// 3. Kiểm tra Tuổi (> 18 tuổi)
function kiemTraTuoiHopLe() {
    const inputNgaySinh = document.getElementById('ngay_sinh').value;
    const thongBaoLoi = document.getElementById('thong_bao_tuoi');
    const btnDatHang = document.getElementById('btnDatHang');

    // Nếu chưa chọn ngày sinh thì chưa làm gì cả
    if (!inputNgaySinh) return false;

    const ngaySinh = new Date(inputNgaySinh);
    const homNay = new Date();
    
    let tuoi = homNay.getFullYear() - ngaySinh.getFullYear();
    const thang = homNay.getMonth() - ngaySinh.getMonth();
    
    // Nếu chưa tới tháng sinh, hoặc bằng tháng nhưng chưa tới ngày thì trừ đi 1 tuổi
    if (thang < 0 || (thang === 0 && homNay.getDate() < ngaySinh.getDate())) {
        tuoi--;
    }

    if (tuoi < 18) {
        // Chưa đủ tuổi: Hiện cảnh báo đỏ, KHÓA nút gửi
        thongBaoLoi.classList.remove('d-none');
        if(btnDatHang) btnDatHang.disabled = true;
        return false;
    } else {
        // Đủ tuổi: Ẩn thông báo, MỞ nút gửi
        thongBaoLoi.classList.add('d-none');
        if(btnDatHang) btnDatHang.disabled = false;
        return true;
    }
}

// 4. Kiểm tra Validation cuối cùng khi Submit Form
function validateThanhToan() {
    const tragop = document.getElementById('tragop');
    
    // Chỉ kiểm tra pháp lý nếu khách hàng chọn Mua Trả Góp
    if (tragop && tragop.checked) {
        const cccd = document.getElementById('cccd').value.trim();
        const ngaySinh = document.getElementById('ngay_sinh').value;

        if (!cccd || cccd.length < 9) {
            alert('Vui lòng nhập số CCCD / CMND hợp lệ!');
            document.getElementById('cccd').focus();
            return false;
        }

        if (!ngaySinh) {
            alert('Vui lòng cung cấp ngày tháng năm sinh để xác minh!');
            document.getElementById('ngay_sinh').focus();
            return false;
        }

        if (!kiemTraTuoiHopLe()) {
            alert('Bạn phải từ đủ 18 tuổi trở lên để tham gia mua trả góp!');
            return false;
        }
    }
    
    return true; // OK cho đi tiếp
}

// Chạy hàm kiểm tra ngay khi load lại trang
document.addEventListener('DOMContentLoaded', function() {
    const tragop = document.getElementById('tragop');
    if (tragop && tragop.checked) {
        toggleTraGopBox();
    }
});
</script>
</body>
</html>