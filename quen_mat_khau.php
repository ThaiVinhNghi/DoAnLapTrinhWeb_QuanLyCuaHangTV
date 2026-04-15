<?php
session_start();
require_once 'thu_vien/connect.php';

$thongBao = '';
$loaiThongBao = 'danger';
$thanhCong = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $hovaten = trim($_POST['hovaten'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');

    if ($username === '' || $hovaten === '' || $new_password === '') {
        $thongBao = "Vui lòng nhập đầy đủ thông tin xác minh!";
    } else {
        // Kiểm tra xem Tên đăng nhập và Họ tên có khớp trong hệ thống nhân viên không
        $sql = "SELECT ID FROM nhanvien WHERE TenDangNhap = ? AND HoVaTen = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $hovaten);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $idNhanVien = $row['ID'];

            // Mã hóa mật khẩu mới
            $matKhauHash = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Cập nhật cả 2 cột mật khẩu
            $sql_update = "UPDATE nhanvien SET MatKhau = ?, MatKhauHash = ? WHERE ID = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("ssi", $new_password, $matKhauHash, $idNhanVien);
            
            if ($stmt_update->execute()) {
                $thongBao = "Cấp lại mật khẩu thành công! Vui lòng đăng nhập lại hệ thống.";
                $loaiThongBao = "success";
                $thanhCong = true;
            } else {
                $thongBao = "Có lỗi xảy ra khi cập nhật dữ liệu.";
            }
            $stmt_update->close();
        } else {
            $thongBao = "Tên đăng nhập hoặc Họ và tên không chính xác!";
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
    <title>Khôi phục mật khẩu - Quản Trị Hệ Thống</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="tai_nguyen/css/style.css">
    <style>
        body { background-color: #f4f6f8; }
        .reset-card { width: 100%; max-width: 450px; padding: 40px; background: #fff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
        .form-control { background-color: #f8f9fa; border: 1px solid #eee; padding: 12px; }
        .form-control:focus { background-color: #fff; box-shadow: none; border-color: #1e3c72; }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
    <div class="reset-card">
        <div class="text-center mb-3">
            <i class="bi bi-shield-lock" style="font-size: 3rem; color: #1e3c72;"></i>
        </div>
        <h4 class="text-center mb-1 fw-bold" style="color: #1e3c72;">KHÔI PHỤC TRUY CẬP</h4>
        <p class="text-center text-muted mb-4 small">Hệ thống Quản Trị N&U</p>

        <?php if ($thongBao != ''): ?>
            <div class="alert alert-<?php echo $loaiThongBao; ?> text-center rounded-3 py-2 small fw-bold">
                <?php echo $loaiThongBao == 'danger' ? '<i class="bi bi-exclamation-triangle-fill"></i>' : '<i class="bi bi-check-circle-fill"></i>'; ?> 
                <?php echo $thongBao; ?>
            </div>
        <?php endif; ?>

        <?php if (!$thanhCong): ?>
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label fw-bold text-secondary small mb-1">TÊN ĐĂNG NHẬP</label>
                    <input type="text" class="form-control rounded-3" id="username" name="username" required placeholder="Nhập tài khoản nhân viên...">
                </div>
                
                <div class="mb-3">
                    <label for="hovaten" class="form-label fw-bold text-secondary small mb-1">HỌ VÀ TÊN</label>
                    <input type="text" class="form-control rounded-3" id="hovaten" name="hovaten" required placeholder="Nhập đầy đủ Họ và tên (Có dấu)...">
                </div>

                <div class="mb-4">
                    <label for="new_password" class="form-label fw-bold text-secondary small mb-1">MẬT KHẨU MỚI</label>
                    <input type="password" class="form-control rounded-3" id="new_password" name="new_password" required placeholder="Thiết lập mật khẩu mới...">
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn fw-bold text-white text-uppercase py-2" style="background-color: #1e3c72; border-radius: 50px;">
                        Khôi Phục Mật Khẩu
                    </button>
                </div>
            </form>
        <?php else: ?>
            <div class="d-grid mt-4">
                <a href="login.php" class="btn fw-bold text-white text-uppercase py-2" style="background-color: #1e3c72; border-radius: 50px;">
                    Đăng nhập Hệ Thống
                </a>
            </div>
        <?php endif; ?>
        
        <div class="text-center mt-4 pt-3 border-top">
            <a href="login.php" class="text-secondary fw-bold text-decoration-none small"><i class="bi bi-arrow-left"></i> Quay lại trang Đăng nhập</a>
        </div>
    </div>
</div>

</body>
</html>