<?php
session_start();
require_once 'thu_vien/connect.php';

// Kiểm tra xem có truyền ID sản phẩm không
if (!isset($_GET['id'])) {
    header("Location: trang_chu.php");
    exit();
}

$id = $_GET['id'];

// Lấy thông tin chi tiết Tivi (JOIN để lấy Tên Hãng và Tên Loại)
$sql = "SELECT sp.*, hsx.TenHangSanXuat, lsp.TenLoai 
        FROM SanPham sp 
        LEFT JOIN HangSanXuat hsx ON sp.HangSanXuatID = hsx.ID
        LEFT JOIN LoaiSanPham lsp ON sp.LoaiSanPhamID = lsp.ID
        WHERE sp.ID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Nếu không tìm thấy sản phẩm thì quay về trang chủ
if ($result->num_rows == 0) {
    header("Location: trang_chu.php");
    exit();
}

$sp = $result->fetch_assoc();
$hinhAnh = !empty($sp['HinhAnh']) ? "uploads/" . $sp['HinhAnh'] : "uploads/no-image.jpg";
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <title><?php echo $sp['TenSanPham']; ?> - TIVI STORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>

<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow">
        <div class="container">
            <a class="navbar-brand fw-bold" href="trang_chu.php"><i class="bi bi-tv"></i> TIVI STORE</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="trang_chu.php">Trang Chủ</a></li>
                </ul>
                <div class="d-flex align-items-center">
                    <a href="gio_hang.php" class="btn btn-warning me-2">
                        <i class="bi bi-cart-fill"></i> Giỏ hàng
                        <?php
                        // Đếm số loại sản phẩm trong giỏ hàng
                        $soLoaiSP = isset($_SESSION['gio_hang']) ? count($_SESSION['gio_hang']) : 0;
                        echo '<span class="badge bg-danger rounded-pill">' . $soLoaiSP . '</span>';
                        ?>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        <div class="card shadow border-0">
            <div class="row g-0">
                <div class="col-md-5 text-center p-4" style="background-color: #fff;">
                    <img src="<?php echo $hinhAnh; ?>" class="img-fluid rounded" alt="Ảnh Tivi">
                </div>

                <div class="col-md-7">
                    <div class="card-body p-5">
                        <h2 class="card-title fw-bold text-primary mb-3"><?php echo $sp['TenSanPham']; ?></h2>

                        <div class="mb-3">
                            <span class="badge bg-danger fs-6 me-2"><?php echo $sp['TenHangSanXuat']; ?></span>
                            <span class="badge bg-secondary fs-6"><?php echo $sp['TenLoai']; ?></span>
                        </div>

                        <h3 class="text-danger fw-bold mb-4"><?php echo number_format($sp['DonGia'], 0, ',', '.'); ?>
                            VNĐ</h3>

                        <p class="card-text text-muted mb-4" style="line-height: 1.8;">
                            <strong>Mô tả sản phẩm:</strong><br>
                            <?php echo !empty($sp['MoTa']) ? $sp['MoTa'] : "Chưa có mô tả cho sản phẩm này."; ?>
                        </p>

                        <p class="card-text"><strong>Tình trạng:</strong>
                            <?php if ($sp['SoLuong'] > 0): ?>
                                <span class="text-success fw-bold">Còn <?php echo $sp['SoLuong']; ?> sản phẩm</span>
                            <?php else: ?>
                                <span class="text-danger fw-bold">Hết hàng</span>
                            <?php endif; ?>
                        </p>

                        <hr class="my-4">

                        <?php if ($sp['SoLuong'] > 0): ?>
                            <form action="them_gio_hang.php" method="POST" class="d-flex align-items-center gap-3">
                                <input type="hidden" name="id_sp" value="<?php echo $sp['ID']; ?>">

                                <label class="fw-bold">Số lượng:</label>
                                <input type="number" id="soLuongInput" name="so_luong" value="1" min="1" max="<?php echo $sp['SoLuong']; ?>"
                                    class="form-control text-center" style="width: 80px;" onchange="validateQuantity()">

                                <button type="submit" id="addToCartBtn" class="btn btn-primary btn-lg px-4">
                                    <i class="bi bi-cart-plus-fill"></i> Chọn Mua Ngay
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-danger mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                                <div>
                                    <strong>Sản phẩm này hiện đã hết hàng</strong>
                                    <small class="d-block">Cảm ơn bạn, hãy quay lại sau để cập nhật hàng mới</small>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function validateQuantity() {
        const input = document.getElementById('soLuongInput');
        const maxQuantity = <?php echo $sp['SoLuong']; ?>;
        const currentValue = parseInt(input.value) || 1;
        
        if (currentValue > maxQuantity) {
            input.value = maxQuantity;
            alert('Số lượng không thể vượt quá ' + maxQuantity + ' sản phẩm trong kho');
        }
        if (currentValue < 1) {
            input.value = 1;
        }
    }
    </script>

                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>