<?php
session_start();
require_once 'thu_vien/connect.php';

$thongBao = '';
$loaiThongBao = '';

// Lấy sẵn tên đăng nhập nếu đang đăng nhập
$tenDangNhapSan = isset($_SESSION['khach_hang_tendangnhap']) ? $_SESSION['khach_hang_tendangnhap'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tenDangNhap = trim($_POST['TenDangNhap']);
    $matKhauCu = trim($_POST['MatKhauCu']);
    $matKhauMoi = trim($_POST['MatKhauMoi']);
    $nhapLaiMatKhauMoi = trim($_POST['NhapLaiMatKhauMoi']);

    // Cập nhật lại biến hiển thị
    $tenDangNhapSan = $tenDangNhap;

    // Kiểm tra tính hợp lệ của dữ liệu đầu vào
    if (empty($tenDangNhap) || empty($matKhauCu) || empty($matKhauMoi) || empty($nhapLaiMatKhauMoi)) {
        $thongBao = "Vui lòng nhập đầy đủ thông tin!";
        $loaiThongBao = "danger";
    } elseif ($matKhauMoi !== $nhapLaiMatKhauMoi) {
        $thongBao = "Mật khẩu mới nhập lại không khớp!";
        $loaiThongBao = "danger";
    } elseif (strlen($matKhauMoi) < 6) {
        $thongBao = "Mật khẩu mới phải có ít nhất 6 ký tự!";
        $loaiThongBao = "danger";
    } else {
        // Lấy thông tin mật khẩu hiện tại trong database
        $sql_check = "SELECT ID, MatKhau, MatKhauHash FROM khachhang WHERE TenDangNhap = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $tenDangNhap);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($row = $res_check->fetch_assoc()) {
            $khachHangID = $row['ID'];
            $passwordValid = false;
            
            // Xác thực mật khẩu cũ (hỗ trợ cả hash và plain text cũ)
            if (!empty($row['MatKhauHash'])) {
                $passwordValid = password_verify($matKhauCu, $row['MatKhauHash']);
            } else if (!empty($row['MatKhau'])) {
                $passwordValid = ($matKhauCu === $row['MatKhau']);
            }

            if (!$passwordValid) {
                // Nếu không đúng mật khẩu cũ
                $thongBao = "Mật khẩu cũ không chính xác!";
                $loaiThongBao = "danger";
            } else {
                // Mã hóa mật khẩu mới trước khi lưu
                $matKhauMoiHash = password_hash($matKhauMoi, PASSWORD_BCRYPT);
                
                // Cập nhật mật khẩu mới vào cơ sở dữ liệu
                $sql_update = "UPDATE khachhang SET MatKhau = ?, MatKhauHash = ? WHERE ID = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("ssi", $matKhauMoi, $matKhauMoiHash, $khachHangID);

                if ($stmt_update->execute()) {
                    $thongBao = "Đổi mật khẩu thành công! Vui lòng đăng nhập lại.";
                    $loaiThongBao = "success";
                } else {
                    $thongBao = "Có lỗi xảy ra khi cập nhật mật khẩu!";
                    $loaiThongBao = "danger";
                }
                $stmt_update->close();
            }
        } else {
            $thongBao = "Tài khoản không tồn tại!";
            $loaiThongBao = "danger";
        }
        $stmt_check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đổi mật khẩu - N&U Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="tai_nguyen/css/style.css">
</head>
<body>

<div class="container d-flex justify-content-center mt-5">
    <div class="login-card w-100" style="max-width: 450px;">
        <div class="brand-icon">
            <i class="bi bi-key-fill"></i>
        </div>
        <h3 class="text-center mb-1 fw-bold text-danger">ĐỔI MẬT KHẨU</h3>
        <p class="text-center text-muted mb-4 small">Bảo mật tài khoản của bạn</p>

        <?php if ($thongBao != ''): ?>
            <div class="alert alert-<?php echo $loaiThongBao; ?> text-center rounded-3 py-2">
                <i class="bi <?php echo ($loaiThongBao == 'danger') ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill'; ?>"></i> 
                <?php echo $thongBao; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="TenDangNhap" class="form-label fw-bold text-secondary small">TÊN ĐĂNG NHẬP</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-3"><i class="bi bi-person-fill"></i></span>
                    <input type="text" class="form-control border-start-0 rounded-end-3" id="TenDangNhap" name="TenDangNhap" value="<?php echo htmlspecialchars($tenDangNhapSan); ?>" required placeholder="Nhập tên đăng nhập...">
                </div>
            </div>

            <div class="mb-3">
                <label for="MatKhauCu" class="form-label fw-bold text-secondary small">MẬT KHẨU CŨ</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-3"><i class="bi bi-unlock-fill"></i></span>
                    <input type="password" class="form-control border-start-0 rounded-end-3" id="MatKhauCu" name="MatKhauCu" required placeholder="Nhập mật khẩu hiện tại...">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="MatKhauMoi" class="form-label fw-bold text-secondary small">MẬT KHẨU MỚI</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-3"><i class="bi bi-key-fill"></i></span>
                    <input type="password" class="form-control border-start-0 rounded-end-3" id="MatKhauMoi" name="MatKhauMoi" required placeholder="Nhập mật khẩu mới...">
                </div>
            </div>

            <div class="mb-4">
                <label for="NhapLaiMatKhauMoi" class="form-label fw-bold text-secondary small">XÁC NHẬN MẬT KHẨU MỚI</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-3"><i class="bi bi-check-circle-fill"></i></span>
                    <input type="password" class="form-control border-start-0 rounded-end-3" id="NhapLaiMatKhauMoi" name="NhapLaiMatKhauMoi" required placeholder="Nhập lại mật khẩu mới...">
                </div>
            </div>

            <div class="d-grid gap-3">
                <button type="submit" class="btn btn-login text-uppercase">
                    Xác nhận đổi <i class="bi bi-arrow-right-circle-fill ms-1"></i>
                </button>
                <a href="trang_chu.php" class="btn btn-back text-center text-decoration-none">
                    <i class="bi bi-house-door-fill me-1"></i> Trở về Trang chủ
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
