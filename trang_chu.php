<?php
session_start();
require_once 'connect.php';

/*
|--------------------------------------------------------------------------
| 1) LẤY GIÁ TRỊ LỌC TỪ URL
|--------------------------------------------------------------------------
| Ví dụ:
| trang_chu.php?loc_gia_sale=10-20&loc_gia_noibat=duoi10
|
| - loc_gia_sale   : lọc cho khu Ưu đãi khủng
| - loc_gia_noibat : lọc cho khu Tivi nổi bật nhất
*/
$loc_gia_sale = isset($_GET['loc_gia_sale']) ? $_GET['loc_gia_sale'] : '';
$loc_gia_noibat = isset($_GET['loc_gia_noibat']) ? $_GET['loc_gia_noibat'] : '';

/*
|--------------------------------------------------------------------------
| 2) TẠO ĐIỀU KIỆN SQL CHO KHU KHUYẾN MÃI
|--------------------------------------------------------------------------
| Ở đây đang lọc theo giá gốc DonGia.
| Nếu sau này muốn lọc theo giá sau giảm, sẽ phải đổi công thức SQL.
*/
$dieuKienSale = '';
switch ($loc_gia_sale) {
    case 'duoi10':
        $dieuKienSale = " AND sp.DonGia < 10000000";
        break;
    case '10-20':
        $dieuKienSale = " AND sp.DonGia BETWEEN 10000000 AND 20000000";
        break;
    case '20-50':
        $dieuKienSale = " AND sp.DonGia BETWEEN 20000000 AND 50000000";
        break;
    case 'tren50':
        $dieuKienSale = " AND sp.DonGia > 50000000";
        break;
}

