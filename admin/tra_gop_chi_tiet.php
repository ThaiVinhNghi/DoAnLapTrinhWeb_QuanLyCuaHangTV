<?php
session_start();
require_once '../connect.php';

$id_tragop = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_tragop <= 0) {
    echo "<script>alert('Không tìm thấy mã trả góp!'); window.location.href='tra_gop.php';</script>";
    exit();
}

$thongBao = '';

// =========================
// 1. DUYỆT HỒ SƠ
// =========================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['duyet_tragop'])) {
    $nhanVienID = isset($_SESSION['nhan_vien_id']) ? (int)$_SESSION['nhan_vien_id'] : 1;

    $conn->begin_transaction();

    try {
        $sql_tg_check = "SELECT * FROM tragop WHERE ID = ?";
        $stmt_check = $conn->prepare($sql_tg_check);
        $stmt_check->bind_param("i", $id_tragop);
        $stmt_check->execute();
        $tragopCheck = $stmt_check->get_result()->fetch_assoc();

        if (!$tragopCheck) {
            throw new Exception("Hồ sơ trả góp không tồn tại.");
        }

        if ($tragopCheck['TrangThai'] != 'Chờ duyệt') {
            throw new Exception("Hồ sơ này đã được xử lý.");
        }

        $ngayLap = date('Y-m-d H:i:s');
        $ghiChuHoaDon = 'Sinh từ hồ sơ trả góp #TG' . $id_tragop;

        $sql_hd = "INSERT INTO hoadon (NhanVienID, KhachHangID, NgayLap, GhiChuHoaDon)
                   VALUES (?, ?, ?, ?)";
        $stmt_hd = $conn->prepare($sql_hd);
        $stmt_hd->bind_param("iiss", $nhanVienID, $tragopCheck['KhachHangID'], $ngayLap, $ghiChuHoaDon);
        $stmt_hd->execute();

        $id_hoadon_moi = $conn->insert_id;

        $sql_ct = "SELECT * FROM tragop_chitiet WHERE TraGopID = ?";
        $stmt_ct = $conn->prepare($sql_ct);
        $stmt_ct->bind_param("i", $id_tragop);
        $stmt_ct->execute();
        $result_ct = $stmt_ct->get_result();

        while ($ct = $result_ct->fetch_assoc()) {
            $sql_insert_hdct = "INSERT INTO hoadon_chitiet (HoaDonID, SanPhamID, SoLuongBan, DonGiaBan)
                                VALUES (?, ?, ?, ?)";
            $stmt_insert_hdct = $conn->prepare($sql_insert_hdct);
            $stmt_insert_hdct->bind_param("iiid", $id_hoadon_moi, $ct['SanPhamID'], $ct['SoLuong'], $ct['DonGia']);
            $stmt_insert_hdct->execute();
        }

        $trangThaiMoi = 'Đã chuyển hóa đơn';
        $tinhTrangTraMoi = 'Đang góp';

        $sql_update_tg = "UPDATE tragop
                          SET NhanVienID = ?, HoaDonID = ?, TrangThai = ?, TinhTrangTra = ?
                          WHERE ID = ?";
        $stmt_update_tg = $conn->prepare($sql_update_tg);
        $stmt_update_tg->bind_param("iissi", $nhanVienID, $id_hoadon_moi, $trangThaiMoi, $tinhTrangTraMoi, $id_tragop);
        $stmt_update_tg->execute();

        $conn->commit();
        $thongBao = "<div class='alert alert-success'><i class='bi bi-check-circle'></i> Đã duyệt hồ sơ trả góp và tạo hóa đơn #HD{$id_hoadon_moi} thành công!</div>";
    } catch (Exception $e) {
        $conn->rollback();
        $thongBao = "<div class='alert alert-danger'>Lỗi: " . $e->getMessage() . "</div>";
    }
}

