<?php
session_start();
require_once 'connect.php';
require_once 'nhatky_helper.php';

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
    } else {
        $sql = "SELECT ID, HoVaTen, TenDangNhap, MatKhau
                FROM khachhang
                WHERE TenDangNhap = ?
                LIMIT 1";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("s", $tenDangNhap);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                if ($matKhau === $row['MatKhau']) {
                    $_SESSION['khach_hang_id'] = (int)$row['ID'];
                    $_SESSION['khachhang_id'] = (int)$row['ID']; // alias
                    $_SESSION['khach_hang_ten'] = $row['HoVaTen'];
                    $_SESSION['khachhang_hoten'] = $row['HoVaTen']; // alias
                    $_SESSION['khach_hang_tendangnhap'] = $row['TenDangNhap'];
                    $_SESSION['khachhang_tendangnhap'] = $row['TenDangNhap']; // alias

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

                    header("Location: thanh_toan.php");
                    exit();
                } else {
                    $thongBao = "Mật khẩu không chính xác!";
                    $loaiThongBao = "danger";

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
    <style>
        body { 
            background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            animation: fadeIn 0.6s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .brand-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 20px;
            box-shadow: 0 5px 15px rgba(255, 75, 43, 0.3);
        }
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 1px solid #ced4da;
        }
        .form-control:focus {
            border-color: #ff4b2b;
            box-shadow: 0 0 0 0.2rem rgba(255, 75, 43, 0.25);
        }
        .btn-login {
            border-radius: 10px;
            padding: 12px;
            font-weight: bold;
            background: linear-gradient(135deg, #ff416c, #ff4b2b);
            border: none;
            color: white;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 75, 43, 0.4);
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
    </style>
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
            </div>
        </form>
    </div>
</div>

</body>
</html>