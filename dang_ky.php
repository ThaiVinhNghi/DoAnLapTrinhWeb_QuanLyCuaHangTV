<?php
session_start();
require_once 'connect.php';

$thongBao = '';
$loaiThongBao = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $hoVaTen = trim($_POST['HoVaTen']);
    $dienThoai = trim($_POST['DienThoai']);
    $diaChi = trim($_POST['DiaChi']);
    $tenDangNhap = trim($_POST['TenDangNhap']);
    $matKhau = trim($_POST['MatKhau']);
    $nhapLaiMatKhau = trim($_POST['NhapLaiMatKhau']);

    if ($matKhau !== $nhapLaiMatKhau) {
        $thongBao = "Mật khẩu nhập lại không khớp!";
        $loaiThongBao = "danger";
    } elseif (empty($hoVaTen) || empty($dienThoai) || empty($diaChi) || empty($tenDangNhap) || empty($matKhau)) {
        $thongBao = "Vui lòng nhập đầy đủ thông tin!";
        $loaiThongBao = "danger";
    } else {
        $sql_check = "SELECT ID FROM khachhang WHERE TenDangNhap = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $tenDangNhap);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check->num_rows > 0) {
            $thongBao = "Tên đăng nhập này đã có người sử dụng. Vui lòng chọn tên khác!";
            $loaiThongBao = "danger";
        } else {
            $sql_insert = "INSERT INTO khachhang (HoVaTen, DienThoai, DiaChi, TenDangNhap, MatKhau)
                           VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sssss", $hoVaTen, $dienThoai, $diaChi, $tenDangNhap, $matKhau);

            if ($stmt_insert->execute()) {
                header("Location: login_khach.php?dangky=thanhcong");
                exit();
            } else {
                $thongBao = "Có lỗi xảy ra: " . $conn->error;
                $loaiThongBao = "danger";
            }

            $stmt_insert->close();
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
    <title>Đăng ký khách hàng - TIVI STORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { 
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem 1rem; /* Thêm padding để tránh bị cắt nội dung trên màn hình nhỏ */
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            padding: 40px;
            width: 100%;
            max-width: 500px; /* Nới rộng một chút cho form đăng ký */
            animation: fadeIn 0.6s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .brand-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
            box-shadow: 0 5px 15px rgba(30,60,114,0.3);
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #ced4da;
        }
        .form-control:focus {
            border-color: #2a5298;
            box-shadow: 0 0 0 0.2rem rgba(42, 82, 152, 0.25);
        }
        .btn-login {
            border-radius: 10px;
            padding: 12px;
            font-weight: bold;
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            border: none;
            color: white;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30,60,114,0.4);
            color: white;
        }
        .btn-back {
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            border: 2px solid #dee2e6;
            color: #6c757d;
            transition: all 0.2s;
        }
        .btn-back:hover {
            background-color: #f8f9fa;
            border-color: #c1c9d0;
            color: #495057;
        }
        /* Custom scrollbar nếu nội dung quá dài trên mobile */
        .login-card {
            max-height: 95vh;
            overflow-y: auto;
        }
        .login-card::-webkit-scrollbar {
            width: 6px;
        }
        .login-card::-webkit-scrollbar-thumb {
            background-color: #ced4da;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="container d-flex justify-content-center">
    <div class="login-card">
        <div class="brand-icon">
            <i class="bi bi-person-plus-fill"></i> </div>
        <h3 class="text-center mb-1 fw-bold" style="color: #1e3c72;">ĐĂNG KÝ KHÁCH HÀNG</h3>
        <p class="text-center text-muted mb-4 small">Cửa Hàng Tivi N&U</p>

        <?php if ($thongBao != ''): ?>
            <div class="alert alert-<?php echo $loaiThongBao; ?> text-center rounded-3 py-2">
                <i class="bi <?php echo ($loaiThongBao == 'danger') ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill'; ?>"></i> 
                <?php echo $thongBao; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            
            <div class="mb-3">
                <label for="HoVaTen" class="form-label fw-bold text-secondary small">HỌ VÀ TÊN</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-3"><i class="bi bi-person-vcard-fill"></i></span>
                    <input type="text" class="form-control border-start-0 rounded-end-3" id="HoVaTen" name="HoVaTen" required placeholder="Nhập họ và tên...">
                </div>
            </div>

            <div class="mb-3">
                <label for="DienThoai" class="form-label fw-bold text-secondary small">ĐIỆN THOẠI</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-3"><i class="bi bi-telephone-fill"></i></span>
                    <input type="text" class="form-control border-start-0 rounded-end-3" id="DienThoai" name="DienThoai" required placeholder="Nhập số điện thoại...">
                </div>
            </div>

            <div class="mb-3">
                <label for="DiaChi" class="form-label fw-bold text-secondary small">ĐỊA CHỈ</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-3"><i class="bi bi-geo-alt-fill"></i></span>
                    <input type="text" class="form-control border-start-0 rounded-end-3" id="DiaChi" name="DiaChi" required placeholder="Nhập địa chỉ...">
                </div>
            </div>

            <hr class="text-muted my-4">

            <div class="mb-3">
                <label for="TenDangNhap" class="form-label fw-bold text-secondary small">TÊN ĐĂNG NHẬP</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-3"><i class="bi bi-person-fill"></i></span>
                    <input type="text" class="form-control border-start-0 rounded-end-3" id="TenDangNhap" name="TenDangNhap" required placeholder="Nhập tài khoản...">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="MatKhau" class="form-label fw-bold text-secondary small">MẬT KHẨU</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-3"><i class="bi bi-key-fill"></i></span>
                    <input type="password" class="form-control border-start-0 rounded-end-3" id="MatKhau" name="MatKhau" required placeholder="Nhập mật khẩu...">
                </div>
            </div>

            <div class="mb-4">
                <label for="NhapLaiMatKhau" class="form-label fw-bold text-secondary small">NHẬP LẠI MẬT KHẨU</label>
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted rounded-start-3"><i class="bi bi-check-circle-fill"></i></span>
                    <input type="password" class="form-control border-start-0 rounded-end-3" id="NhapLaiMatKhau" name="NhapLaiMatKhau" required placeholder="Xác nhận lại mật khẩu...">
                </div>
            </div>

            <div class="d-grid gap-3">
                <button type="submit" class="btn btn-login text-uppercase">
                    Đăng Ký <i class="bi bi-person-plus-fill ms-1"></i>
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