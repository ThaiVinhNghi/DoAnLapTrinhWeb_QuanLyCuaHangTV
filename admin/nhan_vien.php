<?php
session_start();
require_once '../thu_vien/connect.php';

// Kiểm tra quyền: Chỉ Admin (QuyenHan = 1) mới được vào trang này
if (!isset($_SESSION['quyen_han']) || $_SESSION['quyen_han'] != 1) {
    echo "<script>alert('Bạn không có quyền truy cập khu vực này!'); window.location.href='index.php';</script>";
    exit();
}

// Truy vấn lấy danh sách nhân viên
$sql = "SELECT * FROM nhanvien ORDER BY ID ASC";
$result = $conn->query($sql);

// Gọi giao diện Header và Sidebar
require_once 'header.php';
require_once 'sidebar.php';
?>

<div class="col-md-9 col-lg-10 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-success fw-bold"><i class="bi bi-person-badge-fill"></i> Quản lý Đội ngũ Nhân Viên</h3>
        <a href="nhan_vien_them.php" class="btn btn-success fw-bold">
            <i class="bi bi-person-plus-fill"></i> Thêm Nhân Viên Mới
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center mb-0">
                    <thead class="table-success">
                        <tr>
                            <th width="60">ID</th>
                            <th class="text-start">Họ và Tên</th>
                            <th>Tên Đăng Nhập</th>
                            <th>Số Điện Thoại</th>
                            <th>Ngày Vào Làm</th>
                            <th>Quyền Hạn</th>
                            <th width="120">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php 
                                    // Hiển thị nhãn quyền hạn
                                    $quyenBadge = ($row['QuyenHan'] == 1) 
                                        ? '<span class="badge bg-danger shadow-sm"><i class="bi bi-shield-lock"></i> Quản trị viên</span>' 
                                        : '<span class="badge bg-secondary shadow-sm"><i class="bi bi-person"></i> Nhân viên</span>';
                                        
                                    // Format ngày vào làm
                                    $ngayVaoLam = !empty($row['NgayVaoLam']) ? date('d/m/Y', strtotime($row['NgayVaoLam'])) : '<span class="text-muted small">Chưa cập nhật</span>';
                                ?>
                                <tr>
                                    <td class="fw-bold"><?php echo $row['ID']; ?></td>
                                    <td class="text-start fw-bold text-dark"><?php echo htmlspecialchars($row['HoVaTen']); ?></td>
                                    <td><code class="text-primary fw-bold"><?php echo htmlspecialchars($row['TenDangNhap']); ?></code></td>
                                    <td><?php echo htmlspecialchars($row['DienThoai']); ?></td>
                                    <td><?php echo $ngayVaoLam; ?></td>
                                    <td><?php echo $quyenBadge; ?></td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="nhan_vien_sua.php?id=<?php echo $row['ID']; ?>" class="btn btn-sm btn-outline-primary" title="Sửa thông tin">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            
                                            <?php if($row['ID'] != $_SESSION['nhanvien_id']): ?>
                                                <a href="nhan_vien_xoa.php?id=<?php echo $row['ID']; ?>" class="btn btn-sm btn-outline-danger" title="Xóa nhân viên" onclick="return confirm('Bạn có chắc muốn xóa nhân viên này?')">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-light text-muted" disabled title="Bạn không thể tự xóa chính mình"><i class="bi bi-trash"></i></button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Chưa có nhân viên nào trong danh sách!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php 
require_once 'footer.php'; 
?>