// =========================
// 2. THU TIỀN
// =========================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['thu_tien'])) {
    $nhanVienID = isset($_SESSION['nhan_vien_id']) ? (int)$_SESSION['nhan_vien_id'] : 1;
    $soTienThu = isset($_POST['SoTienThu']) ? (float)$_POST['SoTienThu'] : 0;
    $kyThu = isset($_POST['KyThu']) ? (int)$_POST['KyThu'] : 1;
    $ghiChuThu = isset($_POST['GhiChuThu']) ? trim($_POST['GhiChuThu']) : '';
    $ngayThu = date('Y-m-d H:i:s');

    $conn->begin_transaction();

    try {
        $sql_tg = "SELECT TongPhaiTra, SoTienDaTra, TinhTrangTra FROM tragop WHERE ID = ?";
        $stmt_tg = $conn->prepare($sql_tg);
        $stmt_tg->bind_param("i", $id_tragop);
        $stmt_tg->execute();
        $tragopThu = $stmt_tg->get_result()->fetch_assoc();

        if (!$tragopThu) {
            throw new Exception("Không tìm thấy hồ sơ trả góp.");
        }

        if ($tragopThu['TinhTrangTra'] == 'Đã tất toán') {
            throw new Exception("Hồ sơ này đã tất toán.");
        }

        if ($tragopThu['TinhTrangTra'] == 'Nợ xấu') {
            throw new Exception("Hồ sơ này đang ở trạng thái nợ xấu.");
        }

        if ($soTienThu <= 0) {
            throw new Exception("Số tiền thu không hợp lệ.");
        }

        $sql_insert = "INSERT INTO tragop_thanhtoan
                       (TraGopID, NhanVienID, NgayThu, KyThu, SoTienThu, GhiChu)
                       VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iisids", $id_tragop, $nhanVienID, $ngayThu, $kyThu, $soTienThu, $ghiChuThu);
        $stmt_insert->execute();

        $soTienDaTraMoi = $tragopThu['SoTienDaTra'] + $soTienThu;
        $tinhTrangTraMoi = ($soTienDaTraMoi >= $tragopThu['TongPhaiTra']) ? 'Đã tất toán' : 'Đang góp';

        $sql_update = "UPDATE tragop
                       SET SoTienDaTra = ?, TinhTrangTra = ?
                       WHERE ID = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("dsi", $soTienDaTraMoi, $tinhTrangTraMoi, $id_tragop);
        $stmt_update->execute();

        $conn->commit();
        $thongBao = "<div class='alert alert-success'><i class='bi bi-cash-coin'></i> Thu tiền thành công!</div>";
    } catch (Exception $e) {
        $conn->rollback();
        $thongBao = "<div class='alert alert-danger'>Lỗi thu tiền: " . $e->getMessage() . "</div>";
    }
}

// =========================
// 3. NHẮC NHỞ
// =========================
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nhac_nho'])) {
    $conn->begin_transaction();

    try {
        $sql_lay = "SELECT SoLanNhacNho, TinhTrangTra FROM tragop WHERE ID = ?";
        $stmt_lay = $conn->prepare($sql_lay);
        $stmt_lay->bind_param("i", $id_tragop);
        $stmt_lay->execute();
        $row_lay = $stmt_lay->get_result()->fetch_assoc();

        if (!$row_lay) {
            throw new Exception("Không tìm thấy hồ sơ trả góp.");
        }

        if ($row_lay['TinhTrangTra'] == 'Đã tất toán') {
            throw new Exception("Hồ sơ này đã tất toán.");
        }

        if ($row_lay['TinhTrangTra'] == 'Nợ xấu') {
            throw new Exception("Hồ sơ này đã là nợ xấu.");
        }

        $soLanNhac = (int)$row_lay['SoLanNhacNho'] + 1;
        $tinhTrangTra = ($soLanNhac >= 3) ? 'Nợ xấu' : 'Đang góp';

        $sql_update = "UPDATE tragop
                       SET SoLanNhacNho = ?, TinhTrangTra = ?
                       WHERE ID = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("isi", $soLanNhac, $tinhTrangTra, $id_tragop);
        $stmt_update->execute();

        $conn->commit();

        if ($soLanNhac >= 3) {
            $thongBao = "<div class='alert alert-danger'><i class='bi bi-exclamation-triangle'></i> Đã nhắc đủ 3 lần. Hồ sơ này đã chuyển sang <strong>Nợ xấu</strong>.</div>";
        } else {
            $thongBao = "<div class='alert alert-warning'><i class='bi bi-bell'></i> Đã nhắc nhở khách hàng lần <strong>{$soLanNhac}</strong>.</div>";
        }
    } catch (Exception $e) {
        $conn->rollback();
        $thongBao = "<div class='alert alert-danger'>Lỗi nhắc nhở: " . $e->getMessage() . "</div>";
    }
}

// =========================
// 4. LẤY THÔNG TIN HỒ SƠ
// =========================
$sql = "SELECT tg.*, kh.HoVaTen, kh.DienThoai, kh.DiaChi
        FROM tragop tg
        JOIN khachhang kh ON tg.KhachHangID = kh.ID
        WHERE tg.ID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_tragop);
$stmt->execute();
$traGop = $stmt->get_result()->fetch_assoc();

if (!$traGop) {
    echo "<script>alert('Hồ sơ trả góp không tồn tại!'); window.location.href='tra_gop.php';</script>";
    exit();
}

// =========================
// 5. CHI TIẾT SẢN PHẨM
// =========================
$sql_ct = "SELECT ct.*, sp.TenSanPham, sp.HinhAnh
           FROM tragop_chitiet ct
           JOIN sanpham sp ON ct.SanPhamID = sp.ID
           WHERE ct.TraGopID = ?";
