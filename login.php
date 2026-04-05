<?php
session_start();
require_once 'connect.php';
require_once 'nhatky_helper.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = "Vui lòng nhập đầy đủ tên đăng nhập và mật khẩu!";
    } else {
        // ĐÃ SỬA: Thêm cột NgayVaoLam vào câu lệnh SQL
        $sql = "SELECT ID, HoVaTen, TenDangNhap, QuyenHan, NgayVaoLam
                FROM nhanvien
                WHERE TenDangNhap = ? AND MatKhau = ?
                LIMIT 1";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("ss", $username, $password);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();

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
    <style>
        body { 
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
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
    </style>
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
                <label for="password" class="form-label fw-bold text-secondary small">MẬT KHẨU</label>
                <div class="input-group">
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