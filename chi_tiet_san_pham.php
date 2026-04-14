<?php
session_start();
require_once 'thu_vien/connect.php';

// Kiểm tra xem có truyền ID sản phẩm không
if (!isset($_GET['id'])) {
    header("Location: trang_chu.php");
    exit();
}

$id = (int)$_GET['id'];

// Lấy thông tin chi tiết Tivi
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

// Tính giá sau khi giảm (nếu có)
$giaGoc = (float)$sp['DonGia'];
$phanTram = isset($sp['PhanTramGiam']) ? (int)$sp['PhanTramGiam'] : 0;
$giaBanThucTe = $giaGoc - ($giaGoc * $phanTram / 100);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($sp['TenSanPham']); ?> - N&U Store</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="tai_nguyen/css/style.css">
    
    <style>
        /* Chỉ giữ lại style cho Navbar để đồng bộ menu đen */
        .navbar-premium {
            background-color: rgba(10, 10, 10, 0.95) !important;
            backdrop-filter: blur(10px);
            padding: 15px 0;
        }
        .navbar-premium .nav-link {
            color: rgba(255,255,255,0.7) !important;
            text-transform: uppercase;
            font-size: 0.85rem;
            font-weight: 600;
            letter-spacing: 1px;
            transition: color 0.3s;
        }
        .navbar-premium .nav-link:hover, .navbar-premium .nav-link.active {
            color: #fff !important;
        }
        .btn-pill {
            border-radius: 50px;
            padding: 8px 20px;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-light">

    <nav class="navbar navbar-expand-lg navbar-dark navbar-premium sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold fs-4 text-white" href="trang_chu.php"><i class="bi bi-tv text-danger"></i> N&U</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item"><a class="nav-link" href="trang_chu.php">Khám Phá</a></li>
                    <li class="nav-item"><a class="nav-link active" href="san_pham.php">Sản Phẩm</a></li>
                    <li class="nav-item"><a class="nav-link" href="trang_chu.php#tin-tuc">Tin Tức</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <a href="gio_hang.php" class="btn btn-outline-light btn-pill">
                        <i class="bi bi-cart"></i> Giỏ hàng 
                        <span class="badge bg-danger ms-1"><?php echo isset($_SESSION['gio_hang']) ? count($_SESSION['gio_hang']) : 0; ?></span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-5 mb-5">
        
        <div class="mb-4">
            <a href="san_pham.php" class="text-decoration-none text-secondary fw-bold">
                <i class="bi bi-arrow-left"></i> Quay lại Sản Phẩm
            </a>
        </div>

        <div class="card shadow-sm border-0">
            <div class="row g-0">
                
                <div class="col-md-5 p-4 text-center border-end bg-white d-flex align-items-center justify-content-center position-relative">
                    <?php if ($phanTram > 0): ?>
                        <span class="badge bg-danger position-absolute fs-6 px-3 py-2" style="top: 15px; left: 15px;">-<?php echo $phanTram; ?>%</span>
                    <?php endif; ?>
                    <img src="<?php echo $hinhAnh; ?>" class="img-fluid rounded" alt="Ảnh Tivi" style="max-height: 400px; object-fit: contain;">
                </div>

                <div class="col-md-7">
                    <div class="card-body p-4 p-md-5">
                        
                        <h2 class="card-title fw-bold text-primary mb-3"><?php echo htmlspecialchars($sp['TenSanPham']); ?></h2>

                        <div class="mb-3">
                            <span class="badge bg-danger fs-6 me-2"><?php echo htmlspecialchars($sp['TenHangSanXuat']); ?></span>
                            <span class="badge bg-secondary fs-6"><?php echo htmlspecialchars($sp['TenLoai']); ?></span>
                        </div>

                        <hr class="my-4">

                        <div class="mb-4">
                            <?php if ($phanTram > 0): ?>
                                <span class="text-muted text-decoration-line-through fs-5 d-block mb-1"><?php echo number_format($giaGoc, 0, ',', '.'); ?> VNĐ</span>
                                <h2 class="text-danger fw-bold m-0"><?php echo number_format($giaBanThucTe, 0, ',', '.'); ?> VNĐ</h2>
                            <?php else: ?>
                                <h2 class="text-danger fw-bold m-0"><?php echo number_format($giaGoc, 0, ',', '.'); ?> VNĐ</h2>
                            <?php endif; ?>
                        </div>

                        <p class="card-text text-muted mb-4" style="line-height: 1.8;">
                            <strong>Mô tả sản phẩm:</strong><br>
                            <?php echo !empty($sp['MoTa']) ? nl2br(htmlspecialchars($sp['MoTa'])) : "Chưa có mô tả cho sản phẩm này."; ?>
                        </p>

                        <p class="card-text mb-4"><strong>Tình trạng:</strong>
                            <?php if ($sp['SoLuong'] > 0): ?>
                                <span class="text-success fw-bold">Còn <?php echo $sp['SoLuong']; ?> sản phẩm</span>
                            <?php else: ?>
                                <span class="text-danger fw-bold">Hết hàng</span>
                            <?php endif; ?>
                        </p>

                        <?php if ($sp['SoLuong'] > 0): ?>
                            <form action="them_gio_hang.php" method="POST" class="d-flex align-items-center gap-3 bg-light p-3 border rounded">
                                <input type="hidden" name="id_sp" value="<?php echo $sp['ID']; ?>">

                                <label class="fw-bold m-0">Số lượng:</label>
                                <input type="number" id="soLuongInput" name="so_luong" value="1" min="1" max="<?php echo $sp['SoLuong']; ?>" class="form-control text-center fw-bold" style="width: 80px;" onchange="validateQuantity()">

                                <button type="submit" class="btn btn-primary btn-lg px-4 ms-auto">
                                    <i class="bi bi-cart-plus-fill"></i> Mua Ngay
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-danger mb-0 d-flex align-items-center gap-3">
                                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                                <div>
                                    <strong>Sản phẩm này hiện đã hết hàng</strong>
                                    <small class="d-block">Hãy quay lại sau hoặc chọn mua sản phẩm khác nhé!</small>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <a href="https://zalo.me/0931082845" target="_blank" class="position-fixed bottom-0 end-0 m-4 bg-success rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; z-index: 1000;">
        <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="white" viewBox="0 0 16 16">
            <path d="M8 15c4.418 0 8-3.134 8-7s-3.582-7-8-7-8 3.134-8 7c0 1.76.743 3.37 1.97 4.6-.097 1.016-.417 2.13-.771 2.966-.079.186.074.394.273.362 2.256-.37 3.597-.938 4.18-1.234A9.06 9.06 0 0 0 8 15z" />
        </svg>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
</body>
</html>