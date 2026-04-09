<?php
session_start();
require_once 'connect.php';

/* LẤY DANH SÁCH HÃNG CHO BỘ LỌC */
$sql_hang = "SELECT * FROM HangSanXuat";
$rs_hang = $conn->query($sql_hang);
$ds_hang = [];
if ($rs_hang && $rs_hang->num_rows > 0) {
    while ($h = $rs_hang->fetch_assoc()) {
        $ds_hang[] = $h;
    }
}

/* LẤY GIÁ TRỊ LỌC KHUYẾN MÃI */
$loc_gia_sale    = isset($_GET['loc_gia_sale']) ? $_GET['loc_gia_sale'] : '';
$loc_hang_sale   = isset($_GET['loc_hang_sale']) ? (int) $_GET['loc_hang_sale'] : 0;

$dieuKienSale = '';
if ($loc_hang_sale > 0) $dieuKienSale .= " AND sp.HangSanXuatID = $loc_hang_sale";
switch ($loc_gia_sale) {
    case 'duoi10': $dieuKienSale .= " AND sp.DonGia < 10000000"; break;
    case '10-20':  $dieuKienSale .= " AND sp.DonGia BETWEEN 10000000 AND 20000000"; break;
    case '20-50':  $dieuKienSale .= " AND sp.DonGia BETWEEN 20000000 AND 50000000"; break;
    case 'tren50': $dieuKienSale .= " AND sp.DonGia > 50000000"; break;
}

/* LẤY GIÁ TRỊ LỌC NỔI BẬT */
$loc_gia_noibat  = isset($_GET['loc_gia_noibat']) ? $_GET['loc_gia_noibat'] : '';
$loc_hang_noibat = isset($_GET['loc_hang_noibat']) ? (int) $_GET['loc_hang_noibat'] : 0;

