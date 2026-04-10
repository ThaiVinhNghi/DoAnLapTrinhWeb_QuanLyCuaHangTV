<?php
session_start();
require_once '../thu_vien/connect.php';

// Gọi giao diện Header và Sidebar
require_once 'header.php';
require_once 'sidebar.php';

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

<div class="col-md-9 col-lg-10 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold"><i class="bi bi-shield-check"></i> Quản lý Bảo Hành</h3>
        </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="80" class="ps-3 text-start">Mã BH</th>
                            <th width="80">Mã HĐ</th>
                            <th class="text-start">Khách Hàng</th>
                            <th class="text-start">Sản Phẩm</th>
                            <th width="80">SL</th>
                            <th>Ngày Kích Hoạt</th>
                            <th>Ngày Hết Hạn</th>
                            <th>Trạng Thái</th>
                            <th width="120">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="ps-3 fw-bold text-danger text-start">#BH<?php echo $row['ID']; ?></td>
                                    <td class="fw-bold">#HD<?php echo $row['HoaDonID']; ?></td>
                                    <td class="text-start">
                                        <span class="fw-bold text-primary"><?php echo htmlspecialchars($row['HoVaTen']); ?></span><br>
                                        <small class="text-muted"><i class="bi bi-telephone-fill"></i> <?php echo htmlspecialchars($row['DienThoai']); ?></small>
                                    </td>
                                    <td class="text-start fw-bold">
                                        <?php echo htmlspecialchars($row['TenSanPham']); ?>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill bg-info text-dark">
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
                                    <td>
                                        <a href="bao_hanh_chi_tiet.php?hoadon_id=<?php echo $row['HoaDonID']; ?>&sanpham_id=<?php echo $row['SanPhamID']; ?>" class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                            <i class="bi bi-eye"></i> Xem
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Chưa có dữ liệu bảo hành nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
// Gọi giao diện Footer
require_once 'footer.php'; 
?>