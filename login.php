<?php
session_start();
require_once 'thu_vien/connect.php';
require_once 'thu_vien/nhatky_helper.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!";
    } else {
        // ĐÃ SỬA: Query lấy MatKhauHash + MatKhau (legacy)
        $sql = "SELECT ID, HoVaTen, TenDangNhap, QuyenHan, NgayVaoLam, MatKhau, MatKhauHash
                FROM nhanvien
                WHERE TenDangNhap = ?
                LIMIT 1";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();

                // === KIỂM TRA PASSWORD ===
                $passwordValid = false;
                
                // Nếu có MatKhauHash thì dùng password_verify
                if (!empty($row['MatKhauHash'])) {
                    $passwordValid = password_verify($password, $row['MatKhauHash']);
                } else if (!empty($row['MatKhau'])) {
                    // Fallback: so sánh plain text (legacy support)
                    $passwordValid = ($password === $row['MatKhau']);
                }
                
                if (!$passwordValid) {
                    $error = "Tên đăng nhập hoặc mật khẩu không đúng!";

                    ghiNhatKy(
                        $conn,
                        'NhanVien',
                        null,
                        $username,
                        null,
                        'DangNhap',
                        'nhanvien',
                        null,
                        'Đăng nhập thất bại',
                        'ThatBai'
                    );
                } else {
                    // ===== PASSWORD ĐÚNG, LƯU SESSION =====

                    // Lưu các session chung cho mọi nhân viên
                $_SESSION['nhanvien_id'] = (int)$row['ID'];
                $_SESSION['nhan_vien_id'] = (int)$row['ID']; 
                $_SESSION['nhanvien_hoten'] = $row['HoVaTen'];
                $_SESSION['ho_ten'] = $row['HoVaTen'];
                $_SESSION['nhanvien_tendangnhap'] = $row['TenDangNhap'];
                $_SESSION['ten_dang_nhap'] = $row['TenDangNhap'];
                $_SESSION['quyen_han'] = (int)$row['QuyenHan'];

                // === TÍNH THÂM NIÊN LÀM VIỆC ===
                // Nếu DB không có ngày, mặc định coi như mới vào làm hôm nay
                $ngayVaoLam = !empty($row['NgayVaoLam']) ? new DateTime($row['NgayVaoLam']) : new DateTime();
                $ngayHienTai = new DateTime();
                $khoangCach = $ngayHienTai->diff($ngayVaoLam);
                
                // Lưu session xem nhân viên này đã làm >= 1 năm hay chưa
                $_SESSION['tham_nien_1_nam'] = ($khoangCach->y >= 1) ? true : false;
                // ================================

                // NẾU LÀ ADMIN, PHẢI LƯU THÊM SESSION DÀNH RIÊNG CHO ADMIN
                if ((int)$row['QuyenHan'] === 1) {
                    $_SESSION['admin_id'] = (int)$row['ID'];
                    $_SESSION['admin_hoten'] = $row['HoVaTen'];
                    $_SESSION['admin_tendangnhap'] = $row['TenDangNhap'];
                } else {
                    unset($_SESSION['admin_id']);
                    unset($_SESSION['admin_hoten']);
                    unset($_SESSION['admin_tendangnhap']);
                }

                ghiNhatKy(
                    $conn,
                    ((int)$row['QuyenHan'] === 1) ? 'Admin' : 'NhanVien',
                    $row['ID'],
                    $row['TenDangNhap'],
                    $row['HoVaTen'],
                    'DangNhap',
                    'nhanvien',
                    $row['ID'],
                    'Đăng nhập hệ thống quản trị',
                    'ThanhCong'
                );

                header("Location: admin/index.php");
                exit();
                } // Close: if (!$passwordValid) else
            } else {
                $error = "Tên đăng nhập hoặc mật khẩu không đúng!";

                ghiNhatKy(
                    $conn,
                    'NhanVien',
                    null,
                    $username,
                    null,
                    'DangNhap',
                    'nhanvien',
                    null,
                    'Đăng nhập thất bại',
                    'ThatBai'
                );
            }

            $stmt->close();
        } else {
            $error = "Không thể chuẩn bị câu lệnh đăng nhập!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập hệ thống - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="tai_nguyen/css/style.css">
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="login-card">
        <div class="brand-icon">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <h3 class="text-center mb-1 fw-bold" style="color: #1e3c72;">QUẢN TRỊ HỆ THỐNG</h3>
        <p class="text-center text-muted mb-4 small">Cửa Hàng Tivi N&U</p>

        <?php if ($error != ''): ?>
            <div class="alert alert-danger text-center rounded-3 py-2"><i class="bi bi-exclamation-triangle-fill"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label fw-bold text-secondary small">TÊN ĐĂNG NHẬP</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-3"><i class="bi bi-person-fill"></i></span>
                    <input type="text" class="form-control border-start-0 rounded-end-3" id="username" name="username" required placeholder="Nhập tài khoản...">
                </div>
            </div>
            
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <label for="password" class="form-label fw-bold text-secondary small mb-0">MẬT KHẨU</label>
                    <a href="quen_mat_khau.php" class="text-decoration-none small text-primary fw-bold">Quên mật khẩu?</a>
                </div>
                <div class="input-group mt-2">
                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-3"><i class="bi bi-key-fill"></i></span>
                    <input type="password" class="form-control border-start-0 rounded-end-3" id="password" name="password" required placeholder="Nhập mật khẩu...">
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
        </form>
    </div>
</div>

</body>
</html>