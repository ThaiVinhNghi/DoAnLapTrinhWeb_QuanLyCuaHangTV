<?php
session_start();
require_once 'connect.php';

/*
|--------------------------------------------------------------------------
| LẤY DANH SÁCH HÃNG SẢN XUẤT TỪ DATABASE (DÙNG CHO BỘ LỌC)
|--------------------------------------------------------------------------
*/
$sql_hang = "SELECT * FROM HangSanXuat";
$rs_hang = $conn->query($sql_hang);
$ds_hang = [];
if ($rs_hang && $rs_hang->num_rows > 0) {
    while ($h = $rs_hang->fetch_assoc()) {
        $ds_hang[] = $h;
    }
}

/*
|--------------------------------------------------------------------------
| 1) LẤY GIÁ TRỊ LỌC TỪ URL (GỒM CẢ GIÁ VÀ HÃNG)
|--------------------------------------------------------------------------
*/
$loc_gia_sale    = isset($_GET['loc_gia_sale']) ? $_GET['loc_gia_sale'] : '';
$loc_hang_sale   = isset($_GET['loc_hang_sale']) ? (int)$_GET['loc_hang_sale'] : 0;

$loc_gia_noibat  = isset($_GET['loc_gia_noibat']) ? $_GET['loc_gia_noibat'] : '';
$loc_hang_noibat = isset($_GET['loc_hang_noibat']) ? (int)$_GET['loc_hang_noibat'] : 0;

/*
|--------------------------------------------------------------------------
| 2) TẠO ĐIỀU KIỆN SQL CHO KHU KHUYẾN MÃI
|--------------------------------------------------------------------------
*/
$dieuKienSale = '';

if ($loc_hang_sale > 0) {
    $dieuKienSale .= " AND sp.HangSanXuatID = $loc_hang_sale";
}

switch ($loc_gia_sale) {
    case 'duoi10': $dieuKienSale .= " AND sp.DonGia < 10000000"; break;
    case '10-20':  $dieuKienSale .= " AND sp.DonGia BETWEEN 10000000 AND 20000000"; break;
    case '20-50':  $dieuKienSale .= " AND sp.DonGia BETWEEN 20000000 AND 50000000"; break;
    case 'tren50': $dieuKienSale .= " AND sp.DonGia > 50000000"; break;
}

/*
|--------------------------------------------------------------------------
| 3) TẠO ĐIỀU KIỆN SQL CHO KHU NỔI BẬT
|--------------------------------------------------------------------------
*/
$dieuKienNoiBat = '';

if ($loc_hang_noibat > 0) {
    $dieuKienNoiBat .= " AND sp.HangSanXuatID = $loc_hang_noibat";
}