/*
|--------------------------------------------------------------------------
| 3) TẠO ĐIỀU KIỆN SQL CHO KHU NỔI BẬT
|--------------------------------------------------------------------------
*/
$dieuKienNoiBat = '';
switch ($loc_gia_noibat) {
    case 'duoi10':
        $dieuKienNoiBat = " AND sp.DonGia < 10000000";
        break;
    case '10-20':
        $dieuKienNoiBat = " AND sp.DonGia BETWEEN 10000000 AND 20000000";
        break;
    case '20-50':
        $dieuKienNoiBat = " AND sp.DonGia BETWEEN 20000000 AND 50000000";
        break;
    case 'tren50':
        $dieuKienNoiBat = " AND sp.DonGia > 50000000";
        break;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siêu Thị Tivi - Uy Tín, Chất Lượng</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <style>
        /* Cuộn mượt khi bấm menu */
        html {
            scroll-behavior: smooth;
        }

        body {
            background-color: #f8f9fa;
        }

        /* Card sản phẩm */
        .product-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border-radius: 18px;
            overflow: hidden;
        }

        .product-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 12px 22px rgba(0,0,0,0.15);
        }

        /* Ảnh sản phẩm */
        .product-img {
            height: 220px;
            object-fit: contain;
            width: 100%;
            background-color: #fff;
        }

        /* Khi bấm menu tới section sẽ không bị navbar che */
        #lien-he, #khuyen-mai {
            scroll-margin-top: 85px;
        }

        /* Header của từng khu: tiêu đề bên trái, lọc bên phải */
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        /* Form lọc */
        .filter-form {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        /* Dropdown lọc */
        .filter-box {
            min-width: 240px;
            height: 46px;
            border-radius: 12px;
        }

        /* Nút lọc */
        .filter-btn {
            height: 46px;
            padding: 0 18px;
            border-radius: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        /* Nút bỏ lọc */
        .clear-btn {
            height: 46px;
            padding: 0 16px;
            border-radius: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
        }

        /* Badge hãng */
        .brand-badge {
            top: 10px;
            right: 10px;
            z-index: 10;
            border-radius: 10px;
            padding: 8px 10px;
            font-size: 0.95rem;
        }

        /* Chiều cao vùng tên sản phẩm để card đều nhau */
        .product-title {
            min-height: 58px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Responsive cho mobile */
        @media (max-width: 768px) {
            .section-header {
                align-items: flex-start;
            }

            .filter-form {
                width: 100%;
            }

            .filter-box {
                width: 100%;
                min-width: unset;
            }

            .filter-btn,
            .clear-btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>

<!-- =========================================================
     NAVBAR
========================================================= -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="trang_chu.php">
            <i class="bi bi-tv"></i> N&U
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Menu trái -->
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="trang_chu.php">Trang Chủ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="trang_chu.php#khuyen-mai">Khuyến Mãi</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="trang_chu.php#lien-he">Liên Hệ</a>
                </li>
            </ul>

            <!-- Form tìm kiếm -->
            <form class="d-flex me-3" action="tim_kiem.php" method="GET">
                <input class="form-control me-2" type="search" name="tukhoa" placeholder="Tìm tên Tivi..." required>
                <button class="btn btn-outline-light" type="submit">
                    <i class="bi bi-search"></i>
                </button>
            </form>

            <!-- Khu giỏ hàng + đăng nhập -->
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

<!-- =========================================================
     BANNER
========================================================= -->
<div class="container mt-4">
    <div class="p-4 p-md-5 mb-4 rounded text-bg-dark text-center"
         style="background: linear-gradient(to right, #0052D4, #4364F7, #6FB1FC);">
        <h1 class="display-5 fw-bold">Đón Lễ Lớn - Sale Tivi Lên Đến 50%</h1>
        <p class="lead my-3">
            Sở hữu ngay những chiếc Smart Tivi 4K, OLED, QLED với giá tốt nhất thị trường.
        </p>
    </div>
</div>

<!-- =========================================================
     KHU KHUYẾN MÃI
========================================================= -->
<div class="container mb-5 mt-5" id="khuyen-mai">

    <!-- Header khu khuyến mãi -->
    <div class="section-header mb-4 border-bottom border-danger pb-2">
        <h3 class="text-danger text-uppercase fw-bold mb-0">
            <i class="bi bi-fire"></i> Ưu Đãi Khủng
        </h3>

        <!--
            Form lọc riêng cho khu khuyến mãi
            Giữ lại giá trị loc_gia_noibat bằng input hidden
            để khi lọc sale thì khu nổi bật không bị mất trạng thái
        -->
        <form method="GET" action="" class="filter-form">
            <select name="loc_gia_sale" class="form-select filter-box">
                <option value="">Chọn mức giá ưu đãi</option>
                <option value="duoi10" <?php if ($loc_gia_sale == 'duoi10') echo 'selected'; ?>>Dưới 10 triệu</option>
                <option value="10-20" <?php if ($loc_gia_sale == '10-20') echo 'selected'; ?>>10 - 20 triệu</option>
                <option value="20-50" <?php if ($loc_gia_sale == '20-50') echo 'selected'; ?>>20 - 50 triệu</option>
                <option value="tren50" <?php if ($loc_gia_sale == 'tren50') echo 'selected'; ?>>Trên 50 triệu</option>
            </select>

            <!-- Giữ trạng thái lọc của khu nổi bật -->
            <input type="hidden" name="loc_gia_noibat" value="<?php echo htmlspecialchars($loc_gia_noibat); ?>">

            <button type="submit" class="btn btn-danger filter-btn">
                <i class="bi bi-funnel-fill"></i> Lọc
            </button>

            <?php if ($loc_gia_sale != ''): ?>
                <a href="trang_chu.php?loc_gia_noibat=<?php echo urlencode($loc_gia_noibat); ?>#khuyen-mai"
                   class="btn btn-outline-danger clear-btn">
                    Bỏ lọc
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php
        /*
        |--------------------------------------------------------------------------
        | QUERY KHUYẾN MÃI
        |--------------------------------------------------------------------------
        | Điều kiện:
        | - Còn hàng
        | - Có giảm giá
        | - Có thêm điều kiện lọc giá nếu người dùng chọn
        */
        $sql_km = "SELECT sp.*, hsx.TenHangSanXuat
                   FROM SanPham sp
                   LEFT JOIN HangSanXuat hsx ON sp.HangSanXuatID = hsx.ID
                   WHERE sp.SoLuong > 0 
                     AND sp.PhanTramGiam > 0
                     $dieuKienSale
                   ORDER BY sp.ID ASC
                   LIMIT 8";

        $result_km = $conn->query($sql_km);

        /*
        |--------------------------------------------------------------------------
        | Mảng lưu ID sản phẩm đã xuất hiện trong khuyến mãi
        | Để khu nổi bật không bị trùng lại
        |--------------------------------------------------------------------------
        */
        $dsKhuyenMaiIDs = [];

        if ($result_km && $result_km->num_rows > 0) {
            while ($row = $result_km->fetch_assoc()) {
                $dsKhuyenMaiIDs[] = (int)$row['ID'];

                $idSP = (int)$row['ID'];
                $tenSP = htmlspecialchars($row['TenSanPham']);
                $tenHang = htmlspecialchars($row['TenHangSanXuat'] ?? 'Không rõ');
                $hinhAnh = !empty($row['HinhAnh']) ? "uploads/" . $row['HinhAnh'] : "uploads/no-image.jpg";

                $phanTram = isset($row['PhanTramGiam']) ? (int)$row['PhanTramGiam'] : 0;
                $giaGoc = (float)$row['DonGia'];
                $giaKhuyenMai = $giaGoc - ($giaGoc * $phanTram / 100);

                $giaGoc_format = number_format($giaGoc, 0, ',', '.');
                $giaKhuyenMai_format = number_format($giaKhuyenMai, 0, ',', '.');
                ?>
                <div class="col">
                    <div class="card h-100 product-card border-danger shadow position-relative" style="border-width: 2px;">
                        <!-- Badge giảm giá -->
                        <span class="badge bg-danger position-absolute px-2 py-2 fs-6 shadow-sm"
                              style="top: -10px; left: -10px; z-index: 10;">
                            <i class="bi bi-lightning-fill"></i> -<?php echo $phanTram; ?>%
                        </span>

                        <!-- Badge hãng -->
                        <span class="badge bg-dark position-absolute brand-badge">
                            <?php echo $tenHang; ?>
                        </span>

                        <!-- Ảnh -->
                        <a href="chi_tiet_san_pham.php?id=<?php echo $idSP; ?>">
                            <img src="<?php echo $hinhAnh; ?>"
                                 class="card-img-top product-img p-3"
                                 alt="<?php echo $tenSP; ?>">
                        </a>

                        <!-- Nội dung card -->
                        <div class="card-body d-flex flex-column text-center bg-light">
                            <h6 class="card-title product-title">
                                <a href="chi_tiet_san_pham.php?id=<?php echo $idSP; ?>"
                                   class="text-decoration-none text-dark fw-bold">
                                    <?php echo $tenSP; ?>
                                </a>
                            </h6>

                            <div class="mt-auto mb-3">
                                <span class="text-muted text-decoration-line-through small d-block">
                                    <?php echo $giaGoc_format; ?> đ
                                </span>
                                <h5 class="card-text text-danger fw-bold mb-0">
                                    <?php echo $giaKhuyenMai_format; ?> đ
                                </h5>
                            </div>

                            <!-- Nút thêm giỏ -->
                            <form action="them_gio_hang.php" method="POST">
                                <input type="hidden" name="id_sp" value="<?php echo $idSP; ?>">
                                <button type="submit" class="btn btn-danger w-100 rounded-pill fw-bold">
                                    <i class="bi bi-cart-plus"></i> Săn Ngay
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div class="col-12"><p class="text-center">Không có sản phẩm khuyến mãi phù hợp mức giá đã chọn.</p></div>';
        }
        ?>
    </div>
</div>

<!-- =========================================================
     KHU TIVI NỔI BẬT
========================================================= -->
<div class="container mb-5 mt-5">

    <!-- Header khu nổi bật -->
    <div class="section-header mb-4 border-bottom pb-2">
        <h3 class="text-primary text-uppercase fw-bold mb-0">Tivi Nổi Bật Nhất</h3>

        <!--
            Form lọc riêng cho khu nổi bật
            Giữ lại giá trị lọc của khu sale bằng hidden
        -->
        <form method="GET" action="" class="filter-form">
            <select name="loc_gia_noibat" class="form-select filter-box">
                <option value="">Chọn mức giá nổi bật</option>
                <option value="duoi10" <?php if ($loc_gia_noibat == 'duoi10') echo 'selected'; ?>>Dưới 10 triệu</option>
                <option value="10-20" <?php if ($loc_gia_noibat == '10-20') echo 'selected'; ?>>10 - 20 triệu</option>
                <option value="20-50" <?php if ($loc_gia_noibat == '20-50') echo 'selected'; ?>>20 - 50 triệu</option>
                <option value="tren50" <?php if ($loc_gia_noibat == 'tren50') echo 'selected'; ?>>Trên 50 triệu</option>
            </select>

            <!-- Giữ trạng thái lọc của khu khuyến mãi -->
            <input type="hidden" name="loc_gia_sale" value="<?php echo htmlspecialchars($loc_gia_sale); ?>">

            <button type="submit" class="btn btn-primary filter-btn">
                <i class="bi bi-funnel-fill"></i> Lọc
            </button>

            <?php if ($loc_gia_noibat != ''): ?>
                <a href="trang_chu.php?loc_gia_sale=<?php echo urlencode($loc_gia_sale); ?>"
                   class="btn btn-outline-primary clear-btn">
                    Bỏ lọc
                </a>
            <?php endif; ?>
        </form>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php
        /*
        |--------------------------------------------------------------------------
        | QUERY KHU NỔI BẬT
        |--------------------------------------------------------------------------
        | Lấy sản phẩm còn hàng, sau đó loại bớt sản phẩm đã có trong khuyến mãi
        */
        $sql = "SELECT sp.*, hsx.TenHangSanXuat
                FROM SanPham sp
                LEFT JOIN HangSanXuat hsx ON sp.HangSanXuatID = hsx.ID
                WHERE sp.SoLuong > 0
                  $dieuKienNoiBat
                ORDER BY sp.ID DESC";

        $result = $conn->query($sql);

        $soSanPhamNoiBat = 0;

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $idSP = (int)$row['ID'];

                /*
                |--------------------------------------------------------------------------
                | Nếu sản phẩm đã xuất hiện ở khu khuyến mãi thì bỏ qua
                |--------------------------------------------------------------------------
                */
                if (in_array($idSP, $dsKhuyenMaiIDs)) {
                    continue;
                }

                $soSanPhamNoiBat++;

                $tenSP = htmlspecialchars($row['TenSanPham']);
                $tenHang = htmlspecialchars($row['TenHangSanXuat'] ?? 'Không rõ');
                $hinhAnh = !empty($row['HinhAnh']) ? "uploads/" . $row['HinhAnh'] : "uploads/no-image.jpg";

                $giaGoc = (float)$row['DonGia'];
                $phanTram = isset($row['PhanTramGiam']) ? (int)$row['PhanTramGiam'] : 0;

                /*
                |--------------------------------------------------------------------------
                | Nếu sản phẩm nổi bật cũng có giảm giá
                | thì hiển thị giá gạch ngang + giá sau giảm
                |--------------------------------------------------------------------------
                */
                if ($phanTram > 0) {
                    $giaKhuyenMai = $giaGoc - ($giaGoc * $phanTram / 100);

                    $giaHienThi = '
                        <span class="text-muted text-decoration-line-through small d-block">' . number_format($giaGoc, 0, ',', '.') . ' đ</span>
                        <h5 class="card-text text-danger fw-bold mt-1 mb-3">' . number_format($giaKhuyenMai, 0, ',', '.') . ' đ</h5>
                    ';

                    $badgeGiamGia = '
                        <span class="badge bg-danger position-absolute" style="top: 10px; left: 10px; z-index: 10;">
                            -' . $phanTram . '%
                        </span>
                    ';
                } else {
                    $giaHienThi = '
                        <h5 class="card-text text-danger fw-bold mt-auto mb-3">' . number_format($giaGoc, 0, ',', '.') . ' đ</h5>
                    ';
                    $badgeGiamGia = '';
                }
                ?>
                <div class="col">
                    <div class="card h-100 product-card border-0 shadow-sm position-relative">
                        <?php echo $badgeGiamGia; ?>

                        <!-- Badge hãng -->
                        <span class="badge bg-secondary position-absolute brand-badge">
                            <?php echo $tenHang; ?>
                        </span>

                        <!-- Ảnh -->
                        <a href="chi_tiet_san_pham.php?id=<?php echo $idSP; ?>">
                            <img src="<?php echo $hinhAnh; ?>"
                                 class="card-img-top product-img p-3"
                                 alt="<?php echo $tenSP; ?>">
                        </a>

                        <!-- Nội dung -->
                        <div class="card-body d-flex flex-column text-center">
                            <h6 class="card-title product-title">
                                <a href="chi_tiet_san_pham.php?id=<?php echo $idSP; ?>"
                                   class="text-decoration-none text-dark fw-bold">
                                    <?php echo $tenSP; ?>
                                </a>
                            </h6>

                            <div class="mt-auto">
                                <?php echo $giaHienThi; ?>
                            </div>

                            <form action="them_gio_hang.php" method="POST">
                                <input type="hidden" name="id_sp" value="<?php echo $idSP; ?>">
                                <button type="submit" class="btn btn-primary w-100 rounded-pill">
                                    <i class="bi bi-cart-plus"></i> Thêm vào giỏ
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php
            }

            /*
            |--------------------------------------------------------------------------
            | Nếu query có dữ liệu nhưng bị loại hết do trùng với khu khuyến mãi
            |--------------------------------------------------------------------------
            */
            if ($soSanPhamNoiBat == 0) {
                echo '<div class="col-12"><p class="text-center">Không có sản phẩm nổi bật phù hợp mức giá đã chọn.</p></div>';
            }
        } else {
            echo '<div class="col-12"><p class="text-center">Hiện tại chưa có sản phẩm nào.</p></div>';
        }
        ?>
    </div>
</div>

<!-- =========================================================
     LIÊN HỆ
========================================================= -->
<div class="container mb-5 mt-5" id="lien-he">
    <h3 class="border-bottom pb-2 mb-4 text-primary text-uppercase fw-bold">Liên Hệ Với Chúng Tôi</h3>

    <div class="row g-4">
        <!-- Thông tin liên hệ -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3">
                        <i class="bi bi-geo-alt-fill text-danger"></i> Cửa Hàng Tivi N&U
                    </h5>
                    <p><strong>Địa chỉ:</strong> 54/98 Đường Trần Quang Khải, Phường Mỹ Thới, TP. Long Xuyên</p>
                    <p><strong>Điện thoại:</strong> <a href="tel:0123456789" class="text-decoration-none fw-bold text-primary">0123.456.789</a></p>
                    <p><strong>Email:</strong> <a href="mailto:hotro@tivinu.com" class="text-decoration-none">hotro@tivinu.com</a></p>
                    <p><strong>Giờ mở cửa:</strong> 8:00 - 21:00 (Tất cả các ngày trong tuần)</p>
                </div>
            </div>
        </div>

        <!-- Card hỗ trợ -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <h5 class="fw-bold mb-3">Hỗ trợ khách hàng 24/7</h5>
                    <p>
                        Quý khách cần tư vấn chọn mua Tivi hoặc hỗ trợ bảo hành?
                        Đừng ngần ngại gọi ngay cho chúng tôi để được phục vụ và giải đáp nhanh nhất!
                    </p>
                    <div>
                        <a href="tel:0123456789" class="btn btn-light btn-lg fw-bold mt-2 text-primary rounded-pill px-4 shadow-sm">
                            <i class="bi bi-telephone-fill"></i> Gọi Ngay
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =========================================================
     FOOTER
========================================================= -->
<footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
        <p class="mb-0">© 2026 - Cửa Hàng Tivi Nghi và Uy.</p>
        <p class="text-muted small">Chất lượng tạo nên thương hiệu</p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>