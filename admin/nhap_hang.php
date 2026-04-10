<?php
session_start();
require_once '../thu_vien/connect.php';
require_once 'header.php';
require_once 'sidebar.php';

// Truy vấn lấy danh sách phiếu nhập + Tên nhân viên + Tên nhà cung cấp
$sql = "SELECT pn.*, nv.HoVaTen, ncc.TenNhaCungCap 
        FROM phieunhap pn
        LEFT JOIN nhanvien nv ON pn.NhanVienID = nv.ID
        LEFT JOIN nhacungcap ncc ON pn.NhaCungCapID = ncc.ID
        ORDER BY pn.NgayNhap DESC";
$result = $conn->query($sql);
?>

<div class="col-md-9 col-lg-10 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold"><i class="bi bi-box-seam"></i> Lịch sử Nhập hàng</h3>
        <a href="nhap_hang_them.php" class="btn btn-primary fw-bold">
            <i class="bi bi-plus-lg"></i> Tạo Phiếu Nhập Mới
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>Mã Phiếu</th>
                            <th>Ngày Nhập</th>
                            <th>Nhà Cung Cấp</th>
                            <th>Người Lập</th>
                            <th>Tổng Tiền</th>
                            <th>Thao Tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold">#PN-<?php echo $row['ID']; ?></td>
                                    <td><?php echo date("d/m/Y H:i", strtotime($row['NgayNhap'])); ?></td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            <?php echo $row['TenNhaCungCap']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $row['HoVaTen']; ?></td>
                                    <td class="text-danger fw-bold">
                                        <?php echo number_format($row['TongTien'], 0, ',', '.'); ?> đ
                                    </td>
                                    <td>
                                        <a href="nhap_hang_chi_tiet.php?id=<?php echo $row['ID']; ?>" class="btn btn-sm btn-outline-primary mb-1">
                                            <i class="bi bi-eye"></i> Xem chi tiết
                                        </a>

                                        <a href="nhap_hang_sua.php?id=<?php echo $row['ID']; ?>" class="btn btn-sm btn-outline-warning mb-1">
                                            <i class="bi bi-pencil-square"></i> Sửa
                                        </a>

                                        <a href="nhap_hang_xoa.php?id=<?php echo $row['ID']; ?>"
                                           class="btn btn-sm btn-outline-danger mb-1"
                                           onclick="return confirm('Bạn có chắc muốn xóa phiếu nhập này không?');">
                                            <i class="bi bi-trash"></i> Xóa
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="py-4 text-muted">Chưa có giao dịch nhập hàng nào.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>