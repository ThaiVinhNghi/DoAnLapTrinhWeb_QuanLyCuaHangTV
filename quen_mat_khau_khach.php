<?php
session_start();
require_once 'thu_vien/connect.php';

$thongBao = '';
$loaiThongBao = 'danger';
$thanhCong = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tenDangNhap = trim($_POST['TenDangNhap'] ?? '');
    $dienThoai = trim($_POST['DienThoai'] ?? '');
    $matKhauMoi = trim($_POST['MatKhauMoi'] ?? '');

    if ($tenDangNhap === '' || $dienThoai === '' || $matKhauMoi === '') {
        $thongBao = "Vui lòng nhập đầy đủ thông tin!";
    } else {
        // Kiểm tra xem Tên đăng nhập và SĐT có khớp không
        $sql = "SELECT ID FROM khachhang WHERE TenDangNhap = ? AND DienThoai = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $tenDangNhap, $dienThoai);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $idKhach = $row['ID'];

            // Mã hóa mật khẩu mới
            $matKhauHash = password_hash($matKhauMoi, PASSWORD_DEFAULT);
            
            // Cập nhật cả 2 cột (MatKhau trần để dự phòng hệ thống cũ, và MatKhauHash bảo mật)
            $sql_update = "UPDATE khachhang SET MatKhau = ?, MatKhauHash = ? WHERE ID = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssi", $matKhauMoi, $matKhauHash, $idKhach);
            
            if ($stmt_update->execute()) {
                $thongBao = "Khôi phục mật khẩu thành công! Vui lòng đăng nhập lại.";
                $loaiThongBao = "success";
                $thanhCong = true;
            } else {
                $thongBao = "Có lỗi xảy ra khi cập nhật mật khẩu.";
            }
            $stmt_update->close();
        } else {
            $thongBao = "Tên đăng nhập hoặc Số điện thoại không khớp với hệ thống!";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên mật khẩu Khách Hàng - N&U Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="tai_nguyen/css/style.css">
</head>
<body>

<div class="container d-flex justify-content-center mt-5 pt-5">
    <div class="login-card" style="width: 100%; max-width: 420px; padding: 40px; background: #fff; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1);">
        <div class="text-center mb-4">
            <i class="bi bi-key text-danger" style="font-size: 3rem;"></i>
        </div>
        <h3 class="text-center mb-1 fw-bold text-danger">QUÊN MẬT KHẨU</h3>
        <p class="text-center text-muted mb-4 small">Xác minh thông tin để đặt lại mật khẩu</p>

        <?php if ($thongBao != ''): ?>
            <div class="alert alert-<?php echo $loaiThongBao; ?> text-center rounded-3 py-2">
                <?php echo $loaiThongBao == 'danger' ? '<i class="bi bi-exclamation-triangle-fill"></i>' : '<i class="bi bi-check-circle-fill"></i>'; ?> 
                <?php echo $thongBao; ?>
            </div>
        <?php endif; ?>

        <?php if (!$thanhCong): ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="TenDangNhap" class="form-label fw-bold text-secondary small">TÊN ĐĂNG NHẬP</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted"><i class="bi bi-person-fill"></i></span>
                        <input type="text" class="form-control border-start-0" id="TenDangNhap" name="TenDangNhap" required placeholder="Tài khoản của bạn">
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="DienThoai" class="form-label fw-bold text-secondary small">SỐ ĐIỆN THOẠI ĐĂNG KÝ</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted"><i class="bi bi-telephone-fill"></i></span>
                        <input type="text" class="form-control border-start-0" id="DienThoai" name="DienThoai" required placeholder="Nhập số điện thoại">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="MatKhauMoi" class="form-label fw-bold text-secondary small">MẬT KHẨU MỚI</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted"><i class="bi bi-shield-lock-fill"></i></span>
                        <input type="password" class="form-control border-start-0" id="MatKhauMoi" name="MatKhauMoi" required placeholder="Nhập mật khẩu mới">
                    </div>
                </div>

                <div class="d-grid gap-3">
                    <button type="submit" class="btn btn-danger py-2 fw-bold text-uppercase" style="border-radius: 50px;">
                        Đặt Lại Mật Khẩu <i class="bi bi-arrow-right-circle ms-1"></i>
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="d-grid mt-4">
                <a href="login_khach.php" class="btn btn-danger py-2 fw-bold text-uppercase" style="border-radius: 50px;">
                    Chuyển đến Đăng Nhập
                </a>
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-4 border-top pt-3">
            <a href="login_khach.php" class="text-secondary fw-bold text-decoration-none small"><i class="bi bi-arrow-left"></i> Quay lại Đăng nhập</a>
        </div>
    </div>
</div>

</body>
</html>