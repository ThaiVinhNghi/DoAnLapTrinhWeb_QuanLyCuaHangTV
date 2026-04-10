<?php
session_start();
require_once '../thu_vien/connect.php';
require_once '../thu_vien/nhatky_helper.php';

// Kiểm tra quyền (Nhân viên hoặc Admin đều được vào)
if (!isset($_SESSION['nhanvien_id'])) {
    echo "<script>alert('Bạn không có quyền truy cập!'); window.location.href='../login.php';</script>";
    exit();
}

// Xử lý Xóa bài viết
if (isset($_GET['xoa'])) {
    $id_xoa = (int)$_GET['xoa'];
    
    // Lấy thông tin ảnh để xóa file vật lý (tùy chọn, giúp nhẹ server)
    $sql_get_img = "SELECT TieuDe, HinhAnh FROM baiviet WHERE ID = $id_xoa";
    $res_img = $conn->query($sql_get_img);
    if ($res_img->num_rows > 0) {
        $row_img = $res_img->fetch_assoc();
        if (!empty($row_img['HinhAnh']) && file_exists("../uploads/" . $row_img['HinhAnh'])) {
            unlink("../uploads/" . $row_img['HinhAnh']);
        }
        $tieuDeXoa = $row_img['TieuDe'];
    }

    $sql_xoa = "DELETE FROM baiviet WHERE ID = $id_xoa";
    if ($conn->query($sql_xoa) === TRUE) {
        ghiNhatKyTuSession($conn, 'XoaBaiViet', 'baiviet', $id_xoa, "Xóa bài viết: $tieuDeXoa");
        echo "<script>alert('Đã xóa bài viết thành công!'); window.location.href='bai_viet.php';</script>";
        exit();
    }
}

// Truy vấn danh sách bài viết kèm tên người đăng
$sql = "SELECT bv.*, nv.HoVaTen 
        FROM baiviet bv 
        LEFT JOIN nhanvien nv ON bv.NhanVienID = nv.ID 
        ORDER BY bv.NgayDang DESC";
$result = $conn->query($sql);

require_once 'header.php';
require_once 'sidebar.php';
?>

<div class="col-md-9 col-lg-10 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-info fw-bold"><i class="bi bi-newspaper"></i> Quản lý Tin tức & Bài viết</h3>
        <a href="bai_viet_them.php" class="btn btn-info text-white fw-bold shadow-sm">
            <i class="bi bi-plus-circle"></i> Viết bài mới
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center mb-0">
                    <thead class="table-info text-dark">
                        <tr>
                            <th width="80">ID</th>
                            <th width="120">Ảnh bìa</th>
                            <th class="text-start">Tiêu đề bài viết</th>
                            <th>Người đăng</th>
                            <th>Ngày đăng</th>
                            <th>Trạng thái</th>
                            <th width="100">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <?php 
                                    $img = !empty($row['HinhAnh']) ? "../uploads/" . $row['HinhAnh'] : "../uploads/no-image.jpg";
                                ?>
                                <tr>
                                    <td class="fw-bold"><?php echo $row['ID']; ?></td>
                                    <td>
                                        <img src="<?php echo $img; ?>" width="80" height="50" style="object-fit: cover;" class="rounded border">
                                    </td>
                                    <td class="text-start fw-bold text-dark">
                                        <?php echo htmlspecialchars($row['TieuDe']); ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['HoVaTen']); ?></span></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($row['NgayDang'])); ?></td>
                                    <td>
                                        <?php if ($row['TrangThai'] == 1): ?>
                                            <span class="badge bg-success">Hiển thị</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Đã ẩn</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1 justify-content-center">
                                            <a href="bai_viet_sua.php?id=<?php echo $row['ID']; ?>" class="btn btn-sm btn-outline-primary" title="Sửa bài viết">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a href="bai_viet.php?xoa=<?php echo $row['ID']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa bài viết này? Không thể khôi phục!');" title="Xóa bài viết">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">Chưa có bài viết nào được xuất bản.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'footer.php'; ?>