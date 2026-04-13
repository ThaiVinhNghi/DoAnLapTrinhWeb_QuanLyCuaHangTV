<?php
session_start();
require_once 'thu_vien/connect.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['khach_hang_id'])) {
    echo "<script>alert('Vui lòng đăng nhập để đánh giá sản phẩm!'); window.location.href='login_khach.php';</script>";
    exit();
}

$kh_id = (int)$_SESSION['khach_hang_id'];
$sp_id = isset($_GET['sp_id']) ? (int)$_GET['sp_id'] : 0;
$hd_id = isset($_GET['hd_id']) ? (int)$_GET['hd_id'] : 0;

if ($sp_id <= 0 || $hd_id <= 0) {
    echo "<script>alert('Thông tin không hợp lệ!'); window.location.href='san_pham.php#san-pham-da-mua';</script>";
    exit();
}

// Lấy thông tin sản phẩm để hiển thị
$sql_sp = "SELECT TenSanPham, HinhAnh FROM SanPham WHERE ID = ?";
$stmt_sp = $conn->prepare($sql_sp);
$stmt_sp->bind_param("i", $sp_id);
$stmt_sp->execute();
$rs_sp = $stmt_sp->get_result();
if (!$rs_sp || $rs_sp->num_rows == 0) {
    echo "<script>alert('Sản phẩm không tồn tại!'); window.location.href='san_pham.php';</script>";
    exit();
}
$sanpham = $rs_sp->fetch_assoc();
$stmt_sp->close();
$img_sp = !empty($sanpham['HinhAnh']) ? "uploads/" . $sanpham['HinhAnh'] : "uploads/no-image.jpg";

// XỬ LÝ LƯU ĐÁNH GIÁ VÀO DATABASE KHI BẤM NÚT GỬI
$thongbao = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btnGuiDanhGia'])) {
    $diem = (int)$_POST['rating'];
    $noidung = trim($_POST['noidung']);
    $ngaydanhgia = date('Y-m-d H:i:s');

    // Sử dụng prepared statement để tránh SQL Injection
    $sql_insert = "INSERT INTO DanhGia (SanPhamID, KhachHangID, HoaDonID, DiemDanhGia, NoiDung, NgayDanhGia) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iiiiss", $sp_id, $kh_id, $hd_id, $diem, $noidung, $ngaydanhgia);
    
    if ($stmt_insert->execute()) {
        $thongbao = "<div class='alert alert-success rounded-pill text-center shadow-sm'><i class='bi bi-check-circle-fill'></i> Cảm ơn bạn đã gửi đánh giá!</div>";
        $stmt_insert->close();
    } else {
        $thongbao = "<div class='alert alert-danger rounded-pill text-center shadow-sm'><i class='bi bi-exclamation-triangle-fill'></i> Có lỗi xảy ra, vui lòng thử lại!</div>";
        $stmt_insert->close();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đánh Giá Sản Phẩm - N&U Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="tai_nguyen/css/style.css">
</head>
<body>

    <nav class="navbar navbar-dark navbar-premium sticky-top">
        <div class="container justify-content-center">
            <a class="navbar-brand fw-bold fs-4 text-white m-0" href="trang_chu.php"><i class="bi bi-tv text-danger"></i> N&U Store</a>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                
                <a href="san_pham.php#san-pham-da-mua" class="text-decoration-none text-secondary d-inline-block mb-4 fw-bold">
                    <i class="bi bi-arrow-left"></i> Quay lại Đơn hàng
                </a>

                <div class="premium-card overflow-hidden">
                    <div class="bg-black text-white text-center py-4">
                        <h4 class="fw-bold m-0 letter-spacing-1">ĐÁNH GIÁ SẢN PHẨM</h4>
                    </div>
                    
                    <div class="card-body p-4 p-md-5">
                        <?php echo $thongbao; ?>
                        
                        <div class="d-flex align-items-center gap-4 mb-4 pb-4 border-bottom">
                            <img src="<?php echo $img_sp; ?>" class="rounded" width="100" style="mix-blend-mode: multiply; object-fit: contain;" alt="Tivi">
                            <div>
                                <span class="badge bg-light text-secondary border mb-2">Hóa đơn #<?php echo $hd_id; ?></span>
                                <h5 class="fw-bold m-0 text-dark"><?php echo htmlspecialchars($sanpham['TenSanPham']); ?></h5>
                            </div>
                        </div>

                        <form action="" method="POST">
                            <div class="mb-4 text-center">
                                <h6 class="fw-bold mb-3">Bạn cảm thấy sản phẩm thế nào?</h6>
                                <div class="star-rating">
                                    <input type="radio" id="star5" name="rating" value="5" required>
                                    <label for="star5" title="5 sao"><i class="bi bi-star-fill"></i></label>
                                    
                                    <input type="radio" id="star4" name="rating" value="4">
                                    <label for="star4" title="4 sao"><i class="bi bi-star-fill"></i></label>
                                    
                                    <input type="radio" id="star3" name="rating" value="3">
                                    <label for="star3" title="3 sao"><i class="bi bi-star-fill"></i></label>
                                    
                                    <input type="radio" id="star2" name="rating" value="2">
                                    <label for="star2" title="2 sao"><i class="bi bi-star-fill"></i></label>
                                    
                                    <input type="radio" id="star1" name="rating" value="1">
                                    <label for="star1" title="1 sao"><i class="bi bi-star-fill"></i></label>
                                </div>
                            </div>

                            <div class="form-floating mb-4">
                                <textarea class="form-control bg-light border-0" id="noidung" name="noidung" style="height: 120px; border-radius: 12px;" placeholder="Nhập nhận xét" required></textarea>
                                <label for="noidung" class="text-muted">Nhập nhận xét của bạn về chất lượng Tivi...</label>
                            </div>

                            <button type="submit" name="btnGuiDanhGia" class="btn btn-dark w-100 btn-pill shadow-sm">
                                <i class="bi bi-send-fill"></i> Gửi Đánh Giá
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>