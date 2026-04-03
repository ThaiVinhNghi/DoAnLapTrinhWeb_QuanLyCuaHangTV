<?php
session_start();
require_once 'connect.php';
require_once 'nhatky_helper.php'; // Để ghi nhật ký khách hàng

// 1. Kiểm tra đăng nhập
if (!isset($_SESSION['khach_hang_id'])) {
    echo "<script>alert('Vui lòng đăng nhập để thực hiện chức năng này!'); window.location.href='login_khach.php';</script>";
    exit();
}

$khachHangID = (int)$_SESSION['khach_hang_id'];

// 2. Lấy thông tin từ URL
$hd_id = isset($_GET['hd_id']) ? (int)$_GET['hd_id'] : 0;
$sp_id = isset($_GET['sp_id']) ? (int)$_GET['sp_id'] : 0;
$action = isset($_GET['action']) ? $_GET['action'] : ''; // 'doi' hoặc 'tra'

if ($hd_id <= 0 || $sp_id <= 0 || !in_array($action, ['doi', 'tra'])) {
    echo "<script>alert('Dữ liệu không hợp lệ!'); window.location.href='trang_chu.php';</script>";
    exit();
}

$loaiYeuCau = ($action == 'doi') ? 'Đổi hàng' : 'Trả hàng';

// 3. Lấy thông tin chi tiết sản phẩm đã mua từ hóa đơn
$sql_check = "SELECT sp.TenSanPham, sp.HinhAnh, hdct.SoLuongBan, hdct.DonGiaBan, hd.NgayLap 
              FROM hoadon_chitiet hdct
              JOIN hoadon hd ON hdct.HoaDonID = hd.ID
              JOIN sanpham sp ON hdct.SanPhamID = sp.ID
              WHERE hd.ID = ? AND sp.ID = ? AND hd.KhachHangID = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("iii", $hd_id, $sp_id, $khachHangID);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows == 0) {
    echo "<script>alert('Không tìm thấy thông tin sản phẩm trong hóa đơn của bạn!'); window.location.href='trang_chu.php';</script>";
    exit();
}
$sanPham = $result_check->fetch_assoc();

$thongBao = '';

