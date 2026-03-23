<?php
session_start();
require_once '../connect.php';

$hoaDonID = isset($_GET['hoadon_id']) ? (int)$_GET['hoadon_id'] : 0;
$sanPhamID = isset($_GET['sanpham_id']) ? (int)$_GET['sanpham_id'] : 0;

if ($hoaDonID <= 0 || $sanPhamID <= 0) {
    echo "<script>alert('Không tìm thấy nhóm bảo hành!'); window.location.href='bao_hanh.php';</script>";
    exit();
}

// Lấy thông tin chung khách hàng + hóa đơn + sản phẩm
$sql_info = "SELECT 
                h.ID AS MaHoaDon,
                h.NgayLap,
                h.GhiChuHoaDon,
                k.HoVaTen,
                k.DienThoai,
                k.DiaChi,
                sp.ID AS MaSanPham,
                sp.TenSanPham,
                sp.HinhAnh
             FROM hoadon h
             JOIN khachhang k ON h.KhachHangID = k.ID
             JOIN sanpham sp ON sp.ID = ?
             WHERE h.ID = ?";

$stmt_info = $conn->prepare($sql_info);
$stmt_info->bind_param("ii", $sanPhamID, $hoaDonID);
$stmt_info->execute();
$thongTin = $stmt_info->get_result()->fetch_assoc();

if (!$thongTin) {
    echo "<script>alert('Dữ liệu bảo hành không tồn tại!'); window.location.href='bao_hanh.php';</script>";
    exit();
}

// Lấy danh sách các serial bảo hành của cùng hóa đơn + cùng sản phẩm
$sql_ds = "SELECT *
           FROM baohanh
           WHERE HoaDonID = ? AND SanPhamID = ?
           ORDER BY ID ASC";

$stmt_ds = $conn->prepare($sql_ds);
$stmt_ds->bind_param("ii", $hoaDonID, $sanPhamID);
$stmt_ds->execute();
$result_ds = $stmt_ds->get_result();

$danhSachBaoHanh = [];
while ($row = $result_ds->fetch_assoc()) {
    $danhSachBaoHanh[] = $row;
}

if (count($danhSachBaoHanh) == 0) {
    echo "<script>alert('Không có phiếu bảo hành nào!'); window.location.href='bao_hanh.php';</script>";
    exit();
}

$hinhAnh = !empty($thongTin['HinhAnh']) ? "../uploads/" . $thongTin['HinhAnh'] : "../uploads/no-image.jpg";
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Chi tiết bảo hành - Hóa đơn #HD<?php echo $hoaDonID; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container mt-4 mb-5" style="max-width: 1100px;">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold">
            Chi tiết bảo hành - #HD<?php echo $hoaDonID; ?>
        </h3>
        <a href="bao_hanh.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Quay lại danh sách
        </a>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-person-lines-fill text-primary"></i> Thông tin khách hàng
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Họ tên:</strong> <?php echo htmlspecialchars($thongTin['HoVaTen']); ?></p>
                    <p><strong>Điện thoại:</strong> <?php echo htmlspecialchars($thongTin['DienThoai']); ?></p>
                    <p><strong>Địa chỉ:</strong> <?php echo htmlspecialchars($thongTin['DiaChi']); ?></p>
                    <p><strong>Mã hóa đơn:</strong> #HD<?php echo $thongTin['MaHoaDon']; ?></p>
                    <p><strong>Ngày mua:</strong> <?php echo date('d/m/Y', strtotime($thongTin['NgayLap'])); ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0">
                    <h5 class="fw-bold mb-0">
                        <i class="bi bi-shield-check text-success"></i> Thông tin bảo hành
                    </h5>
                </div>
                <div class="card-body">
                    <p><strong>Sản phẩm:</strong> <?php echo htmlspecialchars($thongTin['TenSanPham']); ?></p>
                    <p><strong>Số lượng bảo hành:</strong> <?php echo count($danhSachBaoHanh); ?></p>
                    <p><strong>Ngày kích hoạt đầu tiên:</strong> <?php echo date('d/m/Y', strtotime($danhSachBaoHanh[0]['NgayKichHoat'])); ?></p>
                    <p><strong>Ngày hết hạn:</strong> <?php echo date('d/m/Y', strtotime($danhSachBaoHanh[0]['NgayHetHan'])); ?></p>
                    <p>
                        <strong>Trạng thái:</strong>
                        <?php if ($danhSachBaoHanh[0]['TrangThai'] == 'Đang bảo hành'): ?>
                            <span class="badge bg-success">Đang bảo hành</span>
                        <?php elseif ($danhSachBaoHanh[0]['TrangThai'] == 'Hết hạn'): ?>
                            <span class="badge bg-secondary">Hết hạn</span>
                        <?php else: ?>
                            <span class="badge bg-danger"><?php echo htmlspecialchars($danhSachBaoHanh[0]['TrangThai']); ?></span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0 mt-3">
        <div class="card-body">
            <h5 class="fw-bold mb-3"><i class="bi bi-tv"></i> Sản phẩm bảo hành</h5>

            <div class="d-flex align-items-center mb-4">
                <img src="<?php echo $hinhAnh; ?>" width="80" class="rounded me-3 border" alt="Tivi">
                <div>
                    <h5 class="mb-1"><?php echo htmlspecialchars($thongTin['TenSanPham']); ?></h5>
                    <p class="mb-0 text-muted">
                        Hóa đơn #HD<?php echo $thongTin['MaHoaDon']; ?> -
                        Số lượng bảo hành: <strong><?php echo count($danhSachBaoHanh); ?></strong>
                    </p>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 80px;">STT</th>
                            <th style="width: 120px;">Mã BH</th>
                            <th>Số serial</th>
                            <th style="width: 150px;">Ngày kích hoạt</th>
                            <th style="width: 150px;">Ngày hết hạn</th>
                            <th style="width: 150px;">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $stt = 1; ?>
                        <?php foreach ($danhSachBaoHanh as $bh): ?>
                            <tr>
                                <td class="text-center"><?php echo $stt++; ?></td>
                                <td>#BH<?php echo $bh['ID']; ?></td>
                                <td><?php echo htmlspecialchars($bh['SoSerial']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($bh['NgayKichHoat'])); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($bh['NgayHetHan'])); ?></td>
                                <td>
                                    <?php if ($bh['TrangThai'] == 'Đang bảo hành'): ?>
                                        <span class="badge bg-success">Đang bảo hành</span>
                                    <?php elseif ($bh['TrangThai'] == 'Hết hạn'): ?>
                                        <span class="badge bg-secondary">Hết hạn</span>
                                    <?php else: ?>
                                        <span class="badge bg-danger"><?php echo htmlspecialchars($bh['TrangThai']); ?></span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <p class="mt-3 mb-0">
                <strong>Ghi chú hóa đơn:</strong>
                <?php echo !empty($thongTin['GhiChuHoaDon']) ? htmlspecialchars($thongTin['GhiChuHoaDon']) : 'Không có'; ?>
            </p>
        </div>

        <div class="card-footer bg-white text-end p-4">
            <a href="xuat_bao_hanh.php?hoadon_id=<?php echo $hoaDonID; ?>&sanpham_id=<?php echo $sanPhamID; ?>" target="_blank" class="btn btn-warning btn-lg text-dark">
                <i class="bi bi-printer-fill"></i> Xuất phiếu bảo hành
            </a>
        </div>
    </div>

</div>

</body>
</html>