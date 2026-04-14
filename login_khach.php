<?php
session_start();
require_once 'thu_vien/connect.php';
require_once 'thu_vien/nhatky_helper.php';
require_once 'thu_vien/rate_limit_helper.php';

if (isset($_SESSION['khach_hang_id'])) {
    header("Location: thanh_toan.php");
    exit();
}

$thongBao = '';
$loaiThongBao = 'danger';

if (isset($_GET['dangky']) && $_GET['dangky'] == 'thanhcong') {
    $thongBao = "Đăng ký thành công! Vui lòng đăng nhập.";
    $loaiThongBao = "success";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tenDangNhap = trim($_POST['TenDangNhap'] ?? '');
    $matKhau = trim($_POST['MatKhau'] ?? '');

    if ($tenDangNhap === '' || $matKhau === '') {
        $thongBao = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!";
        $loaiThongBao = "danger";
    } elseif (isRateLimited($conn, $tenDangNhap)) {
        // ===== KIỂM TRA RATE LIMITING =====
        $limitStatus = getRateLimitStatus($conn, $tenDangNhap);
        $thongBao = "⛔ Tài khoản đã bị khóa tạm thời do quá nhiều lần đăng nhập thất bại.<br>";
        $thongBao .= "Vui lòng thử lại sau " . (LOCKOUT_DURATION / 60) . " phút.";
        $loaiThongBao = "danger";
    } else {
        $sql = "SELECT ID, HoVaTen, TenDangNhap, MatKhau, MatKhauHash
                FROM khachhang
                WHERE TenDangNhap = ?
                LIMIT 1";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $tenDangNhap);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                // === KIỂM TRA PASSWORD ===
                $passwordValid = false;
                
                // Nếu có MatKhauHash thì dùng password_verify
                if (!empty($row['MatKhauHash'])) {
                    $passwordValid = password_verify($matKhau, $row['MatKhauHash']);
                } else if (!empty($row['MatKhau'])) {
                    // Fallback: so sánh plain text (legacy support)
                    $passwordValid = ($matKhau === $row['MatKhau']);
                }
                
                if ($passwordValid) {
                    $_SESSION['khach_hang_id'] = (int)$row['ID'];
                    $_SESSION['khach_hang_ten'] = $row['HoVaTen'];
                    $_SESSION['khach_hang_tendangnhap'] = $row['TenDangNhap'];

                    ghiNhatKy(
                        $conn,
                        'KhachHang',
                        $row['ID'],
                        $row['TenDangNhap'],
                        $row['HoVaTen'],
                        'DangNhap',
                        'khachhang',
                        $row['ID'],
                        'Khách hàng đăng nhập',
                        'ThanhCong'
                    );

                    // ===== GHI LẠI LOGIN ATTEMPT (SUCCESS) =====
                    recordLoginAttempt($conn, $tenDangNhap, 'success');

                    header("Location: thanh_toan.php");
                    exit();
                } else {
                    $thongBao = "Mật khẩu không chính xác!";
                    $loaiThongBao = "danger";
                    
                    // ===== GHI LẠI LOGIN ATTEMPT (FAIL) =====
                    recordLoginAttempt($conn, $tenDangNhap, 'fail');

                    ghiNhatKy(
                        $conn,
                        'KhachHang',
                        null,
                        $tenDangNhap,
                        null,
                        'DangNhap',
                        'khachhang',
                        null,
                        'Đăng nhập khách hàng thất bại: sai mật khẩu',
                        'ThatBai'
                    );
                }
            } else {
                $thongBao = "Tài khoản không tồn tại!";
                $loaiThongBao = "danger";
                
                // ===== GHI LẠI LOGIN ATTEMPT (FAIL) =====
                recordLoginAttempt($conn, $tenDangNhap, 'fail');

                ghiNhatKy(
                    $conn,
                    'KhachHang',
                    null,
                    $tenDangNhap,
                    null,
                    'DangNhap',
                    'khachhang',
                    null,
                    'Đăng nhập khách hàng thất bại: tài khoản không tồn tại',
                    'ThatBai'
                );
            }

            $stmt->close();
        } else {
            $thongBao = "Không thể chuẩn bị câu lệnh đăng nhập!";
            $loaiThongBao = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập Khách Hàng - Tivi N&U</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="tai_nguyen/css/style.css">
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="login-card">
        <div class="brand-icon">
            <i class="bi bi-person-heart"></i>
        </div>
        <h3 class="text-center mb-1 fw-bold text-danger">ĐĂNG NHẬP</h3>
        <p class="text-center text-muted mb-4 small">Mở khóa nhiều ưu đãi từ N&U</p>

        <?php if ($thongBao != ''): ?>
            <div class="alert alert-<?php echo $loaiThongBao; ?> text-center rounded-3 py-2">
                <?php echo $loaiThongBao == 'danger' ? '<i class="bi bi-exclamation-triangle-fill"></i>' : '<i class="bi bi-check-circle-fill"></i>'; ?> 
                <?php echo $thongBao; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="TenDangNhap" class="form-label fw-bold text-secondary small">TÊN ĐĂNG NHẬP</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-3"><i class="bi bi-person-fill"></i></span>
                    <input type="text" class="form-control border-start-0 rounded-end-3" id="TenDangNhap" name="TenDangNhap" required placeholder="Nhập tên đăng nhập...">
                </div>
            </div>
            
            <div class="mb-4">
                <label for="MatKhau" class="form-label fw-bold text-secondary small">MẬT KHẨU</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-3"><i class="bi bi-key-fill"></i></span>
                    <input type="password" class="form-control border-start-0 rounded-end-3" id="MatKhau" name="MatKhau" required placeholder="Nhập mật khẩu...">
                </div>
            </div>

            <div class="d-grid gap-3">
                <button type="submit" class="btn btn-login text-uppercase">
                    Đăng Nhập <i class="bi bi-box-arrow-in-right ms-1"></i>
                </button>
                <a href="trang_chu.php" class="btn btn-back text-center text-decoration-none">
                    <i class="bi bi-house-door-fill me-1"></i> Trở về Trang chủ
                </a>
            </div>
            
            <div class="text-center mt-4">
                <span class="text-muted small">Chưa có tài khoản?</span> 
                <a href="dang_ky.php" class="text-danger fw-bold text-decoration-none">Đăng ký ngay</a>
                <br>
                <a href="doi_mat_khau.php" class="text-secondary fw-bold text-decoration-none mt-2 d-inline-block"><i class="bi bi-key"></i> Đổi mật khẩu</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>