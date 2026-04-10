<?php
session_start();
require_once '../thu_vien/connect.php';

// 1. Chưa đăng nhập thì mới cho về login
if (!isset($_SESSION['nhanvien_id']) && !isset($_SESSION['nhan_vien_id'])) {
    header("Location: ../login.php");
    exit();
}

// 2. Đã đăng nhập nhưng không phải admin -> cảnh báo rồi quay lại trang admin
if (!isset($_SESSION['admin_id'])) {
    echo "<script>alert('Bạn không có quyền truy cập khu vực này!'); window.location.href='index.php';</script>";
    exit();
}

// Gọi giao diện chung khu admin
require_once 'header.php';
require_once 'sidebar.php';

$tuKhoa = trim($_GET['tu_khoa'] ?? '');
$loai = trim($_GET['loai'] ?? '');

$sql = "SELECT * FROM nhatky_hethong WHERE 1=1";
$params = [];
$types = "";

// Lọc từ khóa
if ($tuKhoa !== '') {
    $sql .= " AND (
        TenDangNhap LIKE ? OR
        HoTen LIKE ? OR
        HanhDong LIKE ? OR
        MoTa LIKE ?
    )";
    $keywordLike = "%" . $tuKhoa . "%";
    $params[] = $keywordLike;
    $params[] = $keywordLike;
    $params[] = $keywordLike;
    $params[] = $keywordLike;
    $types .= "ssss";
}

// Lọc loại người dùng
if ($loai !== '') {
    $sql .= " AND LoaiNguoiDung = ?";
    $params[] = $loai;
    $types .= "s";
}

$sql .= " ORDER BY ID DESC";

$stmt = $conn->prepare($sql);
if ($stmt) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = false;
}
?>

<div class="col-md-9 col-lg-10 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold">
            <i class="bi bi-clock-history"></i> Nhật ký hệ thống
        </h3>
        <a href="index.php" class="btn btn-success">
            <i class="bi bi-arrow-left"></i> Về trang quản trị
        </a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="get" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-muted">Từ khóa</label>
                    <input type="text"
                           name="tu_khoa"
                           class="form-control"
                           placeholder="Tìm theo tên, tài khoản, hành động..."
                           value="<?php echo htmlspecialchars($tuKhoa); ?>">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold text-muted">Loại người dùng</label>
                    <select name="loai" class="form-select">
                        <option value="">-- Tất cả loại --</option>
                        <option value="Admin" <?php if ($loai == 'Admin') echo 'selected'; ?>>Admin</option>
                        <option value="NhanVien" <?php if ($loai == 'NhanVien') echo 'selected'; ?>>Nhân viên</option>
                        <option value="KhachHang" <?php if ($loai == 'KhachHang') echo 'selected'; ?>>Khách hàng</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel-fill"></i> Lọc
                    </button>
                    <a href="nhatky_hethong.php" class="btn btn-outline-secondary w-100">
                        Làm mới
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark text-center">
                        <tr>
                            <th width="70">ID</th>
                            <th width="160">Thời gian</th>
                            <th width="120">Loại</th>
                            <th width="180">Người dùng</th>
                            <th width="140">Hành động</th>
                            <th width="120">Bảng</th>
                            <th width="90">Bản ghi</th>
                            <th>Mô tả</th>
                            <th width="130">IP</th>
                            <th width="110">Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="text-center fw-bold"><?php echo (int)$row['ID']; ?></td>

                                    <td class="text-center">
                                        <?php echo !empty($row['ThoiGian']) ? date('d/m/Y H:i:s', strtotime($row['ThoiGian'])) : ''; ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if ($row['LoaiNguoiDung'] == 'Admin'): ?>
                                            <span class="badge bg-danger">Admin</span>
                                        <?php elseif ($row['LoaiNguoiDung'] == 'NhanVien'): ?>
                                            <span class="badge bg-primary">Nhân viên</span>
                                        <?php elseif ($row['LoaiNguoiDung'] == 'KhachHang'): ?>
                                            <span class="badge bg-success">Khách hàng</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <?php echo htmlspecialchars($row['LoaiNguoiDung']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>

                                    <td>
                                        <div class="fw-bold text-dark">
                                            <?php echo htmlspecialchars($row['HoTen'] ?? ''); ?>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($row['TenDangNhap'] ?? ''); ?>
                                        </small>
                                    </td>

                                    <td class="fw-semibold text-primary">
                                        <?php echo htmlspecialchars($row['HanhDong'] ?? ''); ?>
                                    </td>

                                    <td class="text-center">
                                        <?php echo htmlspecialchars($row['BangTacDong'] ?? ''); ?>
                                    </td>

                                    <td class="text-center">
                                        <?php echo !empty($row['BanGhiID']) ? (int)$row['BanGhiID'] : ''; ?>
                                    </td>

                                    <td>
                                        <?php echo htmlspecialchars($row['MoTa'] ?? ''); ?>
                                    </td>

                                    <td class="text-center">
                                        <?php echo htmlspecialchars($row['DiaChiIP'] ?? ''); ?>
                                    </td>

                                    <td class="text-center">
                                        <?php if (($row['TrangThai'] ?? '') == 'ThanhCong'): ?>
                                            <span class="badge bg-success">Thành công</span>
                                        <?php elseif (($row['TrangThai'] ?? '') == 'ThatBai'): ?>
                                            <span class="badge bg-danger">Thất bại</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">
                                                <?php echo htmlspecialchars($row['TrangThai'] ?? ''); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    Chưa có dữ liệu nhật ký hệ thống.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
if (isset($stmt) && $stmt) {
    $stmt->close();
}
require_once 'footer.php';
?>
