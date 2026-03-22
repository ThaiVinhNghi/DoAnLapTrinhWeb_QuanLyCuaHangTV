<?php
session_start();
require_once '../connect.php';

// Truy vấn lấy danh sách khách hàng
$sql = "SELECT * FROM khachhang ORDER BY ID DESC";
$result = $conn->query($sql);

// Gọi giao diện Header và Sidebar
require_once 'header.php';
require_once 'sidebar.php';
?>

<div class="col-md-9 col-lg-10 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold"><i class="bi bi-people-fill"></i> Danh sách Khách Hàng</h3>
        <div>
            <a href="khach_hang_them.php" class="btn btn-primary"><i class="bi bi-person-plus"></i> Thêm Khách Hàng</a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="table-primary">
                        <tr>
                            <th>ID</th>
                            <th class="text-start">Họ và Tên</th>
                            <th>Số Điện Thoại</th>
                            <th class="text-start">Địa Chỉ</th>
                            <th width="150">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo $row['ID']; ?></td>
                                    <td class="text-start fw-bold"><?php echo htmlspecialchars($row['HoVaTen']); ?></td>
                                    <td><?php echo htmlspecialchars($row['DienThoai']); ?></td>
                                    <td class="text-start"><?php echo htmlspecialchars($row['DiaChi']); ?></td>
                                    <td>
                                        <a href="khach_hang_sua.php?id=<?php echo $row['ID']; ?>" class="btn btn-sm btn-outline-primary" title="Sửa"><i class="bi bi-pencil-square"></i></a>
                                        <a href="khach_hang_xoa.php?id=<?php echo $row['ID']; ?>" class="btn btn-sm btn-outline-danger" title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa khách hàng này?')"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Chưa có khách hàng nào!</td>
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