// 4. Xử lý khi khách hàng bấm nút "Gửi yêu cầu"
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['gui_yeu_cau'])) {
    $lyDo = trim($_POST['ly_do'] ?? '');
    $tinhTrang = trim($_POST['tinh_trang'] ?? '');
    $soLuongDoiTra = (int)$_POST['so_luong'];

    if (empty($lyDo) || empty($tinhTrang)) {
        $thongBao = "<div class='alert alert-danger'>Vui lòng nhập đầy đủ lý do và tình trạng sản phẩm!</div>";
    } elseif ($soLuongDoiTra <= 0 || $soLuongDoiTra > $sanPham['SoLuongBan']) {
        $thongBao = "<div class='alert alert-danger'>Số lượng không hợp lệ!</div>";
    } else {
        $conn->begin_transaction();
        try {
            // Tính tiền hoàn (nếu là trả hàng thì tính tiền, đổi hàng thì có thể tính bù trừ sau, ở đây lưu tạm giá trị lúc mua)
            $tongTienHoan = $soLuongDoiTra * $sanPham['DonGiaBan'];
            if ($action == 'doi') $tongTienHoan = 0; // Đổi hàng thường không hoàn tiền mặt ngay

            // Thêm vào bảng doitra
            $sql_insert_dt = "INSERT INTO doitra (HoaDonID, KhachHangID, LoaiYeuCau, LyDo, TongTienHoan) VALUES (?, ?, ?, ?, ?)";
            $stmt_dt = $conn->prepare($sql_insert_dt);
            $stmt_dt->bind_param("iissd", $hd_id, $khachHangID, $loaiYeuCau, $lyDo, $tongTienHoan);
            $stmt_dt->execute();
            
            $doiTraID = $conn->insert_id; // Lấy ID phiếu vừa tạo

            // Thêm vào bảng doitra_chitiet
            $sql_insert_ct = "INSERT INTO doitra_chitiet (DoiTraID, SanPhamID, SoLuong, DonGiaHoan, TinhTrangSanPham) VALUES (?, ?, ?, ?, ?)";
            $stmt_ct = $conn->prepare($sql_insert_ct);
            $stmt_ct->bind_param("iiids", $doiTraID, $sp_id, $soLuongDoiTra, $sanPham['DonGiaBan'], $tinhTrang);
            $stmt_ct->execute();

            // Ghi nhật ký
            ghiNhatKyKhachHangTuSession($conn, 'TaoYeuCauDoiTra', 'doitra', $doiTraID, "Yêu cầu $loaiYeuCau cho hóa đơn #HD$hd_id");

            $conn->commit();
            echo "<script>alert('Gửi yêu cầu thành công! Cửa hàng sẽ liên hệ với bạn sớm nhất.'); window.location.href='trang_chu.php#san-pham-da-mua';</script>";
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $thongBao = "<div class='alert alert-danger'>Có lỗi xảy ra: " . $e->getMessage() . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu <?php echo $loaiYeuCau; ?> - Siêu Thị Tivi N&U</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary sticky-top shadow">
    <div class="container">
        <a class="navbar-brand fw-bold" href="trang_chu.php"><i class="bi bi-tv"></i> N&U</a>
        <a href="trang_chu.php" class="btn btn-outline-light btn-sm ms-auto"><i class="bi bi-arrow-left"></i> Quay lại</a>
    </div>
</nav>

<div class="container mt-5 mb-5" style="max-width: 800px;">
    <div class="card shadow border-0 rounded-4">
        <div class="card-header bg-white border-0 pt-4 pb-0 text-center">
            <h3 class="fw-bold <?php echo ($action == 'doi') ? 'text-warning' : 'text-danger'; ?>">
                <i class="bi bi-arrow-repeat"></i> PHIẾU YÊU CẦU <?php echo mb_strtoupper($loaiYeuCau, 'UTF-8'); ?>
            </h3>
            <p class="text-muted">Vui lòng điền thông tin chính xác để chúng tôi hỗ trợ bạn nhanh nhất</p>
        </div>
        <div class="card-body p-4">
            
            <?php echo $thongBao; ?>

            <div class="d-flex align-items-center bg-light p-3 rounded mb-4 border">
                <img src="<?php echo !empty($sanPham['HinhAnh']) ? 'uploads/'.$sanPham['HinhAnh'] : 'uploads/no-image.jpg'; ?>" width="80" class="rounded me-3 border bg-white" alt="Tivi">
                <div>
                    <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($sanPham['TenSanPham']); ?></h5>
                    <p class="mb-0 text-muted small">
                        Hóa đơn: <span class="fw-bold text-dark">#HD<?php echo $hd_id; ?></span> | 
                        Mua ngày: <span class="fw-bold text-dark"><?php echo date('d/m/Y', strtotime($sanPham['NgayLap'])); ?></span>
                    </p>
                    <p class="mb-0 text-muted small">Số lượng đã mua: <span class="fw-bold text-dark"><?php echo $sanPham['SoLuongBan']; ?></span> chiếc</p>
                </div>
            </div>

            <form action="" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Số lượng cần <?php echo mb_strtolower($loaiYeuCau, 'UTF-8'); ?></label>
                    <input type="number" name="so_luong" class="form-control" value="1" min="1" max="<?php echo $sanPham['SoLuongBan']; ?>" required>
                    <small class="text-danger">* Tối đa <?php echo $sanPham['SoLuongBan']; ?> chiếc</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Tình trạng sản phẩm hiện tại</label>
                    <select name="tinh_trang" class="form-select" required>
                        <option value="">-- Chọn tình trạng --</option>
                        <option value="Nguyên seal, chưa sử dụng">Nguyên seal, chưa sử dụng</option>
                        <option value="Đã khui hộp, còn mới">Đã khui hộp, còn đầy đủ phụ kiện</option>
                        <option value="Sản phẩm bị lỗi kỹ thuật (NSX)">Sản phẩm bị lỗi màn hình, âm thanh (Lỗi NSX)</option>
                        <option value="Bị trầy xước, móp méo">Bị trầy xước, móp méo bên ngoài</option>
                        <option value="Khác">Tình trạng khác...</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Lý do chi tiết</label>
                    <textarea name="ly_do" class="form-control" rows="4" placeholder="Vui lòng mô tả rõ lý do bạn muốn <?php echo mb_strtolower($loaiYeuCau, 'UTF-8'); ?> sản phẩm này..." required></textarea>
                </div>

                <div class="d-grid">
                    <button type="submit" name="gui_yeu_cau" class="btn <?php echo ($action == 'doi') ? 'btn-warning' : 'btn-danger'; ?> btn-lg fw-bold text-white shadow-sm">
                        <i class="bi bi-send-check"></i> XÁC NHẬN GỬI YÊU CẦU
                    </button>
                </div>
            </form>
            
        </div>
    </div>
</div>

</body>
</html>