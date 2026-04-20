<?php
session_start();
require_once 'thu_vien/connect.php';
require_once 'thu_vien/nhatky_helper.php';

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['khach_hang_id'])) {
    echo "<script>alert('Vui lòng đăng nhập để thực hiện chức năng này!'); window.location.href='login_khach.php';</script>";
    exit();
}

$khachHangID = (int)$_SESSION['khach_hang_id'];

// 2. Lấy thông tin từ URL
$hd_id   = isset($_GET['hd_id'])  ? (int)$_GET['hd_id']  : 0;
$sp_id   = isset($_GET['sp_id'])  ? (int)$_GET['sp_id']   : 0;
$action  = isset($_GET['action']) ? $_GET['action']        : '';

if ($hd_id <= 0 || $sp_id <= 0 || !in_array($action, ['doi','tra'])) {
    echo "<script>alert('Dữ liệu không hợp lệ!'); window.location.href='san_pham.php#san-pham-da-mua';</script>";
    exit();
}

$loaiYeuCau = ($action == 'doi') ? 'Đổi hàng' : 'Trả hàng';

// 3. Lấy thông tin sản phẩm đã mua
$sql_check = "SELECT sp.TenSanPham, sp.HinhAnh, hdct.SoLuongBan, hdct.DonGiaBan, hd.NgayLap
              FROM hoadon_chitiet hdct
              JOIN hoadon hd  ON hdct.HoaDonID  = hd.ID
              JOIN sanpham sp  ON hdct.SanPhamID  = sp.ID
              WHERE hd.ID = ? AND sp.ID = ? AND hd.KhachHangID = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("iii", $hd_id, $sp_id, $khachHangID);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows == 0) {
    echo "<script>alert('Không tìm thấy thông tin sản phẩm trong hóa đơn của bạn!'); window.location.href='san_pham.php#san-pham-da-mua';</script>";
    exit();
}
$sanPham = $result_check->fetch_assoc();

// 4. Chặn gửi lần 2
$sql_ck_dt = "SELECT ID FROM doitra WHERE HoaDonID = ? AND KhachHangID = ? LIMIT 1";
$stmt_ck   = $conn->prepare($sql_ck_dt);
$stmt_ck->bind_param("ii", $hd_id, $khachHangID);
$stmt_ck->execute();
$res_ck    = $stmt_ck->get_result();
if ($res_ck && $res_ck->num_rows > 0) {
    echo "<script>alert('Bạn đã gửi yêu cầu đổi/trả cho đơn hàng này rồi. Mỗi đơn chỉ được 1 lần!'); window.location.href='san_pham.php#san-pham-da-mua';</script>";
    $stmt_ck->close();
    exit();
}
$stmt_ck->close();

// 5. Nếu là Đổi hàng → lấy danh sách SP có trong kho để khách chọn (trừ SP đang đổi)
$dsSanPhamDoi = [];
if ($action == 'doi') {
    $sql_sp_doi = "SELECT ID, TenSanPham, DonGia, SoLuong, HinhAnh FROM sanpham WHERE SoLuong > 0 AND ID != ? ORDER BY TenSanPham ASC";
    $stmt_sp = $conn->prepare($sql_sp_doi);
    $stmt_sp->bind_param("i", $sp_id);
    $stmt_sp->execute();
    $res_sp = $stmt_sp->get_result();
    while ($s = $res_sp->fetch_assoc()) {
        $dsSanPhamDoi[] = $s;
    }
    $stmt_sp->close();
}

$thongBao = '';