$stmt_ct = $conn->prepare($sql_ct);
$stmt_ct->bind_param("i", $id_tragop);
$stmt_ct->execute();
$result_ct = $stmt_ct->get_result();

$danhSachSanPham = [];
while ($row = $result_ct->fetch_assoc()) {
    $danhSachSanPham[] = $row;
}

// =========================
// 6. LỊCH SỬ THU TIỀN
// =========================
$sql_thu = "SELECT tt.*, nv.HoVaTen
            FROM tragop_thanhtoan tt
            LEFT JOIN nhanvien nv ON tt.NhanVienID = nv.ID
            WHERE tt.TraGopID = ?
            ORDER BY tt.ID DESC";
$stmt_thu = $conn->prepare($sql_thu);
$stmt_thu->bind_param("i", $id_tragop);
$stmt_thu->execute();
$result_thu = $stmt_thu->get_result();

$conNo = max(0, $traGop['TongPhaiTra'] - $traGop['SoTienDaTra']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết trả góp #TG<?php echo $id_tragop; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container mt-4 mb-5" style="max-width: 1100px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold">Chi tiết hồ sơ trả góp #TG<?php echo $id_tragop; ?></h3>
        <a href="tra_gop.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại danh sách
        </a>
    </div>

    <?php echo $thongBao; ?>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-person-lines-fill text-primary"></i> Thông tin khách hàng</h5>
                </div>
                <div class="card-body">
                    <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($traGop['HoVaTen']); ?></p>
                    <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($traGop['DienThoai']); ?></p>
                    <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($traGop['DiaChi']); ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="fw-bold mb-0"><i class="bi bi-credit-card text-success"></i> Thông tin trả góp</h5>
                </div>
                <div class="card-body">
                    <p><strong>Ngày đăng ký:</strong> <?php echo date('H:i - d/m/Y', strtotime($traGop['NgayDangKy'])); ?></p>
                    <p><strong>Tổng tiền hàng:</strong> <span class="text-danger fw-bold"><?php echo number_format($traGop['TongTien'], 0, ',', '.'); ?> đ</span></p>
                    <p><strong>Trả trước:</strong> <?php echo number_format($traGop['SoTienTraTruoc'], 0, ',', '.'); ?> đ</p>
                    <p><strong>Còn lại ban đầu:</strong> <?php echo number_format($traGop['SoTienConLai'], 0, ',', '.'); ?> đ</p>
                    <p><strong>Số tháng:</strong> <?php echo (int)$traGop['SoThangTraGop']; ?> tháng</p>
                    <p><strong>Lãi suất:</strong> <?php echo $traGop['LaiSuat']; ?> % / tháng</p>
                    <p><strong>Tổng phải trả:</strong> <?php echo number_format($traGop['TongPhaiTra'], 0, ',', '.'); ?> đ</p>
                    <p><strong>Góp mỗi tháng:</strong> <?php echo number_format($traGop['TienGopMoiThang'], 0, ',', '.'); ?> đ</p>
                    <p><strong>Đã trả:</strong> <span class="text-success fw-bold"><?php echo number_format($traGop['SoTienDaTra'], 0, ',', '.'); ?> đ</span></p>
                    <p><strong>Còn nợ:</strong> <span class="text-danger fw-bold"><?php echo number_format($conNo, 0, ',', '.'); ?> đ</span></p>
                    <p><strong>Số lần nhắc nhở:</strong> <?php echo (int)$traGop['SoLanNhacNho']; ?></p>

                    <p>
                        <strong>Hồ sơ:</strong>
                        <?php if ($traGop['TrangThai'] == 'Chờ duyệt'): ?>
                            <span class="badge bg-warning text-dark">Chờ duyệt</span>
                        <?php elseif ($traGop['TrangThai'] == 'Đã chuyển hóa đơn'): ?>
                            <span class="badge bg-primary">Đã chuyển hóa đơn</span>
                        <?php elseif ($traGop['TrangThai'] == 'Đã duyệt'): ?>
                            <span class="badge bg-success">Đã duyệt</span>
                        <?php else: ?>
                            <span class="badge bg-secondary"><?php echo htmlspecialchars($traGop['TrangThai']); ?></span>
                        <?php endif; ?>
                    </p>

                    <p>
                        <strong>Tình trạng trả:</strong>
                        <?php if ($traGop['TinhTrangTra'] == 'Chờ duyệt'): ?>
                            <span class="badge bg-secondary">Chờ duyệt</span>
                        <?php elseif ($traGop['TinhTrangTra'] == 'Đang góp'): ?>
                            <span class="badge bg-primary">Đang góp</span>
                        <?php elseif ($traGop['TinhTrangTra'] == 'Đã tất toán'): ?>
                            <span class="badge bg-success">Đã tất toán</span>
                        <?php elseif ($traGop['TinhTrangTra'] == 'Nợ xấu'): ?>
                            <span class="badge bg-danger">Nợ xấu</span>
                        <?php else: ?>
                            <span class="badge bg-dark"><?php echo htmlspecialchars($traGop['TinhTrangTra']); ?></span>
                        <?php endif; ?>
                    </p>

                    <p><strong>Ghi chú:</strong> <?php echo !empty($traGop['GhiChu']) ? htmlspecialchars($traGop['GhiChu']) : 'Không có'; ?></p>

                    <?php if (!empty($traGop['HoaDonID'])): ?>
                        <p class="mb-0"><strong>Hóa đơn đã tạo:</strong> <span class="text-primary fw-bold">#HD<?php echo $traGop['HoaDonID']; ?></span></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <h5 class="fw-bold mb-3"><i class="bi bi-tv"></i> Danh sách sản phẩm trả góp</h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Sản phẩm</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-end">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($danhSachSanPham as $sp): ?>
                            <?php $hinhAnh = !empty($sp['HinhAnh']) ? "../uploads/" . $sp['HinhAnh'] : "../uploads/no-image.jpg"; ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $hinhAnh; ?>" width="60" class="rounded me-3 border" alt="Tivi">
                                        <span class="fw-bold"><?php echo htmlspecialchars($sp['TenSanPham']); ?></span>
                                    </div>
                                </td>
                                <td class="text-center"><?php echo (int)$sp['SoLuong']; ?></td>
                                <td class="text-end"><?php echo number_format($sp['DonGia'], 0, ',', '.'); ?> đ</td>
                                <td class="text-end text-danger fw-bold"><?php echo number_format($sp['ThanhTien'], 0, ',', '.'); ?> đ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white p-4">
            <div class="d-flex flex-wrap gap-2 justify-content-end">
                <?php if ($traGop['TrangThai'] == 'Chờ duyệt'): ?>
                    <form action="" method="POST" class="m-0">
                        <button type="submit" name="duyet_tragop" class="btn btn-success btn-lg px-4">
                            <i class="bi bi-check2-all"></i> DUYỆT HỒ SƠ
                        </button>
                    </form>
                <?php endif; ?>

                <?php if ($traGop['TrangThai'] != 'Chờ duyệt' && $traGop['TinhTrangTra'] != 'Đã tất toán' && $traGop['TinhTrangTra'] != 'Nợ xấu'): ?>
                    <button class="btn btn-primary btn-lg px-4" data-bs-toggle="modal" data-bs-target="#modalThuTien">
                        <i class="bi bi-cash-coin"></i> Thu tiền
                    </button>

                    <form action="" method="POST" class="m-0">
                        <button type="submit" name="nhac_nho" class="btn btn-warning btn-lg px-4 text-dark">
                            <i class="bi bi-bell-fill"></i> Nhắc nhở
                        </button>
                    </form>
                <?php endif; ?>

                <?php if (!empty($traGop['HoaDonID'])): ?>
                    <a href="hoa_don_chi_tiet.php?id=<?php echo $traGop['HoaDonID']; ?>" class="btn btn-secondary btn-lg px-4">
                        <i class="bi bi-receipt"></i> Xem hóa đơn
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-4">
        <div class="card-body">
            <h5 class="fw-bold mb-3"><i class="bi bi-clock-history"></i> Lịch sử thu tiền</h5>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 90px;">Kỳ</th>
                            <th>Ngày thu</th>
                            <th class="text-end">Số tiền thu</th>
                            <th>Nhân viên</th>
                            <th>Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_thu && $result_thu->num_rows > 0): ?>
                            <?php while ($thu = $result_thu->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo (int)$thu['KyThu']; ?></td>
                                    <td><?php echo date('H:i - d/m/Y', strtotime($thu['NgayThu'])); ?></td>
                                    <td class="text-end text-success fw-bold"><?php echo number_format($thu['SoTienThu'], 0, ',', '.'); ?> đ</td>
                                    <td><?php echo !empty($thu['HoVaTen']) ? htmlspecialchars($thu['HoVaTen']) : 'Chưa rõ'; ?></td>
                                    <td><?php echo !empty($thu['GhiChu']) ? htmlspecialchars($thu['GhiChu']) : 'Không có'; ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-3">Chưa có lần thu tiền nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="modalThuTien" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form action="" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thu tiền trả góp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Số tiền thu</label>
                    <input type="number" name="SoTienThu" class="form-control" min="1000" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kỳ thu</label>
                    <input type="number" name="KyThu" class="form-control" min="1" value="1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ghi chú</label>
                    <input type="text" name="GhiChuThu" class="form-control" placeholder="Ví dụ: Khách đóng kỳ tháng 1">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="thu_tien" class="btn btn-success">Xác nhận thu tiền</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>