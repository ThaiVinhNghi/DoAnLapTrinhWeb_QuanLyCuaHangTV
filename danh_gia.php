<?php
session_start();
require_once 'thu_vien/connect.php';

// 1. Kiểm tra đăng nhập
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

// 2. Kiểm tra xem Hóa đơn này có đúng là của khách hàng này không?
// (Đã xóa cột TrangThai trong câu lệnh SQL để tránh lỗi cơ sở dữ liệu)
$sql_check_hd = "SELECT ID FROM hoadon WHERE ID = ? AND KhachHangID = ?";
$stmt_hd = $conn->prepare($sql_check_hd);
$stmt_hd->bind_param("ii", $hd_id, $kh_id);
$stmt_hd->execute();
$rs_hd = $stmt_hd->get_result();

if ($rs_hd->num_rows == 0) {
    echo "<script>alert('Không tìm thấy hóa đơn của bạn!'); window.location.href='san_pham.php#san-pham-da-mua';</script>";
    exit();
}
$stmt_hd->close();



$hoadon = $rs_hd->fetch_assoc();
if ($hoadon['TrangThai'] != 2) { 
    echo "<script>alert('Đơn hàng của bạn đang được xử lý. Bạn chỉ có thể đánh giá sau khi đơn hàng đã được giao thành công!'); window.location.href='san_pham.php#san-pham-da-mua';</script>";
    exit();
}



// 3. Kiểm tra xem Khách hàng ĐÃ ĐÁNH GIÁ đơn hàng này chưa? (Mỗi đơn hàng 1 lần)
$sql_check_dg = "SELECT ID FROM DanhGia WHERE HoaDonID = ? AND SanPhamID = ? AND KhachHangID = ?";
$stmt_dg = $conn->prepare($sql_check_dg);
$stmt_dg->bind_param("iii", $hd_id, $sp_id, $kh_id);
$stmt_dg->execute();
$rs_dg = $stmt_dg->get_result();
$daDanhGia = ($rs_dg->num_rows > 0); // Trả về TRUE nếu đã đánh giá
$stmt_dg->close();

if ($daDanhGia) {
     echo "<script>alert('Bạn đã đánh giá sản phẩm này trong đơn hàng này rồi. Cảm ơn bạn!'); window.location.href='san_pham.php#san-pham-da-mua';</script>";
     exit();
}

// 4. Lấy thông tin sản phẩm để hiển thị
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

// 5. XỬ LÝ LƯU ĐÁNH GIÁ VÀO DATABASE KHI BẤM NÚT GỬI
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
        // Thông báo và tự động chuyển về trang Sản phẩm sau 2 giây
        $thongbao = "
            <div class='alert alert-success rounded-pill text-center shadow-sm mb-4'>
                <i class='bi bi-check-circle-fill'></i> Cảm ơn bạn đã gửi đánh giá! Hệ thống đang chuyển hướng...
            </div>
            <script>
                setTimeout(function(){
                    window.location.href = 'san_pham.php#san-pham-da-mua';
                }, 2000);
            </script>
        ";
        $stmt_insert->close();
        
        // Cập nhật lại biến $daDanhGia để ẩn form
        $daDanhGia = true; 
    } else {
        $thongbao = "<div class='alert alert-danger rounded-pill text-center shadow-sm mb-4'><i class='bi bi-exclamation-triangle-fill'></i> Có lỗi xảy ra, vui lòng thử lại!</div>";
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
        
        /* Hiệu ứng chọn sao đánh giá */
        .star-rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 10px;
        }
        .star-rating input[type="radio"] { display: none; }
        .star-rating label {
            font-size: 2.5rem;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s ease-in-out;
        }
        .star-rating label:hover,
        .star-rating label:hover ~ label,
        .star-rating input[type="radio"]:checked ~ label {
            color: #ffc107; /* Màu vàng */
        }

        .premium-card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            background: #fff;
        }
        
        .btn-pill {
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
    </style>
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
                        
                        <div class="d-flex align-items-center gap-4 mb-4 pb-4 border-bottom">
                            <img src="<?php echo $img_sp; ?>" class="rounded" width="100" style="mix-blend-mode: multiply; object-fit: contain;" alt="Tivi">
                            <div>
                                <span class="badge bg-light text-secondary border mb-2">Hóa đơn #<?php echo $hd_id; ?></span>
                                <h5 class="fw-bold m-0 text-dark"><?php echo htmlspecialchars($sanpham['TenSanPham']); ?></h5>
                            </div>
                        </div>

                        <?php echo $thongbao; ?>

                        <?php if (!$daDanhGia): ?>
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
                        <?php endif; ?>

                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>