$dieuKienNoiBat = '';
if ($loc_hang_noibat > 0) $dieuKienNoiBat .= " AND sp.HangSanXuatID = $loc_hang_noibat";
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
    <title>Siêu Thị Tivi N&U - Tất Cả Sản Phẩm</title>
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
                    <li class="nav-item"><a class="nav-link" href="trang_chu.php">Trang Chủ</a></li>
                    <li class="nav-item"><a class="nav-link active" href="san_pham.php">Sản Phẩm</a></li>
                    <li class="nav-item"><a class="nav-link" href="trang_chu.php#tin-tuc">Tin Tức</a></li>
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
                        <span class="text-white fw-bold mx-2"><i class="bi bi-person-check-fill"></i> Xin chào,
                            <?php echo htmlspecialchars($_SESSION['khach_hang_ten']); ?></span>
                        <a href="logout_khach.php" class="btn btn-danger btn-sm"><i class="bi bi-box-arrow-right"></i> Đăng xuất</a>
                    <?php else: ?>
                        <a href="dang_ky.php" class="btn btn-outline-light"><i class="bi bi-person-plus"></i> Đăng ký</a>
                        <a href="login_khach.php" class="btn btn-light"><i class="bi bi-box-arrow-in-right"></i> Đăng nhập</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="bg-light py-2 border-bottom">
        <div class="container">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="trang_chu.php" class="text-decoration-none">Trang chủ</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Sản phẩm</li>
                </ol>
            </nav>
        </div>
    </div>


    <div class="container mb-5 mt-4" id="khuyen-mai">
        <div class="section-header mb-4 border-bottom border-danger pb-2">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <h3 class="text-danger text-uppercase fw-bold mb-0">
                    <i class="bi bi-fire"></i> Ưu Đãi Khủng
                </h3>
                <div class="flash-sale-timer" id="timer-display">
                    <i class="bi bi-stopwatch"></i> Kết thúc sau: <span id="time-left">00:00:00</span>
                </div>
            </div>

            <form method="GET" action="san_pham.php#khuyen-mai" class="filter-form">
                <select name="loc_hang_sale" class="form-select filter-box">
                    <option value="0">-- Mọi Hãng --</option>
                    <?php foreach ($ds_hang as $h): ?>
                        <option value="<?php echo $h['ID']; ?>" <?php if ($loc_hang_sale == $h['ID']) echo 'selected'; ?>>
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
                    <a href="san_pham.php?loc_gia_noibat=<?php echo urlencode($loc_gia_noibat); ?>&loc_hang_noibat=<?php echo urlencode($loc_hang_noibat); ?>#khuyen-mai"
                        class="btn btn-outline-danger clear-btn">Bỏ lọc</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php
            $sql_km = "SELECT sp.*, hsx.TenHangSanXuat
                   FROM SanPham sp
                   LEFT JOIN HangSanXuat hsx ON sp.HangSanXuatID = hsx.ID
                   WHERE sp.SoLuong > 0 AND sp.PhanTramGiam > 0 $dieuKienSale
                   ORDER BY sp.ID ASC"; // Bỏ LIMIT 8 đi để hiện hết nếu muốn
            $result_km = $conn->query($sql_km);
            $dsKhuyenMaiIDs = [];

            if ($result_km && $result_km->num_rows > 0) {
                while ($row = $result_km->fetch_assoc()) {
                    $dsKhuyenMaiIDs[] = (int) $row['ID'];
                    $idSP = (int) $row['ID'];
                    $tenSP = htmlspecialchars($row['TenSanPham']);
                    $tenHang = htmlspecialchars($row['TenHangSanXuat'] ?? 'Không rõ');
                    $hinhAnh = !empty($row['HinhAnh']) ? "uploads/" . $row['HinhAnh'] : "uploads/no-image.jpg";
                    $phanTram = isset($row['PhanTramGiam']) ? (int) $row['PhanTramGiam'] : 0;
                    $giaGoc = (float) $row['DonGia'];
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
            <h3 class="text-primary text-uppercase fw-bold mb-0">Tất Cả Tivi Nổi Bật</h3>

            <form method="GET" action="san_pham.php#noi-bat" class="filter-form">
                <select name="loc_hang_noibat" class="form-select filter-box">
                    <option value="0">-- Mọi Hãng --</option>
                    <?php foreach ($ds_hang as $h): ?>
                        <option value="<?php echo $h['ID']; ?>" <?php if ($loc_hang_noibat == $h['ID']) echo 'selected'; ?>>
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
                    <a href="san_pham.php?loc_gia_sale=<?php echo urlencode($loc_gia_sale); ?>&loc_hang_sale=<?php echo urlencode($loc_hang_sale); ?>#noi-bat"
                        class="btn btn-outline-primary clear-btn">Bỏ lọc</a>
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
                    $idSP = (int) $row['ID'];
                    if (in_array($idSP, $dsKhuyenMaiIDs)) continue; // Tránh trùng sản phẩm đã hiện ở phần Khuyến Mãi

                    $soSanPhamNoiBat++;
                    $tenSP = htmlspecialchars($row['TenSanPham']);
                    $tenHang = htmlspecialchars($row['TenHangSanXuat'] ?? 'Không rõ');
                    $hinhAnh = !empty($row['HinhAnh']) ? "uploads/" . $row['HinhAnh'] : "uploads/no-image.jpg";
                    $giaGoc = (float) $row['DonGia'];
                    $phanTram = isset($row['PhanTramGiam']) ? (int) $row['PhanTramGiam'] : 0;

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
                                    <th width="280">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $kh_id = (int) $_SESSION['khach_hang_id'];
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
                                        $duocDoiTra = true;
                                        if (!empty($row_mua['TraGopID']) && $row_mua['TinhTrangTra'] !== 'Đã tất toán') {
                                            $duocDoiTra = false;
                                        }
                                        ?>
                                        <tr>
                                            <td><img src="<?php echo $img_mua; ?>" width="80" class="img-fluid rounded border" alt="Tivi"></td>
                                            <td class="text-start fw-bold text-dark"><?php echo htmlspecialchars($row_mua['TenSanPham']); ?></td>
                                            <td class="text-primary fw-bold">#HD<?php echo $row_mua['HoaDonID']; ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($row_mua['NgayLap'])); ?></td>
                                            <td class="fw-bold"><?php echo (int) $row_mua['SoLuongBan']; ?></td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-2 justify-content-center align-items-center">
                                                    <?php if ($duocDoiTra): ?>
                                                        <a href="doitra_yeucau.php?hd_id=<?php echo $row_mua['HoaDonID']; ?>&sp_id=<?php echo $row_mua['SanPhamID']; ?>&action=doi" class="btn btn-sm btn-warning fw-bold text-dark shadow-sm">
                                                            <i class="bi bi-arrow-left-right"></i> Đổi
                                                        </a>
                                                        <a href="doitra_yeucau.php?hd_id=<?php echo $row_mua['HoaDonID']; ?>&sp_id=<?php echo $row_mua['SanPhamID']; ?>&action=tra" class="btn btn-sm btn-danger fw-bold shadow-sm">
                                                            <i class="bi bi-arrow-return-left"></i> Trả
                                                        </a>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary px-3 py-2 shadow-sm" title="Bạn cần thanh toán hết các kỳ trả góp trước khi đổi/trả sản phẩm này.">
                                                            <i class="bi bi-lock-fill me-1"></i> Trả góp
                                                        </span>
                                                    <?php endif; ?>
                                                    <a href="danh_gia.php?sp_id=<?php echo $row_mua['SanPhamID']; ?>&hd_id=<?php echo $row_mua['HoaDonID']; ?>" class="btn btn-sm btn-info fw-bold text-white shadow-sm">
                                                        <i class="bi bi-star-fill text-warning"></i> Đánh giá
                                                    </a>
                                                </div>
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

    <footer class="bg-dark text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-0">© 2026 - Cửa Hàng Tivi Nghi và Uy.</p>
            <p class="text-muted small">Chất lượng tạo nên thương hiệu</p>
        </div>
    </footer>

    <a href="https://zalo.me/0931082845" target="_blank" class="nút-chat-nổi">
        <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="white" viewBox="0 0 16 16">
            <path d="M8 15c4.418 0 8-3.134 8-7s-3.582-7-8-7-8 3.134-8 7c0 1.76.743 3.37 1.97 4.6-.097 1.016-.417 2.13-.771 2.966-.079.186.074.394.273.362 2.256-.37 3.597-.938 4.18-1.234A9.06 9.06 0 0 0 8 15z" />
        </svg>
    </a>

    <div class="modal fade" id="adminLoginModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock-fill text-warning"></i> Khu vực Quản trị viên</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tài khoản</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-person-fill"></i></span>
                                <input type="text" class="form-control" id="adminUsername" name="username" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">Mật khẩu</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="bi bi-key-fill"></i></span>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-dark w-100 fw-bold py-2">Đăng Nhập Hệ Thống</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('keydown', function(event) {
            if (event.ctrlKey && event.shiftKey && (event.key === 'A' || event.key === 'a')) {
                event.preventDefault();
                var myModal = new bootstrap.Modal(document.getElementById('adminLoginModal'));
                myModal.show();
                document.getElementById('adminLoginModal').addEventListener('shown.bs.modal', function () {
                    document.getElementById('adminUsername').focus();
                });
            }
        });
    </script>
    <script src="script.js"></script>
</body>
</html>