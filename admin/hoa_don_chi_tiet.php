<?php
session_start();
require_once '../connect.php';

// Lấy ID hóa đơn từ thanh địa chỉ (URL)
$id_hoadon = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_hoadon == 0) {
    echo "<script>alert('Không tìm thấy mã hóa đơn!'); window.location.href='hoa_don.php';</script>";
    exit();
}

$thongBao = '';

// --- XỬ LÝ KHI ADMIN BẤM NÚT "DUYỆT ĐƠN HÀNG" ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['duyet_don'])) {
    $nhanVienID = isset($_SESSION['nhan_vien_id']) ? (int)$_SESSION['nhan_vien_id'] : 1;

    $conn->begin_transaction();

    try {
        // 1. Duyệt hóa đơn
        $sql_duyet = "UPDATE hoadon SET NhanVienID = ? WHERE ID = ?";
        $stmt_duyet = $conn->prepare($sql_duyet);
        $stmt_duyet->bind_param("ii", $nhanVienID, $id_hoadon);
        $stmt_duyet->execute();

        // 2. Lấy danh sách sản phẩm trong hóa đơn
        $sql_sp = "SELECT SanPhamID, SoLuongBan 
                   FROM hoadon_chitiet 
                   WHERE HoaDonID = ?";
        $stmt_sp = $conn->prepare($sql_sp);
        $stmt_sp->bind_param("i", $id_hoadon);
        $stmt_sp->execute();
        $result_sp = $stmt_sp->get_result();

        while ($sp = $result_sp->fetch_assoc()) {
            $sanPhamID = (int)$sp['SanPhamID'];
            $soLuong = (int)$sp['SoLuongBan'];

            // Đếm xem hiện tại đã có bao nhiêu dòng bảo hành cho hóa đơn + sản phẩm này
            $sql_dem = "SELECT COUNT(*) AS tong
                        FROM baohanh
                        WHERE HoaDonID = ? AND SanPhamID = ?";
            $stmt_dem = $conn->prepare($sql_dem);
            $stmt_dem->bind_param("ii", $id_hoadon, $sanPhamID);
            $stmt_dem->execute();
            $row_dem = $stmt_dem->get_result()->fetch_assoc();

            $soLuongDaCo = (int)$row_dem['tong'];

            // Nếu chưa đủ số lượng thì tạo thêm
            for ($i = $soLuongDaCo + 1; $i <= $soLuong; $i++) {
                $ngayKichHoat = date('Y-m-d');
                $ngayHetHan = date('Y-m-d', strtotime('+24 months'));

                // Tạo serial tự động
                $soSerial = 'BH-' . $id_hoadon . '-' . $sanPhamID . '-' . $i . '-' . strtoupper(substr(md5(uniqid()), 0, 5));

                $sql_insert_bh = "INSERT INTO baohanh (HoaDonID, SanPhamID, SoSerial, NgayKichHoat, NgayHetHan, TrangThai)
                                  VALUES (?, ?, ?, ?, ?, 'Đang bảo hành')";
                $stmt_insert_bh = $conn->prepare($sql_insert_bh);
                $stmt_insert_bh->bind_param("iisss", $id_hoadon, $sanPhamID, $soSerial, $ngayKichHoat, $ngayHetHan);
                $stmt_insert_bh->execute();
            }
        }

        $conn->commit();
        $thongBao = "<div class='alert alert-success'><i class='bi bi-check-circle'></i> Đã duyệt đơn hàng và tự tạo phiếu bảo hành thành công!</div>";
    } catch (Exception $e) {
        $conn->rollback();
        $thongBao = "<div class='alert alert-danger'>Lỗi: " . $e->getMessage() . "</div>";
    }
}

// --- TRUY VẤN LẤY THÔNG TIN HÓA ĐƠN & KHÁCH HÀNG ---
$sql_hd = "SELECT h.*, k.HoVaTen, k.DienThoai, k.DiaChi 
           FROM hoadon h 
           JOIN khachhang k ON h.KhachHangID = k.ID 
           WHERE h.ID = ?";
$stmt_hd = $conn->prepare($sql_hd);
$stmt_hd->bind_param("i", $id_hoadon);
$stmt_hd->execute();
$hoaDon = $stmt_hd->get_result()->fetch_assoc();

if (!$hoaDon) {
    echo "<script>alert('Hóa đơn không tồn tại!'); window.location.href='hoa_don.php';</script>";
    exit();
}

// --- TRUY VẤN LẤY TÊN NHÂN VIÊN ĐÃ DUYỆT (NẾU CÓ) ---
$tenNhanVienDuyet = "Chưa có (Đang chờ duyệt)";
$daDuyet = false;

if (!empty($hoaDon['NhanVienID'])) {
    $daDuyet = true;
    $sql_nv = "SELECT HoVaTen FROM nhanvien WHERE ID = ?";
    $stmt_nv = $conn->prepare($sql_nv);
    $stmt_nv->bind_param("i", $hoaDon['NhanVienID']);
    $stmt_nv->execute();
    $res_nv = $stmt_nv->get_result();
    if ($row_nv = $res_nv->fetch_assoc()) {
        $tenNhanVienDuyet = $row_nv['HoVaTen'];
    }
}