switch ($loc_gia_noibat) {
    case 'duoi10': $dieuKienNoiBat .= " AND sp.DonGia < 10000000"; break;
    case '10-20':  $dieuKienNoiBat .= " AND sp.DonGia BETWEEN 10000000 AND 20000000"; break;
    case '20-50':  $dieuKienNoiBat .= " AND sp.DonGia BETWEEN 20000000 AND 50000000"; break;
    case 'tren50': $dieuKienNoiBat .= " AND sp.DonGia > 50000000"; break;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siêu Thị Tivi - Uy Tín, Chất Lượng</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="style.css">
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
                <li class="nav-item"><a class="nav-link" href="trang_chu.php#khuyen-mai">Khuyến Mãi</a></li>
                <li class="nav-item"><a class="nav-link" href="trang_chu.php#noi-bat">Nổi Bật</a></li>
                <li class="nav-item"><a class="nav-link" href="trang_chu.php#lien-he">Liên Hệ</a></li>
            </ul>
            <form class="d-flex me-3" action="tim_kiem.php" method="GET">
                <input class="form-control me-2" type="search" name="tukhoa" placeholder="Tìm tên Tivi..." required>
                <button class="btn btn-outline-light" type="submit"><i class="bi bi-search"></i></button>
            </form>
            <div class="d-flex align-items-center flex-wrap gap-2">
                <a href="gio_hang.php" class="btn btn-warning position-relative fw-bold">
                    <i class="bi bi-cart-fill"></i> Giỏ hàng
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        <?php echo isset($_SESSION['gio_hang']) ? count($_SESSION['gio_hang']) : 0; ?>
                    </span>
                </a>
                <?php if (isset($_SESSION['khach_hang_id'])): ?>
                    <span class="text-white fw-bold mx-2"><i class="bi bi-person-check-fill"></i> Xin chào, <?php echo htmlspecialchars($_SESSION['khach_hang_ten']); ?></span>
                    <a href="logout_khach.php" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light"><i class="bi bi-person-circle"></i> Admin</a>
                    <a href="dang_ky.php" class="btn btn-outline-light"><i class="bi bi-person-plus"></i> Đăng ký</a>
                    <a href="login_khach.php" class="btn btn-light"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div id="bannerCarousel" class="carousel slide carousel-fade shadow rounded overflow-hidden" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="0" class="active"></button>
            <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="1"></button>
            <button type="button" data-bs-target="#bannerCarousel" data-bs-slide-to="2"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active" data-bs-interval="3000">
                <div class="p-4 p-md-5 text-bg-dark text-center d-flex flex-column justify-content-center align-items-center" 
                     style="height: 320px; background: linear-gradient(to right, #0052D4, #4364F7, #6FB1FC);">
                    <h1 class="display-5 fw-bold">Đón Lễ Lớn - Sale Tivi Lên Đến 50%</h1>
                    <p class="lead my-3">Sở hữu ngay những chiếc Smart Tivi 4K, OLED, QLED với giá tốt nhất thị trường.</p>
                    <a href="#khuyen-mai" class="btn btn-warning btn-lg fw-bold rounded-pill shadow-sm">
                        <i class="bi bi-fire"></i> Săn Sale Ngay
                    </a>
                </div>
            </div>
            <div class="carousel-item" data-bs-interval="3000">
                <div class="p-4 p-md-5 text-bg-dark text-center d-flex flex-column justify-content-center align-items-center" 
                     style="height: 320px; background: linear-gradient(to right, #ff416c, #ff4b2b);">
                    <h1 class="display-5 fw-bold">Thế Giới Tivi OLED & QLED</h1>
                    <p class="lead my-3">Trải nghiệm hình ảnh sắc nét, âm thanh sống động như rạp chiếu phim.</p>
                    <a href="#noi-bat" class="btn btn-light btn-lg fw-bold rounded-pill text-danger shadow-sm">
                        <i class="bi bi-star-fill"></i> Xem Chi Tiết
                    </a>
                </div>
            </div>
            <div class="carousel-item" data-bs-interval="3000">
                <div class="p-4 p-md-5 text-bg-dark text-center d-flex flex-column justify-content-center align-items-center" 
                     style="height: 320px; background: linear-gradient(to right, #11998e, #38ef7d);">
                    <h1 class="display-5 fw-bold">Bảo Hành Chính Hãng 2 Năm</h1>
                    <p class="lead my-3">Miễn phí lắp đặt tận nhà - Hỗ trợ trả góp 0% lãi suất.</p>
                    <a href="#lien-he" class="btn btn-dark btn-lg fw-bold rounded-pill shadow-sm">
                        <i class="bi bi-telephone-fill"></i> Liên Hệ Tư Vấn
                    </a>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#bannerCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#bannerCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </button>
    </div>
</div>

<div class="container mb-5 mt-5" id="khuyen-mai">
    <div class="section-header mb-4 border-bottom border-danger pb-2">
        <div class="d-flex align-items-center gap-3 flex-wrap">
            <h3 class="text-danger text-uppercase fw-bold mb-0">
                <i class="bi bi-fire"></i> Ưu Đãi Khủng
            </h3>
            <div class="flash-sale-timer" id="timer-display">
                <i class="bi bi-stopwatch"></i> Kết thúc sau: <span id="time-left">00:00:00</span>
            </div>
        </div>

        <form method="GET" action="trang_chu.php#khuyen-mai" class="filter-form">
            <select name="loc_hang_sale" class="form-select filter-box">
                <option value="0">-- Mọi Hãng --</option>
                <?php foreach($ds_hang as $h): ?>
                    <option value="<?php echo $h['ID']; ?>" <?php if($loc_hang_sale == $h['ID']) echo 'selected'; ?>>
                        <?php echo $h['TenHangSanXuat']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="loc_gia_sale" class="form-select filter-box">
                <option value="">-- Mọi Mức Giá --</option>
                <option value="duoi10" <?php if ($loc_gia_sale == 'duoi10') echo 'selected'; ?>>Dưới 10 triệu</option>
                <option value="10-20" <?php if ($loc_gia_sale == '10-20') echo 'selected'; ?>>10 - 20 triệu</option>
                <option value="20-50" <?php if ($loc_gia_sale == '20-50') echo 'selected'; ?>>20 - 50 triệu</option>
                <option value="tren50" <?php if ($loc_gia_sale == 'tren50') echo 'selected'; ?>>Trên 50 triệu</option>
            </select>

            <input type="hidden" name="loc_gia_noibat" value="<?php echo htmlspecialchars($loc_gia_noibat); ?>">
            <input type="hidden" name="loc_hang_noibat" value="<?php echo htmlspecialchars($loc_hang_noibat); ?>">

            <button type="submit" class="btn btn-danger filter-btn"><i class="bi bi-funnel-fill"></i> Lọc</button>

            <?php if ($loc_gia_sale != '' || $loc_hang_sale > 0): ?>
                <a href="trang_chu.php?loc_gia_noibat=<?php echo urlencode($loc_gia_noibat); ?>&loc_hang_noibat=<?php echo urlencode($loc_hang_noibat); ?>#khuyen-mai" class="btn btn-outline-danger clear-btn">Bỏ lọc</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php
        $sql_km = "SELECT sp.*, hsx.TenHangSanXuat
                   FROM SanPham sp
                   LEFT JOIN HangSanXuat hsx ON sp.HangSanXuatID = hsx.ID
                   WHERE sp.SoLuong > 0 AND sp.PhanTramGiam > 0 $dieuKienSale
                   ORDER BY sp.ID ASC LIMIT 8";
        $result_km = $conn->query($sql_km);
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
                ?>
                <div class="col">
                    <div class="card h-100 product-card border-danger shadow position-relative" style="border-width: 2px;">
                        <span class="badge bg-danger position-absolute px-2 py-2 fs-6 shadow-sm" style="top: -10px; left: -10px; z-index: 10;">
                            <i class="bi bi-lightning-fill"></i> -<?php echo $phanTram; ?>%
                        </span>
                        <span class="badge bg-dark position-absolute brand-badge"><?php echo $tenHang; ?></span>
                        <a href="chi_tiet_san_pham.php?id=<?php echo $idSP; ?>">
                            <img src="<?php echo $hinhAnh; ?>" class="card-img-top product-img p-3" alt="<?php echo $tenSP; ?>">
                        </a>
                        <div class="card-body d-flex flex-column text-center bg-light">
                            <h6 class="card-title product-title">
                                <a href="chi_tiet_san_pham.php?id=<?php echo $idSP; ?>" class="text-decoration-none text-dark fw-bold"><?php echo $tenSP; ?></a>
                            </h6>
                            <div class="mt-auto mb-3">
                                <span class="text-muted text-decoration-line-through small d-block"><?php echo number_format($giaGoc, 0, ',', '.'); ?> đ</span>
                                <h5 class="card-text text-danger fw-bold mb-0"><?php echo number_format($giaKhuyenMai, 0, ',', '.'); ?> đ</h5>
                            </div>
                            <form action="them_gio_hang.php" method="POST">
                                <input type="hidden" name="id_sp" value="<?php echo $idSP; ?>">
                                <button type="submit" class="btn btn-danger w-100 rounded-pill fw-bold"><i class="bi bi-cart-plus"></i> Săn Ngay</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div class="col-12"><div class="alert alert-warning text-center w-100"><i class="bi bi-emoji-frown"></i> Không có sản phẩm khuyến mãi phù hợp bộ lọc.</div></div>';
        }
        ?>
    </div>
