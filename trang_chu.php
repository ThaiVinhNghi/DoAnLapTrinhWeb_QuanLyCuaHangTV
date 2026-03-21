<?php
session_start();
require_once 'connect.php';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siêu Thị Tivi - Uy Tín, Chất Lượng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; }
        .product-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border-radius: 15px;
            overflow: hidden;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
        }
        .product-img {
            height: 200px;
            object-fit: contain;
            width: 100%;
            background-color: #fff;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="trang_chu.php"><i class="bi bi-tv"></i> N&U</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link active" href="trang_chu.php">Trang Chủ</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Khuyến Mãi</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Liên Hệ</a></li>
            </ul>

            <form class="d-flex me-3" action="tim_kiem.php" method="GET">
                <input class="form-control me-2" type="search" name="tukhoa" placeholder="Tìm tên Tivi..." required>
                <button class="btn btn-outline-light" type="submit"><i class="bi bi-search"></i></button>
            </form>

            <div class="d-flex align-items-center flex-wrap gap-2">
                <a href="gio_hang.php" class="btn btn-warning position-relative fw-bold">
                    <i class="bi bi-cart-fill"></i> Giỏ hàng
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php
                            $soLoaiSP = isset($_SESSION['gio_hang']) ? count($_SESSION['gio_hang']) : 0;
                            echo $soLoaiSP;
                        ?>
                    </span>
                </a>

                <?php if (isset($_SESSION['khach_hang_id'])): ?>
                    <span class="text-white fw-bold">
                        <i class="bi bi-person-check-fill"></i>
                        Xin chào, <?php echo htmlspecialchars($_SESSION['khach_hang_ten']); ?>
                    </span>

                    <a href="logout_khach.php" class="btn btn-danger">
                        <i class="bi bi-box-arrow-right"></i> Đăng xuất
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light">
                        <i class="bi bi-person-circle"></i> Admin
                    </a>

                    <a href="dang_ky.php" class="btn btn-outline-light">
                        <i class="bi bi-person-plus"></i> Đăng ký
                    </a>

                    <a href="login_khach.php" class="btn btn-light">
                        <i class="bi bi-box-arrow-in-right"></i> Đăng nhập
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="p-4 p-md-5 mb-4 rounded text-bg-dark text-center" style="background: linear-gradient(to right, #0052D4, #4364F7, #6FB1FC);">
        <h1 class="display-5 fw-bold">Đón Lễ Lớn - Sale Tivi Lên Đến 50%</h1>
        <p class="lead my-3">Sở hữu ngay những chiếc Smart Tivi 4K, OLED, QLED với giá tốt nhất thị trường.</p>
    </div>
</div>

<div class="container mb-5">
    <h3 class="border-bottom pb-2 mb-4 text-primary text-uppercase fw-bold">Tivi Nổi Bật Nhất</h3>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php
        $sql = "SELECT sp.*, hsx.TenHangSanXuat
                FROM SanPham sp
                LEFT JOIN HangSanXuat hsx ON sp.HangSanXuatID = hsx.ID
                WHERE sp.SoLuong > 0
                ORDER BY sp.ID DESC";
        $result = $conn->query($sql);

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $hinhAnh = !empty($row['HinhAnh']) ? "uploads/" . $row['HinhAnh'] : "uploads/no-image.jpg";
                $tenSP = htmlspecialchars($row['TenSanPham']);
                $giaBan = number_format($row['DonGia'], 0, ',', '.');
                $tenHang = htmlspecialchars($row['TenHangSanXuat'] ?? 'Không rõ');
                $idSP = (int)$row['ID'];

                echo '
                <div class="col">
                    <div class="card h-100 product-card border-0 shadow-sm">
                        <span class="badge bg-danger position-absolute" style="top: 10px; right: 10px; z-index: 10;">' . $tenHang . '</span>

                        <a href="chi_tiet_san_pham.php?id=' . $idSP . '">
                            <img src="' . $hinhAnh . '" class="card-img-top product-img p-3" alt="' . $tenSP . '">
                        </a>

                        <div class="card-body d-flex flex-column text-center">
                            <h6 class="card-title" style="height: 40px; overflow: hidden;">
                                <a href="chi_tiet_san_pham.php?id=' . $idSP . '" class="text-decoration-none text-dark fw-bold">' . $tenSP . '</a>
                            </h6>
                            <h5 class="card-text text-danger fw-bold mt-auto mb-3">' . $giaBan . ' đ</h5>

                            <form action="them_gio_hang.php" method="POST">
                                <input type="hidden" name="id_sp" value="' . $idSP . '">
                                <button type="submit" class="btn btn-primary w-100 rounded-pill">
                                    <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                                </button>
                            </form>
                        </div>
                    </div>
                </div>';
            }
        } else {
            echo '<div class="col-12"><p class="text-center">Hiện tại chưa có sản phẩm nào.</p></div>';
        }
        ?>
    </div>
</div>

<footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
        <p class="mb-0">© 2026 - Cửa Hàng Tivi Nghi và Uy.</p>
        <p class="text-muted small">Chất lượng tạo nên thương hiệu</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>