<?php
session_start();
// Kiểm tra đăng nhập. Nằm trong thư mục admin nên phải dùng ../ để lùi ra ngoài tìm file login
if (!isset($_SESSION['nhanvien_id'])) {
    header("Location: ../login.php");
    exit();
}

// Gọi kết nối CSDL từ ngoài thư mục gốc
require_once '../connect.php';
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Sản phẩm - Cửa Hàng Tivi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="index.php">📺 Admin Tivi Store</a>
        <div class="d-flex text-white align-items-center">
            <span class="me-3">Xin chào, <strong><?php echo $_SESSION['ho_ten']; ?></strong>!</span>
            <a href="../logout.php" class="btn btn-sm btn-danger">Đăng xuất</a>
        </div>
    </div>
</nav>

<div class="container-fluid mt-4">
    <div class="row">
        <div class="col-md-3 col-lg-2">
            <div class="list-group">
                <a href="index.php" class="list-group-item list-group-item-action">Bảng điều khiển</a>
                <a href="san_pham.php" class="list-group-item list-group-item-action active">Quản lý Sản phẩm</a>
                <a href="danh_muc.php" class="list-group-item list-group-item-action">Quản lý Danh mục</a>
                <a href="hoa_don.php" class="list-group-item list-group-item-action">Quản lý Hóa đơn</a>
                <a href="khach_hang.php" class="list-group-item list-group-item-action">Quản lý Khách hàng</a>
                <?php if ($_SESSION['quyen_han'] == 1): ?>
                    <a href="nhan_vien.php" class="list-group-item list-group-item-action text-primary">Quản lý Nhân viên (Admin)</a>
                <?php endif; ?>
            </div>
        </div>

        <div class="col-md-9 col-lg-10">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Danh sách Tivi</h5>
                    <a href="them_san_pham.php" class="btn btn-success btn-sm">
                        <i class="bi bi-plus-circle"></i> Thêm Tivi mới
                    </a>
                </div>
                <div class="card-body">
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Hình ảnh</th>
                                    <th>Tên Sản Phẩm</th>
                                    <th>Loại</th>
                                    <th>Hãng</th>
                                    <th>Giá Bán</th>
                                    <th>SL</th>
                                    <th width="150">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT sp.*, lsp.TenLoai, hsx.TenHangSanXuat 
                                        FROM SanPham sp
                                        LEFT JOIN LoaiSanPham lsp ON sp.LoaiSanPhamID = lsp.ID
                                        LEFT JOIN HangSanXuat hsx ON sp.HangSanXuatID = hsx.ID
                                        ORDER BY sp.ID DESC";
                                $result = $conn->query($sql);

                                if ($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        // ĐÃ SỬA: Lùi ra ngoài thư mục (../) để tìm ảnh
                                        $hinhAnh = !empty($row['HinhAnh']) ? "../uploads/".$row['HinhAnh'] : "../uploads/no-image.jpg";
                                        
                                        echo "<tr>";
                                        echo "<td>" . $row['ID'] . "</td>";
                                        echo "<td><img src='" . $hinhAnh . "' width='60' height='60' style='object-fit:cover;' class='rounded'></td>";
                                        echo "<td><strong>" . $row['TenSanPham'] . "</strong></td>";
                                        echo "<td>" . $row['TenLoai'] . "</td>";
                                        echo "<td>" . $row['TenHangSanXuat'] . "</td>";
                                        echo "<td class='text-danger fw-bold'>" . number_format($row['DonGia'], 0, ',', '.') . " đ</td>";
                                        echo "<td>" . $row['SoLuong'] . "</td>";
                                        echo "<td>
                                                <a href='sua_san_pham.php?id=" . $row['ID'] . "' class='btn btn-warning btn-sm' title='Sửa'><i class='bi bi-pencil-square'></i></a>
                                                <a href='xoa_san_pham.php?id=" . $row['ID'] . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Bạn có chắc chắn muốn xóa Tivi này không?\");' title='Xóa'><i class='bi bi-trash'></i></a>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='8' class='text-center'>Chưa có sản phẩm nào.</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>