</div>

<div class="container mb-5 mt-5" id="noi-bat">
    <div class="section-header mb-4 border-bottom pb-2">
        <h3 class="text-primary text-uppercase fw-bold mb-0">Tivi Nổi Bật Nhất</h3>

        <form method="GET" action="trang_chu.php#noi-bat" class="filter-form">
            <select name="loc_hang_noibat" class="form-select filter-box">
                <option value="0">-- Mọi Hãng --</option>
                <?php foreach($ds_hang as $h): ?>
                    <option value="<?php echo $h['ID']; ?>" <?php if($loc_hang_noibat == $h['ID']) echo 'selected'; ?>>
                        <?php echo $h['TenHangSanXuat']; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select name="loc_gia_noibat" class="form-select filter-box">
                <option value="">-- Mọi Mức Giá --</option>
                <option value="duoi10" <?php if ($loc_gia_noibat == 'duoi10') echo 'selected'; ?>>Dưới 10 triệu</option>
                <option value="10-20" <?php if ($loc_gia_noibat == '10-20') echo 'selected'; ?>>10 - 20 triệu</option>
                <option value="20-50" <?php if ($loc_gia_noibat == '20-50') echo 'selected'; ?>>20 - 50 triệu</option>
                <option value="tren50" <?php if ($loc_gia_noibat == 'tren50') echo 'selected'; ?>>Trên 50 triệu</option>
            </select>

            <input type="hidden" name="loc_gia_sale" value="<?php echo htmlspecialchars($loc_gia_sale); ?>">
            <input type="hidden" name="loc_hang_sale" value="<?php echo htmlspecialchars($loc_hang_sale); ?>">

            <button type="submit" class="btn btn-primary filter-btn"><i class="bi bi-funnel-fill"></i> Lọc</button>

            <?php if ($loc_gia_noibat != '' || $loc_hang_noibat > 0): ?>
                <a href="trang_chu.php?loc_gia_sale=<?php echo urlencode($loc_gia_sale); ?>&loc_hang_sale=<?php echo urlencode($loc_hang_sale); ?>#noi-bat" class="btn btn-outline-primary clear-btn">Bỏ lọc</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
        <?php
        $sql = "SELECT sp.*, hsx.TenHangSanXuat
                FROM SanPham sp
                LEFT JOIN HangSanXuat hsx ON sp.HangSanXuatID = hsx.ID
                WHERE sp.SoLuong > 0 $dieuKienNoiBat
                ORDER BY sp.ID DESC";
        $result = $conn->query($sql);
        $soSanPhamNoiBat = 0;

        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $idSP = (int)$row['ID'];
                if (in_array($idSP, $dsKhuyenMaiIDs)) continue;

                $soSanPhamNoiBat++;
                $tenSP = htmlspecialchars($row['TenSanPham']);
                $tenHang = htmlspecialchars($row['TenHangSanXuat'] ?? 'Không rõ');
                $hinhAnh = !empty($row['HinhAnh']) ? "uploads/" . $row['HinhAnh'] : "uploads/no-image.jpg";
                $giaGoc = (float)$row['DonGia'];
                $phanTram = isset($row['PhanTramGiam']) ? (int)$row['PhanTramGiam'] : 0;

                if ($phanTram > 0) {
                    $giaKhuyenMai = $giaGoc - ($giaGoc * $phanTram / 100);
                    $giaHienThi = '<span class="text-muted text-decoration-line-through small d-block">' . number_format($giaGoc, 0, ',', '.') . ' đ</span><h5 class="card-text text-danger fw-bold mt-1 mb-3">' . number_format($giaKhuyenMai, 0, ',', '.') . ' đ</h5>';
                    $badgeGiamGia = '<span class="badge bg-danger position-absolute" style="top: 10px; left: 10px; z-index: 10;">-' . $phanTram . '%</span>';
                } else {
                    $giaHienThi = '<h5 class="card-text text-danger fw-bold mt-auto mb-3">' . number_format($giaGoc, 0, ',', '.') . ' đ</h5>';
                    $badgeGiamGia = '';
                }
                ?>
                <div class="col">
                    <div class="card h-100 product-card border-0 shadow-sm position-relative">
                        <?php echo $badgeGiamGia; ?>
                        <span class="badge bg-secondary position-absolute brand-badge"><?php echo $tenHang; ?></span>
                        <a href="chi_tiet_san_pham.php?id=<?php echo $idSP; ?>">
                            <img src="<?php echo $hinhAnh; ?>" class="card-img-top product-img p-3" alt="<?php echo $tenSP; ?>">
                        </a>
                        <div class="card-body d-flex flex-column text-center">
                            <h6 class="card-title product-title">
                                <a href="chi_tiet_san_pham.php?id=<?php echo $idSP; ?>" class="text-decoration-none text-dark fw-bold"><?php echo $tenSP; ?></a>
                            </h6>
                            <div class="mt-auto"><?php echo $giaHienThi; ?></div>
                            <form action="them_gio_hang.php" method="POST">
                                <input type="hidden" name="id_sp" value="<?php echo $idSP; ?>">
                                <button type="submit" class="btn btn-primary w-100 rounded-pill"><i class="bi bi-cart-plus"></i> Thêm vào giỏ</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php
            }
            if ($soSanPhamNoiBat == 0) {
                echo '<div class="col-12"><div class="alert alert-warning text-center w-100"><i class="bi bi-emoji-frown"></i> Không có sản phẩm nổi bật phù hợp bộ lọc.</div></div>';
            }
        } else {
            echo '<div class="col-12"><p class="text-center">Hiện tại chưa có sản phẩm nào.</p></div>';
        }
        ?>
    </div>