// --- TRUY VẤN LẤY CHI TIẾT SẢN PHẨM TRONG ĐƠN HÀNG ---
$sql_ct = "SELECT ct.SanPhamID, ct.SoLuongBan, ct.DonGiaBan, sp.TenSanPham, sp.HinhAnh 
           FROM hoadon_chitiet ct 
           JOIN sanpham sp ON ct.SanPhamID = sp.ID 
           WHERE ct.HoaDonID = ?";
$stmt_ct = $conn->prepare($sql_ct);
$stmt_ct->bind_param("i", $id_hoadon);
$stmt_ct->execute();
$chiTietResult = $stmt_ct->get_result();

// Đưa dữ liệu vào mảng để dễ dùng lại
$danhSachSanPham = [];
$tongTienDonHang = 0;

while ($item = $chiTietResult->fetch_assoc()) {
    $item['ThanhTien'] = $item['SoLuongBan'] * $item['DonGiaBan'];
    $tongTienDonHang += $item['ThanhTien'];
    $danhSachSanPham[] = $item;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết Đơn hàng #HD<?php echo $id_hoadon; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container mt-4 mb-5" style="max-width: 900px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold">Chi tiết hóa đơn #HD<?php echo $id_hoadon; ?></h3>
        <a href="hoa_don.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại danh sách
        </a>
    </div>

    <?php echo $thongBao; ?>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-white pb-0 border-0 mt-2">
                    <h5 class="fw-bold"><i class="bi bi-person-lines-fill text-primary"></i> Thông tin Khách hàng</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Họ tên:</strong> <?php echo htmlspecialchars($hoaDon['HoVaTen']); ?></p>
                    <p class="mb-2"><strong>Điện thoại:</strong> <?php echo htmlspecialchars($hoaDon['DienThoai']); ?></p>
                    <p class="mb-2"><strong>Địa chỉ giao:</strong> <?php echo htmlspecialchars($hoaDon['DiaChi']); ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-white pb-0 border-0 mt-2">
                    <h5 class="fw-bold"><i class="bi bi-info-circle-fill text-info"></i> Thông tin Hóa đơn</h5>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Ngày đặt:</strong> <?php echo date('H:i - d/m/Y', strtotime($hoaDon['NgayLap'])); ?></p>
                    <p class="mb-2"><strong>Ghi chú:</strong> <?php echo !empty($hoaDon['GhiChuHoaDon']) ? htmlspecialchars($hoaDon['GhiChuHoaDon']) : 'Không có'; ?></p>
                    <p class="mb-0">
                        <strong>Trạng thái:</strong>
                        <?php if ($daDuyet): ?>
                            <span class="badge bg-success">Đã duyệt bởi: <?php echo htmlspecialchars($tenNhanVienDuyet); ?></span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Chờ xử lý</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Sản phẩm</th>
                            <th class="text-center">Số lượng</th>
                            <th class="text-end">Đơn giá</th>
                            <th class="text-end pe-3">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($danhSachSanPham as $item): ?>
                            <?php
                                $hinhAnh = !empty($item['HinhAnh']) ? "../uploads/" . $item['HinhAnh'] : "../uploads/no-image.jpg";
                            ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <img src="<?php echo $hinhAnh; ?>" width="50" class="rounded me-3 border" alt="Tivi">
                                        <span class="fw-bold"><?php echo htmlspecialchars($item['TenSanPham']); ?></span>
                                    </div>
                                </td>
                                <td class="text-center"><?php echo (int)$item['SoLuongBan']; ?></td>
                                <td class="text-end text-danger"><?php echo number_format($item['DonGiaBan'], 0, ',', '.'); ?> đ</td>
                                <td class="text-end fw-bold text-danger pe-3"><?php echo number_format($item['ThanhTien'], 0, ',', '.'); ?> đ</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fs-5"><strong>Tổng cộng:</strong></td>
                            <td class="text-end fs-4 fw-bold text-danger pe-3"><?php echo number_format($tongTienDonHang, 0, ',', '.'); ?> đ</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="card-footer bg-white p-4">
            <div class="d-flex justify-content-end gap-2 flex-wrap">
                <?php if (!$daDuyet): ?>
                    <form action="" method="POST" class="m-0">
                        <button type="submit" name="duyet_don" class="btn btn-success btn-lg px-4">
                            <i class="bi bi-check2-all"></i> DUYỆT ĐƠN HÀNG
                        </button>
                    </form>

                    <button class="btn btn-outline-secondary btn-lg px-4" disabled>
                        <i class="bi bi-printer"></i> Xuất hóa đơn
                    </button>
                <?php else: ?>
                    <a href="xuat_hoa_don.php?id=<?php echo $id_hoadon; ?>" target="_blank" class="btn btn-primary btn-lg px-4">
                        <i class="bi bi-printer-fill"></i> Xuất hóa đơn
                    </a>

                    <button class="btn btn-secondary btn-lg px-4" disabled>
                        <i class="bi bi-lock-fill"></i> Đơn hàng đã được duyệt
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

</body>
</html>