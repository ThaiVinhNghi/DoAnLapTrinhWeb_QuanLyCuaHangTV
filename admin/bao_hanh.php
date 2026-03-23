<?php
session_start();
require_once '../connect.php';

// Lấy danh sách bảo hành theo nhóm: 1 hóa đơn + 1 sản phẩm = 1 dòng
$sql = "SELECT 
            MIN(bh.ID) AS ID,
            bh.HoaDonID,
            bh.SanPhamID,
            COUNT(bh.ID) AS SoLuongBaoHanh,
            MIN(bh.NgayKichHoat) AS NgayKichHoat,
            MAX(bh.NgayHetHan) AS NgayHetHan,
            bh.TrangThai,
            sp.TenSanPham,
            k.HoVaTen,
            k.DienThoai
        FROM baohanh bh
        JOIN hoadon h ON bh.HoaDonID = h.ID
        JOIN khachhang k ON h.KhachHangID = k.ID
        JOIN sanpham sp ON bh.SanPhamID = sp.ID
        GROUP BY bh.HoaDonID, bh.SanPhamID, bh.TrangThai, sp.TenSanPham, k.HoVaTen, k.DienThoai
        ORDER BY MIN(bh.ID) DESC";

$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Bảo hành</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<div class="container mt-4 mb-5" style="max-width: 1450px;">
    <h2 class="text-primary fw-bold mb-4">
        <i class="bi bi-shield-check"></i> Quản lý Bảo hành
    </h2>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th class="ps-3">Mã BH</th>
                            <th>Mã HĐ</th>
                            <th>Khách hàng</th>
                            <th>Sản phẩm</th>
                            <th class="text-center">Số lượng BH</th>
                            <th>Ngày kích hoạt</th>
                            <th>Ngày hết hạn</th>
                            <th>Trạng thái</th>
                            <th class="text-center">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-danger">#BH<?php echo $row['ID']; ?></td>
                                    <td>#HD<?php echo $row['HoaDonID']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['HoVaTen']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($row['DienThoai']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['TenSanPham']); ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-info text-dark fs-6">
                                            <?php echo (int)$row['SoLuongBaoHanh']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('d/m/Y', strtotime($row['NgayKichHoat'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['NgayHetHan'])); ?></td>
                                    <td>
                                        <?php if ($row['TrangThai'] == 'Đang bảo hành'): ?>
                                            <span class="badge bg-success">Đang bảo hành</span>
                                        <?php elseif ($row['TrangThai'] == 'Hết hạn'): ?>
                                            <span class="badge bg-secondary">Hết hạn</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger"><?php echo htmlspecialchars($row['TrangThai']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="bao_hanh_chi_tiet.php?hoadon_id=<?php echo $row['HoaDonID']; ?>&sanpham_id=<?php echo $row['SanPhamID']; ?>" class="btn btn-info btn-sm text-white">
                                            <i class="bi bi-eye"></i> Xem chi tiết
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">Chưa có phiếu bảo hành nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>