</div>

<?php if (isset($_SESSION['khach_hang_id'])): ?>
<div class="container mb-5 mt-5" id="san-pham-da-mua">
    <div class="section-header mb-4 border-bottom border-success pb-2">
        <h3 class="text-success text-uppercase fw-bold mb-0">
            <i class="bi bi-box-seam"></i> Sản phẩm bạn đã mua
        </h3>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle text-center mb-0">
                    <thead class="table-success">
                        <tr>
                            <th width="120">Hình ảnh</th>
                            <th class="text-start">Tên sản phẩm</th>
                            <th width="120">Hóa đơn</th>
                            <th width="120">Ngày mua</th>
                            <th width="100">Số lượng</th>
                            <th width="220">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $kh_id = (int)$_SESSION['khach_hang_id'];
                        
                        // SỬA SQL: Dùng LEFT JOIN để kết nối thêm bảng tragop (nếu hóa đơn này là mua trả góp)
                        $sql_damua = "SELECT sp.TenSanPham, sp.HinhAnh, hd.ID as HoaDonID, hd.NgayLap, hdct.SoLuongBan, sp.ID as SanPhamID,
                                             tg.ID as TraGopID, tg.TinhTrangTra
                                FROM hoadon hd
                                JOIN hoadon_chitiet hdct ON hd.ID = hdct.HoaDonID
                                JOIN sanpham sp ON hdct.SanPhamID = sp.ID
                                LEFT JOIN tragop tg ON hd.ID = tg.HoaDonID
                                WHERE hd.KhachHangID = $kh_id
                                ORDER BY hd.ID DESC";
                                
                        $result_damua = $conn->query($sql_damua);
                        
                        if ($result_damua && $result_damua->num_rows > 0):
                            while ($row_mua = $result_damua->fetch_assoc()):
                                $img_mua = !empty($row_mua['HinhAnh']) ? "uploads/" . $row_mua['HinhAnh'] : "uploads/no-image.jpg";
                                
                                // LOGIC KIỂM TRA QUYỀN ĐỔI TRẢ
                                $duocDoiTra = true;
                                // Nếu ID trả góp tồn tại VÀ tình trạng chưa phải là "Đã tất toán"
                                if (!empty($row_mua['TraGopID']) && $row_mua['TinhTrangTra'] !== 'Đã tất toán') {
                                    $duocDoiTra = false;
                                }
                        ?>
                            <tr>
                                <td><img src="<?php echo $img_mua; ?>" width="80" class="img-fluid rounded border" alt="Tivi"></td>
                                <td class="text-start fw-bold text-dark"><?php echo htmlspecialchars($row_mua['TenSanPham']); ?></td>
                                <td class="text-primary fw-bold">#HD<?php echo $row_mua['HoaDonID']; ?></td>
                                <td><?php echo date('d/m/Y', strtotime($row_mua['NgayLap'])); ?></td>
                                <td class="fw-bold"><?php echo (int)$row_mua['SoLuongBan']; ?></td>
                                <td>
                                    <?php if ($duocDoiTra): ?>
                                        <div class="d-flex gap-2 justify-content-center">
                                            <a href="doitra_yeucau.php?hd_id=<?php echo $row_mua['HoaDonID']; ?>&sp_id=<?php echo $row_mua['SanPhamID']; ?>&action=doi" class="btn btn-sm btn-warning fw-bold text-dark shadow-sm">
                                                <i class="bi bi-arrow-left-right"></i> Đổi hàng
                                            </a>
                                            <a href="doitra_yeucau.php?hd_id=<?php echo $row_mua['HoaDonID']; ?>&sp_id=<?php echo $row_mua['SanPhamID']; ?>&action=tra" class="btn btn-sm btn-danger fw-bold shadow-sm">
                                                <i class="bi bi-arrow-return-left"></i> Trả hàng
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge bg-secondary px-3 py-2 shadow-sm" title="Bạn cần thanh toán hết các kỳ trả góp trước khi đổi/trả sản phẩm này.">
                                            <i class="bi bi-lock-fill me-1"></i> Chưa tất toán trả góp
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Bạn chưa có sản phẩm nào đã mua.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="container mb-5 mt-5" id="lien-he">
    <h3 class="border-bottom pb-2 mb-4 text-primary text-uppercase fw-bold">Liên Hệ Với Chúng Tôi</h3>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h5 class="fw-bold mb-3"><i class="bi bi-geo-alt-fill text-danger"></i> Cửa Hàng Tivi N&U</h5>
                    <p><strong>Địa chỉ:</strong> 54/98 Đường Trần Quang Khải, Phường Mỹ Thới, TP. Long Xuyên</p>
                    <p><strong>Điện thoại:</strong> <a href="tel:0123456789" class="text-decoration-none fw-bold text-primary">0123.456.789</a></p>
                    <p><strong>Email:</strong> <a href="mailto:hotro@tivinu.com" class="text-decoration-none">hotro@tivinu.com</a></p>
                    <p><strong>Giờ mở cửa:</strong> 8:00 - 21:00 (Tất cả các ngày trong tuần)</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <h5 class="fw-bold mb-3">Hỗ trợ khách hàng 24/7</h5>
                    <p>Quý khách cần tư vấn chọn mua Tivi hoặc hỗ trợ bảo hành? Đừng ngần ngại gọi ngay cho chúng tôi!</p>
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

<footer class="bg-dark text-white text-center py-4 mt-5">
    <div class="container">
        <p class="mb-0">© 2026 - Cửa Hàng Tivi Nghi và Uy.</p>
        <p class="text-muted small">Chất lượng tạo nên thương hiệu</p>
    </div>
</footer>

<a href="https://zalo.me/0931082845" target="_blank" class="nút-chat-nổi">
    <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="white" viewBox="0 0 16 16">
      <path d="M8 15c4.418 0 8-3.134 8-7s-3.582-7-8-7-8 3.134-8 7c0 1.76.743 3.37 1.97 4.6-.097 1.016-.417 2.13-.771 2.966-.079.186.074.394.273.362 2.256-.37 3.597-.938 4.18-1.234A9.06 9.06 0 0 0 8 15z"/>
    </svg>
</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="script.js"></script>

</body>
</html>