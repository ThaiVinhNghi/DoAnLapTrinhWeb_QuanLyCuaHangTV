<?php
session_start();
require_once 'thu_vien/connect.php';

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
    <title>N&U Store | Tất Cả Sản Phẩm</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="tai_nguyen/css/style.css">
</head>

<body>

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
                    <li class="nav-item"><a class="nav-link" href="trang_chu.php#lien-he">Hỗ Trợ</a></li>
                </ul>
                <div class="d-flex align-items-center gap-3">
                    <a href="gio_hang.php" class="text-white text-decoration-none position-relative fs-5">
                        <i class="bi bi-bag"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.6rem;">
                            <?php echo isset($_SESSION['gio_hang']) ? count($_SESSION['gio_hang']) : 0; ?>
                        </span>
                    </a>
                    <div class="vr bg-secondary mx-2" style="width: 2px; height: 24px;"></div>
                    <?php if (isset($_SESSION['khach_hang_id'])): ?>
                        <div class="dropdown">
                            <a class="text-white text-decoration-none dropdown-toggle fw-bold" href="#" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['khach_hang_ten']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3">
                                <li><a class="dropdown-item" href="san_pham.php#san-pham-da-mua">Đơn hàng của tôi</a></li>
                                <li><a class="dropdown-item" href="doi_mat_khau.php">Đổi mật khẩu</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger fw-bold" href="logout_khach.php">Đăng xuất</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="login_khach.php" class="text-white text-decoration-none fw-bold" style="font-size: 0.9rem;">Đăng Nhập</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <div class="page-header-premium">
        <div class="container">
            <span class="badge border border-light text-light mb-3 px-3 py-2 fw-bold text-uppercase" style="letter-spacing: 2px;">Cửa hàng chính hãng</span>
            <h1 class="page-title">Trải Nghiệm Hiển Thị<br>Đỉnh Cao</h1>
        </div>
    </div>


    <div class="container mb-5" id="khuyen-mai">
        
        <div class="filter-bar d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-5">
            <h4 class="fw-bold mb-0 text-danger text-uppercase d-flex align-items-center gap-2">
                <i class="bi bi-fire fs-3"></i> Hot Sale
            </h4>
            
            <form method="GET" action="san_pham.php#khuyen-mai" class="d-flex flex-wrap gap-2 align-items-center m-0">
                <select name="loc_hang_sale" class="form-select w-auto">
                    <option value="0">-- Mọi Hãng --</option>
                    <?php foreach ($ds_hang as $h): ?>
                        <option value="<?php echo $h['ID']; ?>" <?php if ($loc_hang_sale == $h['ID']) echo 'selected'; ?>><?php echo $h['TenHangSanXuat']; ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="loc_gia_sale" class="form-select w-auto">
                    <option value="">-- Mọi Mức Giá --</option>
                    <option value="duoi10" <?php if ($loc_gia_sale == 'duoi10') echo 'selected'; ?>>Dưới 10 triệu</option>
                    <option value="10-20" <?php if ($loc_gia_sale == '10-20') echo 'selected'; ?>>10 - 20 triệu</option>
                    <option value="20-50" <?php if ($loc_gia_sale == '20-50') echo 'selected'; ?>>20 - 50 triệu</option>
                    <option value="tren50" <?php if ($loc_gia_sale == 'tren50') echo 'selected'; ?>>Trên 50 triệu</option>
                </select>
                <input type="hidden" name="loc_gia_noibat" value="<?php echo htmlspecialchars($loc_gia_noibat); ?>">
                <input type="hidden" name="loc_hang_noibat" value="<?php echo htmlspecialchars($loc_hang_noibat); ?>">
                
                <button type="submit" class="btn btn-dark btn-pill px-4"><i class="bi bi-funnel-fill"></i> Lọc</button>
                <?php if ($loc_gia_sale != '' || $loc_hang_sale > 0): ?>
                    <a href="san_pham.php?loc_gia_noibat=<?php echo urlencode($loc_gia_noibat); ?>&loc_hang_noibat=<?php echo urlencode($loc_hang_noibat); ?>#khuyen-mai" class="btn text-danger fw-bold text-decoration-none">Xóa lọc</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php
            $sql_km = "SELECT sp.*, hsx.TenHangSanXuat FROM SanPham sp LEFT JOIN HangSanXuat hsx ON sp.HangSanXuatID = hsx.ID WHERE sp.SoLuong > 0 AND sp.PhanTramGiam > 0 $dieuKienSale ORDER BY sp.ID ASC";
            $result_km = $conn->query($sql_km);
            $dsKhuyenMaiIDs = [];

            if ($result_km && $result_km->num_rows > 0) {
                while ($row = $result_km->fetch_assoc()) {
                    $dsKhuyenMaiIDs[] = (int) $row['ID'];
                    $idSP = (int) $row['ID'];
                    $tenSP = htmlspecialchars($row['TenSanPham']);
                    $tenHang = htmlspecialchars($row['TenHangSanXuat'] ?? 'N&U');
                    $hinhAnh = !empty($row['HinhAnh']) ? "uploads/" . $row['HinhAnh'] : "uploads/no-image.jpg";
                    $phanTram = isset($row['PhanTramGiam']) ? (int) $row['PhanTramGiam'] : 0;
                    $giaGoc = (float) $row['DonGia'];
                    $giaKhuyenMai = $giaGoc - ($giaGoc * $phanTram / 100);
                    ?>
                    <div class="col">
                        <div class="card h-100 premium-card position-relative">
                            <span class="badge bg-danger position-absolute badge-premium shadow-sm" style="top: 15px; left: 15px; z-index: 10;">
                                <i class="bi bi-lightning-charge-fill"></i> -<?php echo $phanTram; ?>%
                            </span>
                            <span class="badge bg-dark position-absolute" style="top: 15px; right: 15px; z-index: 10; opacity: 0.8; border-radius: 6px;"><?php echo $tenHang; ?></span>
                            
                            <a href="chi_tiet_san_pham.php?id=<?php echo $idSP; ?>" class="img-zoom-wrapper d-block text-center mt-3">
                                <img src="<?php echo $hinhAnh; ?>" class="img-fluid" style="height: 180px; object-fit: contain;" alt="<?php echo $tenSP; ?>">
                            </a>
                            
                            <div class="card-body d-flex flex-column px-4 pb-4 pt-0">
                                <h6 class="card-title fw-bold lh-base mb-3" style="min-height: 2.4rem;">
                                    <a href="chi_tiet_san_pham.php?id=<?php echo $idSP; ?>" class="text-decoration-none text-dark"><?php echo $tenSP; ?></a>
                                </h6>
                                <div class="mt-auto mb-4">
                                    <span class="text-muted text-decoration-line-through small d-block"><?php echo number_format($giaGoc, 0, ',', '.'); ?> đ</span>
                                    <h4 class="card-text text-danger fw-bold mb-0"><?php echo number_format($giaKhuyenMai, 0, ',', '.'); ?> đ</h4>
                                </div>
                                <form action="them_gio_hang.php" method="POST">
                                    <input type="hidden" name="id_sp" value="<?php echo $idSP; ?>">
                                    <button type="submit" class="btn btn-outline-danger w-100 btn-pill fw-bold"><i class="bi bi-cart-plus"></i> Chọn Mua</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo '<div class="col-12"><div class="alert bg-white shadow-sm text-center border-0 py-4"><i class="bi bi-info-circle text-danger fs-4 d-block mb-2"></i> Không tìm thấy sản phẩm khuyến mãi phù hợp.</div></div>';
            }
            ?>
        </div>
    </div>


    <div class="bg-white py-5 border-top" id="noi-bat">
        <div class="container">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-5 gap-3">
                <h3 class="fw-bold mb-0 text-dark text-uppercase letter-spacing-1">Tất Cả Sản Phẩm</h3>
                
                <form method="GET" action="san_pham.php#noi-bat" class="d-flex flex-wrap gap-2 align-items-center m-0">
                    <select name="loc_hang_noibat" class="form-select bg-light border-0" style="border-radius: 30px;">
                        <option value="0">-- Mọi Hãng --</option>
                        <?php foreach ($ds_hang as $h): ?>
                            <option value="<?php echo $h['ID']; ?>" <?php if ($loc_hang_noibat == $h['ID']) echo 'selected'; ?>><?php echo $h['TenHangSanXuat']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="loc_gia_noibat" class="form-select bg-light border-0" style="border-radius: 30px;">
                        <option value="">-- Mọi Mức Giá --</option>
                        <option value="duoi10" <?php if ($loc_gia_noibat == 'duoi10') echo 'selected'; ?>>Dưới 10 triệu</option>
                        <option value="10-20" <?php if ($loc_gia_noibat == '10-20') echo 'selected'; ?>>10 - 20 triệu</option>
                        <option value="20-50" <?php if ($loc_gia_noibat == '20-50') echo 'selected'; ?>>20 - 50 triệu</option>
                        <option value="tren50" <?php if ($loc_gia_noibat == 'tren50') echo 'selected'; ?>>Trên 50 triệu</option>
                    </select>
                    <input type="hidden" name="loc_gia_sale" value="<?php echo htmlspecialchars($loc_gia_sale); ?>">
                    <input type="hidden" name="loc_hang_sale" value="<?php echo htmlspecialchars($loc_hang_sale); ?>">
                    
                    <button type="submit" class="btn btn-dark btn-pill px-4"><i class="bi bi-funnel-fill"></i> Lọc</button>
                    <?php if ($loc_gia_noibat != '' || $loc_hang_noibat > 0): ?>
                        <a href="san_pham.php?loc_gia_sale=<?php echo urlencode($loc_gia_sale); ?>&loc_hang_sale=<?php echo urlencode($loc_hang_sale); ?>#noi-bat" class="btn text-secondary fw-bold text-decoration-none">Xóa lọc</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
                <?php
                $sql = "SELECT sp.*, hsx.TenHangSanXuat FROM SanPham sp LEFT JOIN HangSanXuat hsx ON sp.HangSanXuatID = hsx.ID WHERE sp.SoLuong > 0 $dieuKienNoiBat ORDER BY sp.ID DESC";
                $result = $conn->query($sql);
                $soSanPhamNoiBat = 0;

                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $idSP = (int) $row['ID'];
                        if (in_array($idSP, $dsKhuyenMaiIDs)) continue; // Bỏ qua sp đã hiển thị ở trên

                        $soSanPhamNoiBat++;
                        $tenSP = htmlspecialchars($row['TenSanPham']);
                        $tenHang = htmlspecialchars($row['TenHangSanXuat'] ?? 'N&U');
                        $hinhAnh = !empty($row['HinhAnh']) ? "uploads/" . $row['HinhAnh'] : "uploads/no-image.jpg";
                        $giaGoc = (float) $row['DonGia'];
                        $phanTram = isset($row['PhanTramGiam']) ? (int) $row['PhanTramGiam'] : 0;
                        ?>
                        <div class="col">
                            <div class="card h-100 premium-card position-relative bg-light">
                                <?php if ($phanTram > 0): ?>
                                    <span class="badge bg-danger position-absolute badge-premium shadow-sm" style="top: 15px; left: 15px; z-index: 10;">
                                        -<?php echo $phanTram; ?>%
                                    </span>
                                <?php endif; ?>
                                <span class="badge bg-secondary position-absolute" style="top: 15px; right: 15px; z-index: 10; opacity: 0.7; border-radius: 6px;"><?php echo $tenHang; ?></span>
                                
                                <a href="chi_tiet_san_pham.php?id=<?php echo $idSP; ?>" class="img-zoom-wrapper d-block text-center mt-3">
                                    <img src="<?php echo $hinhAnh; ?>" class="img-fluid" style="height: 180px; object-fit: contain; mix-blend-mode: multiply;" alt="<?php echo $tenSP; ?>">
                                </a>
                                
                                <div class="card-body d-flex flex-column px-4 pb-4 pt-0">
                                    <h6 class="card-title fw-bold lh-base mb-3" style="min-height: 2.4rem;">
                                        <a href="chi_tiet_san_pham.php?id=<?php echo $idSP; ?>" class="text-decoration-none text-dark"><?php echo $tenSP; ?></a>
                                    </h6>
                                    <div class="mt-auto mb-4">
                                        <?php if ($phanTram > 0): 
                                            $giaKhuyenMai = $giaGoc - ($giaGoc * $phanTram / 100);
                                        ?>
                                            <span class="text-muted text-decoration-line-through small d-block"><?php echo number_format($giaGoc, 0, ',', '.'); ?> đ</span>
                                            <h4 class="card-text text-danger fw-bold mb-0"><?php echo number_format($giaKhuyenMai, 0, ',', '.'); ?> đ</h4>
                                        <?php else: ?>
                                            <h4 class="card-text text-dark fw-bold mb-0 mt-3"><?php echo number_format($giaGoc, 0, ',', '.'); ?> đ</h4>
                                        <?php endif; ?>
                                    </div>
                                    <form action="them_gio_hang.php" method="POST">
                                        <input type="hidden" name="id_sp" value="<?php echo $idSP; ?>">
                                        <button type="submit" class="btn btn-dark w-100 btn-pill fw-bold"><i class="bi bi-cart-plus"></i> Thêm vào giỏ</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                    if ($soSanPhamNoiBat == 0) {
                        echo '<div class="col-12"><div class="alert bg-white shadow-sm text-center border-0 py-4"><i class="bi bi-info-circle text-secondary fs-4 d-block mb-2"></i> Không tìm thấy sản phẩm phù hợp.</div></div>';
                    }
                } else {
                    echo '<div class="col-12"><p class="text-center text-muted">Hiện tại chưa có sản phẩm nào.</p></div>';
                }
                ?>
            </div>
        </div>
    </div>


    <?php if (isset($_SESSION['khach_hang_id'])): ?>
        <div class="container my-5 pb-5" id="san-pham-da-mua">
            <h3 class="fw-bold mb-4 text-dark text-uppercase letter-spacing-1 border-bottom border-2 border-dark pb-2 d-inline-block">Đơn Hàng Của Bạn</h3>
            
            <div class="card premium-card border-0 shadow-sm mt-3">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-center mb-0 border-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="120" class="py-3 text-secondary text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Hình ảnh</th>
                                    <th class="py-3 text-secondary text-uppercase text-start" style="font-size: 0.8rem; letter-spacing: 1px;">Sản phẩm</th>
                                    <th width="120" class="py-3 text-secondary text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Mã HĐ</th>
                                    <th width="130" class="py-3 text-secondary text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Ngày mua</th>
                                    <th width="100" class="py-3 text-secondary text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">SL</th>
                                    <th width="100" class="py-3 text-secondary text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Trạng thái</th>
                                    <th width="260" class="py-3 text-secondary text-uppercase" style="font-size: 0.8rem; letter-spacing: 1px;">Tùy chọn</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                <?php
                                $kh_id = (int) $_SESSION['khach_hang_id'];
                                // Lấy danh sách đơn hàng (Đã xuất hóa đơn) VÀ Các hồ sơ trả góp đang CHỜ DUYỆT (chưa có hóa đơn)
                                $sql_damua = "
                                    SELECT sp.TenSanPham, sp.HinhAnh, hd.ID as HoaDonID, hd.NgayLap, hd.NhanVienID,
                                           hdct.SoLuongBan, sp.ID as SanPhamID,
                                           tg.ID as TraGopID, tg.TinhTrangTra,
                                           dt.ID as DoiTraID, dt.LoaiYeuCau, dt.TrangThai as TrangThaiDoiTra,
                                           sp_moi.TenSanPham as TenSPMoi
                                    FROM hoadon hd
                                    JOIN hoadon_chitiet hdct ON hd.ID = hdct.HoaDonID
                                    JOIN sanpham sp ON hdct.SanPhamID = sp.ID
                                    LEFT JOIN tragop tg ON hd.ID = tg.HoaDonID
                                    LEFT JOIN doitra dt ON hd.ID = dt.HoaDonID AND dt.KhachHangID = $kh_id
                                    LEFT JOIN sanpham sp_moi ON dt.SanPhamMoiID = sp_moi.ID
                                    WHERE hd.KhachHangID = $kh_id

                                    UNION ALL
                                    
                                    SELECT sp.TenSanPham, sp.HinhAnh, NULL as HoaDonID, tg.NgayDangKy as NgayLap, NULL as NhanVienID,
                                           tgct.SoLuong as SoLuongBan, sp.ID as SanPhamID,
                                           tg.ID as TraGopID, tg.TinhTrangTra,
                                           NULL as DoiTraID, NULL as LoaiYeuCau, NULL as TrangThaiDoiTra,
                                           NULL as TenSPMoi
                                    FROM tragop tg
                                    JOIN tragop_chitiet tgct ON tg.ID = tgct.TraGopID
                                    JOIN sanpham sp ON tgct.SanPhamID = sp.ID
                                    WHERE tg.KhachHangID = $kh_id AND tg.TrangThai = 'Chờ duyệt'

                                    ORDER BY NgayLap DESC
                                ";
                                $result_damua = $conn->query($sql_damua);

                                if ($result_damua && $result_damua->num_rows > 0):
                                    while ($row_mua = $result_damua->fetch_assoc()):
                                        $img_mua = !empty($row_mua['HinhAnh']) ? "uploads/" . $row_mua['HinhAnh'] : "uploads/no-image.jpg";
                                        
                                        // Kiểm tra đã được admin duyệt chưa
                                        $daDuyet = !empty($row_mua['NhanVienID']);
                                        
                                        // Kiểm tra đã đánh giá chưa
                                        $daDanhGia = false;
                                        if (!empty($row_mua['HoaDonID'])) {
                                            $sql_ck_dg = "SELECT ID FROM DanhGia WHERE HoaDonID = " . (int)$row_mua['HoaDonID'] . " AND SanPhamID = " . (int)$row_mua['SanPhamID'] . " AND KhachHangID = $kh_id LIMIT 1";
                                            $res_ck_dg = $conn->query($sql_ck_dg);
                                            $daDanhGia = ($res_ck_dg && $res_ck_dg->num_rows > 0);
                                        }
                                        
                                        // Kiểm tra đã đổi/trả chưa và lấy trạng thái
                                        $daDoiTra = !empty($row_mua['DoiTraID']);
                                        $loaiDoiTra = $row_mua['LoaiYeuCau'] ?? '';
                                        $trangThaiDoiTra = $row_mua['TrangThaiDoiTra'] ?? ''; // Lấy trạng thái từ bảng doitra
                                        $tenSPMoi = $row_mua['TenSPMoi'] ?? '';
                                        
                                        // Kiểm tra trả góp
                                        $duocDoiTra = true;
                                        if (!empty($row_mua['TraGopID']) && $row_mua['TinhTrangTra'] !== 'Đã tất toán') {
                                            $duocDoiTra = false;
                                        }
                                        ?>
                                        <tr>
                                            <td class="py-3"><img src="<?php echo $img_mua; ?>" width="70" class="img-fluid rounded" alt="Tivi" style="mix-blend-mode: multiply;"></td>
                                            <td class="text-start fw-bold text-dark py-3">
                                                <?php echo htmlspecialchars($row_mua['TenSanPham']); ?>
                                                <?php if (!empty($tenSPMoi)): ?>
                                                    <div class="mt-1">
                                                        <?php if ($trangThaiDoiTra == 'Chờ xử lý'): ?>
                                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle" style="font-size:0.75rem;" title="Đang đợi admin duyệt">
                                                                <i class="bi bi-arrow-repeat"></i> Xin đổi sang: <?php echo htmlspecialchars($tenSPMoi); ?>
                                                            </span>
                                                        <?php elseif ($trangThaiDoiTra == 'Đã hoàn tất'): ?>
                                                            <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size:0.75rem;">
                                                                <i class="bi bi-check2-circle"></i> Đã đổi thành: <?php echo htmlspecialchars($tenSPMoi); ?>
                                                            </span>
                                                        <?php elseif ($trangThaiDoiTra == 'Từ chối'): ?>
                                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle text-decoration-line-through" style="font-size:0.75rem;" title="Đổi hàng bị từ chối">
                                                                <i class="bi bi-x"></i> Xin đổi sang: <?php echo htmlspecialchars($tenSPMoi); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-secondary fw-bold py-3">
                                                <?php echo !empty($row_mua['HoaDonID']) ? '#' . $row_mua['HoaDonID'] : '<span class="badge bg-secondary text-white shadow-sm border border-secondary" title="Mã trả góp">#TG' . $row_mua['TraGopID'] . '</span>'; ?>
                                            </td>
                                            <td class="text-secondary py-3"><?php echo date('d/m/Y', strtotime($row_mua['NgayLap'])); ?></td>
                                            <td class="fw-bold text-secondary py-3"><?php echo (int) $row_mua['SoLuongBan']; ?></td>
                                            <td class="py-3">
                                                <?php if (!$daDuyet): ?>
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill" style="font-size:0.75rem;">
                                                        <i class="bi bi-hourglass-split"></i> Chờ xét duyệt
                                                    </span>
                                                <?php elseif ($daDoiTra): ?>
                                                    <!-- Trạng thái sản phẩm Đã mua nhưng bị đem Đổi / Trả -->
                                                    <?php if ($trangThaiDoiTra == 'Chờ xử lý'): ?>
                                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill" style="font-size:0.75rem;" title="Admin chưa duyệt">
                                                            <i class="bi bi-arrow-repeat"></i> Chờ duyệt <?php echo mb_strtolower($loaiDoiTra, 'UTF-8'); ?>
                                                        </span>
                                                    <?php elseif ($trangThaiDoiTra == 'Đã hoàn tất'): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size:0.75rem;">
                                                            <i class="bi bi-check-circle-fill"></i> Đã duyệt <?php echo mb_strtolower($loaiDoiTra, 'UTF-8'); ?>
                                                        </span>
                                                    <?php elseif ($trangThaiDoiTra == 'Từ chối'): ?>
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 rounded-pill" style="font-size:0.75rem;">
                                                            <i class="bi bi-x-circle-fill"></i> Từ chối <?php echo mb_strtolower($loaiDoiTra, 'UTF-8'); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 rounded-pill" style="font-size:0.75rem;">
                                                            Đang xử lý
                                                        </span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill" style="font-size:0.75rem;">
                                                        <i class="bi bi-check-circle-fill"></i> Đã duyệt
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="py-3">
                                                <div class="d-flex flex-wrap gap-2 justify-content-center align-items-center">
                                                    <?php if (!$daDuyet): ?>
                                                        <!-- Chưa duyệt: chưa cho đổi/trả, đánh giá -->
                                                        <span class="text-muted small fst-italic">Chờ xử lý hóa đơn...</span>
                                                    <?php elseif (!$duocDoiTra): ?>
                                                        <!-- Đang trả góp chưa tất toán -->
                                                        <span class="badge bg-light text-secondary border px-3 py-2 rounded-pill" title="Chưa tất toán trả góp">
                                                            <i class="bi bi-lock-fill"></i> Đang Trả Góp
                                                        </span>
                                                    <?php elseif ($daDoiTra): ?>
                                                        <!-- Tùy chọn vô hiệu hóa vì Đã có yêu cầu đổi/trả -->
                                                        <span class="badge bg-light text-secondary px-3 py-2 border rounded-pill shadow-sm">
                                                            <i class="bi bi-info-circle"></i> Đã yêu cầu <?php echo mb_strtolower($loaiDoiTra, 'UTF-8'); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <!-- Chưa đổi/trả: hiện nút -->
                                                        <a href="doitra_yeucau.php?hd_id=<?php echo $row_mua['HoaDonID']; ?>&sp_id=<?php echo $row_mua['SanPhamID']; ?>&action=doi" class="btn btn-sm btn-outline-dark fw-bold rounded-pill px-3">Đổi</a>
                                                        <a href="doitra_yeucau.php?hd_id=<?php echo $row_mua['HoaDonID']; ?>&sp_id=<?php echo $row_mua['SanPhamID']; ?>&action=tra" class="btn btn-sm btn-outline-danger fw-bold rounded-pill px-3">Trả</a>
                                                    <?php endif; ?>

                                                    <?php if ($daDanhGia): ?>
                                                        <!-- Đã đánh giá: khóa nút -->
                                                        <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                                                            <i class="bi bi-star-fill"></i> Đã đánh giá
                                                        </span>
                                                    <?php elseif ($daDuyet): ?>
                                                        <!-- Đã duyệt, chưa đánh giá: hiện nút -->
                                                        <a href="danh_gia.php?sp_id=<?php echo $row_mua['SanPhamID']; ?>&hd_id=<?php echo $row_mua['HoaDonID']; ?>" class="btn btn-sm btn-dark fw-bold rounded-pill px-3 shadow-sm">
                                                            <i class="bi bi-star-fill text-warning" style="font-size: 0.8rem;"></i> Đánh giá
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                    endwhile;
                                else:
                                    ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-5"><i class="bi bi-bag-x display-4 d-block mb-3 opacity-25"></i>Bạn chưa mua sản phẩm nào.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <footer class="bg-black text-white-50 text-center py-4 border-top border-secondary">
        <div class="container">
            <p class="mb-1 text-white fw-bold">© 2026 - Cửa Hàng Tivi Nghi và Uy.</p>
            <p class="small mb-0">Chất lượng tạo nên thương hiệu. Mọi bản quyền được bảo lưu.</p>
        </div>
    </footer>

    <a href="https://zalo.me/0931082845" target="_blank" class="position-fixed bottom-0 end-0 m-4 bg-primary rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; z-index: 1000; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
        <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" fill="white" viewBox="0 0 16 16">
            <path d="M8 15c4.418 0 8-3.134 8-7s-3.582-7-8-7-8 3.134-8 7c0 1.76.743 3.37 1.97 4.6-.097 1.016-.417 2.13-.771 2.966-.079.186.074.394.273.362 2.256-.37 3.597-.938 4.18-1.234A9.06 9.06 0 0 0 8 15z" />
        </svg>
    </a>

    <div class="modal fade" id="adminLoginModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header bg-black text-white border-0 py-4">
                    <h5 class="modal-title fw-bold mx-auto"><i class="bi bi-cpu text-danger me-2"></i> SYSTEM ADMIN</h5>
                    <button type="button" class="btn-close btn-close-white position-absolute end-0 me-4" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-5">
                    <form action="login.php" method="POST">
                        <div class="form-floating mb-4">
                            <input type="text" class="form-control bg-light border-0" id="adminUsername" name="username" placeholder="Tài khoản" required style="border-radius: 8px;">
                            <label for="adminUsername" class="text-muted"><i class="bi bi-person-fill me-1"></i> Tài khoản</label>
                        </div>
                        <div class="form-floating mb-5">
                            <input type="password" class="form-control bg-light border-0" id="adminPassword" name="password" placeholder="Mật khẩu" required style="border-radius: 8px;">
                            <label for="adminPassword" class="text-muted"><i class="bi bi-key-fill me-1"></i> Mật khẩu</label>
                        </div>
                        <button type="submit" class="btn btn-dark btn-pill w-100 py-3 fs-6">Truy Cập Hệ Thống</button>
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
    <script src="tai_nguyen/js/script.js"></script>
</body>
</html>