// 6. Xử lý POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['gui_yeu_cau'])) {
    $lyDo           = trim($_POST['ly_do']       ?? '');
    $tinhTrang      = trim($_POST['tinh_trang']  ?? '');
    $soLuongDoiTra  = (int)($_POST['so_luong']   ?? 1);
    $spMoiID        = ($action == 'doi') ? (int)($_POST['san_pham_doi'] ?? 0) : 0;

    $errors = [];
    if (empty($lyDo))     $errors[] = 'Vui lòng nhập lý do chi tiết!';
    if (empty($tinhTrang)) $errors[] = 'Vui lòng chọn tình trạng sản phẩm!';
    if ($soLuongDoiTra <= 0 || $soLuongDoiTra > $sanPham['SoLuongBan'])
        $errors[] = 'Số lượng không hợp lệ!';
    if ($action == 'doi' && $spMoiID <= 0)
        $errors[] = 'Vui lòng chọn sản phẩm muốn đổi sang!';

    if (!empty($errors)) {
        $thongBao = "<div class='alert alert-danger rounded-3'>
            <ul class='mb-0'>" . implode('', array_map(fn($e) => "<li>$e</li>", $errors)) . "</ul>
        </div>";
    } else {
        $conn->begin_transaction();
        try {
            $tongTienHoan = ($action == 'doi') ? 0 : $soLuongDoiTra * $sanPham['DonGiaBan'];

            // Chèn vào doitra
            if ($action == 'doi' && $spMoiID > 0) {
                $sql_insert_dt = "INSERT INTO doitra (HoaDonID, KhachHangID, LoaiYeuCau, LyDo, TongTienHoan, SanPhamMoiID) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt_dt = $conn->prepare($sql_insert_dt);
                $stmt_dt->bind_param("iissdi", $hd_id, $khachHangID, $loaiYeuCau, $lyDo, $tongTienHoan, $spMoiID);
            } else {
                $sql_insert_dt = "INSERT INTO doitra (HoaDonID, KhachHangID, LoaiYeuCau, LyDo, TongTienHoan) VALUES (?, ?, ?, ?, ?)";
                $stmt_dt = $conn->prepare($sql_insert_dt);
                $stmt_dt->bind_param("iissd", $hd_id, $khachHangID, $loaiYeuCau, $lyDo, $tongTienHoan);
            }
            $stmt_dt->execute();
            $doiTraID = $conn->insert_id;

            // Chèn chi tiết
            $sql_insert_ct = "INSERT INTO doitra_chitiet (DoiTraID, SanPhamID, SoLuong, DonGiaHoan, TinhTrangSanPham) VALUES (?, ?, ?, ?, ?)";
            $stmt_ct = $conn->prepare($sql_insert_ct);
            $stmt_ct->bind_param("iiids", $doiTraID, $sp_id, $soLuongDoiTra, $sanPham['DonGiaBan'], $tinhTrang);
            $stmt_ct->execute();

            ghiNhatKyKhachHangTuSession($conn, 'TaoYeuCauDoiTra', 'doitra', $doiTraID, "Yêu cầu $loaiYeuCau cho hóa đơn #HD$hd_id");
            $conn->commit();

            echo "<script>alert('Gửi yêu cầu thành công! Cửa hàng sẽ liên hệ với bạn sớm nhất.'); window.location.href='san_pham.php#san-pham-da-mua';</script>";
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $thongBao = "<div class='alert alert-danger rounded-3'><i class='bi bi-exclamation-triangle-fill'></i> Có lỗi xảy ra: " . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $loaiYeuCau; ?> Sản Phẩm - N&U Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="tai_nguyen/css/style.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }

        .navbar-premium {
            background-color: rgba(10, 10, 10, 0.95) !important;
            backdrop-filter: blur(10px);
            padding: 15px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .premium-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            background: #fff;
            overflow: hidden;
        }

        .card-header-dark {
            background: #0a0a0a;
            color: #fff;
            padding: 1.5rem;
            text-align: center;
        }

        .card-header-dark.doi { background: linear-gradient(135deg, #1a1a1a, #3a2800); border-bottom: 2px solid #ffc107; }
        .card-header-dark.tra { background: linear-gradient(135deg, #1a0000, #3a0000); border-bottom: 2px solid #dc3545; }

        .product-info-box {
            background: #f8f9fa;
            border-radius: 14px;
            border: 1px solid #e9ecef;
            padding: 1rem 1.2rem;
            margin-bottom: 1.5rem;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1.5px solid #dee2e6;
            padding: 0.6rem 1rem;
            font-size: 0.95rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0a0a0a;
            box-shadow: 0 0 0 3px rgba(10,10,10,0.08);
        }

        .form-label {
            font-weight: 600;
            font-size: 0.88rem;
            text-transform: uppercase;
            letter-spacing: .5px;
            color: #555;
            margin-bottom: .4rem;
        }

        .btn-submit-doi  { background: #ffc107; color: #000; border: none; }
        .btn-submit-doi:hover  { background: #e0a800; color: #000; }
        .btn-submit-tra  { background: #dc3545; color: #fff; border: none; }
        .btn-submit-tra:hover  { background: #bb2d3b; }

        .btn-pill {
            border-radius: 50px;
            padding: 0.75rem 2rem;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: .5px;
        }

        .sp-doi-card {
            border: 2px solid #dee2e6;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            cursor: pointer;
            transition: all 0.2s;
            background: #fff;
        }
        .sp-doi-card:hover { border-color: #0a0a0a; }
        .sp-doi-card.selected { border-color: #198754; background: #f0fff4; }

        .sp-moi-box {
            background: linear-gradient(135deg, #f0fff4, #e8f5e9);
            border: 2px solid #c8e6c9;
            border-radius: 14px;
            padding: 1.25rem;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark navbar-premium sticky-top">
        <div class="container justify-content-between">
            <a class="navbar-brand fw-bold fs-4 text-white m-0" href="trang_chu.php">
                <i class="bi bi-tv text-danger"></i> N&U Store
            </a>
            <a href="san_pham.php#san-pham-da-mua" class="btn btn-outline-light btn-sm rounded-pill px-3">
                <i class="bi bi-arrow-left"></i> Đơn hàng của tôi
            </a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">

                <div class="premium-card">
                    <!-- Header -->
                    <div class="card-header-dark <?php echo $action; ?>">
                        <?php if ($action == 'doi'): ?>
                            <i class="bi bi-arrow-repeat fs-3 text-warning"></i>
                            <h4 class="fw-bold mt-2 mb-0 text-warning">YÊU CẦU ĐỔI HÀNG</h4>
                        <?php else: ?>
                            <i class="bi bi-box-arrow-in-left fs-3 text-danger"></i>
                            <h4 class="fw-bold mt-2 mb-0 text-danger">YÊU CẦU TRẢ HÀNG</h4>
                        <?php endif; ?>
                        <p class="text-white-50 small mb-0 mt-1">Vui lòng điền thông tin chính xác để chúng tôi hỗ trợ bạn nhanh nhất</p>
                    </div>

                    <div class="card-body p-4 p-md-5">

                        <?php echo $thongBao; ?>

                        <!-- Sản phẩm đang đổi/trả -->
                        <div class="product-info-box d-flex align-items-center gap-3 mb-4">
                            <img src="<?php echo !empty($sanPham['HinhAnh']) ? 'uploads/'.$sanPham['HinhAnh'] : 'uploads/no-image.jpg'; ?>"
                                 width="80" class="rounded-3 border bg-white p-1" style="object-fit:contain; mix-blend-mode:multiply;" alt="Tivi">
                            <div>
                                <span class="badge bg-secondary mb-1">Hóa đơn #HD<?php echo $hd_id; ?></span>
                                <h5 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($sanPham['TenSanPham']); ?></h5>
                                <div class="small text-muted">
                                    Mua ngày: <strong class="text-dark"><?php echo date('d/m/Y', strtotime($sanPham['NgayLap'])); ?></strong>
                                    &nbsp;·&nbsp;
                                    SL đã mua: <strong class="text-dark"><?php echo $sanPham['SoLuongBan']; ?></strong> chiếc
                                    &nbsp;·&nbsp;
                                    Đơn giá: <strong class="text-danger"><?php echo number_format($sanPham['DonGiaBan'], 0, ',', '.'); ?>đ</strong>
                                </div>
                            </div>
                        </div>

                        <form action="" method="POST">

                            <?php if ($action == 'doi'): ?>
                            <!-- ===== COMBOBOX CHỌN SẢN PHẨM MỚI (chỉ khi Đổi hàng) ===== -->
                            <div class="sp-moi-box mb-4">
                                <label class="form-label mb-2">
                                    <i class="bi bi-search text-success me-1"></i>
                                    CHỌN SẢN PHẨM MUỐN ĐỔI SANG <span class="text-danger">*</span>
                                </label>
                                <select name="san_pham_doi" class="form-select" required id="selectSPDoi">
                                    <option value="">-- Click để chọn Tivi trong kho --</option>
                                    <?php foreach ($dsSanPhamDoi as $sp_moi): ?>
                                        <option value="<?php echo $sp_moi['ID']; ?>"
                                                data-gia="<?php echo number_format($sp_moi['DonGia'], 0, ',', '.'); ?>"
                                                data-kho="<?php echo $sp_moi['SoLuong']; ?>">
                                            <?php echo htmlspecialchars($sp_moi['TenSanPham']); ?>
                                            — <?php echo number_format($sp_moi['DonGia'], 0, ',', '.'); ?>đ
                                            (Còn <?php echo $sp_moi['SoLuong']; ?> chiếc)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="spDoiInfo" class="mt-2 small text-muted d-none">
                                    <i class="bi bi-info-circle text-success"></i>
                                    Bạn đã chọn: <strong id="spDoiTen"></strong> —
                                    Giá: <strong class="text-danger" id="spDoiGia"></strong> —
                                    Kho: <strong id="spDoiKho"></strong> chiếc
                                </div>
                            </div>
                            <?php endif; ?>

                            <!-- Số lượng -->
                            <div class="mb-3">
                                <label class="form-label">Số lượng cần <?php echo mb_strtolower($loaiYeuCau, 'UTF-8'); ?></label>
                                <input type="number" name="so_luong" class="form-control"
                                       value="1" min="1" max="<?php echo $sanPham['SoLuongBan']; ?>" required>
                                <div class="form-text text-danger">* Tối đa <?php echo $sanPham['SoLuongBan']; ?> chiếc</div>
                            </div>

                            <!-- Tình trạng -->
                            <div class="mb-3">
                                <label class="form-label">Tình trạng sản phẩm hiện tại</label>
                                <select name="tinh_trang" class="form-select" required>
                                    <option value="">-- Chọn tình trạng --</option>
                                    <option value="Nguyên seal, chưa sử dụng">Nguyên seal, chưa sử dụng</option>
                                    <option value="Đã khui hộp, còn mới">Đã khui hộp, còn đầy đủ phụ kiện</option>
                                    <option value="Sản phẩm bị lỗi kỹ thuật (NSX)">Sản phẩm bị lỗi màn hình, âm thanh (Lỗi NSX)</option>
                                    <option value="Bị trầy xước, móp méo">Bị trầy xước, móp méo bên ngoài</option>
                                    <option value="Khác">Tình trạng khác...</option>
                                </select>
                            </div>

                            <!-- Lý do -->
                            <div class="mb-4">
                                <label class="form-label">Lý do chi tiết</label>
                                <textarea name="ly_do" class="form-control" rows="4"
                                          placeholder="Vui lòng mô tả rõ lý do bạn muốn <?php echo mb_strtolower($loaiYeuCau, 'UTF-8'); ?> sản phẩm này..."
                                          required></textarea>
                            </div>

                            <!-- Nút submit -->
                            <button type="submit" name="gui_yeu_cau"
                                    class="btn btn-submit-<?php echo $action; ?> btn-pill w-100 shadow-sm">
                                <i class="bi bi-send-check-fill me-1"></i>
                                XÁC NHẬN GỬI YÊU CẦU <?php echo mb_strtoupper($loaiYeuCau, 'UTF-8'); ?>
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Hiển thị thông tin SP mới khi khách chọn combobox
const selectSP = document.getElementById('selectSPDoi');
if (selectSP) {
    selectSP.addEventListener('change', function() {
        const opt = this.options[this.selectedIndex];
        const infoBox = document.getElementById('spDoiInfo');
        if (this.value) {
            document.getElementById('spDoiTen').textContent = opt.text.split('—')[0].trim();
            document.getElementById('spDoiGia').textContent = opt.dataset.gia + 'đ';
            document.getElementById('spDoiKho').textContent = opt.dataset.kho;
            infoBox.classList.remove('d-none');
        } else {
            infoBox.classList.add('d-none');
        }
    });
}
</script>
</body>
</html>