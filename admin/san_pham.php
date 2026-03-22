<?php
session_start();
require_once '../connect.php';

// Gọi giao diện Header và Sidebar
require_once 'header.php';
require_once 'sidebar.php';

// Lấy danh sách sản phẩm kèm tên loại và tên hãng
$sql = "SELECT sp.*, lsp.TenLoai, hsx.TenHangSanXuat 
        FROM SanPham sp
        LEFT JOIN LoaiSanPham lsp ON sp.LoaiSanPhamID = lsp.ID
        LEFT JOIN HangSanXuat hsx ON sp.HangSanXuatID = hsx.ID
        ORDER BY sp.ID DESC";
$result = $conn->query($sql);
?>

<div class="col-md-9 col-lg-10 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-primary fw-bold"><i class="bi bi-tv"></i> Quản lý Sản Phẩm (Tivi)</h3>
        <a href="them_san_pham.php" class="btn btn-success fw-bold">
            <i class="bi bi-plus-circle"></i> Thêm Tivi Mới
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="60">ID</th>
                            <th width="80">Hình Ảnh</th>
                            <th class="text-start">Tên Sản Phẩm</th>
                            <th>Loại</th>
                            <th>Hãng</th>
                            <th>Giá Bán</th>
                            <th width="60">SL</th>
                            <th width="120">Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                                <?php 
                                    // Kiểm tra ảnh, dùng ảnh mặc định nếu không có
                                    $hinhAnh = !empty($row['HinhAnh']) ? "../uploads/".$row['HinhAnh'] : "../uploads/no-image.jpg"; 
                                ?>
                                <tr>
                                    <td class="fw-bold"><?php echo $row['ID']; ?></td>
                                    <td>
                                        <img src="<?php echo $hinhAnh; ?>" alt="Tivi" class="rounded border shadow-sm" style="width: 50px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td class="text-start fw-bold text-primary">
                                        <?php echo htmlspecialchars($row['TenSanPham']); ?>
                                    </td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row['TenLoai']); ?></span></td>
                                    <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($row['TenHangSanXuat']); ?></span></td>
                                    <td class="text-danger fw-bold">
                                        <?php echo number_format($row['DonGia'], 0, ',', '.'); ?> đ
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill <?php echo ($row['SoLuong'] > 0) ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $row['SoLuong']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="sua_san_pham.php?id=<?php echo $row['ID']; ?>" class="btn btn-sm btn-outline-warning" title="Sửa"><i class="bi bi-pencil-square"></i></a>
                                        <a href="xoa_san_pham.php?id=<?php echo $row['ID']; ?>" class="btn btn-sm btn-outline-danger" title="Xóa" onclick="return confirm('Bạn có chắc chắn muốn xóa Tivi này không?');"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">Chưa có sản phẩm nào trong kho.</td>
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