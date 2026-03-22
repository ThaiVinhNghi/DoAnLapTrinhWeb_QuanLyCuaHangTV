<?php
session_start();
// Gọi file kết nối CSDL từ thư mục gốc
require_once '../connect.php';

// Câu lệnh SQL nối 2 bảng hoadon và khachhang để lấy thông tin người đặt
$sql = "SELECT h.ID, k.HoVaTen, k.DienThoai, h.NgayLap, h.NhanVienID, h.GhiChuHoaDon 
        FROM hoadon h
        JOIN khachhang k ON h.KhachHangID = k.ID
        ORDER BY h.NgayLap DESC"; // Sắp xếp đơn mới nhất lên đầu

$result = $conn->query($sql);

// Gọi giao diện Header và Sidebar
require_once 'header.php';
require_once 'sidebar.php';
?>

<div class="col-md-9 col-lg-10 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold"><i class="bi bi-receipt"></i> Quản lý Hóa Đơn (Đơn Đặt Hàng)</h3>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center mb-0">
                    <thead class="table-primary">
                        <tr>
                            <th>Mã ĐH</th>
                            <th class="text-start">Tên Khách Hàng</th>
                            <th>Số Điện Thoại</th>
                            <th>Ngày Đặt</th>
                            <th>Trạng Thái</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php 
                                    // Logic trạng thái: Nếu NhanVienID trống (NULL) tức là đơn online mới, chưa có nhân viên nào duyệt
                                    $trangThai = empty($row['NhanVienID']) 
                                        ? '<span class="badge bg-warning text-dark px-2 py-1"><i class="bi bi-hourglass-split"></i> Chờ duyệt</span>' 
                                        : '<span class="badge bg-success px-2 py-1"><i class="bi bi-check-circle"></i> Đã duyệt</span>';
                                ?>
                                <tr>
                                    <td class="fw-bold text-danger">#HD<?php echo $row['ID']; ?></td>
                                    <td class="text-start fw-bold"><?php echo htmlspecialchars($row['HoVaTen']); ?></td>
                                    <td><?php echo htmlspecialchars($row['DienThoai']); ?></td>
                                    <td><?php echo date('H:i - d/m/Y', strtotime($row['NgayLap'])); ?></td>
                                    <td><?php echo $trangThai; ?></td>
                                    <td>
                                        <a href="hoa_don_chi_tiet.php?id=<?php echo $row['ID']; ?>" class="btn btn-sm btn-info text-white fw-bold shadow-sm">
                                            <i class="bi bi-eye"></i> Xem chi tiết
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Chưa có đơn hàng nào trong hệ thống!</td>
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